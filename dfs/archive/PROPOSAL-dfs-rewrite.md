# Datafeed Studio — Rewrite Proposal (Headless "DFS-NG")

> Companion to `ARCHITECTURE.md` (the audit of the current system). This is the forward plan:
> a modernized, **headless** rewrite of DFS as a feed-ingestion + price-API service on the existing
> Hetzner box. Decisions already agreed are marked **[DECIDED]**.
>
> _Draft v1 — 2026-06-21._

---

## 1. Scope & goals

**What DFS-NG is:** ingest merchant/affiliate feeds → normalize, classify (gender/segment), filter →
store offers → expose a clean, cached **JSON price API** to WordPress (RSG). Nothing else.

**Goals (from your drivers):**
1. **Lower running cost / RAM** — run comfortably alongside n8n on the existing 8 GB Hetzner box.
2. **Maintainability** — typed framework, ORM, migrations, tests; no ionCube, no stored-SQL execution.
3. **New capability** — per-feed **mens/womens segmentation + kids exclusion** at ingest.
4. **Unblock RSG** — multi-country price payload in one call (MSRP owned by WordPress).
5. **Security** — eliminate the SQL-injection class, exposed secrets, and phone-home licensing.

**Non-goals:** public **page** rendering, themes, priceboxes UI, pcpages, content/blog, on-site
reviews, DFS export *sites*, the WordPress-plugin licensing — **all dropped** (audit shows 99.5% of
traffic is the API; rendered pages are bots; export sites = 0). **Carve-outs that must survive**
(confirmed by the WP audit, §13): the **click-redirect endpoint** (`<base>/index.php?id=…` that `/go/`
forwards to, with click logging) and **ingestion of RSG's `export.cvs`/`export_brands.cvs`** handshake.

**[DECIDED]** Stack: modernized-LAMP — Linux + **Nginx/PHP-FPM 8.3 + Laravel**, **MariaDB/InnoDB**,
Redis; Docker on the existing **RSGMedia Hetzner** box. Headless.

---

## 2. Target architecture

```
                       ┌─────────────── existing Hetzner box (8 vCPU / 8 GB) ───────────────┐
Cloudflare ── WP(RSG) ─┼─► Nginx Proxy Manager ─► nginx + php-fpm (DFS-NG API, Laravel) ─┐   │
                       │                                   │                              │   │
                       │        Redis (cache/queue) ◄──────┤                              │   │
                       │        Horizon worker  ───────────┘  reads/writes               ▼   │
                       │             │  (scheduled imports)                         MariaDB    │
                       │             └─► download feeds (curl/SFTP) ─► parse ─► classify ─► upsert offers
                       │        n8n (orchestration / alerting, optional) ────────────────────► │
                       └────────────────────────────────────────────────────────────────────┘
```

- **API container** (nginx + php-fpm): stateless, serves the JSON API, reads MariaDB + Redis cache.
- **Worker container** (Horizon): runs scheduled feed imports as queued jobs; memory-capped.
- **MariaDB** (InnoDB, utf8mb4): offers/products/feeds/clients. **Redis**: response cache + queue.
- Routed through the existing **Nginx Proxy Manager** (TLS, e.g. `dfs.rsgmedia.info`).
- **n8n** (already present) optionally orchestrates imports and posts failure alerts to Slack/email.

---

## 3. Data model (MariaDB / InnoDB / utf8mb4)

```
merchants        id, name, network(AWIN|CJ|AMAZON|LS|DIRECT…), logo_url, default_country, active
feeds            id, merchant_id, country_code, source_url, credentials(enc),
                 parser_config(json), segment_rules(json), schedule, status,
                 last_import_at, last_ok_at, total_offers
products         id, brand, model, gender(enum: mens|womens|unisex|kids),
                 canonical_key, name, slug, created_at, updated_at          ← optional canonical layer
offers           id, feed_id, merchant_id, country_code, source_key,
                 product_id(nullable FK), name, model_number,
                 price DECIMAL(11,2), currency, msrp DECIMAL(11,2) NULL,
                 deep_link, original_deep_link, image_url, in_stock,
                 gender, category, subcategory, attrs(json),
                 last_seen_at, created_at, updated_at
                 UNIQUE(feed_id, source_key)        -- idempotent upsert key
                 INDEX(country_code, gender), FULLTEXT(name)
api_clients      id, name, api_key_hash, allowed_ips(json), active, created_at   ← replaces sites.client_ip
click_hits       id, offer_id|mykey, clickref, country_code, created_at          ← or stream to analytics
```

