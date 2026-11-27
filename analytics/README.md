# Analytics — current state

_Rewritten 26 Nov 2026 to the current state; the long version with every first reading, rule history and check: `archive/README-until-2026-11-26.md`. Event dictionary: `UMAMI-PLAN-2026-11-16.md`. Server: `../hosting/hetzner/RUNBOOK.md`. Site side: `../theme/RUNBOOK.md` ("Umami", "Tracking vocabulary", "Amazon US tag per page type")._

Everything lives in the shared Postgres 16 on rsgmedia, database `umami`: Umami's own tables plus schema `rsg` (Search Console, GA4, revenue, metrics, notebook). Read-only role `rsg_analyst` (on rsgmedia: `echo "select …" | rsg-sql`; 120 s timeout; password in `~/metrics/analyst.env`).

## Daily and weekly jobs on rsgmedia (UTC)
| Time | Job | Source | Log |
|---|---|---|---|
| 06:40 daily | Search Console days today−7 … today−3 (final data only) | `../hosting/hetzner/gsc/`, `/etc/cron.d/rsg-gsc` | `~/gsc/daily.log` |
| Mon 06:50 | GA4 last + this month | `../hosting/hetzner/ga4/`, `/etc/cron.d/rsg-ga4` | `~/ga4/weekly.log` |
| 06:55 daily | Revenue: CJ, Rakuten, Awin, Impact (last 90 days), Raptive (last 7 days), offers, ECB rates | `../hosting/hetzner/revenue/run_daily.sh`, `/etc/cron.d/rsg-revenue` | `~/revenue/` |
| 07:10 daily | Metrics layer + flags | `../hosting/hetzner/metrics/`, `/etc/cron.d/rsg-metrics` | `~/metrics/daily.log` |
| Mon 07:40 | Popularity (Search Console clicks/day) → WordPress "similar shoes" | `../hosting/hetzner/popularity/` | — |
| 3rd of month | Umami retention (3 years) | `/usr/local/bin/umami-retention.sh` | — |

Monitoring: healthchecks.io `rsg-umami` (Umami up, tracker public, dashboard private, Search Console ≤ 6 days old, metrics build ≤ 30 h, revenue job failures).

## Umami
- **Umami 3.5.0**, self-hosted, no event cap or retention limit. Public host `t.runningshoesguru.com` serves only `/js/main.js`, `/recorder.js`, `/api/m`, `/api/record`, `/api/websites/<id>/recorder`; dashboard/API only from the server (`http://127.0.0.1:3010`, admin login `_private/umami-admin.txt` → to 1Password).
- Websites: RSG live `31037802-afa8-48d7-9ff0-badf153a16d8`, staging `2d604d33-2cb6-4ed3-a7d0-359d5cf21671`.
- On the site: every page except editors/previews/feeds; page views tagged with the page type; Web Vitals; JS errors (only those with a stack frame on our host); events `spot_seen`, `shop_click`, `drawer_open`, `price_switch`, `section_seen`, `next_click`, `jump_click`, `page_left`, `search_submit`, `search_result`, `quiz` (Shoe Finder steps), `gallery_swipe`, `cw_view`, `cw_seen`, homepage `home_*`, `plans_signup`. **Replays off** (US privacy risk), heatmaps on for 10 % of sessions (option `rsg_umami_rec_rate`), never EU/EEA/UK/CH.
- Switches: `?rsg-notrack=1` (this browser never tracked; `=0` undoes); `?rsg-rec=1` (record this session, tests).
- Tables: `website_event` (event_type 1 = page view, 2 = custom event, 5 = Web Vitals; `tag` = page type), `event_data`, `session`, `heatmap_event`, `error_event`/`error_issue`. Funnels saved in Umami's `report` table (Shoe Finder, reviews and comparisons price seen → shop click).
- Pitfalls: Umami reads client IP headers before X-Forwarded-For, so it runs with `CLIENT_IP_HEADER=x-real-ip` + `SKIP_LOCATION_HEADERS=1`; an NPM regex `{n}` quantifier must be quoted; WP Rocket must not minify the tracker. **Upgrades:** backup first, pin the version, read the release notes, check the tracker/recorder paths still match the NPM config.
- Our old view/click logs (`newguru_wp_rsg_views`, `_clicks`, Rocket.net) run in parallel until ~30 Nov.

