# Datafeed Studio — Architecture Document

> Reverse-engineered from the live deployment at `45.33.39.133` (web root `/var/www/html`).
> Product: **Datafeed Studio v3.4** (`VERSION` file), a commercial PHP platform (datafeedstudio.com)
> for building affiliate **price-comparison** and **store** websites driven by merchant datafeeds
> and affiliate-network APIs.
>
> _Status: living document — updated as investigation continues._

---

## 1. Overview

Datafeed Studio is a **multi-tenant** PHP application. A single installation serves many
public websites (rows in the `sites` table), each resolved by hostname at request time, each
with its own theme, config file, and CSS. It aggregates product data from merchant feeds and
affiliate APIs into a central MySQL database and renders price-comparison / storefront pages.

There are three runtime surfaces:

| Surface | Entry point | Purpose |
|---|---|---|
| **Public site** | `/index.php` (front controller) | Renders storefront / price-compare pages to visitors |
| **Admin back-office** | `/admin/views/*.php` | Site, feed, product, theme & content management |
| **REST API** | `/api/v1/index.php` | JSON product search (IP-restricted, used by mobile/iOS) |

Plus an **offline ingestion tier**: CLI scripts under `/admin/bin/` run as background/cron jobs
to download and parse feeds.

### Tech stack
- **PHP 5.x** era code (procedural + light OO), `mysqli`.
- **MySQL**, database `dfs`, MyISAM tables, `latin1` charset.
- **Apache + mod_rewrite** for pretty URLs (`.htaccess`).
- **PHPTAL** (PHP Template Attribute Language, Zope/TAL-style) for view rendering.
- **ionCube**-encoded files for license protection (`/ioncube` loader bundled).
- **phpseclib** for SFTP feed downloads; bundled **PHPTAL** library; `class-IXR.php` (XML-RPC, for the WordPress plugin).

---

## 2. Request flow — public site

`/.htaccess` rewrites all non-file requests to `index.php`, mapping SEO-friendly URLs to internal scripts:

```
api/v1/(.*)              -> api/v1/index.php
c/<cat>/<slug>.html      -> index.php?script=show_category.php&catid=…&autoslug=1
category-<id>.h…         -> index.php?script=show_category.php&catid=…
<key>.html               -> index.php?script=display_product.php&mykey=…
everything else          -> index.php
```

`index.php` then executes roughly this pipeline:

1. `session_start()`, pull in DB, config, all models, PHPTAL, cache lib, common helpers.
2. Optional `user/functions.inc` hook (per-install customization) and `redirect($db)`.
3. **Geo / currency** — `get_country_code_for_ip()` → country row → currency symbol. Supports a fixed country list (`DS_SUPPORTED_COUNTRIES`).
4. **Cache check** — builds `cacheName = SERVER_NAME + md5(REQUEST_URI) + iso_code`; on GET, serves a fresh file-cache hit and exits (see §6).
5. **Tenant resolution** — `pcpage_findby_permalink()` (single price-compare landing page) else `site_findby_url(get_site_url_from_request())`. Unknown host → 404 + optional alert email.
6. Loads the tenant's config: `site_configs/{site_id}.inc` (defines `$PUBLIC_URL`, titles, blog flags, menu keys, etc.).
7. **Template selection** — content page type, or `get_site_template($permalink)`.
8. **Command** — instantiates `commands/{template}Command.php` (e.g. `searchCommand`), whose `addTemplateData($tpl,$db,$site)` attaches data to the PHPTAL template.
9. Renders `templates/{theme}/{template}.html` via PHPTAL, injecting title/meta/CSS/menu/categories/analytics, then echoes; writes to cache on the way out.

### The "Command" layer (public controllers)
`/commands/*.php` — one class per page type, each exposing `addTemplateData()`:
`indexCommand`, `searchCommand`, `display_productCommand`, `show_categoryCommand`,
`content_pageCommand`, `content_postCommand`, `blogCommand`, `contactCommand`, `pcpageCommand`.
These are the public-facing equivalent of controllers: they query models and populate the view.

---

## 3. Data model (MySQL `dfs`)

Core tables (from `database.sql`; MyISAM / latin1):