**Notes**
- **Offer = (feed × source product)**; `country_code` inherited from the feed (matches today's
  per-country feeds: Running Warehouse AU/DE/…, Amazon US/UK/CA). The "all-countries" payload is just
  offers grouped by `country_code` at query time.
- **`products` (canonical) is optional/phase-2** — current DFS has no cross-merchant identity (it
  text-matches by name). We can ship offers + full-text search first (faithful port) and add
  canonical brand+model grouping later for cleaner dedupe and segmentation.
- **No `sql_code` / `price_compare_sql`** — the stored-SQL execution feature is dropped (unused:
  `category`=0, `pcpages`=0 rows). Categories become real columns/relations.
- `msrp` is present but **populated only where a feed actually carries it** — per §7, RSG owns MSRP.

---

## 4. Ingestion pipeline

Replaces `admin/bin/import.php` + cron with Laravel scheduler + Horizon queue jobs.

**Per feed, as a queued job:**
1. **Download** — stream via cURL/SFTP to a temp file (phpseclib equiv; TLS verification **on**).
2. **Parse** — stream rows (`fgetcsv` generator, never load whole file); apply the feed's
   `parser_config` column map + value `filters` (replace/append/prepend/stock — ported 1:1).
3. **Classify & segment** (NEW — §5): assign `gender`; apply per-feed **include/exclude** rules
   (e.g. drop kids). Excluded rows are skipped → smaller working set.
4. **Upsert** — batch `INSERT … ON DUPLICATE KEY UPDATE` into `offers` keyed by `(feed_id, source_key)`,
   stamping `last_seen_at`. **No DROP/RENAME.**
5. **Age-out** — after a successful import, soft-delete/remove offers for that feed with
   `last_seen_at < import_start` (handles removed products) — transactional, incremental.

**Scheduler (replaces crontab):** feed import `0 6 * * *`; API imports `30 9,21 * * *`;
cache handled by Redis TTL (no file-purge cron). All visible/restartable in Horizon.

**Why this is cheaper:** streaming + batched upserts keep memory flat (vs rebuilding a 1M-row table +
FULLTEXT each run); ingest-time exclusion shrinks the catalogue from ~1M to tens of thousands.

---

## 5. Segmentation / classifier (the new capability)

Goal: **auto-separate mens/womens and exclude kids, per feed.**

- **Per-feed `segment_rules` (JSON), with global defaults**, e.g.:
  ```json
  {
    "exclude": [{"field":"name","regex":"(?i)\\b(kids?|youth|junior|toddler|infant|\\bGS\\b|\\bPS\\b|\\bTD\\b)\\b"}],
    "gender": [
      {"match":{"field":"category","regex":"(?i)women"}, "set":"womens"},
      {"match":{"field":"name","regex":"(?i)\\bwomen'?s?\\b|\\bwmns?\\b"}, "set":"womens"},
      {"match":{"field":"name","regex":"(?i)\\bmen'?s?\\b"}, "set":"mens"},
      {"default":"unisex"}
    ]
  }
  ```
- Rules run in the ingest pipeline; results stored on the offer (`gender`, or excluded). Some feeds
  expose a gender/age column directly → map it; others need name/category keyword rules.
- **Testable**: a fixtures suite of real feed lines → expected gender/exclude, run in CI. This is the
  thing the current `replace/append` filter engine fundamentally cannot do (no row exclusion, no
  classification; the auto-`category` system is unused).
- **Why this is genuinely new:** measured against live data, **0 of 1,009,356 products carry
  `category="Male"/"Female"`** — the `category` field holds feed categories ("Apparel & Accessories"
  742k, blank 218k, numeric IDs, "Running"…), never gender. So gender separation today is done
  **100% manually** via per-box men's/women's `ds_query` strings and tokens (`+women`, `-children`).
  The classifier replaces that manual gymnastics and lets the theme's `CATEGORY=="Male"/"Female"`
  branch actually work from data.
