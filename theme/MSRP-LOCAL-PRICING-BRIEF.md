# Local MSRP / RRP — project brief (kickoff for a new session)

**Status:** scoped, not started. Approach decided. Start a fresh session from this file.
**Author note:** written 2026-06-24 at end of the comparison-photos session.

---

## 1. What we want to do
Fill the **per-country RRP / MSRP fields on single review posts** with **accurate local prices**, so
international visitors see the correct "was"/list price (and so we can show local pricing properly).
- **Retroactively** (backfill existing reviews) **and going forward** (auto-fill at/after review creation).
- The number must be the **actual local RRP in the local currency**, NOT a USD figure converted — markets
  are priced independently (e.g. Clifton 10 = **$155 US** but **£140 GB**; no clean ratio).

## 2. Where these fields are consumed today (so we know what we're improving)
- `get_geo_rrp()` (`functions.php:1867`) shows the struck-through "was"/RRP next to the live price on
  review/guide/top/best templates. It reads **`rrp_us`** by default and **`rrp_gb` / `rrp_ca` / `rrp_au`**
  for those three countries **only**. Every other country (incl. all of the EU) currently falls back to
  `rrp_us`. `RSG_get_country_code()` also collapses anything outside a ~17-country allowlist to `US`.
- A **`rrp_de`** field also exists on posts but **`get_geo_rrp()` does not read it** (latent/unused today).
- Comparison tables hardcode `get_field('rrp_us', …)` with a literal `US$` prefix.
- (Separately, DFS `RRP_PRICE` feeds only the widget struck-through list — and it's empty, see §4.)
> Implication: to actually surface new per-country values we likely also need to **extend
> `get_geo_rrp()`** (and the comparison tables) to read the new/`rrp_de` fields. Scraping fills the data;
> a small template change surfaces it. Treat those as two steps.

## 3. Decision: scrape the brands' local sites (this is the way)
Confirmed the right approach after ruling out the alternatives:
- **DFS feed — dead end.** Probed the live feed (Clifton 10, US/GB/AU/CA/DE): per-offer `RRP_PRICE` is
  **empty / `0.00` almost everywhere**; the one non-zero value just mirrored the sale price. Also thin
  per-country merchant coverage (GB returned 0 items, AU/DE only 1). DFS has no usable MSRP.
- **Manual entry — rejected.** Doesn't scale across the catalog.
- **Derive from `rrp_us` via a per-brand multiplier — rejected.** Inaccurate; $155→£140 proves there's no
  clean ratio. User explicitly doesn't want approximation.
- **Scrape brand local sites — YES.** Brands publish the official RRP per country, and it's in the
  fetchable page. Validated live (2026-06-24):
  - HOKA US Clifton 10 = **$155.00 USD**; HOKA GB Clifton 10 = **£140.00 GBP** (Bondi 9 = £160).
  - HOKA reuses the **same product ID across locales** — swap the path segment `/en/us/` → `/en/gb/`
    (and `/en/eu/`, `/en/au/`, `/en/ca/`) to read each country's RRP. Price + currency are right in the
    HTML (no login, no hard JS wall). `web_fetch` returned it fine (pages are big → grep the saved file,
    or parse JSON-LD `Product`/`Offer`).

## 4. Target fields (confirm at kickoff)
Existing ACF on reviews: **`rrp_us`, `rrp_gb`, `rrp_ca`, `rrp_au`, `rrp_de`**.
- Countries to populate: **US (have), GB, EU, AU, CA** (+ maybe more later).
- **EU decision:** use existing **`rrp_de`** as the eurozone representative, or add a new **`rrp_eu`**?
  (Adding/registering an ACF field needs wp-admin access — see open items.) Hoka has an `/en/eu/` EUR store.
- Store the **local currency** implicitly per field (each field = that market's currency).

## 5. Proposed architecture (for the build session)
1. **Per-brand adapter** (the only real work): for each brand, define
   (a) the **locale URL pattern** per country, and (b) how an RSG model maps to its **product page/ID**.
   - HOKA is easy (shared product ID across locales). ASICS / Brooks / Nike / Adidas / Saucony / NB etc.
     each need their own pattern — but **~6 brands cover most of the catalog** (do a brand-count audit first).
2. **Model → product resolution:** cleanest is to **store one brand product ID/URL per review** (a single
   paste at review time, or auto-resolved once by searching the brand site). All locales derive from it.
   Consider a new meta field e.g. `brand_product_ref`.
3. **Scraper job:** iterate reviews, fetch each brand-locale URL, parse **price + currency** (prefer
   JSON-LD `Offer.price`/`priceCurrency`; fall back to the visible price). Write `rrp_*` via WP-CLI/ACF.
   Run as a **batch** for backfill and on a **schedule** to catch RRP changes. Be polite: cache, throttle.
4. **Going-forward hook:** on review save (or a nightly sweep of reviews missing RRP), auto-fill from the
   stored product ref.
5. **Discontinued-model fallback:** old models get delisted from brand sites → fall back to a **Wayback
   snapshot near the review date** or a major retailer that lists MSRP (e.g. Running Warehouse), else flag
   the handful that fail for manual review.

## 6. Caveats / risks to handle
- **Sale vs RRP:** take the *regular/list* price, not a discounted one. Brand sites usually list RRP for
  current models; when on sale, grab the struck "was" / JSON-LD list price. Add a sanity check.
- **Per-brand adapter maintenance** as sites change; **model-name matching** is the fiddly part.
- **Anti-bot / rate limits:** brand pages fetched fine here, but throttle + cache for a full-catalog run.
- **Discontinued models** unscrapeable from live sites (need the fallback above) — expect some gaps.
- **EU is not one price** strictly, but the brand EUR store gives a single defensible eurozone RRP.
- Prices are tax-inclusive in GB/EU/AU and tax-exclusive in US — that's expected, store as published.

## 7. Open decisions to settle at kickoff
- Exact country set + **EU = `rrp_de` vs new `rrp_eu`** (and whether to add fields → needs wp-admin/ACF).
- Whether to **extend `get_geo_rrp()`** + comparison tables now or after the data lands.
- **Scope/priority:** all reviews vs current-models-first vs high-traffic-first.
- **Where the scraper runs:** server cron (WP-CLI) vs a session-driven batch; storage of the product ref.
- **Accuracy bar / verification:** spot-check %, and how to flag/queue mismatches.

## 8. Efficient first steps for the new session
1. **Brand-coverage audit:** `wp` count reviews per brand → rank which brand adapters unlock the most posts.
2. **Build the HOKA adapter first** (already proven) end-to-end: resolve product ref → fetch US/GB/EU/AU/CA
   → parse → dry-run write on the **6 pilot shoes** → eyeball the table.
3. Add ASICS + Brooks adapters (covers the pilots' other two brands), then expand by audit order.
4. Decide the EU field + `get_geo_rrp()` surfacing change, then run the backfill in batches.

## 9. Access / how to probe (see `CLAUDE.md` → Connect & access)
- SSH + WP-CLI on the host; DFS probe pattern in this session worked via `wp eval-file`.
- `web_fetch` on a brand page returns a large file → **grep the saved file** or target JSON-LD; brand pages
  rendered without a JS wall in testing. Use `WebSearch` to find a brand's product URL when the pattern is
  unknown.
- Reuse the "probe reality before committing" discipline that de-risked DFS and the comparison photos.
