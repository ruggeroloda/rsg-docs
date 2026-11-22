# RSG — context for the analyst
_Maintained by Claude in the RSG project; last updated 16 Nov 2026. If something here contradicts the data, trust the data
and say so in the report._

## The business
- RunningShoesGuru.com: independent running-shoe (and some GPS-watch) reviews since ~2010. Testers buy the shoes and run
  ≥ 50 miles in them. Peak: 1M+ monthly sessions; now ~10 % of the old traffic (GA4 ~160k page views / 30 days, Nov 2026).
- Money: **Raptive display ads** (per page view / viewability) + **affiliate clicks** (mostly Amazon US; also Running
  Warehouse, Nike, Zappos, Hoka, Puma, adidas AU, Running Warehouse AU…) + deals emails. Orders and commissions are not
  imported yet: **shop clicks are the proxy for affiliate revenue**, page views for ad revenue.
- Main market: **US**. Price boxes show offers for the visitor's country: US, GB, AU (since 15 Nov), others fall back to
  US prices where no local feed exists (e.g. NL). Amazon US links carry one tracking ID per spot group (orders per ID
  readable in Amazon reports after ~4 weeks; not in the database yet).
- Owner: Ruggero (ecommerce professional, ex Nike/Dell/TOMS). Wants direct, honest, concise analysis with trade-offs; no
  reassurance, no padding. Not a statistician by trade, but numerate: show the baseline and how sure you are.

## Page types (Umami `tag` / `page_type`)
`review` current shoe review · `old_review` older review (often has a "newer version" card) · `gps_review` GPS watch review
· `comparison` shoe A vs B (vs C) · `review_list` review listings (by brand/type) · `top_list` "best" lists (/top/) ·
`guide` old buying guides (/guide/, mostly dead since the Sept 2023 helpful-content update) · `article` blog posts ·
`home` · `shoe_finder` quiz · `archive` date/author/category archives · `page` other pages.