- API gains a `gender`/`segment` filter so RSG can request "women's only" cleanly.

**Brand allowlist (3a) — already half-built.** RSG already exports `export_brands.cvs` (live, HTTP 200):
`361 degrees, Adidas, Altra, Asics, Atreyu, Brooks, Decathlon, ECCO, Hoka One One, Icebug, Inov-8,
K-Swiss, Karhu, La Sportiva, Lululemon, Merrell, Mizuno, New Balance, Newton, Nike, Nobull, ON, Pearl
Izumi, Puma, Reebok, Ryka, Salming, Salomon, Saucony, Scott, Skechers, Skora, The North Face, Topo
Athletic, Under Armour, …`. Wire this list as an **ingest brand-allowlist filter** (recognize brand
from name/feed field; drop non-allowlisted rows). This alone removes most of the **732k Zappos** rows
(72% of the catalogue) → dramatically smaller working set, cheaper box. Brand recognition = the same
classifier engine as gender.

---

## 6. API design (the contract the new service must honour)

**Endpoint:** `GET /api/v1/products`
**Auth:** `X-API-Key` header (hashed, per-client) + optional IP allowlist (`api_clients`) — replaces
the `sites.client_ip` check and the buggy `ios=yes` bypass.
**Params (ported from live usage):** `search`, `country` (`US` | `ALL`), `gender` (new),
`clickref`, `override_tag`, `referer`, `amazon` (live-blend toggle), `redirect_url`, `ignorezero`.

**Response — single country (backward-compatible with today's `{datafeed:{items:[…]}}`):**
keeps the 33 UPPERCASE fields so we can cut over WP with zero template changes initially.

**Response — multi-country (the RSG target):**
```json
{ "items": [
  { "MYKEY":"…", "NAME":"…", "BRAND":"…", "GENDER":"womens", "IMAGE_URL":"…",
    "offers_by_country": {
      "USA": {"DISPLAY_PRICE":129,"CURRENCY_PREFIX":"$","CURRENCY_CODE":"USD","DEEP_LINK":"…","IN_STOCK":1},
      "GBR": {"DISPLAY_PRICE":110,"CURRENCY_PREFIX":"£","CURRENCY_CODE":"GBP","DEEP_LINK":"…","IN_STOCK":1}
    }
  }
]}
```
- **MSRP intentionally excluded** — owned by WordPress post-meta (§7).
- `DEEP_LINK` keeps the tracked `/go/`-style redirect with `clickref`/`override_tag`; `ORIGINAL_DEEP_LINK`
  preserved. **Affiliate tracking logic (`get_deep_link`, AWIN/Amazon tag handling) ported 1:1** — this
  is revenue-critical and must be byte-faithful.
- **Caching:** Redis with country-aware keys + HTTP cache headers (Cloudflare-friendly). One cached
  matrix per product replaces N per-country cache entries → higher hit rate, simpler purges.

**Boolean search parity ⚠️:** RSG relies on DFS's query semantics (`makeSqlQueryFromBoolean`,
`get_name_clause`, `order_field`/`order_direction`). These must be **replicated and shadow-tested**, or
RSG result sets shift. Treated as a first-class parity risk (§9).

---

## 7. MSRP strategy [DECIDED — owned by WordPress]

MSRP is static per shoe and missing from the per-country feeds (Running Warehouse/Amazon = 0%; only
Nike/Puma direct = 100%). So:
- **WordPress (post-meta/ACF) owns MSRP**, written **once per shoe, fill-if-empty, never overwrite.**
- A low-frequency **WP backfill job** fills gaps from: (1) brand-direct feeds DFS already ingests
  (Nike/Puma 100%), matched by model/`mykey`; (2) Amazon list price; (3) manual.
- DFS-NG may still *expose* `msrp` on offers where a feed provides it (cheap), but RSG does **not**
  depend on it in the live payload. This lets RSG delete `rrp_xx` ACF fields + `get_geo_rrp()`.

---

## 8. Migration & cutover plan (phased, low-risk)

