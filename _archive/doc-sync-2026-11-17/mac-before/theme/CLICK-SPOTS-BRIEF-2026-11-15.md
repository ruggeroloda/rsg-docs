# Click spots + Amazon tracking IDs — brief (15 Nov 2026)

> **16 Nov 2026:** superseded in part by the analytics plan (project doc `analytics/UMAMI-PLAN-2026-11-16.md`, project copy of this brief updated): spot names now = component + placement (rsg-core `inc/tracking.php`), clicks/seen/device go to Umami instead of new WordPress tables. **Amazon US tags per spot + page marker: on staging `40dff01` (16 Nov), not live.**

Status: **agreed by Ruggero 15 Nov (final ID list same day), not built.** Own deploy **after** the reskin go-live (Ruggero 15 Nov; reskin live 15 Nov 14:30 UTC, `4ac5ef4`): staging first, then live on "deploy to live". **Order:** click spots goes **before** the Modern Web Guidance drawer rebuild (`MODERN-WEB-GUIDANCE-2026-11-15.md` item 4): both change `includes/rsg-dfs-prices.php` and `js/rsg-dfs.js`. When click spots is live, change this line to "live <date>, <commit>" — the modernization chat checks it before touching those files.
Why: the click log (`newguru_wp_rsg_clicks`) can't tell some spots apart (`vs` = 3 spots on comparisons, `top` = 3 spots on guides), has no device, and Amazon US has one tracking ID (`rsg-single-20`) for everything, so Amazon orders/earnings per spot are invisible.

## Decisions (Ruggero, 15 Nov)
- Every spot gets its own code, named **template + spot** (e.g. `rev-card`).
- Track **mobile/desktop** on every click. **Not** the row position in a box.
- Amazon US: separate tracking IDs per spot group, **no mobile/desktop split** (corrected 15 Nov; device is in our own click log only).

## Click codes
**Built in one place:** PHP prints the page type once (`<body data-rsg-pt="rev">`, one function `rsg_page_type()` in `rsg-core`); the markup carries only the spot (`data-dfs-box="card"`); the scripts send `pt-spot`. Shortcodes and shared widgets get the right template automatically, and the same page-type value doubles as the per-page-type body class Raptive asked for (`ads/BASELINE-2026-11.md`).

Page types: `rev` (road/trail/training reviews), `old` (dated + `review`-type reviews), `gps`, `cmp` (comparisons), `top`, `gde` (guides), `list` (review lists, brand/family listings), `home`, `quiz` (shoe finder), `art` (posts, /content/), `page` (anything else).

| Spot | Where | Was |
|---|---|---|
| `card` | sidebar price card (desktop); comparison pair card | `rail` |
| `mob` | in-article price block (phones/tablets) | `mob` |
| `bar` | sticky bottom bar, best-price link | `stick` |
| `drw-card` / `drw-mob` / `drw-bar` | drawer, by what opened it | `drw` |
| `full` | full price comparison lower on the page | `cmp` (reviews) |
| `vs` | mini comparison table in reviews | `vs` (reviews) |
| `snap` | comparison snapshot "deal" row | `vs` (comparisons) |
| `box` | comparison "Best price online" boxes | `cmp` (comparisons) |
| `hdr` | guide/top-list summary card, old-review rating row, guide header "See deal" | `top` |
| `item` | guide/top-list item buttons | `item` |
| `row`, `seo` | shortcodes in posts | `row`, `seo` |
| `lcard` | review-list cards | `card` |
| `rel`, `com` | related reviews, comments box | `rel`, `com` |
| `newer` | "newer version" card on old reviews (`e5b7e23`) | `newer` |
| `shoes-side` / `shoes-in` | articles' "Shoes in this article" box, sidebar / in the article (`67b5534`; the hub page = page type `top`) | `art`, `artm`, `hub`, `hubm` |
| `amz`, `bought`, `text`, `go`, `amzk` | links outside the price boxes (footer script) | same |

Every new spot added later gets its own code (rule; goes in the theme RUNBOOK).

**Device:** new column `dev` (`m`/`d`) = viewport narrower than 992 px at the click (the breakpoint where the boxes switch). Column `box` widened to `varchar(16)`; validation `^[a-z]{2,6}(-[a-z]{2,6}){1,2}$` plus the legacy codes. Old rows keep their old codes (mapping above).
**Network sub-IDs** (`page-box-cc`) carry the new codes automatically (AWIN clickref ≤ 50 chars: fine).

## Amazon US tracking IDs (final, Ruggero 15 Nov)
Before 10 Nov the old price system already used one ID per location (`rsg-comp-20`, `rsg-reviewlist-20`, `rsg-wizard-20`, `rsg-bestof-20`, `rsg-road-*`, `rsg-overview-20`); the new DFS boxes put everything on `rsg-single-20`. Where the meaning is the same we **reuse the old ID** (its Amazon history continues across the switch); review spots get **new IDs** (the old `rsg-road-*` were tied to the old layout). Checked 15 Nov: none of the reused IDs is in the database; in the theme they sit only in the old non-DFS code paths (removed with the reskin). Everything not listed keeps `rsg-single-20`. No mobile/desktop split (device is in our click log only).

| Tracking ID | Spots | |
|---|---|---|
| `rsg-comp-20` | all `cmp-*` spots | reused |
| `rsg-reviewlist-20` | `list-lcard` (review-list cards) | reused |
| `rsg-wizard-20` | `quiz-*` (shoe finder) | reused |
| `rsg-bestof-20` | `top-*`, `gde-*`, `*-row`, `*-seo`, `*-rel`, `home-*`, `*-shoes-*` | reused |
| `rsg-revside-20` | `rev-card` (sidebar card) | new |
| `rsg-revmob-20` | `rev-mob` (in-article block) | new |
| `rsg-revbar-20` | `rev-bar` (sticky bar) | new |
| `rsg-revdrw-20` | `rev-drw-*` (drawer; the opener is in our click log) | new |
| `rsg-revlow-20` | `rev-full`, `rev-vs`, `rev-com` | new |

**How:** the spot is known when the page is built, so PHP writes the right `tag=` into each Amazon US link (no script, works without JavaScript, one cached page for all devices). Map spot group → ID in an `rsg-core` option (IDs changeable without code). Other Amazon stores (UK/DE/CA) unchanged.
**IDs:** all 9 confirmed in Associates Central 15 Nov (Ruggero's screenshot). **Before go-live:** confirm the tags with one test click per spot on staging (the tag shows in the Amazon URL). Read per-ID reports after ~4 weeks.

## Build (staging)
`rsg-core`: `rsg_page_type()` + body attribute/class. Theme `includes/rsg-dfs-prices.php`: spot names; drawer remembers its opener. `js/rsg-dfs.js`: send `pt-spot` + `dev`. Amazon tag per spot group set in PHP when the link is printed. mu-plugin `rsg-dfs-clicks.php` (DFS repo): `ALTER` (`box` varchar(16), `dev` char(1)), validation, footer link script sends `pt-spot` + `dev`. Checks: one click per spot × device on staging lands in the table with the right code; Amazon link carries the right tag; no double logging.