## Search Console
- Property `sc-domain:runningshoesguru.com`, service account `rsg-search-console@quickstart-1593179204049.iam.gserviceaccount.com` (key `_private/gsc-service-account.json` → rsgmedia `~/gsc/secrets/`). History from 1 Jul 2025.
- Tables (`rsg`): `gsc_page_day` (page, **complete**), `gsc_country_day` (country × device, complete), `gsc_page_country_day` and `gsc_query_day` (**partial**: Google drops ~40 % of clicks once page is combined with another dimension: shares only, never totals), `gsc_day_revision` (do "final" days change?), `gsc_load_log`.
- Delay: the flags use a Search Console day only once it is ≥ 4 days old; Umami search visits are the early signal.

## GA4 history (the only history before 16 Nov 2026)
- Property 250633127 (Amsterdam days), real data 3 Feb 2023 – today, hosts www + bare domain only. Same service account (Viewer). Loader image `rsg-ga4:1`: `docker run --rm --network internal --env-file ~/ga4/.env -v /home/ruggero/gsc/secrets:/secrets:ro rsg-ga4:1 [2026-11 2026-12]`.
- Tables (`rsg`): `ga4_page_day`, `ga4_landing_day` (landing × channel), `ga4_site_day` (channel × device × country), `ga4_event_day` (the site's own events), `ga4_load_log`. No user counts.
- **Caveats:** page-level data Feb 2023 – Nov 2024 partly lumped by GA4 (totals match, long tail thin); **22–23 Jan 2024: ~615k fake `affiliate_click`** (leave out); `affiliate_click` from Jan 2024 and **stops 11 Nov 2026** (old price box retired); **"Direct" bot jump from 4 Nov 2026** (use Organic Search landings for before/after); **Search Console UK click spike 31 Oct – 5 Nov 2026** (automated, ~2,100 extra clicks: leave those days or the UK out); never compare GA4-before with Umami-after. Findings: `TRAFFIC-HISTORY-2026-11-20.md`.

## Revenue data (money in US dollars, original currency kept)
- **Tables** (`rsg`, source `../hosting/hetzner/revenue/schema.sql`): `revenue_period` (source × account × grain day/week/month × period × date basis × tag/sub-ID × page; clicks, items, commission pending/confirmed/reversed, earnings + `usd_rate` → `earnings_usd`), `revenue_tag_map` (tag → page type with validity dates), view **`revenue_by_page_type`** (pick one grain: weeks and months overlap). Plan: `REVENUE-DATA-2026-11-23.md`.
- **Exchange rates:** `rsg.fx_ecb_day` (ECB since 1999), `rsg.usd_rate(currency, day)`.
- **Amazon** (no API): Ruggero downloads Tracking-Id weekly (Wednesday, previous Mon–Sun) and Tracking-Id + Category monthly (~5th) into the Mac's `data-drops/amazon/`. Load from a Mac-linked chat: `python3 -I -W ignore analytics/amazon-reader/load_amazon.py data-drops/amazon [filter] > ~/amazon_load.sql`, then pipe it into `docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q` on rsgmedia (each file's period replaced whole). NULL = hidden by Amazon; tag `Other` = page type `hidden`. Loaded: months Jan 2025 – Oct 2026, weeks from 31 Aug 2026. Tags per page type since 23 Nov 2026 (compare by tag only from 24 Nov).
- **CJ** (CID 2684855): Commission Detail API, every commission in `rsg.cj_commission`, by order date, tag = sub-ID; no clicks. History from 2010.
- **Rakuten** (SID 2333397): Signature Orders report API (≤ 365-day windows, nothing older than ~4 years), `rsg.rakuten_order`, by order date, tag = U1; no clicks, no status. A window is never replaced by a download with < half its rows.
- **Awin** (account 131905; token expires **23 Nov 2027**): `rsg.awin_transaction`, by order date, advertiser currency at the ECB rate; declined = reversed. Daily also re-reads 60 days by validation date. Six closed programmes show as `awin <id>`.
- **Impact** (account "rsgmedia", scoped token: Actions, Campaigns, Deals, Promo Codes, Reports — **no catalogues**): `rsg.impact_action`, by event date, EUR at the ECB rate; at most 3 years back. Joined 26 Nov: Road Runner Sports (6 %), New Balance EMEA (4 %).
- **Raptive** (Creator API beta, site `56f01024a1cbb74635a25c23`, `raptive_load.py`): one call per report per day; `rsg.raptive_site_day`, `raptive_page_day` (every page, unlike the dashboard export), `raptive_breakdown_day` (device, ad unit × device, video, country, traffic source, category), `raptive_day_revision`. Loaded from 1 Sep 2026 (available from 1 Jul 2024). Not in the API: monetized RPM, ad network/size/advertiser, ad unit × page. Sticky Outstream viewability looks wrong: ask Raptive before using it.
- **Offers** (`offers_load.py`): `rsg.offer` = active promotions and codes from Awin, Rakuten, CJ (incl. untyped text links with an end date or "code XXX") and Impact deals; **view `rsg.deals_email_offer`** (still live, started in the last 60 days or code ending within 30 days; `market`; status new/running/ending/upcoming). Email links get sub-ID `email-<send date>-<nn>` (Rakuten `u1`, CJ `sid`, Awin `clickref`, Impact `subId1`).
- **Running Warehouse** (no API): its Data Studio report "RWUS Affiliate Report - Running Shoes Guru" (Ruggero's Google account, read in the built-in browser), GA4-based, no money: buyers and items by brand. US purchasers per year and 2026 by month: `archive/README-until-2026-11-26.md`. Read on the 1st of each month (ROADMAP); EU + AU pages not read yet.
- **Commission Factory + AvantLink CA (switched on 27 Nov 2026, Ruggero, after the first Running Room sale showed up only at AvantLink):** `cf_load.py` (transactions → `rsg.cf_transaction`, source `commission_factory`, AUD; offers in `offers_load.py`, 2 on 27 Nov; Pace Athletic 6 %, no sale yet) and `avantlink_load.py` (report 8 → `rsg.avantlink_commission`, source `avantlink`, CAD; Running Room Canada: 139 transactions since Nov 2023 → 106 day rows, $1,459; first sale since our relaunch 26 Nov, CAD 7.20 pending, sub-ID `46508-vs-CA` = New Balance 860 v14 review). Keys `CF_API_KEY`, `AVANTLINK_AFFILIATE_ID`, `AVANTLINK_AUTH_KEY` in `~/revenue/.env`; both in `run_daily.sh` after Impact (last 90 days daily); backfill (3 years) run once 27 Nov 11:15 UTC, log `~/revenue/backfill-cf-avantlink-20261127.log`. Rollback: `*.bak-20261127` in `~/revenue/` (`.env`, `run_daily.sh`, `offers_load.py`, `schema.sql`).
- Not connected: Partnerize (SportsShoes; needs User application key + User API key from Partnerize → Settings → Your account), Webgains.
- **Zappos** (27 Nov): pays 0 % on items ≥ 30 % below original price. 2026: ~14 orders, ~$1,070 sales, ~$85 commission (Rakuten + CJ); 66 human US shop clicks 16–26 Nov → 1 order; cheapest shop on 74 of 258 US shoe × gender, 43 of them ≥ 30 % off. Nike US (CJ) and HOKA US (Rakuten) pay a flat 10 % on sale items too.
- Sub-ID → page type via the post id (`<post id>-<spot>-<country>`) is still to build: those rows sit in the view group `unmapped`.

## Bing Webmaster Tools
- Site verified 21 Nov 2026; API key `_private/bing-webmaster-api-key.txt` → rsgmedia `~/bing/secrets/.env` (not in 1Password yet; may be regenerated). API `https://ssl.bing.com/webmaster/api.svc/json/<Method>?siteUrl=…&apikey=…`: daily clicks/impressions (`GetRankAndTrafficStats`), weekly top pages/queries (partial), crawl stats, keyword demand for any phrase (`GetKeyword*`), every URL with outside links (`GetLinkCounts`, `GetUrlLinks`), crawl issues. **AI Performance (Copilot citations) is not in the API.** No loader yet (ad hoc).

## Metrics layer and flags (for the analyst)
- Image `rsg-metrics:1` (`../hosting/hetzner/metrics/`): rebuilds Umami yesterday + the day before, all Search Console days, then the flags (~50 s). Rebuild a range: `docker run --rm --network internal --env-file ~/metrics/.env rsg-metrics:1 2026-11-17 2026-11-20`.
- **Bot score `rsg.session_quality`**: points per rule (automation screens, data-centre cities, silent hits, desktop Linux, off-market direct visits with tell-tale screens…), ≥ 3 bot, 2 suspect, < 2 human; `reasons` lists the rules. Rules v4 (22 Nov). Bots also send events and Web Vitals: **every metric uses `quality = 'human'` only**. Changing a rule: bump `RULES_V`, rerun the range.
- **`rsg.metric_day`** (day, source `umami`|`gsc`, metric, dims, value, n): visits, page views, seen/clicks/CTR per component, events, Web Vitals p75 (by market `in` = US GB AU CA DE IE NZ / `out`), JS errors per human visitor, sessions by quality; Search Console clicks/impressions/CTR/position by device, country group, page type (`rsg.page_type_map`, else `rsg.guess_page_type(path)`). Umami days UTC, Search Console days Pacific.
- **`rsg.flag`**: unusual moves (1d vs the same weekday of 4 weeks, 7d vs the 4 weeks before; severity alert / win / notable). Umami 7-day flags from ~7 Dec. Black Friday and the prune will flag: the change log explains them.
- `rsg.metric_run`: one row per build.

## Notebook (shared by the analyst, Claude and Ruggero)
Source `../hosting/hetzner/metrics/notebook.sql`; `rsg_analyst` may INSERT/UPDATE these, never DELETE.
- **`rsg.change_log`**: what changed and when (`deploy.sh` adds live theme deploys; everything else `rsg-changelog-add`, README "Working rules"); planned rows become real when they happen.
- **`rsg.finding`** + **`rsg.finding_note`** (Ruggero's answers in chat are written here by Claude), **`rsg.experiment`** (nothing runs without Ruggero's OK and staging → "deploy to live"), **`rsg.report`**.

## Analyst
- Method and formats: `../hosting/hetzner/analyst/` (`CLAUDE.md`, `CONTEXT.md` — keep it current when the site changes —, `METHOD.md`, `REPORTS.md`, `QUERIES.md`); sample report `reports/SAMPLE-weekly-2026-W46.md`.
- **Not scheduled** (Ruggero 16 Nov): he asks Claude in the RSG project; unattended runs + email only when he asks (needs a `claude setup-token` token, a report address and a fresh MailerSend token).

## Privacy
- Privacy policy (page 1409, `/privacy-policy/`, source `privacy-policy-2026-11-16.html`): Umami, GA4, price boxes, affiliate links, Raptive, newsletter/Shoe Finder, contact, comments, rights; opt-out `/?rsg-notrack=1`. Retention 3 years: Umami by the monthly job; our view/click logs: to build before Nov 2029.

## Open
1. Compare Umami vs the old view/click logs vs GA4 (to ~30 Nov), then switch the old logs off or sample them (the view log also serves DFS country checks).
2. Bots: check the human share against GA4 during the parallel run; CN/SG/HK visits with normal screens and > 0 s (~165/day, 0 clicks) are still counted human: a country rule if they matter.
3. Events not built: email sign-ups site-wide, internal link clicks.
4. Raise the heatmap share if phone INP holds.
5. Sub-ID → page type join for the networks; AvantLink CA loader; Bing loader (decide).
6. Ruggero: Umami admin password and the `rsg_analyst` password into 1Password, then delete `_private/umami-admin.txt`; open the site once with `?rsg-notrack=1` on each browser.
