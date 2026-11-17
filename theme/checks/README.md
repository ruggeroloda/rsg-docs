# Site checks (Playwright, run from the cloud shell)

Copied here 13 Nov 2026 so a new chat can reuse them. They need `pip install playwright` + Chrome (`channel="chrome"`); headless Chromium gets no Raptive layout.
Run from the **cloud shell** (it reaches the public site, not Rocket.net SSH). Stage this folder into the container first.

| Script | What it does |
|---|---|
| `adcount.py URL…` | Raptive ad slots per page, phone 390 + desktop 1366. Blocks every request except the site and Raptive's layout files, so no auctions/impressions (no invalid traffic). |
| `raptive_config.py` → `raptive_selectors.py URL` | Saves Raptive's site config (`raptive_site.txt`), then tests each ad unit's page/element selector against a page. The home page is excluded by Raptive's own `og:url` selector. |
| `linktest.py BASE_URL` | Clicks one affiliate link of each kind and prints the click-log beacon; on live the beacon is answered locally (status 299) so nothing is stored. Edit `CASES` for other pages. |
| `shots.py OUTDIR URL…` | Full-page screenshots 390/820/1366 + CLS/LCP at 390 → `OUTDIR/metrics.json`. Ads/YouTube/Typekit blocked. |
| `cwv.py OUT.json URL…` | LCP (time + element) and CLS (at load and after scrolling), TTFB/FCP, phone 390 + desktop 1366, each with Typekit **allowed** and **blocked**. Ads/analytics/YouTube blocked; the price-box view/click beacons are answered locally (299), so nothing reaches the click/view log. One load at a time, 2 s pause. Lab numbers without throttling: compare, don't read as field CWV. |
| `clsdebug.py URL [width]` | Which elements shift, when, by how much. |
| `schema_snap.py out.json URL…` | Decoded JSON-LD + og/twitter/article meta per URL (diff two snapshots before/after a change). |
| `extractcheck.py OUTDIR URL…` | What text extractors keep (trafilatura + readability-lxml): per page the h2/h3 headings, `<dt>` labels/values, reviewer and purchase line inside `<main>` that are MISSING from the extracted text, plus the heading outline (flags headings before the h1). Run staging vs live after markup changes; gate = no new trafilatura MISSING items (readability is informational). Mac device shell or cloud shell; `pip install trafilatura readability-lxml`. |
| `seo_check.py out.json URL…` | Per URL (edge copy, no redirects followed): status, cache, title/description length, canonical, robots meta + X-Robots-Tag, h1 count, og/twitter/article meta, JSON-LD parse + types + offers. Runs from the Mac's shell too (stdlib only). |

Measure CLS/LCP on **live**, not staging (staging lacks most uploads). Count ads before/after any structural change.

Edge rate limit (13 Nov): more than ~1.5 requests/s from one IP gets HTTP 429 from the edge for a while. Crawl sitemaps one request at a time with a short pause (≈1 URL/s).