| Table | Role |
|---|---|
| `sites` | Tenants: `site_type` (store / pricecompare), `theme`, `url` (hostname) |
| `datafeeds` | Merchant feed definitions: name, logo, `datafile`, `feed_type` (automatic/manual), source `url`, `status`, `parser_id`, `last_import`, `total_products` |
| `parsers` | Column-mapping definitions: per-field **column index** into a delimited feed, `parser_separator`, `parser_type`, skip-first-row, prefix, default category |
| `filters` | Per-parser transform rules: `filter_type` (e.g. `replace`, `buyatstock`), `field_name`, params |
| `products` | Live product catalogue (rebuilt each import): `mykey`, merchant, model, name, description, image/thumb/deep-link URLs, prices (`rrp_price`,`display_price`), stock, `datafeed_id`, category. Indexed incl. **FULLTEXT** on `name` |
| `meta` | Per-product editorial overrides keyed by `mykey` (admin edits, slug, custom category, click count) — survives feed re-imports |
| `category` / `category_product` | Custom categories and product membership; `category.sql_code` lets a category be defined by a raw SQL fragment |
| `pcpages` | Standalone price-comparison landing pages: permalink, SEO meta, theme, `sql_code` selecting products, blurb, images |
| `product_hits` | Outbound affiliate click tracking (`mykey`, `clicktime`, `url`) |
| `reviews` | User product reviews with `approved` moderation flag |
| `images` | Image library |
| `amazonproducts` | ASIN/title cache for Amazon API |
| `api_attempts` / `api_searches` | API access logging / saved searches |

**Key design points**
- `mykey` is the cross-table product identifier (string, e.g. `merchant_model`), not the numeric `id`.
- `meta` is deliberately separated from `products` so human edits persist across the destructive feed rebuild.
- Several features (categories, pcpages) embed **raw SQL fragments** stored in the DB and executed at render time — powerful but a major injection surface.

---

## 4. Feed ingestion pipeline (`/admin/bin`)

Background CLI scripts (run via `$PHP_SHELL_COMMAND`, e.g. cron / `import.sh`), `set_time_limit(0)`:

- **`import.php`** — datafeed (file) imports. For each datafeed:
  1. Cleans `/admin/datafiles/*.dsf`.
  2. If `feed_type == automatic`, downloads the source: `sftp://` URLs via **phpseclib `Net_SFTP`**, otherwise **cURL** to a file (`{id}-{timestamp}.dsf`, cache-busted, `SSL_VERIFYPEER=false`).
  3. Optional post-download script (e.g. gunzip — see `*.sh` helpers).
  4. Parses using the feed's `parser` (column-index → field map) and applies `filters`.
  5. Loads rows into **`temp_products`** then atomically swaps: `temp_products` is built (keys disabled for speed), populated, keys enabled, then `products` is dropped and `temp_products` renamed in. This gives near-zero-downtime rebuilds.
- **`import_api.php` / `import_api_cvs.php`** — API-sourced imports (Amazon, AWIN, CJ, LinkShare).
- **`export.php`** — outbound feed/product export with `export_mappings`.
- **`apirefresh.php`**, `convertamazon.php`, `fixprod.php`, `listfeeds.php`, `diagnostics.php`, `patch.php` (DB migration runner), plus shell wrappers (`import.sh`, `gunzip.sh`, …).
- Data artefacts: `/admin/datafiles/*.dsf[.gz]` are downloaded feeds (some multi-GB).
- A lock file (`import.lck`) prevents concurrent imports.

### Parser + filter algorithm (confirmed from `import.php`)
The ingestion engine is a **column-mapped CSV transformer**:
1. A `parser` row stores, for each product field (`name`, `display_price`, `deep_link`, …), the
   **integer column index** into the delimited feed line, plus the delimiter and skip-first-row flag.
2. `create_product()` walks every field calling `set_value()`, which reads `csv_line[parser[field]]`
   and applies any `filters` attached to that field. Filter types: `replace` (str_replace),
   `append`, `prepend`, `buyatstock`/`porstock` (stock normalisation).
3. Post-processing: `mykey = "{datafeed_id}_{slug(mykey)}"`; `autoslug = slug(name)`;
   `sale_price` (if > 0) overrides `display_price`; **products priced under 3 are dropped**;
   timestamps set.
4. **Auto-categorisation**: `set_product_category()` looks up the feed's category string in an
   in-memory match cache (`category_matches`); unseen values auto-create a `category` row
   (`cat_type='auto'`) and a `category_match` mapping, with sub-categories parented to categories.
5. Rows are written to `temp_products`, then the atomic table swap promotes them to `products`.

So adding a new merchant = create a `parser` (column map) + `datafeed` (URL/credentials) in the
admin, no code change. This config-driven feed engine is the single most valuable piece of domain
logic to preserve in any rebuild.

### Affiliate-network integrations (configured in `config.inc` `$API[...]`)
- **Amazon** Product Advertising API (UK / USA / custom endpoints), `admin/includes/amazon.inc`.
- **Affiliate Window / AWIN**, **Commission Junction (CJ)**, **LinkShare/Rakuten**, **TradeDoubler**, **BuyAt** (parsers reference these networks).
- Controllers `amazon_api_c.inc`, `apisearch_c.inc`, etc. drive on-demand API product search from the admin.

