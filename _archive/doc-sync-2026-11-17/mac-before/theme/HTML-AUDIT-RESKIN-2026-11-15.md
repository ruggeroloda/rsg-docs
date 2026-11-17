# Review page HTML after the reskin — audit (15 Nov 2026)

_Mac copy; the project doc `theme/HTML-AUDIT-RESKIN-2026-11-15.md` is the same text (status column kept current there)._

Staging = theme `c66d78c` (reskin through 5.2) vs live `7462d98`. Samples: Pegasus 42, Cumulus 28 (Googlebot UA). Extractors used as proxies: trafilatura (Common Crawl / FineWeb-style datasets) and readability-lxml (reader-mode / many agent fetch tools). Google renders pages itself and does not depend on these. Script: `checks/extractcheck.py`.

## Headline
Wire size fine (~48 KB brotli, live 49). JSON-LD unchanged and solid. Gains: compare table now extracted as a real table, expert score extracted. **Three reskin regressions** in what extractors keep (Pegasus: trafilatura keeps 46/69 page items on staging vs 65/69 on live), all cheap tag/class fixes. Biggest efficiency problem is not the HTML: full-size shop thumbnails (Nike 3.6 MB PNG for a 64×48 thumb), also on live.

## A. Reskin regressions (fix before go-live)
- A1 Trust box `<aside>` + author box ("author" classes, bio in `<div>`) → reviewer name and "bought with our own money" gone from extracted text (live had both). Fix: `<section>`, class rename, bio in `<p>`.
- A2 Rating values gone: class `rsg-ratings` hits trafilatura's rating-widget filter; empty `aria-hidden` `<dd>` per bar. Fix: rename; bar drawn by CSS on the value `dd`.
- A3 Stack height gone: `<dl>` inside `<figure>/<figcaption>`. Fix: row of the facts `<dl>`; figure = drawing only.
- A4 Printed mega-menu: 12 `<h6>` (2 empty) before the `<h1>`. Fix: styled non-heading titles (proposal).

## B. Efficiency (pre-existing, mostly DFS)
- B1 Shop thumbnails at source size (Nike `h_2500,w_2500,f_png` 3.6 MB; Amazon `_SR960,720_` 66 KB) → Nike `c_limit,h_200,w_200,f_auto`, Amazon `_SR160,120_` (≈3 KB each). **→ Done differently 15 Nov (DFS, live):** own 192×144 WebP copies for every non-Amazon shop (~2 KB), Amazon's `medium` image link (Amazon images may not be copied), "no image" picture; see `../DECISIONS.md` 15 Nov and `../dfs/RUNBOOK.md` → Rules (Thumbnails).
- B2 Drawer prints a second full shop list; best price printed 5× → render the drawer from `#rsg-dfs-data` on first open.
- B3 rangeslider.js 18 KB inline in every review → load on comment-form open or native range input.
- B4 No theme `fetchpriority` on the hero photo (live gets it from WP Rocket) → set it.

## C. After Raptive re-maps
- C1 `<style id="rsg-dfs-css">` 16 KB inside `<article>` → `<head>` or cached file.
- C2 h1/hero outside `<article>`, content split into sibling blocks (readability keeps only the prose, also on live) → hero inside the article, one content container.

## Why it happened
- A1, A2: mistakes; the per-step reskin checks (ads, CLS, PHP warnings, used CSS, screenshots) had **no extractor re-run**.
- A3: correct HTML (`<figure>`), untested side effect. A4: old menu markup brought into every page when the menu was printed (to drop admin-ajax). B2: deliberate (instant drawer), judgement call. B1: shop image URLs used as delivered since the DFS build; the 4.1 card put one above the fold.

## Going forward (also `RUNBOOK.md`, `../DECISIONS.md` 15 Nov)
- Every markup change: `checks/extractcheck.py OUT <staging URL> <live URL>` on the sample reviews; no new MISSING items vs live, or the step isn't done.
- Content we want read: `<p>`/`<dl>`/`<table>`/lists, not bare `<div>`/`<span>`; no boilerplate words in its class names (`rating`, `author`, `comment`, `share`, `related`, `sidebar`, `footer`, `menu`, `ad-`); not inside `<aside>`/`<figure>`.
- Headings only for page sections; menus, popups and cards use styled non-heading elements.

## Status (15 Nov ~09:30 CET)
- A1–A3 done on staging `d9c1e11`: same look (element screenshots pixel-identical), trafilatura ≥ live on the 5 sample reviews. Readability (informational) still keeps only the review prose: its sibling scoring, fix = C2.
- A4 proposal to Ruggero: column titles as styled non-heading labels, each column a `<ul>` labelled by its title, no empty titles, logo icons `alt=""`, inline styles → classes. Also found: 4 menu links are 301s (`/gps/garmin-forerunner-265/`, `/gps/coros-pace-3/`, `/gps/huawei-gt-3-review/`, `/content/best-gps-running-watch-guide/`); printing the menu made 46 links newly visible on every page (Googlebot never hovered), incl. brand guides, 2018/2019 posts, "2024" guides and old watches: curate before go-live.
- A4 done on staging `b444ddf` (15 Nov ~09:45 CET): labels + labelled lists, same look. Menu link cleanup (46 newly visible sitewide links + 4 redirects) = pre-go-live check (Ruggero).
