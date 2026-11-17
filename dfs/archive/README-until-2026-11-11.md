# DFS — price engine

_Last updated 2026-11-11._

**What it is:** imports merchant/affiliate feeds, matches them to RSG's shoes, and pushes per-country prices into WordPress; the site logs clicks and page views itself. **Since 10 Nov the new Python DFS is live**; the old 14-year-old PHP app on the Linode only remains as the rollback until Phase F (facts in `../CONTEXT.md`).

**Set-up:** a small Python app in one Docker container: production on its own server **rsg-dfs** (Hetzner CAX11, Helsinki; `PRODUCTION-SERVER.md`), tested first on rsgmedia (the test bench, pushes to staging only). Rebuild Plan doc + Challenge review: https://claude.ai/artifact/FNBUHjND581QjFN8t2p7KP. Diagram: `dfs-architecture.html` / https://claude.ai/artifact/RtUQYyJ7tYyCDLSm3vT5k3

**Status (11 Nov):** steps 1–7 Phase E done: **live since 10 Nov** (`GO-LIVE-2026-11-10.md`). Open: Phase F (retire the old DFS, from ~17 Nov), step 7b (GPS US prices), step 8 (more merchants), network sub-ID checks at the first commissions. **Read `HANDOVER-STEP7.md` and `GO-LIVE-2026-11-10.md` first.**

## Questions settled on 6 Nov (history; later changes in `../DECISIONS.md`)
1. ~~Price delivery vs the per-locale cache~~ **Decided 6 Nov** (`../DECISIONS.md`): US offers in the HTML; after load a script swaps in the visitor's country from the shoe's all-countries price data embedded in the page, if that shoe has offers there. Works with the per-locale cache on or off.
2. ~~Where the price files live~~ **Decided 6 Nov**: pushed into WordPress through a narrow REST route and stored in a hidden post-meta field on each review, embedded in the page (conditions in `../DECISIONS.md`); no separate files, nothing served from Hetzner.
3. ~~Keep `/go/` vs direct links~~ **Decided 6 Nov**: direct links everywhere + first-party click beacon (Amazon included); legacy `/go/` stub for old IDs. Also decided: Amazon prices refreshed every 6 h; all HTML purged after each push (superseded by Q4 freshness line) (details in `../DECISIONS.md`).
4. ~~Price history~~ **Decided 6 Nov**: daily snapshot per shoe × gender × country × merchant; two deal signals; HTML purged after every 6-hourly push (details in `../DECISIONS.md`).
5. ~~Matching rules~~ **Decided 6 Nov**: move into DFS, keyed by WP post ID, with instant preview; one-time import of the 1,520 `ds_query` strings; rules in git.
6. ~~Scope~~ **Decided 6 Nov**: scope is configuration. Build with five feeds: Amazon US, Nike US (CJ), Zappos (Rakuten), Running Warehouse DE, AWIN Nike DE (added 6 Nov); other AWIN feeds next.

## Agreed requirements
- One container: admin app (Flask) + scheduled Python scripts + SQLite. No n8n, no own Cloudflare dependency.
- Feeds screen to add/manage feeds: transport / format / column mapping with 20-row preview / network sub-ID. New merchants or networks = configuration, not code.
- Amazon: keyword search per shoe + rules + block list; no ASIN pinning; follow Creators API terms (untouched links, one tag per marketplace, timestamp).

## Files
Current:
- `HANDOVER-STEP7.md` (start here), `GO-LIVE-2026-11-10.md` (go-live account, lessons, rollback, open items), `PRODUCTION-SERVER.md` (server facts, operations, deploy, restore, rebuild), `PLAN-STEP7.md` (phases A–G; "as built" notes at the top), `DEPLOY-LOG-STEP7.md`.
- `app/`: the new DFS code (git repo, GitHub `ruggeroloda/rsg-dfs`). `app/wordpress/theme/README.md` = every theme file the DFS touched.
- `production/`: scripts + launcher for the production server (`Open DFS production.command` → localhost:18091). `app/docker/` = compose, Dockerfile, host firewall; `app/spike/` = step 1 spike scripts.

History (steps 1–6, done): `HANDOVER-STEP2/3/5/6.md`, `BRIEF-STEP5/6.md`, `DEPLOY-LOG-STEP6.md`, `BRIEF-PRICEBOX-MACHINE-READABLE.md` + `PRICEBOX-MACHINE-READABLE-RECOMMENDATION.md`, `DESIGN-STEP6.4.md`; scratch and rollback copies in `_work/`; logo sources in `logos/`.

Legacy DFS (archive after Phase F):
- `ARCHITECTURE.md`: reverse-engineered legacy system. Note: its hosting sections (§12, §14) are outdated; use `../CONTEXT.md` for current server facts.
- `DFS-Consumer-Contract-Report.md`: how the theme calls DFS (14 fields, request shapes, `/go/`, `export.cvs`).
- `DFS-ACCESS.md`: access + Amazon state (passwords removed → `../_private/`).
- `AMAZON-PATH-HANDOFF.md`: theme's separate Amazon lookup (GPS watches, gift guides); GPS replaced by the Amazon button 10 Nov.
- `legacy-snapshot/`: read-only code snapshot of the live Linode (4 Nov 2026) + crontab.
- `legacy-scripts/`: the Amazon Creators API fetch scripts and old Amazon code.
- `archive/`: superseded plans (Laravel proposal, Flask/Postgres spec, old audit brief, old next-session plan). Kept for reference only.