---

## 5. Admin back-office (`/admin`)

Hand-rolled MVC, no framework:

- `admin/index.php` → redirects to `admin/views/`.
- **Views** (`admin/views/*.php`) are the routable entry pages (≈50 of them: `site.php`, `datafeed.php`, `product.php`, `search.php`, `pricebox.php`, `config.php`, `themes.php`, `content.php`, etc.). Each view `include`s its controller and renders HTML.
- **Controllers** (`admin/controllers/*_c.inc`) handle the POST/GET logic, call models, set status messages.
- **Models** (`admin/models/*_m.inc`) are **procedural function libraries** wrapping raw `mysqli` SQL (e.g. `product_find()`, `product_insert()`, `meta_update()`). No ORM.
- **Includes** (`admin/includes/`): `config.inc` (the big settings file), `common.inc` (helpers: `slug()`, template discovery, error mail), `amazon.inc`, `settings.inc`, `ui.inc`, bundled `phpseclib`.

Notable admin features inferred from controllers/views: site management & multi-site hub (`sitehub`),
datafeed & parser configuration, product search/edit, **priceboxes** (embeddable price-comparison widgets),
content pages & blog, themes editor, analytics, country/geo config, image library, REST-API search console.

---

## 6. Caching

Simple **file-based full-page output cache** (`lib/cache_lib.inc`):
- `check_cache(name, ttl)` serves the cached file if `now - ttl < mtime`, appending an HTML comment timestamp, then `exit`s; otherwise starts output buffering (`ob_start`).
- `write_to_cache(name)` flushes the output buffer to `/cache/{name}`.
- Cache key includes hostname + URI hash + country code, so it's tenant- and geo-aware.
- TTLs: public `$CACHE_TIME_IN_SECONDS` (currently `0` = disabled), API `$DS_API_CACHE_SECONDS` (6h).
- The `/cache` directory is large (~300 MB), confirming heavy historical use.

---

## 7. Templating & theming

- **PHPTAL** templates: `templates/{group}/{theme}/{template}.html`, where group ∈ `store`, `pricecompare`, `pcpages`, `priceboxes`, `export`.
- Bundled themes: `store/default`, `store/modern`, `pricecompare/default` (each group also has `settings.tmpl` + `style.tmpl` metadata used by the themes admin).
- **Per-tenant config & skin**: `site_configs/{id}.inc` (PHP settings) + `site_configs/{id}.css` (currently sites `17` and `19` exist).
- `commands/{template}Command` supplies the data; PHPTAL binds it into the `.html` template.

---

## 8. REST API (`/api/v1`)

- `index.php` front controller: parses the service name from the URI (`/api/v1/<service>?…`), requires `<service>_rest_service.php`, instantiates `<service>_RestService`, calls `handleRawRequest()`.
- **Access control**: requests must come from an IP registered in `sites.client_ip` (unless `ios=yes`); failures are logged to `api_attempts` and 403'd. Toggled by `$DS_API_ENABLED`.
- JSON responses, cached for `$DS_API_CACHE_SECONDS`.
- Implemented services: `products_rest_service.php` (product search), base `rest_service.php`.
- `extra/wordpress/dstudio` ships a WordPress plugin that consumes this API (XML-RPC via `class-IXR.php`).

---

## 9. Configuration & deployment

- Main config: `admin/includes/config.inc` — install folder, public URL, PHP CLI path, cache TTLs, geo settings (`DS_GEO_ENABLED`, default country `US`, supported-country list), and `$API[...]` network credentials/endpoints.
- DB credentials: `lib/database.inc` (host `127.0.0.1`, db `dfs`, user `root`).
- `install.php` — first-run installer (permission checks, DB setup, detects PHP CLI path).
- `patch.sql` / `admin/bin/patch.php` — incremental DB migrations applied on upgrade (see `README.txt` upgrade steps).
- `CHANGELOG.txt`, `EULA.txt`, `license.txt` (license-key gated, ionCube-protected portions).

---

## 10. Security observations

> Documented for the operator's benefit. These are characteristics of the deployed code as-is.

- **SQL injection throughout.** Models build queries by interpolating request input directly, e.g.
  `"select * from products where name like '%$query%'"` (`product_find_matching`). User-controlled
  values reach SQL with little/no escaping across many models and the `sql_code`/`price_compare_sql`
  features deliberately execute stored SQL fragments.
- **Plaintext DB credentials** committed in `lib/database.inc` (and old creds left commented).
- **`info.php` exposes full `phpinfo()`** publicly.
- **Server exposure:** the entire web root — plus a ~1.5 GB `dfs.sql` database dump and `html.zip` —
  is readable over **root SSH/SFTP secured only by a weak password** (port 2200). `.htpasswd` present
  under `/var/www`.
