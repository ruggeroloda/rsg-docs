# Review top on phones (18 Nov 2026)

**DONE — live 18 Nov 2026 18:03 UTC (`67d9ceb`, tag `live-20261118-180328`).** Ruggero: "deploy to live". Rollback: deploy `live-20261118-174205`.

## Why
Ruggero (18 Nov, looking at the Pegasus 42 review on a phone): order and spacing felt wrong. Measured on live at 390 px: the photo gallery (391 px) came before the title, the expert bib sat in a 136 px row that was mostly empty with "User score No ratings yet", the trust block took 272 px (the same content is one 44 px row on desktop), and the summary was below the first screen (≈ 664 px on an iPhone in Safari).

Design canvas (phone today / proposed, desktop today / proposed): https://claude.ai/artifact/MVMx6XJ8c2yMYtjRi6Dmx2

## What it is (phones < 768 px; road, trail, training, GPS reviews)
1. Title.
2. Photo strip: one photo at a time with the next one peeking in (45 px + 8 px gap), scroll snap, dots under it, "n / N" on each photo, "See all N photos" on the 5th; one 44 px nudge the first time a visitor sees a strip in a browser session (skipped with reduced motion). Tap still opens the full-screen viewer (swipebox).
3. Expert bib (104 px) next to the summary sentence; user score as one line under them, only when readers have rated the shoe.
4. Trust block: photo, "Tested by <name>" (author link), role, "N articles", Published · Updated; proof row (bought with our own money, no free samples, How we review) wraps instead of stacking.
5. Verdict, then the rest as before.

Desktop and tablet: unchanged, except that "User score No ratings yet" is gone at every width.

Kept on purpose: the summary is still the verdict's first sentence and the verdict still starts with it (Ruggero). The "All running shoes Reviews" link was not made smaller (the title starts ~22 px lower than on the canvas).

## How
- HTML order unchanged (hero column, then photo column), so machines, Raptive and desktop see the same markup. New classes: `.rsg-top` (first row of `main`) and `.rsg-gal` (photo column) in the four `single-reviews_*.php`; `span.rsg-trust-sep` in `rsg_trust_html()`.
- Order, strip and bib/summary grid: CSS only, `css/rsg-skin.css` 3.1b (`display: contents` on the hero column, flex `order`, scroll snap on the existing photo links).
- `js/rsg-gallery.js` (enqueued on the four review types): dots, "n / N", nudge, Umami `gallery_swipe` (once per page view, property `photos`). Inline styles because WP Rocket's used CSS only keeps selectors present in the HTML.

## Checks (18 Nov; Playwright 390 × 664 and 1366 px; Pegasus 42, Cascadia 19, Nano X4, Forerunner 165, Pegasus Plus (video), Kayano 30 (reader scores), Fresh Foam X More v6 (long name))
- Staging and live: order as designed; verdict top 1,175 → 870 px (Pegasus 42); dots follow the swipe; `gallery_swipe` fires; no horizontal overflow; CLS 0; no JS errors.
- Desktop: positions identical to live except the bib area without the user score line (bib 3 px further right).
- `checks/extractcheck.py` staging vs live (4 reviews): identical trafilatura MISSING lists.
- Live after the deploy: desktop used CSS regenerated; phones get the full stylesheets (no `#wpr-usedcss` for mobile user agents on any page type, not new).

## Watch
Read together with phone price bar G (~25 Nov): `stick` clicks per view, drawer opens, Raptive RPM on reviews, plus `gallery_swipe` per phone review view. If numbers drop, revert the review top first.

## Ideas (not built)
- Show the phone price bar later in the scroll, with a small animation (Ruggero, roadmap "Small / waiting").
