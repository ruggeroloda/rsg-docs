# Own analytics with Umami — plan (16 Nov 2026)

_Status: **approved by Ruggero 16 Nov ("change the plan and start"); phase A started 16 Nov.** Each phase still gets Ruggero's OK before the next. Research: `UMAMI-RESEARCH-2026-11-16.md`. Replaces the WordPress "seen log" in `../theme/CLICK-SPOTS-BRIEF-2026-11-15.md` (that brief keeps the page-type marker, spot names and Amazon tracking IDs). Imports (networks, Amazon reports, Raptive, Search Console, WordPress page data) = later (Ruggero 16 Nov: "not that pressing")._

## Goal
One place for everything that happens in the browser (page views, price spots seen, clicks, drawer, switches, page speed, errors, heatmaps, replays), each event linked to its visit and traffic source, split by layout (phone/desktop), queryable by Claude and the future analyst agents (API, SQL, MCP). **Ruggero never has to open Umami: he asks, Claude answers from the data** (16 Nov). Visitors see no change; Rocket.net cache unchanged.

## Phases (each: staging/test first, Ruggero's OK before the next; nothing on live without "deploy to live")
| Phase | What | Live effect |
|---|---|---|
| **A. Page marker + spot names + Amazon IDs** | `rsg_page_type()`, `data-rsg-pt` on `<body>`, spot attributes in the markup (vocabulary below), Amazon US `tag=` per spot group (CLICK-SPOTS brief). | Amazon reports split by spot group before Black Friday (27 Nov). |
| **B. Umami server** | Install on rsgmedia, subdomain, security, backups, monitoring. No site change. | none |
| **C. Site events (staging)** | Tracker + our event code + recorder (heatmaps/replays) on staging, test website in Umami; page-speed test with and without the recorder; check replay/heatmap data is readable from the database. | none |
| **D. Go live + parallel run** | Live website in Umami; recorder at 5–10 % sample; old click/view logs keep running 2 weeks; compare. | data starts |
| **E. Retire / later** | Decide on the old logs (the view log also serves DFS country checks); raise the recorder sample if speed holds; imports; analyst agents. | — |