- **cURL feed downloads disable TLS verification** (`CURLOPT_SSL_VERIFYPEER=false`).
- **World-writable artefacts** (`chmod 0777`) on downloaded data files and logs.
- **MyISAM / latin1** throughout — no transactions, no FK constraints, legacy encoding.
- Aging stack (PHP 5, MySQL 4.1+ baseline per `README.txt`) implies unpatched runtime risk.

---

## 12. Rebuild assessment (Hetzner + modernization)

### How big is this thing, really?
Despite its age, the **domain is small and comprehensible**: ~14 core tables, one config-driven
feed-ingestion engine, product search (boolean + MySQL FULLTEXT), affiliate deep-link tracking,
multi-tenant hostname routing + theming, price-compare pages, and a thin REST API. None of the
core logic is ionCube-locked — it's all readable PHP, so a rebuild can faithfully port behaviour.
The hard/valuable parts are **domain knowledge, not code volume**: the parser/filter feed mappings,
the affiliate-network quirks (Amazon PA-API, AWIN, CJ, LinkShare, TradeDoubler deep-link/sub-id
formats), and the accumulated category/match data.

### Why the current system is a liability (not just "old")
- **Security**: pervasive SQL injection + by-design execution of admin-stored SQL fragments
  (`sql_code`, `price_compare_sql`). This is structural, not a few bad lines.
- **Operational fragility**: destructive `DROP products / RENAME temp_products` rebuilds (no
  transactions, MyISAM), 0777 files, TLS verification disabled on downloads, root-over-SSH exposure.
- **Stack EOL**: PHP 5 / MySQL 4.1-era assumptions, latin1, no dependency management, no tests.
- **Maintainability**: copy-paste procedural models, ~50 hand-routed admin pages, global-variable
  config — every change is high-risk.

### Verdict (updated 2026-06-21 — rebuild case strengthened)
RSG itself does **not** require a DFS rebuild (Track A unblocks it). But for **DFS as a product**,
the rebuild case is now **strong**, driven by two concrete goals the current design can't meet:

- **Ingest-time segmentation (mens/womens split, exclude kids — per feed).** The current filter
  engine only does *value transforms* (`replace`/`append`/`prepend`/stock). It has **no conditional
  row exclusion and no classification**, and the auto-`category` system is **unused** (0 rows). This
  feature is a structural gap, not a tweak — a rewrite makes per-feed **include/exclude rules + a
  gender/segment classifier** first-class.
- **Less RAM / cheaper hosting.** DFS ingests **~1M products (732k from Zappos alone)**, most not
  running shoes, and rebuilds the whole `products` table + FULLTEXT index every import. Filtering to
  running shoes *at ingest* (same classifier as above) likely cuts the working set to tens of
  thousands → runs on a small Hetzner box with **incremental upserts** instead of full rebuilds.
  **Segmentation and cost are the same win.**

Combined with the §10 security posture and PHP-5/MyISAM EOL stack, "keep patching" is the weaker path.
The feed-ingestion engine and affiliate-link logic should be **ported deliberately**; everything else
redesigned. **De-risk option:** prototype the new exclude/classify filter types on current DFS first
to validate segmentation value before committing to the full rewrite.

### Suggested modern target (illustrative, on Hetzner)
- **Compute**: Hetzner Cloud (e.g. CPX31/41) or a dedicated AX-line box for the import/DB workload;
  managed backups + snapshots. Docker Compose or a small k3s for services.
- **App**: a typed framework — Laravel (closest semantic port from PHP) **or** Node/TS (NestJS) /
  Python (FastAPI/Django) if RSG pushes that way. Server-rendered (Inertia/Blade or Next.js).
- **DB**: PostgreSQL (UTF-8, transactions, proper FKs, JSONB for flexible product attributes),
  parameterized queries / an ORM — eliminates the SQLi class entirely.
- **Search**: Postgres FTS for v1; Meilisearch/Typesense/OpenSearch if catalogue/scale demands it.
- **Ingestion**: a queue-driven worker (Horizon/BullMQ/Celery) doing incremental upserts into a
  staging table + atomic publish — keeping the config-driven parser/filter concept but transactional.
- **Multi-tenancy**: keep hostname routing; tenant config in DB, not per-file `.inc`.
- **Caching/CDN**: Redis + a CDN (Cloudflare) instead of the bespoke file-cache.
- **Ops**: Git, CI, automated migrations, secrets in env/Vault, TLS by default, no public phpinfo.

