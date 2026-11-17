# GPS Amazon button — deploy log, 10 Nov 2026

**Why:** Black Friday. The 29 GPS posts showed broken price boxes ("$0.00 … Product not found", empty links): old DFS has no GPS data and the PA-API path is dead. Option A (Ruggero, 10 Nov): one "Check price on Amazon" button per spot, no price; US prices later via new DFS (step 7b).

**Design**
- `includes/rsg-gps-amazon.php` (required from functions.php): `rsg_gps_amz_urls/button/box/name/footer`.
- Stores/tags: US `rsg-single-20` (also AU and all other countries), GB+IE `runshogur-21` (.co.uk), DE+AT `runshogur01-21` (.de), CA `runningsho053-20` (.ca). Tags from the old DFS DB `amazon_apis`. No AU tag exists.
- HTML = US link; footer script swaps per visitor (localStorage `rsgDfsCountry`, else `/cdn-cgi/trace`) → works with or without the geo cache rule.
- Empty ASIN field → Amazon search "brand model" + tag.
- Templates: `include-single-gps.php` (3 boxes, offers schema off, list price from `rrp_us` or "-"), `single-reviews_gps.php` (2 panels, mobile bar, 3 comparison buttons, MSRP "-" when empty). Old dstudio + `amazon_item_lookup` calls removed from both (dead calls on every uncached render).

**Data:** ASIN audit `dfs/_work/gps-amazon/AUDIT-2026-11-10.md` (+ UK/DE `check_gb/de.json`). Cleared (dead or wrong watch; original values in `dfs/_work/gps-amazon/gps-asins-live.tsv`):
US 24494, 22554, 32812, 43145, 44389, 44823 · GB 23699, 43853, 44394 · DE 22554, 23567, 30795, 43882, 44392 · CA 23699, 43882, 44392, 44823.

**Staging (10 Nov ~07:00):** backups `*.bak-gpsamz-20261110-0601`; lint 8.3 + 7.4 OK; fields cleared on staging; 26 pages 200 with 3 buttons, 3 old duplicates 301 to the new posts; no $0.00 / not found / gender switch; swap tested in headless Chromium (US, GB, DE, AT, CA, AU).

**Live (10 Nov 07:36, Ruggero: "put it live"):** backups `*.bak-gpsamz-20261110-0636`; live `functions.php` got only the one require line (live has no `rsg-dfs-prices.php` require, so staging's functions.php was NOT copied); 4 files linted 8.3 + 7.4 before swap; same 18 fields cleared on live; `rocket_clean_domain()` + `wp cdn purge`; 26 pages 200 with 3 buttons, 3 duplicates 301, no $0.00 / not found / gender switch; swap verified on live (Vantage V3: US/CA search, GB/DE product pages). Original steps: backup live files → upload the 4 files (live `single-reviews_gps.php` differs from staging only in a comment) → clear the same fields on live → lint → check 29 pages → `wp cdn purge`.
**Rollback:** restore `*.bak-gpsamz-*`, remove the require line, restore ASIN values from the TSV.
