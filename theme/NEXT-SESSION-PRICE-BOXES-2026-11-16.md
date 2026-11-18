# Next session — review page fixes from the Googlebot audit (16 Nov 2026)

**DONE — all three steps live (16–17 Nov). Kept as the record of what was built.**

Source: Googlebot-style audit of the live Pegasus 42 review (16 Nov). The page reads as helpful (about 7.5/10 against Google's reviews-system questions). The template-level gaps are below. Ruggero's decisions so far: #5 yes; #6 brand mix 1 + 2 with the comparison-table exclusion, tested; #4 `<template>` approach if right (confirmed: no extra requests). Content fixes are separate (bottom).

## Progress
- **Step 1 ✅ live 16 Nov 14:59 UTC** (`eb19711`, tag `live-20261116-145940`; staging `9329595` + `eb19711`). Plus the trail-scale fix (Ruggero, 16 Nov). Details in Step 1 below.
- **Step 2 ✅ live 16 Nov 15:31 UTC** (`faece23` + `e996e10`, tag `live-20261116-153059`; first popularity push 15:31 UTC). Details in Step 2 below.
- **Step 3 ✅ live since 17 Nov 2026 07:20 UTC** (`1446f99`): it shipped inside the homepage deploy (tag `live-20261117-071928`, listed in `HOMEPAGE-BUILD-2026-11-17.md`), not with its own "deploy to live". Built + tested on staging 16 Nov ~15:40 UTC (tag `staging-20261116-154043`). Details + test results in Step 3 below.

## State on 16 Nov (checked on live)
- **Click spots phase A is live:** `<body data-rsg-pt>`, Amazon US tag per spot (`rsg-revdrw-20`, `rsg-revlow-20`, `rsg-bestof-20` on the Peg 42), `data-spot` / `data-place` on every box (rsg-core 0.6.0 `inc/tracking.php`, commit `f85a0c3`). The status line in `CLICK-SPOTS-BRIEF-2026-11-15.md` still says "on staging, not live": update it.
- **Umami events are live:** `rsg-core/assets/rsg-track.js` sends `spot_seen` (IntersectionObserver over `[data-spot]`, ≥ 50 % on screen for 1 s), `drawer_open` (capture listener, opener), `shop_click`, `price_switch`. The 2-week Umami-vs-click-log parallel run (plan phase D) is in progress.
- A different chat (analytics) also works in `rsg-track.js` / `inc/tracking.php` (clean tree at step 3 start and commit).
- **Raptive (16 Nov 14:44 UTC):** re-mapped review + comparison ads to our markers (exact selectors: Ruggero asked them to confirm), moved the phone outstream player from top-center to bottom-left (it covered our sticky price bar). Both in the analyst change log (ids 65, 66). They recommend no sticky sidebar widgets: **Ruggero keeps the sidebar price cards sticky** (16 Nov). Other page types: Raptive leaves them as they are until redesigned.

## Order (each step: staging first, Ruggero's OK, then "deploy to live")
| Step | What | Files | Touches price-box files? |
|---|---|---|---|
| 1 ✅ | #5 User score: hidden until at least 3 ratings | many (see below) | no |
| 2 ✅ | #6 Similar shoes: new picking rules | `includes/reviews/similar.php` (new), road/trail/training templates, `include-related-reviews-fullwidth.php` | no |
| 3 (staging) | #4 Duplicate price markup out of the rendered page + drawer → native `<dialog>` (MWG item 4) | `includes/rsg-dfs-prices.php`, `js/rsg-dfs.js`, `rsg-core/assets/rsg-track.js`, `css/rsg-dfs.css`, road/trail/training templates (bar) | **yes** |

## Step 1 — user score (✅ live 16 Nov)
- `rsg_user_rating_min()` = 3 in `includes/reviews/functions.php`. Every computation site (`$vote_comments_nr >= rsg_user_rating_min()`) falls into its existing 0 state: review hero, mini comparison table on road/trail/training/GPS reviews, comparison snapshot, top-list/guide "Users Rating" (printed "0.0", now "No ratings yet"), legacy list cards (printed "0/10", now "No ratings yet"), legacy microdata.
- **Correction:** the plan said the schema had no aggregateRating. Wrong: reviews emitted it from 2 ratings, comparisons/old reviews from 1. Now the same minimum of 3 (`rsg-schema.php`). Effect at go-live: 124 of 203 rated road reviews show "No ratings yet" (72 with 1 rating, 52 with 2); ~52 road + ~240 old blog-post reviews lost aggregateRating (their editorial `review` rating stays).
- **Trail scale fix:** `rsg_user_rating_scale($pid)` = 10 for reviews_road/trail/training/gps (shared `comments-2022.php`; trail ratings run 1–10 since 2023), else 5. Used by review schema `bestRating`, `rsg_comparison_user_rating()`, `single-comparison.php` multipliers. Before: trail schema said /5, comparisons showed an 8/10 trail rating as 10/10.
- Checked on live: SL 2 (2 ratings) "No ratings yet" + no aggregateRating; Adistar 3 (3) 8.3/10 + aggregateRating; Cascadia 16 3.4/10, bestRating 10; daily-trainers top list. `extractcheck.py`: no new missing items.

## Step 2 — similar shoes (✅ live 16 Nov)
**Before:** up to 3 same-brand road items from the last 12 months, articles included, padded with the 3 most-commented reviews site-wide (Hyperion 3 on almost every page, twice on Adrenaline GTS 25; trail/training reviews showed road shoes; stability shoes got neutral ones).

**Now (`includes/reviews/similar.php`, `rsg_similar_reviews()`):**
- Road + trail: same review type, newest model of its line, US price, same plate group (none vs any), road shares a `shoes_type` category (Cushioning / Stability / Racing / Lightweight), not this line, not in the page's comparison table. Score = usage overlap (trail: `good_for_running` terrain) 0–3 + cushioning same 2 / adjacent 1 + list price within 25 % 1 + **popularity 0–1** (percentile of Search Console clicks per day; weight 2 let the same 2–3 shoes take over). Ties: expert score, newer. Reviews from the last 30 months first. 1 same-brand card max; never two of one other brand. `stability` field not used.
- Training (6 reviews, 3–4 current): **the 3 latest other training reviews** (Ruggero, 16 Nov), no comparison-table exclusion → mostly 2 cards.
- Section hidden when no picks. Heading on trail/training now has the model name (was "Similar shoes to Brooks ").
- Popularity: **Search Console only** (Ruggero: not GA; Umami too young). rsgmedia `~/popularity/push.sh` (Mon 07:40 UTC, `/etc/cron.d/rsg-popularity`, source Mac `hosting/hetzner/popularity/`) → `POST /wp-json/rsg/v1/popularity` on live + staging (header `X-RSG-Token` = option `rsg_popularity_token`) → post meta `rsg_gsc_cpd` + rebuild of all picks (`rsg_similar_ids`, ~1 s). Also rebuilt 60 s after a review is saved; `wp rsg-similar rebuild` / `show <id>…`.
- Dry run: `theme/_work/similar-shoes-20261116/sim2.py` (+ `reviews.json`, `tax.json`, `trail.json`, `gsc90.csv`); WordPress picks = dry run for all sample pages. Live 15:31 UTC: Peg 42 → Vomero Plus, Cumulus 28, Cloudsurfer Max; Ghost 17 → Glycerin 23, Pegasus 42, Cumulus 28; Cascadia 19 → Caldera 8, Lone Peak 9, Pegasus Trail 5; Nano X4 → Metcon 9, Freedom Crossport.
- Open: trail/training cards keep the old 2-per-row layout (3 cards = 2 + 1); one-line change to 3 across if Ruggero wants.

## Step 3 — duplicate price markup (✅ staging `1446f99`, waiting for "deploy to live")
**Before (Peg 42):** 38 "See deal" links in the rendered page; three hidden copies of this shoe's offers: desktop sidebar card (`d-none d-lg-block`), phone price bar (`#best-price-mobile`, `display:none`), drawer (`hidden`).

**Built:**
- `rsg_prices_defer($html, card|bar|drawer)` prints those three inside `<template data-dfs-defer>`; `js/rsg-dfs.js` `insert()` turns them into real elements when needed, paints them for the visitor's country (`paint(CUR, node)`) and fires `rsg:spots`, which `rsg-track.js` uses to observe the new `[data-spot]` nodes for `spot_seen`.
  - Card: a tiny inline script right after the template inserts it during page parsing from 992 px (no layout shift, no wait for the deferred script); `rsg-dfs.js` covers a window that grows past 992 px later. Card stays sticky; same place relative to Raptive's sidebar ad (measured: card at the top of the sticky wrapper, sidebar ad below, same as live).
  - Bar: phones < 768 px, on the first scroll > 40 px (the template's jQuery shows the bar at 120 px).
  - Drawer: list inserted on the first open.
- Drawer = `<dialog>` + `showModal()`: top layer (above Raptive units without z-index), native focus containment + Escape, `::backdrop` (scrim element removed), page scroll stopped by `html:has(.rsg-dfs-drawer[open])`; × / Escape / tap outside close; focus back to the opener; browsers without `showModal` jump to `#full-price-comparison`. Old focus-trap and keydown code removed.
- Comparisons (sidebar pair card), top lists, articles, old reviews: unchanged.

**Results (staging, Playwright harness `theme/_work/step3/test3.py` + `summ.py`; before = same pages on the old code):**
- Rendered "See deal" links on the Peg 42: phone 38 → 24, desktop 38 → 26 (card shown there). **Raw HTML size unchanged** (the copies stay in the cached HTML inside `<template>`; the plan's "18 KB smaller" was wrong: the saving is in the rendered page, not the download).
- Card, bar, drawer: pixel-identical screenshots before/after (phone drawer, phone bar, desktop drawer; card differs only by font anti-aliasing).
- Events identical before/after (stubbed Umami): spot_seen card/bar/drawer once, drawer_open with the right opener, shop_click component + opener; click-log beacons identical (`b=drw/rail/stick`, sub-IDs `50267-<box>-<cc>`).
- Real run (tracker on, staging website): Umami rows spot_seen/drawer_open/shop_click with component, placement, opener, layout, merchant, country; click log rows 132–133 in staging `newguru_wp_rsg_clicks`.
- Country GB: card, bar and drawer inserted later show £ + Nike UK, sub-IDs `-GB`, beacons `c=GB`.
- Spot attributes on inserted nodes = live (`price_card/sidebar/rail/rsg-revside-20`, `sticky_bar/sticky/stick/rsg-revbar-20`, `drawer/drawer/drw/rsg-revdrw-20`).
- Keyboard: focus on × at open; Tab cycles inside the dialog (plus the browser's own stop); the page behind is inert; Escape closes, focus back on the opener; tap outside closes; scroll lock on/off.
- Window 800 → 1300 px: card inserted, spot_seen fired. Bar never inserted on desktop.
- CLS: Peg 42 desktop 0.0015 → 0.001, phone 0 → 0. Cascadia 19 desktop shows 0.0135 on staging from hero images (staging serves originals; not the price boxes; live 0).
- Raptive slots: staging = live (phone 10, desktop 11). `extractcheck.py`: same as live. WP Rocket used CSS on the optimised staging page keeps the dialog, backdrop, `:has()`, bar, card, toc and shops rules.
- No JS errors on reviews (road, trail, training), comparison, top list, old review, phone + desktop.

**Follow-up DONE — live 18 Nov 2026 (`286c0e7`, `live-20261118-070424`):** the comparison sidebar pair card and every other one-width copy now use `rsg_prices_defer_mq()` (`RUNBOOK.md` Price UI). Ruggero 18 Nov: step 3 kept.

**Must stay identical (checked):** `data-spot` / `data-place` / `data-dfs-box` / `data-tag` / `data-page`, sub-IDs, Amazon tags per spot, opener in `drawer_open` / `shop_click`, Raptive markers (`rsg-page-*`, `.rsg-content`, `.rsg-text`, `.rsg-sidebar`).

**Deploy notes:** `deploy.sh live` (theme + rsg-core `rsg-track.js`), clears minified copies + used CSS; purge; re-run `test3.py` on the live Peg 42 (beacons and events are stubbed/blocked by the harness, so it adds nothing to the logs) + add the change-log row (automatic). Rollback = deploy tag `live-20261116-153059`.

## Coordination with the analytics chat
Step 3 changes `rsg-track.js` (one listener) and both price-box files. Before every deploy: `git status`, GitHub `main`, last staging tag; commit only your own hunks; never deploy over another chat's staging work.

## Session notes (Cowork VM on the Mac, 16 Nov)
- git in the session needs delete permission on the rsg folder; GitHub key `_private/github-rsg-theme-deploy-key` → `~/.ssh/github-rsg-theme`, use `GIT_SSH_COMMAND="ssh -i ~/.ssh/github-rsg-theme"`.
- `deploy.sh live` change log needs the rsgmedia host key known; `RSG_HETZNER_KEY` = a 600 copy of `_private/claude-hetzner-key`.
- Once (~15:21 UTC) the VM got a wrong cached page for staging URLs (live's /reviews/ listing, yesterday's date) right after a live purge; minutes later it was fine. If staging looks wrong, check with curl from the Rocket.net server over SSH.
- Playwright in the VM: `pip install playwright`, `python3 -m playwright install chromium`, missing system libs unpacked without root into `~/pwlibs` (`apt-get download` + `dpkg-deb -x`), run with `LD_LIBRARY_PATH=~/pwlibs/root/usr/lib/aarch64-linux-gnu`.

## Not in this session (editorial, Ruggero / writers)
Measured data per review (weight, stack, durometer); durability claims backed by mileage or worded as early signs; link every competitor the text names; Strava/Garmin links for the Testing Details runs and an outsole photo after testing (stronger than a receipt: the receipt idea is dropped: quarterly delay, shows the discount, proves purchase not testing). Later tool (parked): auto-link the first mention of a reviewed model to its newest review.
