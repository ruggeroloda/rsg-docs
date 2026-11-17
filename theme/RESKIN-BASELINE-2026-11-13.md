# Reskin baseline — 13 Nov 2026

_What the site looks like and measures **before** the reskin. Re-run the same checks after each reskin day and compare. Files: `theme/baseline-2026-11-13/` on the Mac (not in the project: screenshots are ~190 MB). Scripts: `theme/checks/` (README)._

**State:** theme + `rsg-core` `7462d98` (tag `live-20261113-115900`), rsg-core 0.4.0, WP 7.1.4, click log 1.3.0. Raptive layout changed by Raptive on 13 Nov ~16:00 UTC (below-post ads removed, mobile one-ad-per-screen limit): ad counts below are **after** that change.

## Sample pages (21)
`/`, road/trail/training/GPS review, legacy `/review/` (Speedgoat 5), 2 comparisons (one redesign pilot: Clifton vs Bondi), guide, top list, content article, blog post, `/reviews/`, brand listing (`/reviews/asics/all/`), type listing (`/reviews/all/cushioning/`), family listing (`/reviews/nike/pegasus/`), `/blog/`, author, quiz, contact, a 404. Exact URLs: the keys of `cwv-live.json` (+ `/this-page-does-not-exist-rsg-404/` for the 404).

## Screenshots
`baseline-2026-11-13/shots/<page>_{390,820,1366}.png` (full page after scrolling; ads, YouTube **and Typekit blocked** → fallback font in the pictures, compare like with like). `shots/metrics.json`: page height, visible images, broken images (0 everywhere), blank iframes (0).

## Ad slots (Raptive, `adcount.py`, no impressions) — phone / desktop
| Page | Slots | | Page | Slots |
|---|---|---|---|---|
| Home | 1 / 1 | | Guide | 4 / 11 |
| Road review | 8 / 9 | | Top list | 4 / 12 |
| Trail review | 11 / 12 | | Content article | 11 / 11 |
| Training review | 9 / 9 | | Blog post | 6 / 7 |
| GPS review | 8 / 7 | | `/reviews/` + brand/type/family listings | 6 / 10 |
| Legacy `/review/` | 2 / 2 | | `/blog/`, author, quiz, contact | 2 / 2 |
| Comparison (Glycerin/Nimbus) | 6 / 7 | | 404 | 0 / 0 (`adthrive-disable-all`) |
| Comparison (Clifton/Bondi) | 7 / 7 | | | |

vs the step-3 count earlier on 13 Nov: reviews −1 per device (below-post gone), phone blog post 9 → 6 and `/reviews/` 7 → 6 (screen limit). Placement still depends on `.container > .row > .col-lg-8 > *` and `.col-lg-8+.col-4 > div`: recount after every structural change.

## Speed (lab, cloud, no throttling, ads blocked; `cwv.py`) — `cwv-live.json`, `cwv-recheck.json`
- LCP 0.5–1.0 s on almost all pages; CLS ≤ 0.024 except **family listings** (Typekit font swap: Pegasus 0.31 desktop, Ghost 0.09 phone; 0 without Typekit). Typekit also holds text LCP on desktop: legacy Speedgoat 1.5–1.8 s, quiz 1.6–2.0 s (~0.8 s without).
- TTFB edge HIT 0.33–0.61 s (through the cloud proxy); fresh origin render 0.42–0.77 s.
- WP Rocket: used CSS + minified files on 20/21 pages; **family listings get none** (cause unknown, `wprocket-check.txt`).
- Field data (real users): Search Console → Core Web Vitals (not checked here).

## SEO / schema (step 4) — `seo-check.json`, `schema-snapshot.json`, `sitemap-*.{txt,jsonl}`
JSON-LD valid on every template that has it; canonical self + indexable on every sample; sitemaps 2,477 URLs, 1,712 checked all 200/self-canonical. Day-1 fixes decided: listing `og:url`, og/twitter on `/blog/` + authors, `Sitemap:` line in robots.txt (RUNBOOK gap 16).

## Clicks and views (step 3, since 11 Nov 09:40 UTC)
12,864 box views, 234 clicks = **18.2 per 1,000 views** (baseline 19.5); box country US 93.0 / GB 5.5 / DE 1.5 %; trace failure 7.1 %; 0 wrong-country clicks; 324 shoes with offers; quiz 7 days 188 starts / 20 sign-ups. Every affiliate click logged since 13 Nov (new kinds `gps`, `amzk`, `bought`, `text`, `go`).
- **Bot traffic warning:** 13 Nov 12:00–13:00 UTC a distributed crawler (~530 IPs, 37 countries, browser user agents, runs JavaScript) went through the review listings: view log for `/reviews/` listings 132 in that hour vs ~30 normally, GA showed a visitor spike, clicks did not move (2). Treat GA/view counts for that hour as bot-inflated; compare clicks, not views, across such spikes.

## Server load (Rocket.net, 13 Nov)
- 24 h: 500k requests, 72.7 % served by the edge, 27.3 % by the origin; firewall blocked 25 %, challenged 31 %, rate-limited 10k.
- Origin requests 12:13–17:11 UTC (36,760): static files 32 %, plain pages 32 %, pages with query strings 10 %, `admin-ajax` mega-menu 8 %, WP Rocket used-CSS crawler 6 %, price-box beacons 5 %, other REST 3 %, wp-admin/cron 2 %, site search 2 %.
- Main causes of origin load: the 4 daily price purges (all HTML; accepted, prices live in the HTML), deploy purges, bots. **Proposed for reskin day 1:** print the mega-menu in the page HTML instead of loading it by `admin-ajax` (removes ~8 % of origin requests and one request per page view).
- 13 Nov 15:00–15:02 UTC: origin slow (renders 13–26 s), `rsg-site` DOWN 2 min 42 s on the PHP-endpoint step; edge copies unaffected; cause not found (normal traffic). Watch for repeats; if it recurs, send Rocket.net the panel's load graph.

## Backups and monitoring (step 6, 13 Nov)
Rocket.net daily Platform Backups (30, latest 13 Nov 07:09); off-site weekly backup OK 11 Nov (first cron run Sun 15 Nov 04:10 UTC); DFS backup 03:15 OK; rsgmedia backup 03:30 OK; healthchecks `rsg-site` + `rsg-dfs` green, email alerts; WP-Cron 29 events, 0 overdue; DFS jobs all OK since 12 Nov (one Amazon search connection drop 12 Nov 02:00, retry OK).
