# CLS batch (old theme) — 8 Nov 2026 (staging → LIVE, purged by Claude with the Rocket.net API)

List and status: `CLS-FIX-LIST.md`. Working copies + scripts: `theme/_work/cls-20261108/`. Measured with Playwright (ads blocked), `dfs/app/tools/pricebox/cls.js`-style runs; shift sources found with `shifts.js` / `tl.js` (frame-by-frame heights).
Rule: live files are patched in place (they don't have the DFS edits); staging gets the same change on top of its DFS edits.

## 1. `review`-type reviews (62 trail reviews, `include-single-review-trail.php`) — 18:51 staging, 18:54 UTC live
- Cause: the header photo's blurred placeholder `<img>` had no size: the gallery block was 22 px tall until the placeholder loaded (~2.9 s), then 365 px, pushing everything below (0.025); the 6 gallery thumbnails had no size either (0.0026).
- Fix: placeholder gets `width="720" height="460"` + `height:auto` (same ratio as the photo, 571×365 on desktop); thumbnails get `width="240" height="200" alt=""`; CSS `.gallery-link .swipebox img{height:auto}`. Script `theme/_work/cls-20261108/fix_rtype.py`.
- Result: desktop **0.027 → 0.0026** (staging, box off and on), **live 0.004**; mobile 0.004 unchanged (the header photo is desktop-only). Screenshots live vs staging identical (`theme/_work/cls-20261108/` not kept; same 571×365 / 390×245 boxes).
- Backups: live and staging `include-single-review-trail.php.bak-cls-20261108-1854` / `-1851`. Rollback: copy back, purge.

## 2. h1 subtitles (comparison titles on phones) — 19:05 staging, 19:10 UTC live
- Cause (comparison redesign pilots, mobile 0.041): `h1 span.subtitle` is weight 400, but the Typekit kit has only Source Sans 700, so the final subtitle renders with the bold face; before Typekit loads it rendered in narrow Arial 400 (with -0.06em spacing). "Hoka Clifton vs Hoka Bondi" fit one line in Arial and wrapped to two in the real font ~2.5 s in, pushing the author block and everything below.
- Fix: in `header.php`'s existing inline `#rsg-font-fallback` style: `h1 span.subtitle{font-family:"source-sans-pro-fb",Arial,sans-serif;font-weight:700;letter-spacing:0}` + `.wf-sourcesanspro-n7-active h1 span.subtitle{font-family:var(--font-family-header)}` (the size-matched bold fallback until the real font is active). The final render is **pixel-identical** (checked desktop + mobile on a comparison and a review title; the face used was already the 700 one).
- Result live: pilot comparisons mobile **0.041 → 0.0003**, desktop 0.0005 → 0.0002; old-layout comparison 0.0012 → 0.0002. Any page with an h1 subtitle benefits.
- Backups `header.php.bak-cls-20261108-1910` (live) / `-1905` (staging). Live: `rocket_clean_domain()` + Rocket.net purge (WP Rocket page cache held the old inline style).

## 3. Comparison expert score as text — 19:13 staging, 19:16 UTC live
- `single-comparison.php`: NEW helper `rsg_cmp_score( $vote, $name, $label, $brand )` (top of the file, `function_exists` guard) → star icon + **7**/10, the shoe name (+ brand once) as visually hidden text; inline CSS once. Old layout table: the row already says "Expert score", so the label is hidden text there; redesign card (`includes/comparison-redesign-block.php`): "Expert score" small grey line above "★ 7/10".
- Machines: html2text/trafilatura now read "Nike Vomero Premium expert score 7/10" (was a loose "7/10" per column, with the ticket's own label hidden). Score badge decision (DECISIONS 8 Nov) done.
- Checks: lint 8.3 + 7.4; no warnings; CLS unchanged (0.0002–0.0003); screenshots desktop + mobile both layouts (`theme/_work/cls-20261108/` scripts; mobile first version overflowed with the label inline → label moved to its own line before going live).
- Backups `*.bak-score-20261108-1916` (live) / `-1913` (staging), both files. Rollback: copy back, `rocket_clean_domain()`, purge.

## 4. Quiz results (`template-wizard-2024.php` + `print_review_vertical_block()` in `widgets.php`) — 19:21 staging, 19:26 UTC live
- Causes: (a) the 3 pick photos had no size (24 px → 135 px when loaded; desktop 0.025, mobile 0.037); (b) the results icon above the heading (80×80 SVG, desktop) had no size (+56 px); (c) the results heading is `font-weight:400 !important` inline, but the kit only has Source Sans 700, so it ends up bold: Arial 400 before the font → one line more after (mobile 0.056). Also a transient: during the swap the browser briefly used the regular-weight fallback.
- Fix: photo `width="250" height="140" style="height:auto"` (max-width:100% already applies); icon `width="80" height="80" alt=""`; template CSS: `@font-face "source-sans-pro-fb-bold"` (weight 400 → Arial Bold, same metrics as the 700 fallback) + `#wizard-results h2{font-family:"source-sans-pro-fb-bold",Arial,sans-serif;letter-spacing:0}` and, once the font is active, `source-sans-pro,"source-sans-pro-fb-bold",…`. Script `theme/_work/cls-20261108/fix_quiz.py`.
- Result: **desktop 0.06 → 0.0014, mobile 0.063 → 0.0013** (staging box off/on, live both genders). Final render **pixel-identical** to the old live page (heading block and pick photo, desktop + mobile).
- Backups `*.bak-clsquiz-20261108-1926` (live) / `-1921` (staging): `includes/widgets/widgets.php`, `template-wizard-2024.php`. Rollback: copy back, `rocket_clean_domain()`, purge.

## 5. Big page titles on old posts / content pages (`h1.full-page-title`) — 19:46 UTC live + staging
- Cause (shortcode/content pages, mobile 0.059): headings render in Arial Bold with -0.06em letter-spacing until Typekit is active; for these long titles that is narrower than Source Sans, so the title wrapped one line later (3 → 4 lines on a phone).
- Tried first (not deployed): the same fallback for **all** headings site-wide: every phone page went to ~0, but desktop got worse on the home page (0.035 → 0.064) and the reviews list (0.001 → 0.04). Kept targeted instead.
- Fix: `header.php` `#rsg-font-fallback`: `h1.full-page-title{font-family:"source-sans-pro-fb",Arial,sans-serif;letter-spacing:0}` + `.wf-sourcesanspro-n7-active h1.full-page-title{font-family:var(--font-family-header)}`. Tested by injecting it into the live HTML on 5 pages (all improved, desktop and mobile) and final render pixel-identical (2 pages × desktop/mobile), then deployed to staging and live together.
- Result live: altra guide 2018 mobile **0.059 → 0**, desktop 0.0011 → 0.0001; legacy review 0.0019 → 0.0004.
- Backups `header.php.bak-cls2-20261108-1946` (live + staging). Live: `rocket_clean_domain()` + purge.

## 6. Old guide layout header boxes (Mizuno, GPS guide; `template-parts/guide-boxes.php`) — 19:50 staging, 19:53 UTC live
- Mizuno guide phones were already fixed by item 5 (its title is `h1.full-page-title`): mobile **0.08 → 0.0006**.
- Desktop: the 5 header-box shoe images (225×145, shown 188×121) had no size → 5 small shifts. Fix: `width="225" height="145"` + `height:auto` (max-width:100% already applies). Final render pixel-identical (desktop + mobile).
- Result live: Mizuno desktop **0.043 → 0.003**; GPS guide desktop 0.008 (staging).
- Backups `template-parts/guide-boxes.php.bak-cls-20261108-1953` (live) / `-1950` (staging).

## Not fixed: main-layout guides (13 guides, e.g. best-nike-running-shoes) — live desktop 0.014, mobile 0.002–0.069 (intermittent)
- Cause: the guide's plain `h1` reflows when Typekit swaps the font in, twice on phones: Arial (pre-load, -0.06em) → a ~300 ms moment on the size-matched fallback (the "source-sans-pro" face attaches after Typekit sets its `wf-…-active` class) → Source Sans. The line count differs per title and width, so no single fallback setting fixes all titles (a site-wide attempt made the home page and reviews list worse on desktop, see item 5).
- Proper fix = font loading strategy (self-host Source Sans 700 with `preload` + `font-display`, or drop the class-switch rules): belongs to the theme rebuild (`THEME-REBUILD-NEXT-STAGE.md`). All values are already under Google's 0.1 "good" threshold.