## Price spots (event property `component`) — always use the plain name
| component | What Ruggero sees |
|---|---|
| `price_card` | Sidebar price card (desktop; also the comparison pair card) |
| `price_block` | Phone price block (in the content, phones) |
| `sticky_bar` | Phone price bar fixed at the top of reviews after scrolling (design G since 18 Nov 10:16 UTC: shoe name + "Jump to", price + shop, "All N prices" opens the drawer, "See deal"; before: navy one-row bar, 0.3 % clicks per seen) |
| `drawer` | "Compare N shops" drawer (`opener` = what opened it) |
| `full_list` | Full price list at the bottom of a review |
| `mini_table` | Mini comparison table in a review |
| `deal_row` | "Deal" row in the comparison top table |
| `best_price_boxes` | Comparison "Best price online" boxes (lower on the page) |
| `header_card` | Header card on lists/guides, old-review rating row, guide "See deal" |
| `item_button` | Buttons on list/guide items |
| `text_link` | Price links inside article text |
| `review_card` | Cards on review listings |
| `related` | Related reviews |
| `newer_card` | "Newer version" card on old reviews |
| `shoes_box` | "Shoes in this article" box |
| `picks_box` | Picks box (hub/fallback picks) |
| `price_box` | Price box (older templates) |
| `quiz_result` | Shoe Finder results |
| `home_picks` | Homepage "Our picks right now" price links ("from $X at shop", men's) |
`layout` = phone (< 992 px) or desktop. `spot_seen` = the spot was ≥ 50 % on screen for ≥ 1 s (once per page view).

## Homepage events (new homepage, from its go-live; 17 Nov 2026 build)
- `home_section_seen` (section): ≥ 40 % on screen (or half the screen) for 1 s, once per section per page view. Sections in page order: `hero`, `picks` ("Our picks right now"), `comparisons`, `latest` (latest reviews), `brands` (reviews by brand), `types` (reviews by type + GPS watches tile), `how_we_test` (incl. the testers), `plans` (free training plans form). Use it as the scroll-depth funnel.
- `home_click` (section, item = link text ≤ 60 chars, target = path, pos = position within the section): every non-shop link/button in the page body. Shop links in the picks are `shop_click` with component `home_picks` (not here). Header/footer clicks are not tracked.
- `home_search`: hero search submitted (the words are in the site search log, not in Umami).
- Hero shoe rotates daily through the 5 newest reviews (switch 02:00 UTC, on the page after the ~02:45 UTC purge; once live): hero clicks and the hero `home_click` target change with the day's shoe — compare hero click rates per shoe, not day to day.
- `plans_signup` (result: ok / invalid_email / rate_limited / error): training-plans form; sign-ups are also in the Shoe Finder sign-ups table with `quiz_type` = 'plans'.

## Review page events (from 18 Nov 2026 18:03 UTC)
- `gallery_swipe` (photos = number of photos in the review): the visitor swiped the phone photo strip at the top of a review (road, trail, training, GPS), once per page view; phones < 768 px only. Use it per phone review view. The strip nudges itself once per browser session (no event for that).

## Reading depth events (reviews, comparisons, /top/ lists; from 22 Nov 2026 07:13 UTC)
- `page_left` (22 Nov 2026 08:27 UTC): once per page view, the first time the page is hidden or left (closed tab, back button, another app, a link in this or a new tab; browsers don't say which): `at` = the section in the middle of the screen at that moment (`between` = between sections, e.g. an ad), `deepest` + `pos` = the furthest section seen, `t` = visible seconds, `after` = the reader's last action on the page view (`shop_click`, `next_click`, `jump_click`, `drawer_open`, `price_switch`, `search_submit`, `none`). Sent as a beacon (Umami's own JSON fetch needs a CORS pre-check that a closing page never runs). A reader who switches tab and comes back is counted at the first hide.
- `section_seen` (section, pos, t), `next_click` (block, section, to, to_path), `jump_click` (block, section, to): current reviews, comparisons and list posts; not old-template reviews, GPS reviews or the homepage. Dictionary and section names: `analytics/UMAMI-PLAN-2026-11-16.md` "Reading depth and ways onward"; why: `content/ENGAGEMENT-READ-2026-11-21.md`. Clean baseline 1–7 Dec (27–30 Nov left out); no layout change on these pages before 8 Dec. Not in `rsg.metric_day` yet: query `website_event` + `event_data` (humans only).

## Current situation (Nov 2026) — read with `rsg.change_log`
- A relaunch is under way: new price engine (10 Nov), SEO batch (11 Nov), old content erased (12 Nov), guides redirected
  (15 Nov), **reskin live 15 Nov 14:30 UTC**, Umami tracking from 16 Nov 09:25 UTC. Planned: old-article pruning (~22 Nov),
  Black Friday → Cyber Monday (27 Nov–1 Dec: deal traffic, especially GPS reviews via Amazon offers — not a trend).
- **Google's November 2026 core update started 10 Nov** (end not announced on 16 Nov). It overlaps the site changes:
  separate the two by comparing changed vs untouched page groups and pages, and by timing.
- Content: few new reviews for now (restart planned, ~18 tested shoes a year, mainly daily trainers). Reviews and
  comparisons are what still ranks.
- Running experiments: alt-text test (10 test vs 10 control reviews, image + web search) — read ~9 Dec and ~23 Dec;
  details in the change log (11 Nov).

## Data caveats
- Umami starts 16 Nov 09:25 UTC (partial day). Visitor country is less reliable before 16 Nov 10:33 UTC.
- ~40–45 % of Umami sessions are bots (automated Chrome); the bot score is rule-based (`rsg.session_quality.reasons`):
  watch for new bot patterns (one screen size, one city, 0 s visits, many countries) and report them.
- JS errors include third-party errors until the "our errors only" filter is live (pending deploy, Nov 2026).
- Search Console: `gsc_page_day` and `gsc_country_day` are complete; `gsc_query_day` and `gsc_page_country_day` lose ~40 %
  of clicks (Google privacy filtering): use them for shares and patterns, never totals. Page type for Search Console pages
  comes from the latest Umami tag, else from the URL pattern (`rsg.guess_page_type`; a `/reviews/<type>/<slug>/` page not yet visited counts as `review` even if it is an old one).
- Umami counts what browsers send: ad blockers hide some visitors and clicks; trends matter, not equality with GA4.