## B. Server setup (rsgmedia)
- **Image pinned** to an exact version (like n8n), upgraded on purpose after a backup; release notes read first (Umami ships security fixes often; v3.1 raised Node to 22, v3 dropped MySQL).
- **Database:** own database + own user `umami` in the existing Postgres 16 (Umami migrates only its own schema). Nightly backup already dumps all databases: confirm `umami` is in it, and test one restore.
- **Env (in `/home/ruggero/compose/.env`, never in compose):** `APP_SECRET` (required since 3.5; changing it logs everyone out and restarts all visitor IDs → set once, store in 1Password), `DATABASE_URL`, `TRACKER_SCRIPT_NAME` + `COLLECT_API_ENDPOINT` (neutral names, runtime vars), `TRUSTED_PROXIES` (Nginx Proxy Manager's Docker network address, not the broad `private` preset), `DISABLE_TELEMETRY=1`, `DISABLE_UPDATES=1` (we upgrade by plan), `TWO_FACTOR_ENCRYPTION_KEY` (needed for 2FA), `SALT_ROTATION` left at default monthly (unique visitors per month) unless we decide otherwise, `IGNORE_IP` = our servers (rsgmedia, rsg-dfs: site checks and DFS pushes must never count). `MCP_ENABLED` off until the analysts need it.
- **Subdomain:** `t.runningshoesguru.com` (neutral name) → Route 53 A record → rsgmedia; Let's Encrypt via Nginx Proxy Manager.
- **Exposure:** public = only the tracker script path, the recorder script, the collect endpoint(s) (+ CORS preflight). Dashboard, login and API = private (SSH tunnel like Portainer, or an allow-list); admin with 2FA. Claude/analysts reach the API and database from the server side.
- **Real visitor IP:** Nginx Proxy Manager must pass `X-Forwarded-For` with `$proxy_add_x_forwarded_for` (not `$remote_addr`), and Umami must trust only the proxy. Test: a visit from a VPN in GB/DE must show GB/DE, not Germany/Nuremberg for everyone.
- **Monitoring:** Umami heartbeat added to the 5-minute site check (healthchecks.io); a down collector = data gap, never a site problem.
- **Load:** ~160k page views/30 days + ~4–6 events per view ≈ 1M events/month: light for Postgres on the CPX32. Replays add an estimated few GB/month at 5–10 % (disk 17 % used); watch the first week.

## Heatmaps + replays (Ruggero 16 Nov: on, not off)
- **Why:** the `spot_seen` events give exact counts per spot; heatmaps and replays explain *why* (where people stop, dead taps on things that aren't links, rage taps, hesitation before price boxes, last action before leaving).
- **How Claude uses them:** heatmap click/scroll data read from the database and turned into numbers (per page type, layout, screen width); replay recordings (rrweb event data, not video) analysed in bulk for patterns; single sessions replayed in a browser and looked at when useful. Umami's MCP doesn't cover heatmaps/replays → database access. **Verify the storage format is usable in phase C before relying on it.**
- **Setup:** `recorder.js` from the subdomain; heatmaps for everyone; **replays only for non-EU visitors** until the consent question is settled (not legal advice — Ruggero to decide; replays are commonly treated as needing consent in the EU). Mask level `moderate` (all inputs masked; comment and sign-up forms included); block selector for anything personal.
- **Sample:** 5–10 % at go-live; raise only if page speed (INP on phones especially) holds. Phase C measures CWV on staging with and without the recorder (Raptive ads change the page a lot, which the recorder also records).

## Asking questions instead of opening Umami
- Claude answers from Umami's API (traffic, events, sessions, funnels, journeys, retention, attribution, performance) and from SQL on the database (heatmaps, replays, joins with WordPress/our tables later).
- Access today: SSH to rsgmedia from Ruggero's Mac (device bridge) → works whenever the Mac is reachable. Later options: a read-only Umami MCP endpoint connected to Claude (public with API key: weigh the exposure), or scheduled analyst tasks that write reports.

## Event dictionary (draft — the single naming source; changes go here first)
Rules: few event names, `snake_case`; the **same property names on every event**; values from fixed lists (no free text, no URLs, no personal data); `v` = dictionary version on every event, so a later change never mixes meanings; page details (title, brand, shoe, dates) are joined from WordPress by `page_id`, not sent.

**Common properties (every custom event):** `v`, `page_type` (`review`, `old_review`, `gps_review`, `comparison`, `top_list`, `guide`, `review_list`, `home`, `shoe_finder`, `article`, `page`), `page_id`, `layout` (`phone` < 992 px / `desktop`; our layout rule, separate from Umami's own device field), `country` (price box country shown), `gender`.

**Location properties (spots and links):** `placement` (`header`, `sidebar`, `content`, `sticky`, `drawer`, `footer`) + `component`:
| `component` | Plain name |
|---|---|
| `price_card` | Sidebar price card (also the comparison pair card) |
| `price_block` | Phone price block |
| `sticky_bar` | Phone sticky bar |
| `drawer` | "Compare N shops" drawer (+ `opener` = what opened it) |
| `full_list` | Full price list (bottom of review) |
| `mini_table` | Review mini comparison table |
| `deal_row` | Comparison top table "deal" row |
| `best_price_boxes` | Comparison "Best price online" boxes |
| `header_card` | List/guide header card, old-review rating row, guide "See deal" |
| `item_button` | List/guide item buttons |
| `text_link` | Price links inside article text (shortcodes) |
| `review_card` | Review-list cards |
| `related` / `comments_box` | Related reviews / comments box |
| `newer_card` | "Newer version" card (old reviews) |
| `shoes_box` | "Shoes in this article" box |
| `quiz_result` | Shoe finder results |
Every new spot gets a component name and a plain name here before it ships.

**Events:**
| Event | When | Extra properties |
|---|---|---|
| (page view) | automatic | page type + id: **verify on staging** whether page views can carry properties; otherwise `data-tag` = page type |
| `spot_seen` | spot ≥ 50 % on screen for ≥ 1 s, once per spot per page view, sent when it happens; hidden spots never | location |
| `shop_click` | click, middle-click, Ctrl/Cmd-click on a shop link | location, `merchant`, `shoe_id`, `store` (`amazon`/`shop`), `opener` (drawer) |
| `drawer_open` | drawer opened | `opener` |
| `price_switch` | country or gender changed by the visitor | `what`, `to` |
| `internal_click` | related review, newer card, comparison links, menu (later) | location, `target_id` |
| `quiz` | shoe finder start / each step / result / sign-up | `step` |
| `signup` | email sign-up | `form` (where), `lead` (guide PDF) |
| Web Vitals | `data-performance` | automatic |
| errors | `data-errors`, `data-release` = theme commit | automatic |
Network sub-IDs and Amazon tracking IDs are **built from these values** (one string only where a network needs one).

### Reading depth and ways onward (21 Nov 2026; on staging, live after "deploy to live")
On current reviews (road/trail/training), comparisons and list posts (`top_list`, `guide`). Not on old-template reviews, GPS reviews or the homepage (which has its own `home_*` events). Code: `rsg-core/assets/rsg-track.js` `reading()`; sections are found by the templates' own classes and ids (no markup added), so a renamed class or id must be changed there and here. Why and how to read it: `content/ENGAGEMENT-READ-2026-11-21.md`.

| Event | Sent when | Properties |
|---|---|---|
| `section_seen` | any part of a section in the upper 85 % of the screen for 1 s; once per section per page view | `section`, `pos` (place on the page, 1 = first), `t` (seconds the page has been visible) |
| `next_click` | click / middle-click on a link to another page of the site (not offers, `/go/`, images) | `block` (where the link sits), `section`, `to` (page type it opens: review, comparison, review_list, top_list, top_hub, comparison_list, gps_review, guide, author, home, other), `to_path` |
| `jump_click` | click on a link to another spot of the same page | `block`, `section`, `to` (the target's id) |

`section` names. Review: `top`, `verdict`, `pros_cons`, `ratings`, `facts`, `tester_take`, then the text sections by their field name (`who_should_buy_it`, `who_should_not_buy_it`, `introduction`, `first_impressions`, `upper`, `sole_unit`, `conclusions`; trail: `protection`, `durability_section`, `responsiveness_speed`, `comfort_and_fit`; extra sections by their title), `compare_table`, `price_list`, `reviewer`, `comments`, `similar`. Comparison: `top`, `intro`, `jump_line`, `table`, `scores`, `sole_unit`, `upper`, `which_to_buy`, `price_boxes`, `writer`, `latest`. List post: `top`, `picks_table`, `pick_1` … `pick_N` (the category sections in page order), `faq`.

`block` names: `breadcrumb`, `jump_line`, `picks_table`, `shoe_box`, `compare_table`, `similar`, `latest`, `reviewer`, `byline`, `facts`, `verdict`, `top`, `comments`, `text`, `footer`, `menu`, `other_content`, `other`.

## C. Site integration rules
- **Load:** one `<script defer>` from the subdomain (keeps tracker and server versions in step; no copy in the theme that could go stale). `data-website-id` per environment (staging and live are separate websites in Umami) + `data-domains` exact host (`www.runningshoesguru.com`; staging host on staging).
- **Our own code sends events with `umami.track()`** from one delegated listener in `js/rsg-dfs.js`/footer script — **not** `data-umami-event` attributes: with that method other listeners on the element don't fire, which would break our click log and the drawer opener.
- **`data-before-send` filter:** drop automated browsers (`navigator.webdriver`), drop all but UTM parameters from page URLs (no `?dfs=0`, `fbclid`, etc. splitting pages), stamp `v`.
- **Our own visits excluded:** a one-time "don't track this browser" switch for Ruggero's and reviewers' devices (Ruggero 16 Nov: yes; verify the current Umami method on staging).
- **WP Rocket:** exclude the tracker, recorder and our event script from "Delay JavaScript execution", combine/minify and RUCSS; after editing `rsg-dfs.js` refresh its `cache/min` copy (GO-LIVE lesson). Check Rocket.net doesn't apply its own JS deferral to it.
- **Cache:** page HTML stays identical for everyone (marker, spot attributes, Amazon tags are printed once); layout is decided in the browser → no per-device cache variants; one normal purge per deploy.
- **Clicks:** Umami's tracker sends with `fetch` + `keepalive`, so a click event survives the navigation; right-click → "open in new tab" stays uncounted (as today).
- **Privacy:** no cookies, IP not stored, EU server; replays non-EU only (above); one paragraph in the privacy policy (not legal advice: Ruggero to confirm the wording). Raptive's consent banner unchanged.

## Tests before go-live (staging)
1. Real IP/geo: visits via VPN from US, GB, DE land in the right country.
2. Each component × layout: `spot_seen` exactly once when scrolled into view, never for hidden ones; `shop_click` with the right properties; drawer `opener` right.
3. Amazon link carries the right tag per spot group (phase A).
4. Page views carry page type; UTM kept, junk parameters stripped.
5. Bot test (headless browser) not counted; our server IPs not counted; "don't track" switch works.
6. No visual change, CLS unchanged, no new console errors; WP Rocket serves the tracker untouched.
7. Load test light: 50 events/s for a minute against the collector without errors.
8. Recorder: CWV (LCP, INP, CLS) on phone + desktop with vs without; replay not recorded for an EU (VPN) visit; inputs masked; heatmap + replay data readable from the database.

## D. Go live + parallel run (2 weeks)
Compare daily: Umami page views vs our view log vs GA4; Umami `shop_click` vs our click log (expect Umami lower by the ad-blocker share; our same-domain beacon misses ~7–9 %). If Umami loses much more → ask Rocket.net for an edge rule on the main domain (not a PHP forwarder: it would tie site PHP workers to Hetzner's health).

## Common pitfalls (checklist)
- Every visitor showing as Germany / one session → proxy IP headers wrong.
- Changing `APP_SECRET` → everyone becomes a new visitor.
- Unpinned image auto-upgrading → schema migration surprises.
- Dashboard/API open to the internet.
- Inconsistent event names/properties over time → no `v`, no dictionary. Free text or URLs as property values → unusable reports and possible personal data.
- `data-umami-event` attributes swallowing our own click handlers.
- WP Rocket delaying the tracker until user interaction → bounces and short visits never counted.
- Staging/test traffic in live data → separate websites + `data-domains`.
- JavaScript-running bots (13 Nov crawler) inflating views/seen → before-send filter + compare rates, exclude spikes.
- Comparing Umami with GA4 1:1 → they count differently; trends matter, not equality.
- Seen events sent only at page exit → lost on mobile; send each `spot_seen` when it happens.
- Recorder at a high sample on phones → slower INP; start low, measure, then raise.