### Rough effort shape
Small senior team: **core platform + feed engine + admin ≈ a few months**; the long tail is
re-validating every affiliate-network feed/parser and migrating historical data. The `dfs.sql`
dump + existing `parsers`/`filters`/`datafeeds` rows are a ready-made migration source and test corpus.

> ⚠️ This section is **preliminary**. The build-vs-port recommendation and stack choice depend on
> what the **RSG project** actually changes — fill that in to finalize.

---

## 13. RSG integration & the DFS task (evidence-based)

> Context: **RSG = runningshoesguru.com** (WordPress theme `rsg_2022`). It consumes this DFS backend
> for live merchant prices. The RSG modernization (`CONVENTIONS.md`) hinges on one DFS capability:
> can DFS emit **all countries in one payload, each with `price_msrp`?** Findings below are measured
> against the live database.

### Live data footprint (measured 2026-06-21)
| Table | Rows |
|---|---|
| `products` | **1,009,356** |
| `product_hits` (affiliate clicks) | **7,602,685** |
| `datafeeds` | 82 (running-shoe focused) |
| `parsers` / `filters` | 33 / 10 |
| `sites` | 20 (site `1` = `runningshoesguru.com`, theme `store/default`; Rocket.net staging `jfx92wmcr3.onrocket.site` registered as sites 17/21) |
| `meta` (human overrides) | 18 |
| `category` | 0 (auto-categorisation effectively unused) |
| `pcpages` | 0 |
| `reviews` | 6 |

Biggest live feeds: Zappos (~732k), Nike (~145k), Asics (27k), Mizuno (21k), Puma UK/US (32k),
Hoka (10k), Amazon CA/US/UK, Brooks (7k), and the **per-country Running Warehouse** feeds
(US/CA/GB/DE/FR/ES/IT/GR/SE/NO/IE/AU/NZ/MY/ID/SG/HK) — these map exactly to RSG's supported-country
clamp list. Several feeds are `FAILED - NO FILE FOUND` or `SUSPENDED` (Under Armour, Nike UK/CA/DE,
Reebok, Saucony, Fit2Run, The Iconic).

### How per-country pricing works today
DFS stores each country's offer as a **separate per-country merchant feed** (Running Warehouse AU,
DE, …; Amazon US/UK/CA). RSG calls DFS **once per visitor country** (`countrycode` param); internally
`get_products()` applies a **single-country** geo filter (`updateQueryWithGeo()`), returning that
country's offer (`DISPLAY_PRICE`, `CURRENCY_PREFIX/CODE`, `DEEP_LINK`, `MYKEY`). This is why the
current `newrsg-json` is one-country-at-a-time and the CDN cache fragments per geo.

### Q1 — Does DFS expose per-country MSRP? **Field yes, data no (but fixable).**
- The schema **has** MSRP: `products.rrp_price` exists on every offer.
- **Population is the problem.** Measured:
  - Overall: **180,318 / 1,009,356 (~18%)** of products have `rrp_price > 0`; 99% have a live price.
  - **The per-country merchants RSG relies on populate MSRP at 0%:** every `Running Warehouse XX`
    feed = **0%**; `Amazon US/UK/CA` = **0%**.
  - Only US direct-brand feeds populate it: **Nike 100%, Puma/Puma UK 100%, Brooks 19%**.
- **Root cause = parser config, not schema.** Parser 32 (`runningwarehouse_us`, used by all RW feeds)
  leaves the `rrp_price` column mapping **blank** — it maps only name/model/image/deep_link/display_price.
  So even if the RW feed contains an MSRP column, DFS never reads it.
- ⇒ **Per-country MSRP is recoverable as a config change** (map the MSRP column in the per-country
  parsers + re-import), *provided the feeds carry an MSRP/list-price column*.
  **⚠️ One verification step remains:** confirm the RW (and Amazon) affiliate feeds include an
  MSRP/RRP/list-price column. The generic public URL returns an HTML page; the check must use the
  exact affiliate feed URL/params stored in `datafeeds.url` (e.g. the `…?vat=au` variant).

### Q2 — Can DFS emit all countries in one payload? **Not today; modest change.**
The JSON path runs one single-country `get_products()` per request. To produce a **country matrix**,
DFS needs to iterate the supported-country list (or group the product match by per-country
merchant/currency) and assemble a country-keyed JSON object carrying, per country: `display_price`,
currency, `deep_link`, `mykey`, **and** `rrp_price` (once populated). This is a well-scoped change to
the JSON command/template — **not a rebuild**.

### DECISION (2026-06-21): MSRP ownership moves to WordPress
MSRP is effectively **static per shoe**, so it should **not** ride on every feed import. Agreed model:
- **WordPress (post meta / ACF) owns MSRP**, written **once per shoe, fill-if-empty, never overwrite.**
- **Backfill source priority:** (1) brand-direct feeds already ingested — Nike/Puma carry MSRP at
  100%, Brooks 19% — matched to WP shoes by model/`mykey`; (2) Amazon "list price"; (3) manual.