| Phase | Work | Exit criteria |
|---|---|---|
| **0. Infra** | Docker stack on Hetzner (mariadb, php-fpm, nginx via NPM, redis, horizon); add swap; import `dfs.sql` as reference | Stack up, TLS, reference DB queryable |
| **1. Ingestion** | Port parser/filter engine; build offers + segmentation; migrate `feeds`/`parsers`/`filters`; re-import all live feeds | Per-feed offer counts within tolerance of live DFS; segmentation validated on fixtures |
| **2. API parity** | Build single-country API (33-field shape); replicate boolean search; migrate `sites.client_ip`→`api_clients` | **Shadow test:** replay top logged queries old-vs-new, JSON diff within tolerance per country |
| **3. Multi-country + WP** | Multi-country endpoint; new gender filter; integrate on **staging** WP (`jfx92wmcr3-staging.onrocket.site`) | Staging pages render correct prices for all geos; click tracking verified |
| **4. Cutover** | Point WP `ds_url` at DFS-NG (canary % first); monitor; decommission old DFS | Error/latency stable; revenue tracking intact; old box retired |

**Data migration:** bring over `feeds`, `parsers`/`filters` (→ `parser_config`/`segment_rules`),
`meta` editorial overrides, and `sites(client)` → `api_clients`. **Do not** migrate the 1M `products`
rows — rebuild them from feeds (most are stale/non-running noise). `dfs.sql` is the test corpus.

**Parity/cutover checklist:** replay the top-N real API queries (we have them in the access logs) →
diff item sets + per-country prices; verify `clickref`/`DEEP_LINK` tracking end-to-end; verify
per-feed counts; **canary** a small % of WP traffic before full switch; keep old DFS hot for instant
rollback until a full revenue cycle confirms parity.

---

## 9. Risks & open items

- **🔴 The 100% item — WordPress-side contract.** Need the `rsg_2022` theme to confirm *which* of the
  33 fields are used, whether a **mobile app** (`ios=yes`) still consumes DFS, where `/go/` tracking
  lives, and that RSG is the only consumer. *Mitigation:* read access to the Rocket.net box.
- **🟠 Boolean search parity.** Replicate `get_products` query semantics exactly; shadow-test.
- **🟠 Affiliate deep-link formats** per network (AWIN/CJ/Amazon/LinkShare) — port `get_deep_link`
  + tag/sub-id handling 1:1; revenue-critical.
- **🟠 Live Amazon blending (`amazon` param).** Decide: keep on-the-fly Amazon PA-API merge, or drop.
- **🟡 8 GB / no swap contention** with n8n during imports → add swap, memory-cap worker, off-peak
  schedule, ingest-time filtering. Fallback: a €5 isolated box.
- **🟡 Per-feed re-validation long tail** — each of ~30 active feeds needs a parser/segment check.

---

## 10. Hosting, ops, security

- **Reuse the RSGMedia Hetzner box** [DECIDED]: add DFS-NG containers behind the existing NPM/TLS;
  back up via `mysqldump` + volume snapshots; **never `docker compose down -v`**.
