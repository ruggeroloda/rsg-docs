# Click spots + Amazon tracking IDs + seen log — brief (15 Nov 2026, seen log added 16 Nov)

**Built and live 16 Nov 2026 09:25 UTC** as analytics phases A + C (tag `live-20261116-092659`: page-type marker, Amazon US tracking IDs per spot group, spot names, `spot_seen` events in Umami). Current state: `../analytics/README.md`. The rest of this brief is the plan as agreed.

Status: **agreed by Ruggero 15 Nov (final ID list same day); seen log added 16 Nov (Ruggero: "can we do option 3?"), seen-by-device 16 Nov; not built.** Own deploy **after** the reskin go-live (Ruggero 15 Nov; reskin live 15 Nov 14:30 UTC, `4ac5ef4`): staging first, then live on "deploy to live". **Order:** click spots goes **before** the Modern Web Guidance drawer rebuild (`MODERN-WEB-GUIDANCE-2026-11-15.md` item 4): both change `includes/rsg-dfs-prices.php` and `js/rsg-dfs.js`. When click spots is live, change this line to "live <date>, <commit>" — the modernization chat checks it before touching those files.
Why: the click log (`newguru_wp_rsg_clicks`) can't tell some spots apart (`vs` = 3 spots on comparisons, `top` = 3 spots on guides), has no device, and Amazon US has one tracking ID (`rsg-single-20`) for everything, so Amazon orders/earnings per spot are invisible. **Confirmed 16 Nov:** Ruggero sees only `rsg-single-20` in Amazon for 15 Nov clicks — expected until this deploy is live. And we can't tell "nobody saw the box" from "saw it, didn't click" (16 Nov: comparison "Best price online" boxes at half their usual clicks after the reskin; suspected: people don't scroll that far).

## Decisions (Ruggero, 15–16 Nov)
- Every spot gets its own code, named **template + spot** (e.g. `rev-card`).
- Track **mobile/desktop** on every click. **Not** the row position in a box.
- Amazon US: separate tracking IDs per spot group, **no mobile/desktop split** (corrected 15 Nov; device is in our own click log only).
- **Seen log (16 Nov):** count when each spot actually appears on screen, so every spot gets seen → clicked → (orders, where the network reports them). **Seen is always split by device** (16 Nov: people may scroll all the way on one device and not the other).
- **Talking to Ruggero:** name spots in plain words (the "Plain name" column below), never by code alone.

## Click codes
**Built in one place:** PHP prints the page type once (`<body data-rsg-pt="rev">`, one function `rsg_page_type()` in `rsg-core`); the markup carries only the spot (`data-dfs-box="card"`); the scripts send `pt-spot`. Shortcodes and shared widgets get the right template automatically, and the same page-type value doubles as the per-page-type body class Raptive asked for (`ads/BASELINE-2026-11.md`).

Page types: `rev` (road/trail/training reviews), `old` (dated + `review`-type reviews), `gps`, `cmp` (comparisons), `top`, `gde` (guides), `list` (review lists, brand/family listings), `home`, `quiz` (shoe finder), `art` (posts, /content/), `page` (anything else).

| Spot | Plain name (use with Ruggero) | Where | Was |
|---|---|---|---|
| `card` | Sidebar price card | sidebar price card (desktop); comparison pair card | `rail` |
| `mob` | Phone price block | in-article price block (phones/tablets) | `mob` |
| `bar` | Phone sticky bar | sticky bar, best-price link | `stick` |
| `drw-card` / `drw-mob` / `drw-bar` | "Compare N shops" drawer | drawer, by what opened it | `drw` |
| `full` | Full price list (bottom of review) | full price comparison lower on the page | `cmp` (reviews) |
| `vs` | Review mini comparison table | mini comparison table in reviews | `vs` (reviews) |
| `snap` | Comparison top table "deal" row | comparison snapshot "deal" row | `vs` (comparisons) |
| `box` | Comparison "Best price online" boxes (lower) | comparison "Best price online" boxes | `cmp` (comparisons) |
| `hdr` | List/guide header card | guide/top-list summary card, old-review rating row, guide header "See deal" | `top` |
| `item` | List/guide item buttons | guide/top-list item buttons | `item` |
| `row`, `seo` | Price links inside article text | shortcodes in posts | `row`, `seo` |
| `lcard` | Review-list cards | review-list cards | `card` |
| `rel`, `com` | Related reviews / comments box | related reviews, comments box | `rel`, `com` |
| `newer` | "Newer version" card (old reviews) | "newer version" card on old reviews (`e5b7e23`) | `newer` |
| `shoes-side` / `shoes-in` | "Shoes in this article" box | articles' "Shoes in this article" box, sidebar / in the article (`67b5534`; the hub page = page type `top`) | `art`, `artm`, `hub`, `hubm` |
| `amz`, `bought`, `text`, `go`, `amzk` | Other links in the text | links outside the price boxes (footer script) | same |

Every new spot added later gets its own code **and a plain name** (rule; goes in the theme RUNBOOK).

**Device:** new column `dev` (`m`/`d`) = viewport narrower than 992 px at the click (the breakpoint where the boxes switch; tablets count with phones, same layout). Column `box` widened to `varchar(16)`; validation `^[a-z]{2,6}(-[a-z]{2,6}){1,2}$` plus the legacy codes. Old rows keep their old codes (mapping above).
**Network sub-IDs** (`page-box-cc`) carry the new codes automatically (AWIN clickref ≤ 50 chars: fine).
**Data fix (16 Nov):** one click logged merchant `adidas.njih.net` with empty country (adidas AU via AWIN, 15 Nov evening): fix the merchant label/country in the same deploy.

## Seen log (added 16 Nov)
- **What counts as seen:** a spot that is at least 50 % on screen for at least 1 second (the usual ad-viewability rule), counted **once per spot per page view**. Hidden boxes (e.g. the phone block on desktop) are never counted. Drawer = seen when opened.
- **How:** one `IntersectionObserver` in `js/rsg-dfs.js` over every `[data-dfs-box]` element (+ the footer-script spots that are boxes); seen spots collected in the page and sent **once per page view** in one beacon (`navigator.sendBeacon` on `pagehide` / `visibilitychange` hidden) to a new endpoint `POST /wp-json/rsg-dfs/v1/seen` (mu-plugin `rsg-dfs-clicks.php`, same validation style as `/view`: site origin, known page, plain values; no cookie/IP/UA). Adds about one origin request per page view (the view beacon is ~5 % of origin requests today).
- **By device (Ruggero 16 Nov):** every seen count carries the device, and the **page-view log gets the device too** (`newguru_wp_rsg_views` + `dev` char(1), same < 992 px rule, sent by the existing view beacon), so "% of views that reach the spot" is phone sightings ÷ phone views and desktop ÷ desktop, never mixed. Old view rows have no device.
- **Storage:** daily counters, not one row per sighting: table `newguru_wp_rsg_seen` (`day` date, `page_id`, `spot` varchar(16), `dev` char(1), `country` char(2), `n` int; unique key on the first five; `INSERT … ON DUPLICATE KEY UPDATE n=n+1`). Small, and enough for per-page and per-template rates.
- **What Ruggero gets per spot (template + spot, by device):** page views → seen (% of views that reach it) → clicks (% of seen that click) → orders/earnings where the network reports them: **Amazon US per tracking-ID group** (Associates reports per ID; groups above, so e.g. all comparison spots share `rsg-comp-20`), **AWIN/CJ/Rakuten per sub-ID** (`page-box-cc`, at commission level). Order numbers are small at current traffic: read after ~4 weeks.
- **Limits:** ad blockers/no-JS visitors aren't counted (same as clicks); bots that run JavaScript can inflate seen counts (as the 13 Nov crawler did with views): compare rates, and exclude spikes.
- **Query (after go-live):** views, seen + clicks per template-spot and device for a date range, in the theme RUNBOOK next to the click queries.

## Amazon US tracking IDs (final, Ruggero 15 Nov)

**→ Superseded 23 Nov 2026**: one tag per page type, live 23 Nov 13:11 UTC (`../analytics/amazon-tags-2026-11-23/PLAN.md`). The per-spot IDs below are retired.
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
`rsg-core`: `rsg_page_type()` + body attribute/class. Theme `includes/rsg-dfs-prices.php`: spot names; drawer remembers its opener. `js/rsg-dfs.js`: send `pt-spot` + `dev` on clicks, `dev` on the view beacon; seen observer + one beacon per page view. Amazon tag per spot group set in PHP when the link is printed. mu-plugin `rsg-dfs-clicks.php` (DFS repo): `ALTER` (clicks: `box` varchar(16), `dev` char(1); views: `dev` char(1)), validation, footer link script sends `pt-spot` + `dev`; `/seen` endpoint + `rsg_seen` table. Checks: one click per spot × device on staging lands in the table with the right code; views carry the device; Amazon link carries the right tag; no double logging; scroll a review, a comparison and an article on desktop + phone and confirm exactly the spots that came on screen are counted once, with the right device (not the hidden ones); `rsg-dfs.js` min copy refreshed + CDN purge on deploy (GO-LIVE lesson).