- **Per-country only where brands genuinely differ** (likely USD + a couple of regions), not 17 values.
- ⇒ **This removes MSRP from the DFS task entirely.** DFS's job narrows to emitting **live per-country
  prices**; the `rrp_xx` ACF fields and `get_geo_rrp()` fallback in RSG can still be deleted, but the
  replacement value comes from a slow enrichment job into post meta, not from the live DFS payload.

### What this means for build-vs-rebuild
The RSG dependency and the DFS platform rebuild are **separable**:

- **Track A — unblock RSG now (days, on current DFS):** add a **multi-country live-price** JSON
  endpoint (one payload, all countries: `display_price`, currency, `deep_link`, `mykey` per country).
  MSRP is handled separately by the WP backfill job above. This unblocks the WordPress pricing/caching
  rework **without waiting for any rebuild**.
- **Track B — rebuild DFS on Hetzner (separate project):** the case is **stronger than maintainability
  alone** (see §12 update) — the segmentation features you want and the resource/cost target are
  capabilities the current filter engine structurally cannot deliver. Still **not a prerequisite** for
  RSG. Recommendation: **decouple.** Ship Track A now; schedule the rebuild on its own timeline.

### Recommended DFS task spec
1. **Multi-country live-price endpoint** — new theme/command emitting
   `{ country: {price, currency, deep_link, mykey}, … }` for a product key, cached server-side;
   deprecate the per-country fetch. (MSRP intentionally excluded — owned by WP.)
2. **WP MSRP backfill job** (lives in WordPress, not DFS) — fill-if-empty from brand-direct feeds /
   Amazon list price / manual.
3. **(Rebuild track)** see §12 — ingest-time segmentation + incremental upserts + cheaper hosting.

---

## 14. Rewrite plan — stack decisions & "no functionality lost" audit

### Headless evidence (does dropping site-rendering lose anything?)
- **The WordPress plugin (`extra/wordpress/dstudio/dstudio.php`) only ever calls the REST API:**
  it builds `"$ds_url/api/v1/products?search=…&clickref=…&pricebox=…&redirect_url=…"`. RSG consumes
  DFS **through the API**, not the rendered sites.
- **Live access log (last 20,000 requests): 19,903 (99.5%) were `/api/v1`.** Non-API hits are bots
  (`/.git`, `/.env`, `/cgi-sys`) plus 24 stray `/index.php`. The PHPTAL store/pcpage front-end is
  effectively dead traffic.
- ⇒ Going **headless loses no live functionality.** What we keep is the **API + its output
  formatting** (the `pricebox`/JSON templates the API returns); what we drop is the standalone public
  site front-controller and themes.

### "Site activation" — what it actually is
Two distinct things share that word:
1. **License registration** (`register_c.inc`, `ioncube_license_properties()` → phones home to
   datafeedstudio.com): this is **vendor licensing for the commercial product**. In a rewrite you
   own the code → **this disappears entirely. Not a loss.**
2. **Client-site activation** = inserting a `sites` row (`site_type='client'`, `client_ip`) so the
   API allowlist (`api/v1` checks `sites.client_ip`) accepts that site. **This must be preserved**,
   modernized as API-key / IP-allowlist auth. (17 client rows today, incl. the Rocket.net IP
   `131.153.143.194` and staging.)

### Feature inventory — KEEP / DROP / VERIFY
**KEEP (core; port deliberately):**
- REST API `/api/v1/products` — query → per-country/per-merchant offers; `pricebox`/JSON output;
  `clickref`, `redirect_url`, `ignorezero`. (The only live consumer.)
- Feed ingestion engine (curl/SFTP download, parser column-map, filters) + scheduling/cron.
- Affiliate deep-link / `clickref` tracking → `product_hits`.
- Offer/product storage + search (boolean + full-text).
- Admin operations: feed/parser/filter management, product/`meta` editorial overrides.
- Client-site activation → becomes API-key/allowlist auth.
- Geo → currency mapping + supported-country list.
- **NEW:** multi-country price matrix endpoint; ingest-time gender/segment classifier + kids exclusion.

**DROP (unused / vendor / superseded):**
- ionCube license registration + phone-home.
- PHPTAL public site rendering: store/pcpage/pricecompare themes + `index.php` front controller.
- pcpages (0 rows), content pages/blog, on-site reviews display, rendered "store" sites.
- `extra/old_wp` plugin variant; the bespoke file page-cache.