- **Resource guards:** swap on; Horizon worker `--memory` cap; streaming parse + batched upserts;
  imports off-peak (06:00 already off n8n's busy window).
- **Security wins baked in:** ORM/parameterized queries (kills SQLi); no stored-SQL execution; secrets
  in Docker/env (not source); TLS by default; API-key auth; no `phpinfo`; no ionCube/phone-home;
  feed downloads verify TLS.

---

## 11. Effort estimate (one senior engineer)

| Block | Rough |
|---|---|
| Phase 0 infra | ~0.5 wk |
| Phase 1 ingestion + segmentation | ~2–3 wks |
| Phase 2 API + search parity | ~2 wks |
| Phase 3 multi-country + WP staging | ~1–2 wks |
| Phase 4 migration/cutover + buffer | ~1–2 wks |
| **Total** | **~7–10 weeks** calendar, + per-feed re-validation tail |

Cost delta: **~€0** (reuse box) to **+€5/mo** (isolated DFS box). Eliminates the legacy box.

---

## 12. Decision log
- **[DECIDED]** Headless for *page rendering*, but **keep the click-redirect endpoint + the
  `export.cvs` ingestion handshake** (§13). Evidence: 99.5% API traffic; DNS shows RSG fronted by
  Cloudflare→WordPress, not this box.
- **[DECIDED]** MariaDB/InnoDB (low-friction; escape MyISAM). PHP 8.3 + Laravel. Nginx + PHP-FPM.
- **[DECIDED]** Reuse existing Hetzner box (with swap + worker memory limits).
- **[DECIDED]** MSRP owned by WordPress; DFS still emits `RRP_PRICE` (widget price-list reads it).
- **[CLOSED]** WordPress-side consumer audit — done (§13). Contract narrows to **14 fields**; surfaced
  the raw-SQL `[dfscat]` path, the click endpoint, and the reverse `export.cvs` contract.
- **[OPEN]** `[dfscat]` raw-SQL → migrate to a structured category endpoint (needs a theme change,
  coordinate with the RSG modernization). Live-Amazon-blend (`amazon` param) keep/drop.

---

## 12b. Roadmap additions (owner vision, 2026-06-21)

**1. Amazon API modernization + freshness [in-scope, major].**
> **CORRECTION (verified 2026-06-21):** Amazon **price fetching is dead since 2026-01-27.** The fetch
> job `import_api_cvs.php` (wrapper `import_api_csv.sh`) — which queries PA-API and writes the feed
> CSVs — stopped mid-run on Jan 27 (killed by the ~Jan 28 reboot) and is **no longer in crontab**.
> The daily `import.php` keeps re-importing the frozen Jan-27 CSVs, so `products` shows fresh
> `updated_at` on ~5-month-old prices (stale-timestamp trap). The active fetch code (`amazon.inc`
> `ItemSearchCSV`/`ItemLookup`) does target **PA-API 5** (`/paapi5/...`), not v4 — but it hasn't run
> since Jan 27, so v5 key/account validity is unconfirmed. Proximate fix = re-schedule the fetch job;
> the May-1 v4 deprecation is a separate latent risk to test on first re-run. Full detail: `DFS-ACCESS.md`.

Port to **PA-API 5.0** (JSON, AWS SigV4,
`GetItems`/`SearchItems`, **10-ASIN batched** lookups). Key constraint: PA-API 5 has **hard quotas
tied to shipped revenue** (≈1 TPS / 8,640 req/day baseline) — you *cannot* refresh everything
constantly. Design a **budget-aware freshness scheduler** (fits the queue-worker model perfectly):
per-ASIN `last_refreshed` + priority by shoe popularity/traffic + tiered TTLs (hot shoes hourly, long
tail daily/weekly) + backoff on throttle. This improves speed *and* accuracy vs the twice-daily
blanket run, within Amazon's limits.

**2. Contract v2 — full price matrix in post-meta + client-side geo [agreed; phase 2, with a caveat].**
Direction is right and matches `CONVENTIONS.md`: DFS writes **all countries × merchants** for a shoe
into WordPress (post-meta/cached JSON), WP serves one identical HTML for all geos, and **JS reveals the
visitor's country** (Cloudflare cookie) — killing the per-geo cache fragmentation. **Caveat / sequencing:**
the WP-side switch is genuinely phase 2, **but the DFS multi-country endpoint it depends on must ship in
phase 1** of the DFS rewrite (§6) — otherwise phase 2 has nothing to consume. And for big roundup pages
(dozens of shoes), don't inline every country into post-meta — fetch the matrix from **one cached JSON
endpoint keyed by product IDs** (also per `CONVENTIONS.md`). So: *not a disagreement* — just "build the
DFS half now, flip the WP half in phase 2."

**3. Brand + gender + kids classification [in-scope].** (a) Brand allowlist — already half-built via
`export_brands.cvs` (§5). (b) Gender — feed field where present, pattern-match otherwise; **auto-exclude
kids** at ingest, retiring the manual `-children` tokens. Both are the same classifier engine, run once
at ingest, CI-tested against real feed lines.

---

## 13. Confirmed consumer contract (WP audit 2026-06-21 — closes the 100% item)

Source: `DFS-Consumer-Contract-Report.md` (read-only SSH audit of the live `rsg_2022` theme + `dstudio`
plugin). **Only the active theme + `dstudio` plugin consume DFS at runtime** — no app, no other site.

**A. Fields — 14 of 33 used.** New API must reliably populate:
`MYKEY, NAME, DESCRIPTION, MERCHANT_NAME, MERCHANT_LOGO, DISPLAY_PRICE, RRP_PRICE, DEEP_LINK,
IMAGE_URL, COUNTRY_NAME, CURRENCY_PREFIX, CURRENCY_CODE, CATEGORY, USERFIELD1`. The other 19 can be
dropped. Constraints: **`CATEGORY` ∈ {"Male","Female"}** (theme branches on the literal strings for the
gender split — so the segmentation classifier must emit exactly these), `CURRENCY_PREFIX` is the symbol,
`DISPLAY_PRICE`/`RRP_PRICE` are bare numbers (theme concatenates `PREFIX . PRICE`).

**B. Response contract.** `{"datafeed":{"items":[ … ]}}`, items **sorted price-ascending** (`[0]` =
best price; `ds_sort=plth`), accessed positionally `items[0],[1],…`. Empty result **must contain the
literal `Product not found`**. Decoder is tolerant (strips a `<style>` tag, `DS:` cache comments, a
trailing comma, and **all single quotes** — so field values must not rely on apostrophes).

**C. Two request shapes.**
1. Keyword: `…/api/v1/products?search=<kw>&pricebox=newrsg-json&clickref=&override_tag=&referer=&amazon=&redirect_url=https://www.runningshoesguru.com/go&country_code=<CC>[&ignorezero=1]`.
2. **Category (raw SQL):** `[dfscat]` shortcode sends a raw `SELECT … FROM products/category_product…`
   in `search` with `pricebox=newrsg-json-cat`, country forced to `USA/GBR/DEU`. ⇒ The rebuild must
   either accept a constrained category query **or** (preferred) expose a structured category endpoint
   and replace the shortcode's raw SQL during the RSG theme refactor. **Do not silently drop.**

**D. Per-geo call multiplication (the efficiency win).** Today every box is a separate GET, and pages
fire **(men + women) × N price-boxes** calls, once per visitor country. The new **multi-country +
combined (men/women) payload** collapses these to one cached call — the core RSG performance goal.

**E. Click-redirect endpoint (must keep).** Feed `DEEP_LINK → https://www.runningshoesguru.com/go/?id=…
→ <DFS>/index.php?id=<MYKEY>&clickref=&override_tag=&site_id=&src=`. `/go/` is a standalone hardened
bouncer (referer/bot gating, `id` whitelist `^[A-Za-z0-9_-]{1,64}$`, 302) with `$ds_url` **hardcoded**
to `http://45.33.39.133`. **Rebuild must** keep a click endpoint at the same base (port deep-link
resolution + `product_hits` logging) **or** repoint `/go/`'s hardcoded base. Click tracking stays
DFS-side.

**F. Reverse contract — RSG drives what DFS fetches.** `dstudio.php` WP-cron (`dfs_cron_export_products`,
`dfs_cron_export_brands`, hourly + on `save_post`) writes `export.cvs` (product keyword list) and
`export_brands.cvs` (brands). **DFS ingests these to scope its feed pulls.** Confirmed concretely: the
Amazon datafeeds' `amazon_product_list_file` = `https://www.runningshoesguru.com/wp-content/plugins/dstudio/export.cvs`
— `import_api.php` downloads that CSV and queries the Amazon PA-API per shoe, on cron at **09:30 & 21:30**
(`30 9,21 * * *`). The rebuild must keep consuming both CSVs (or replace the handshake). (One orphaned
`dfs_export_products` WP-cron event has no hook — dead, clean up.)

**G. MSRP split.** Main review UI uses ACF `get_geo_rrp()` (`rrp_us` default; `rrp_gb/ca/au` only;
everything else → `rrp_us`) — **WordPress-owned, per §7**. But the **widget price-list still reads DFS
`RRP_PRICE`**, so the API keeps emitting it (§13.A). Misc non-issues: an editor-only "Amazon TEST"
admin link; `remove_bg.php` calls clippingmagic (not DFS).
```