**VERIFY — RESOLVED (audited 2026-06-21):**
- [x] **Output format** — the **live API ignores `pricebox` entirely**: `render_pricebox()` is commented
  out and `performGet()` *always* calls `render_json()`. `pricebox=newrsg-json` (21,353/21,374 calls)
  is **vestigial**. The real contract is the fixed JSON below. (The old pricebox rendering survives
  only in `api/v1-BACKUP/`.)
- [x] **Single consumer** — 21,368/21,374 API calls from `131.153.143.194` (the Rocket.net RSG box),
  6 from `35.214.170.233`. No third-party API consumers; `extra/apiclient` unused externally.
- [x] **Store sites** — 99.5% of all hits are `/api/v1`; non-API hits are bots (`/.git`,`/.env`).
  The 3 `store` sites serve no real traffic. (Final tick: confirm DNS for `runningshoesguru.com`
  points at WordPress, not this box — trivial.)
- [x] **Export** — `site_type='export'` count = **0**; feature unused → drop.
- [x] **Crontab captured:** `0 6 * * *` `import.sh` (feed import); `30 9,21 * * *` `import_api.sh`
  (API imports ×2/day); `0 5 * * *` purge `cache/` files >1 day. New scheduler must reproduce these.

### EXACT API output contract (captured live from `cache/`)
Shape: `{"datafeed":{"items":[ {…}, … ]}}`, **one item per offer, single requested country**
(`COUNTRY_CODE` per call). Field keys are UPPERCASED product columns; `DEEP_LINK` is the tracked
redirect (`…/go/index.php?id=MYKEY&clickref=…`), `DISPLAY_PRICE` is `floor()`ed, `ORIGINAL_DEEP_LINK`
is the raw affiliate URL. Full field set (33):

```
MYKEY, NAME, DESCRIPTION, MERCHANT_NAME, MERCHANT_LOGO, MERCHANT_DESCRIPTION,
DISPLAY_PRICE, RRP_PRICE, USED_PRICE, DELIVERY_COST, DELIVERY_TIME,
DEEP_LINK, ORIGINAL_DEEP_LINK, PRODUCT_URL, IMAGE_URL,
IN_STOCK, STOCK_MSG, IS_PREORDER, PREORDER_MSG, IS_CUSTOM,
COUNTRY_CODE, COUNTRY_NAME, CURRENCY_PREFIX, CURRENCY_CODE,
CATEGORY, SUBCATEGORY, PRODUCT_CATEGORY_ID, DATAFEED_ID,
PRICE_COMPARE_SQL, USERFIELD1..4
```
Live sample confirmed `RRP_PRICE = 0.00` on an Amazon.com offer → re-confirms the MSRP gap.
**Rewrite target:** same item shape, but **grouped by country** in one payload
(`{ items: [ { MYKEY, NAME, …, offers_by_country: { USA:{DISPLAY_PRICE,CURRENCY_*,DEEP_LINK,IN_STOCK}, GBR:{…} } } ] }`),
with MSRP sourced from WordPress (per §13), not this payload.

**API request params RSG sends:** `search`, `clickref`, `override_tag`, `referer`, `amazon`,
`redirect_url`, `ignorezero` (and the now-ignored `pricebox`). The new API must honour these
(esp. `clickref`/`override_tag` for affiliate tracking and `redirect_url` for the `/go/` redirect).

### Stack decisions (this project)
- **Linux** — keep. **Apache → Nginx + PHP-FPM** (lower RAM, better API proxy).
- **DB → MariaDB (InnoDB)** — chosen for low friction: drop-in MySQL-compatible, the `dfs.sql` dump
  and existing SQL import as-is, keeps Adminer/`mysqldump` muscle memory. Non-negotiable: leave
  **MyISAM → InnoDB** (transactions + `INSERT…ON CONFLICT`/upserts, no more DROP/RENAME rebuilds).
  ⚠️ Note: the existing Hetzner box already runs **Postgres 16** (for n8n) — MariaDB will be a
  *second* engine alongside it. Acceptable; flagged so it's a conscious choice.
- **PHP 8.3 + framework (Laravel/Symfony)** — ORM kills the SQLi class; queue workers (Horizon) run
  imports; migrations + tests. Stream-parse feeds (no full-file in memory); batch upserts.

### Hosting decision — reuse the existing RSGMedia Hetzner box
Existing box (`infrastructure.md`): **8 vCPU / 8 GB / 150 GB, Ubuntu 22.04**, Docker with
**Nginx Proxy Manager + SSL, Portainer, Adminer, n8n, Postgres 16**, UFW (only 2222/80/443).
- **Reuse it.** You inherit reverse proxy, SSL, DB admin UI, backups, and **n8n** (can orchestrate/
  alert on imports). Add DFS as containers: `mariadb` + `php-fpm` + worker, routed via NPM.
- **Caveats / mitigations:** 8 GB RAM with **no swap** → bursty feed imports could contend with
  n8n/Postgres. Mitigate by (a) adding swap, (b) memory-limiting the import worker + streaming +
  batched upserts, (c) scheduling imports off-peak, (d) **filtering to running shoes at ingest** so
  the working set is tens of thousands, not ~1M. Disk is ample once the 1.5 GB dump / 300 MB
  file-cache / multi-GB `.dsf` feeds are gone.
- **If hard isolation is wanted** (an import OOM must never touch n8n), add a second small dedicated
  box (CX22-class, ~€5–6/mo) for DFS only. Lean: start co-located with swap + limits; split out only
  if it actually contends.

---

## Appendix A — Directory map (web root)

```
/var/www/html
├── index.php                 # public front controller
├── .htaccess                 # rewrite rules
├── install.php               # installer
├── info.php                  # phpinfo (exposed)
├── VERSION (3.4), CHANGELOG.txt, README.txt, EULA.txt
├── admin/
│   ├── index.php             # -> views/
│   ├── views/                # ~50 admin pages (routable)
│   ├── controllers/*_c.inc   # admin controllers
│   ├── models/*_m.inc        # procedural SQL model functions
│   ├── includes/             # config.inc, common.inc, amazon.inc, phpseclib/
│   ├── bin/                  # CLI import/export/cron scripts + .dsf datafiles
│   └── public/, js/, logs/, helpfiles/
├── commands/                 # public page "command" classes (controllers)
├── templates/                # PHPTAL themes: store/, pricecompare/, pcpages/, priceboxes/, export/
├── site_configs/             # per-tenant {id}.inc + {id}.css
├── lib/                      # database.inc, cache_lib.inc, functions.inc, http.inc, IXR, pagination…
├── api/v1/                   # REST services
├── user/                     # per-install customization hooks
├── ioncube/                  # loader for encoded files
├── extra/                    # WordPress plugin etc.
├── cache/                    # file page cache (~300MB)
├── images/ logos/ dsimages/  # media
└── *.txt (hash-named)        # affiliate-network site-verification tokens
```

## 11. Core helper library (`lib/functions.inc`, 43 functions)

This 32 KB file is the heart of the public runtime. Functional groups:

- **URL / SEO**: `slug_func`, `generate_product_url`, `generate_category_url`, `get_ds_permalink`,
  `get_site_url_from_request`, `get_site_template`.
- **Product search**: `get_products`, `get_merchant_products`, `build_sql`, `get_name_clause`,
  `makeSqlQueryFromBoolean`, `is_boolean_search_mode` / `isBooleanQuery`, `convertQueryStringToParams`.
  Supports a **boolean search mode** and legacy-query migration (`isLegacyQuery`/`updateLegacyQuery`),
  plus **geo-aware** query rewriting (`updateQueryWithGeo`).
- **Affiliate click handling**: `record_hit` (writes `product_hits`), `get_deep_link`,
  `add_tracking_to_deep_link`, `add_tracking_to_awin_api_deep_link`, `override_tag_deep_link` —
  i.e. outbound merchant links get per-site tracking/sub-IDs appended before redirect.
- **Amazon on-the-fly**: `addNetworkProducts`, `amazonPresent`, `addMatchingAmazonProduct`,
  `convertNameIntoAmazonSearchClause` — blends live Amazon API results into feed results.
- **Navigation / categories**: `get_menu_items`, `get_content_pages`, `get_custom_categories`,
  `get_product_categories`, `getSubCategories`.
- **Geo**: `get_country_code_for_ip` (IP → country, drives currency + supported-country gating).
- **Utility**: `resultset_to_array`, `display_rows`, `display_until`, `scaleImage`, `download`,
  `isRobot`, `redirect`, `is_valid_id`.

**Confirmed injection surface:** `pcpageCommand` passes the DB-stored `pcpage.sql_code` straight into
`get_products($db, $pcpage['sql_code'], …)`, and `index.php`/categories do the same with stored
`sql_code` / `price_compare_sql`. These features execute admin-authored SQL by design, so the
catalogue query path is built around running stored SQL fragments — see §10.

---

## Appendix B — Open questions / to investigate further
- ionCube-encoded files: which modules are protected (likely licensing + core import internals).
- `priceboxes` widget embedding mechanism and JS (`pricebox.php` is the largest admin view, 15 KB).
- Full parser/filter algorithm in `import.php` body (column mapping + filter application) — only the download half has been read.
- `extra/wordpress` plugin API contract (XML-RPC via `class-IXR.php`).
- `import_api.php` (23 KB) — exact Amazon/AWIN/CJ/LinkShare API request construction.
```
