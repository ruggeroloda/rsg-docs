# DFS (price engine) — runbook

_Current state only (trimmed 26 Nov 2026; the long version: `archive/RUNBOOK-until-2026-11-26.md`). Code `4123b73` since 26 Nov; 31 feeds, 11 countries. Update this file in the same session as any change. Why: `../DECISIONS.md`. Every deploy: `logs/DEPLOY-LOG.md`. How it was built: `archive/`._

## What it does
Downloads merchant feeds and searches Amazon, matches products to RSG's reviews, builds one price JSON per review and pushes it into WordPress (post meta `_rsg_prices`). The page shows the US offers in its HTML and a script swaps in the visitor's country. Live since 10 Nov 2026.

## Where it runs
| Part | Where | State (11 Nov) |
|---|---|---|
| **Production** | Hetzner **rsg-dfs** `135.181.110.157` (CAX11 ARM, 2 vCPU/4 GB/40 GB, Helsinki), Ubuntu 26.04, Docker, container `dfs` | Code `4123b73` (= GitHub `main`). Scheduler **on**, push **on** → www.runningshoesguru.com. Countries hidden: none |
| Test bench | Hetzner **rsgmedia** `116.203.146.177`, container `dfs` | Code synced by rsync (no git): **branch `sportsshoes-gb`** since 27 Nov (= production + feed `ss_gb`; `.env` has `PARTNERIZE_SS_GB_URL`, backup `~/dfs/.env.bak-20261127`). Copy of production's database for tests: `/data/prod.sqlite` (15 Nov; `-e DFS_DB=/data/prod.sqlite`). Scheduler **off**, push **on** but target = **staging** (only on "Run now"). GB hidden there |
| WordPress side | Rocket.net live + staging | mu-plugins `rsg-dfs-receiver.php` (1.3.0), `rsg-dfs-clicks.php`; shop thumbnails in `wp-content/uploads/rsg-dfs/<hash>.webp` (live + staging); theme `includes/rsg-dfs-prices.php`, `css/rsg-dfs.css`, `js/rsg-dfs.js`, `images/merchants/`; option `rsg_prices_default=1`; user `dfs-push` (ID 115, role `rsg_dfs_pusher`); `_rsg_prices` on 429 reviews |
| Old DFS (rollback) | Linode `45.33.39.133`; `dstudio` plugin still active on live | Feeds nothing since 10 Nov. Retired in Phase F (ROADMAP "Phase F") |

Code: `dfs/app/` = GitHub `ruggeroloda/rsg-dfs` (private). Data: Docker volume `dfs_data` → `/data/dfs.sqlite`, raw files `/data/raw/<day>/`. Secrets: `~/dfs/.env` (mode 600) on each server; variable names in `app/.env.example`.

## Shops per country (26 Nov, shoes with an offer)
| Country | Shops |
|---|---|
| US | Amazon 320, Road Runner Sports 88, Zappos 83, Running Warehouse 67, Nike 15, Mizuno 15, Hoka 13, Puma 9, Brooks 7, adidas 6, New Balance 3 |
| CA | Running Room 90, MEC 10 |
| GB | Hoka UK 16, Nike UK 15, New Balance 5, Puma UK 3 |
| DE | Running Warehouse DE 50, Nike DE 15, New Balance 10 |
| IT | Running Warehouse IT 50, Nike IT 15, New Balance 10 |
| FR, ES, IE, GR, SE | Running Warehouse (EU file per country) 50 each |
| AU | Running Warehouse AU 73, Pace Athletic 58, adidas AU 6 |
Other countries: Amazon "Check price" search in their own store (GB/IE, EU, CA) or the US offers. Brooks US thumbnails show "no image" (brooksrunning.com refuses server requests).

## Access
- Host keys: rsg-dfs ED25519 `SHA256:yayvYhQnFvfWBqHP9vvhf/lLJCFVywPW8o3dZyWx6jQ` (first seen 25 Nov, now in `_private/ssh_known_hosts`); rsgmedia: `../hosting/hetzner/RUNBOOK.md`.
- SSH: `ssh -p 2222 ruggero@135.181.110.157` (rsg-dfs) / `…@116.203.146.177` (rsgmedia). Key only: Ruggero's 1Password key; Claude's standing key `_private/claude-hetzner-key`.
- Screens (Shoes / Feeds / Prices): no login. The tunnel is the access control (the container publishes only on the host's 127.0.0.1:8090; sshd `PermitOpen` allows only that). A Host-header check (`DFS_WEB_HOSTS`) and a per-process form token block other web pages. Mac only: production `dfs/production/Open DFS production.command` → http://localhost:18091; test bench `hosting/hetzner/Open RSG server tools.command` → http://localhost:18090.
- WordPress: Rocket.net SSH from the Mac only (`theme/.ssh-rocket-key`); the cloud shell can't reach Rocket.net.
- GitHub: `dfs/app` pushes with the write deploy key `_private/github-rsg-dfs-deploy-key` (in the repo's `core.sshCommand`; port 22 worked 11 Nov, else use `ssh.github.com:443`). The server pulls with its own **read-only** key.

## Daily schedule (UTC)
| Time | Job | Push + purge? |
|---|---|---|
| 01:30 | Shoe list from WordPress (`/rsg-dfs/v1/shoes`): new reviews, list prices, renames | — |
| 02:00 | Amazon US search: every active shoe × gender, 3 pages (~1,550 calls, ~45 min) | **yes** (~02:45) |
| 03:15 | Server backup (cron) | — |
| 04:30 | Reboot, only if a security update needs it | — |
| 06:30 Zappos · 06:35 Hoka US · 06:45 RW DE · 06:50 RW US · 07:00 Nike DE · 07:05 Nike UK · 07:10 Hoka UK · 07:15 RW AU · 07:20 adidas AU · 07:25–07:35 RW FR/IT/ES/IE/GR/SE · 07:37 Nike IT · 07:46 Running Room CA · 07:48 MEC CA · 07:50 Road Runner Sports US · 07:52–07:56 New Balance DE/IT/GB | Download + match + rebuild | no (waits for 08:00) |
| 08:00 · 14:00 · 20:00 | Amazon re-price of the ASIN pool (~600 calls, ~15 min) | **yes** |
| 12:00 Nike US · 12:10 Puma US · 12:20 Puma UK · 12:40 Pace Athletic AU | Download + match + rebuild | no (waits for 14:00) |
| 22:00 | Price-history snapshot, then thumbnail clean-up (`wp_push.prune_images`, logged as job `images`) | — |
| every 30 min | Heartbeat to healthchecks.io | — |
| 1st of month 07:30 | Monthly health report email (first run 1 Dec) | — |

A slot runs up to 3 h late (scheduler thread checks every 15 s); a failed run retries after 30 min, 3 tries. Slots missed while the switch was off are not caught up. File changes reach the site with the next Amazon push, so at most ~2 h later. A push re-sends every shoe whose JSON hash changed. Check times are part of the hash, so each Amazon slot re-pushes the ~315 shoes with Amazon offers (one full HTML purge, max 4 a day). The push note shows "real N" = shoes with real price/link changes (the 14:15 UTC push on 11 Nov set the baseline: "real 0, 317 no baseline"; counts start with the next push).

## Switches and where settings live
- DFS (`meta` table, on the Feeds/Prices screens): `scheduler` on/off, `wp_push` on/off ("Push now" always works), `countries_hidden` (Prices screen, "Countries on the site").
- Feeds: table `feeds` (transport, format (CSV, or JSON with `json_items` = the key of the item list: item keys act as column names; 25 Nov, code `4563683`), column map, sub-ID, schedule, 70 % row guard, options such as stock and kids), edited on the Feeds screen with a 20-row preview. A feed names the `.env` variable for its secret, never the secret itself.
- Shoes: table `shoes` (from WordPress, synced daily) + `rules` (origin auto/manual) + `blocks`; active and gender overrides on the Shoes screen. Copies of the last 14 WordPress lists: `/data/wp/shoes-<day>.json`.
- Logos: table `merchant_logos` (live theme URLs, `?v=20261110`; RW AU + adidas AU `?v=20261115`; Pace Athletic + MEC `?v=20261125`; Road Runner Sports `?v=20261126`).
- WordPress: site option `rsg_prices_default=1` turns the box on; per page `?dfs=0|1` or post meta `_rsg_prices_on`.

## How to change things
- **Feed, rule, block, active/gender:** on the production screens. A rule saved there becomes `manual` and rebuilds never overwrite it ("Back to auto rule" resets it). Offers change at the next match run (or "Run now").
- **New review or changed list price in WordPress:** picked up by the 01:30 UTC sync (production reads live, the test bench reads staging), priced by the 02:00 Amazon search and the morning feeds, on the site after the 02:45 or 08:15 push. Which reviews count: every published road/trail/training/`review` post with a legacy `ds_query`, plus every one published from 10 Nov 2026 (`RSG_DFS_NEW_FROM` in the receiver). A new shoe gets an automatic rule (brand + model) and the flag "new: check rule" on the Shoes screen: check its matches there. Empty `model` in WordPress → "no model in WordPress" (rule matches nothing). Brand/model changed → automatic rule rebuilt (manual rules untouched). Removed/unpublished → inactive. The sync is refused (failure email, nothing changed) if WordPress lists < 100 shoes or < 90 % of last time. Now: `docker exec dfs python -m dfs.shoes pull`.
- **New merchant logo:** `app/tools/logos/optical.py` (in git, source images next to it; run it in that folder; never hand-size), commit the file to the theme repo's `images/merchants/` and ship it with a theme deploy (never copy it to the server by hand: `deploy.sh` mirrors the repo with `--delete` and removes files that aren't in git; 26 Nov), set the URL on the Prices screen, bump `?v=`.
- **DFS code:** commit in `dfs/app` → push GitHub → test on rsgmedia (rsync `app/` without `.env`, `docker compose -f docker/compose.yml up -d --build`) → on rsg-dfs, with no job running (Feeds screen): `cd ~/dfs/app && git pull && docker compose -f docker/compose.yml up -d --build`. After a `.env` change: `up -d --force-recreate`. Log it in `logs/DEPLOY-LOG.md` (before 11 Nov: `logs/DEPLOY-LOG-STEP6.md`, `-STEP7.md`).
- **Price box (theme files):** only in the theme repo `theme/rsg_2022/` (deploy with `deploy.sh`, see `theme/RUNBOOK.md`). `dfs/app/wordpress/theme/README.md` maps every DFS edit in the theme. After a change to `js/rsg-dfs.js` (or any CSS/JS), `deploy.sh` clears WP Rocket's minified copies (`wp-content/cache/min/`, which `rocket_clean_domain()` doesn't touch) and its used CSS, then purges everything, so pages regenerate pointing to fresh files. Rule (replaces the 10 Nov "copy over, never delete" lesson, which was about clearing without a purge): **never clear the minified copies without the full purge right after**, or edge-cached pages would point to deleted files. `rocket_clean_domain()` doesn't clear `cache/min`.
- **mu-plugins:** `rsg-dfs-receiver.php` = `POST /wp-json/rsg-dfs/v1/prices/<post id>` (writes `_rsg_prices`), `GET /shoes` (the review list, no-store), `/purge` (Rocket.net `html` tag purge with the site token, max once a minute), `POST /image/<hash>` (one thumbnail: WebP only, ≤ 50 KB, ≤ 400 px, bytes must hash to the name; never overwritten) and `POST /images/prune` (deletes our thumbnails not in the DFS list and older than 30 days; refuses an empty list; reports missing files, which the DFS re-uploads); `rsg-dfs-clicks.php` (1.3.0, 13 Nov) = `/click` + `/view` logs + a footer script that logs every other affiliate link (box `gps`/`amzk`/`bought`/`text`/`go`, shop from the link host). Source `dfs/app/wordpress/*.php`, copied by hand to `wp-content/mu-plugins/` (staging first, back up the live file first). Live = staging = repo (receiver 1.3.0 md5 `bb5d278b…` checked 15 Nov; backup of receiver 1.2.0 in `~/rsg_deploy_backups/mu-rsg-dfs-receiver-20261115-091608/`).

## Roll back
- One feed: `UPDATE feeds SET enabled=0 WHERE feed='<slug>'` + `docker exec dfs python -m dfs.jobs build prices` (pushes). Deploy-specific backups and commits: `logs/DEPLOY-LOG.md`.
- Thumbnails only: `git revert 18331cf` on rsg-dfs + rebuild + push (the JSON goes back to the shops' own links; files in `uploads/rsg-dfs/` can stay). Receiver: copy `~/rsg_deploy_backups/mu-rsg-dfs-receiver-20261115-091608/live.php` back (only after the DFS code is reverted: pushes need the image route).
- Whole box (until Phase F): `wp option delete rsg_prices_default` + `wp cdn purge` → old dstudio boxes. Whether the old DFS on the Linode still runs its own nightly import was not checked after 10 Nov: check its last import before relying on this rollback. Per page: `?dfs=0` or meta `_rsg_prices_on='0'`.
- Code: on rsg-dfs `git checkout <previous commit>` + the same compose command.
- Stop updates: Feeds screen scheduler off and/or push off. The site keeps the last pushed prices; Amazon prices hide themselves once older than 24 h.
- Data: restore from backup (`PRODUCTION-SERVER.md` → Restore). Rebuild the server in ~1 h: same file → Rebuild.

## Alerts, monitoring, backups, retention
- **Failure emails** (MailerSend API, sender newsletter@runningshoesguru.com, to Ruggero): failed or refused feed job, failed build or push; max 1 per feed/job per 6 h, 15 a day; manual runs never email. Links point to http://localhost:18091.
- **Heartbeat** `/usr/local/bin/dfs-heartbeat.sh` → healthchecks.io check `rsg-dfs` (Ruggero's account; period 1 h, grace 7 h). Fails if the container is down, disk ≥ 80 %, Drive backup > 36 h old, scheduler off, or no ok job in 8 h. Test: `sudo DRYRUN=1 /usr/local/bin/dfs-heartbeat.sh` (11 Nov: OK).
- **Monthly report** `/usr/local/bin/dfs-monthly-report.sh` (print only: `REPORT_PRINT=1`).
- **Backups:** `/usr/local/bin/dfs-backup.sh` at 03:15: SQLite + `.env` + config, age-encrypted → `/var/backups/dfs` (7) + Google Drive `Server-Backups/servers/dfs` (30 days); Hetzner backups (7 daily). Log `/var/log/dfs-backup.log`. Private key only on the Mac (`_private/backup-age-key.txt`) and in 1Password.
- **Kept for:** raw feed files 3 days; Amazon ASIN without a price drops out after 7 days; price history 730 days (since 7 Nov); WordPress click and view logs (`newguru_wp_rsg_clicks`, `newguru_wp_rsg_views`) **no limit yet** (ROADMAP "DFS simplification").
- **Server upkeep:** unattended **security** updates only, reboot 04:30 if needed; UFW allows only 2222; fail2ban (sshd); container reaches the internet only (`dfs-firewall.service`). No Hetzner Cloud Firewall.

## Rules in force (date = line in `../DECISIONS.md`; before 18 Nov: `../_archive/DECISIONS-until-2026-11-17.md`)
- Countries, merchants and shoes are configuration; scope comes from intent, not past revenue (5–6 Nov). Every published review with a `ds_query` started active; dead ones are switched off by hand ("no offers" flag) (6 Nov).
- One container: Flask screens + scheduler thread (one job at a time) + SQLite. No n8n inside DFS, no Cloudflare dependency of its own (4, 7 Nov).
- Prices live in WordPress post meta `_rsg_prices`, written only through the narrow REST route by the low-privilege user, via `update_post_meta` (modified date untouched); 16 KB cap, strict validation (6–7 Nov).
- Page HTML = US offers (men + women) for bots, schema and no-JS. The script swaps the country (visitor's own choice, else Cloudflare trace, else US) and re-applies the Amazon 24 h rule. Schema = one USD `AggregateOffer` from the HTML's US offers, never swapped, no per-seller offers (7–8 Nov).
- Countries on the site: the 11 in "Shops per country". Reviews without a local offer: "Check price" Amazon search in the visitor's own store (18 Nov). A country with no feed gets the US offers; a country with a feed but no offer for that shoe shows "No offers". No fallback to the server-side country when the trace fails (7, 10, 11 Nov).
- Shoes outside DFS show nothing; DFS shoes with empty lists show "No offers". An inactive shoe keeps its last JSON (7–8 Nov).
- Up to 4 offers per country × gender, one per merchant, cheapest first, ties alphabetical; "from" price (cheapest size); thumbnail on every offer (7–8 Nov).
- Thumbnails (15 Nov): every non-Amazon shop image is our own copy: fetched once, light background flattened, trimmed, centred on white **192×144 WebP** q80 (~2 KB; 2× the biggest box thumbnail 96×72, sharp on retina), name = SHA-1 prefix (no purge ever needed), stored in the DFS database and pushed before the prices (a failed upload stops that push). **Amazon images are never copied** (Associates policy: links only, ≤ 24 h): Amazon's `images.primary.medium` link (160 px), renewed at every re-price. No usable image → the "no image" picture (`dfs/assets/no-image.webp`, drawn by `tools/images/no_image.py`). Failed sources retried after 24 h; sources unused 30 days dropped; files on the site deleted after 30 days unused. `alt=""` stays (shop + price are in the row's text).
- Offer filter: drop offers > 1 % above the list price or below ~40 % of it. List price = WordPress `rrp_<cc>`, else the shop's own; with neither, keep the offer and mark it "no list price". WordPress owns list prices (21 Jun, 6 Nov).
- Matching: rules built from WordPress metadata (brand alias + family + version); product name only, whole words, case-insensitive, hyphen = space; trademark marks ignored. Variant words make a different product (`VARIANTS` in `app/dfs/rulebuild.py`; per-shoe `allow_after`), and so do widths (wide/2E/4E/6E/narrow). Longer name wins. Kids, non-shoes, no-gender and out-of-stock rows are dropped (6–10 Nov). Kids = kids words in the name, or the feed's kids column (`options.kids`): Hoka US uses its category column 29 = "Kids > Footwear > Shoes" because its kids names carry no kids word (19 Nov).
- Gender per offer from the feed, else from the name; each shoe "both" unless overridden (6–7 Nov).
- Amazon: no ASIN pinning for shoes. Search + pool, prices only if checked < 24 h ago, untouched Amazon links, one tag per marketplace, "as of" time shown. Third-party sellers kept. `GetVariations` tested, not used (kept in reserve). Quota is not a constraint (~190k calls/day at Oct–Nov revenue) (6 Nov).
- File feeds: a download is accepted only if the columns exist and it has ≥ 70 % of the last good row count; offers stay while the last accepted import is < 72 h old (7 Nov).
- Push + purge only after the Amazon jobs; check times stay in the hash; one purge per batch; pacing 0.5 s, wait-and-retry on HTTP 429 (7, 11 Nov).
- Links: direct affiliate links, `rel="sponsored nofollow"`, sub-ID `{page}-{box}-{country}` (CJ `sid`, Rakuten `u1`, AWIN `clickref`; RW fixed `from=guru`; Amazon untouched) (6–7 Nov).
- Click and view logs: country codes only, no IP, user agent or cookie (8, 10 Nov).
- Price history: one daily row per shoe × gender × country × merchant, kept 2 years. Deal signals: ≥ 20 % below list price (US only, no DE list prices); "low" = lowest in 90 days and ≥ 10 % below the 30-day median, needs 14 days of history (from ~21 Nov) (6–7 Nov).
- Brand-level merchant exclusions from the old DFS are not carried over (6 Nov).
- rsg-dfs runs the DFS only. Code reaches it only through GitHub after testing; its deploy key is read-only (9 Nov).
- CJ needs paramiko 3.5.x (DSA host key) (6 Nov).
- GPS reviews are not in DFS: a per-country "Check price on Amazon" button; DFS GPS prices (7b) dropped (10–11 Nov).
- Logos: the optical set only; bump `?v=` on every logo change (8 Nov).

## Known gaps and open items
1. Phase F not done: old Linode running, `dstudio` active, `/go/` stub (`app/wordpress/go/index.php`: 301 to the page named in `referer=`) not live, network logins and MailerSend token not rotated (one token shared with the theme).
2. Network sub-ID reports (AWIN Click References, CJ, Rakuten `u1`) waiting for the first commissions.
3. Staging and live share Rocket.net `CDN_SITE_ID` 69650, so a staging purge **may** also clear live (not proven).
4. Planned (ROADMAP "DFS simplification"): retire the test bench as a second running DFS, nightly settings dump to git, matcher fixture tests, view log sampled + retention. Amazon slots: all 4 kept (26 Nov).
5. List prices (checked 19 Nov): WordPress has only `rrp_us` (429 shoes); `rrp_gb/de/au/ca` are empty everywhere, and the lookup is by country, so FR/IT/ES/IE/GR/SE would never use `rrp_de`. Outdated `rrp_us` values hide full-price US offers (filter > 1 % above list). Design and test run: `LIST-PRICES-2026-11-19.md`.
6. Links in emails were meant to carry an "email" sub-ID (6 Nov); not built.
7. **Pace Athletic AU** (Commission Factory feed 11571): the only AU shop with its own list price (AU boxes show "% off" for it); the 40 % floor hides its deepest clearance (30–37 % of RRP): floor kept, Ruggero to confirm. Its price is the cheapest size; links open that size.
8. **More merchants (step 8), next:** SportsShoes UK live **Tue 1 Dec** after "deploy to live" (merge `sportsshoes-gb`, `PARTNERIZE_SS_GB_URL` into production `.env`, logo + theme deploy, one test click); Asics US (Rakuten, old DFS feed 86); AU: Commission Factory brands not joined (Brooks, RunDNA, Mizuno, ASICS); later NZ/SG/MY/HK from the RW AU file (`vat=<cc>`). AU has no list prices in WordPress, so AU offers are not price-filtered.
9. **Road Runner Sports US + New Balance DE/IT/GB** (Impact, 26 Nov): FTP paths `Road-Runner-Sports/Impact-2025_IR.csv.gz`, `New-Balance-EMEA/{DE-Standard,IT-Running,Uk-Standard}_IR.csv.gz` (first scheduled imports ok 27 Nov: 88 / 11 / 10 / 5 shoes). RRS width after the size (only Medium kept); NB width = 2nd SKU part (men's/unisex D, women's B); NB UK name cut at ', size'. RRS 'Original Price' drops on clearance models: not a list-price source on its own.

## Accounts, networks and quotas
- Affiliate networks:
  - **Amazon Associates**: Creators API, US tag `AMAZON_US_PARTNER_TAG`; GPS buttons GB `runshogur-21`, DE `runshogur01-21`, CA `runningsho053-20`, US/other `rsg-single-20`.
  - **CJ** (Nike US, Puma US/UK; SFTP), **Rakuten** (Zappos, Hoka US; FTP), **AWIN** (Nike DE/UK/IT, Hoka UK, adidas AU feed 118206; API key in the feed URL), **Running Warehouse** direct (public CSVs US/EU/AU, `from=guru`).
  - **Impact**: FTP `products.impact.com` (one login = the old DFS one, env `IMPACT_FTP_URL`; catalogue list `catalogs_info_file.xml` at the root); standard "IR" CSV; sub-ID `subId1`. Used: Brooks, Mizuno, adidas ("DataFeedWatch" catalogue), New Balance US, Road Runner Sports, New Balance EMEA (DE Standard, IT Running, UK Standard; one catalogue per country). Not used: NOBULL, Under Armour UK, adidas "YES". The revenue API token has no catalogue scope (feeds come by FTP only).
  - **AvantLink CA** (account 222669, website 279417, API key `_private/avant_link_ca.txt`; API `classic.avantlink.com/api.php`, AffiliateReport works with affiliate ID + key): Running Room feed 301697 (env `AVANTLINK_RR_CA_URL`), MEC datafeed 369093 (merchant 18557, 6 %, zip CSV, `Retail Price` = list; env `AVANTLINK_MEC_CA_URL`); sub-ID `ctc`.
  - **Commission Factory** (affiliate 85747; API key line 2 of `_private/commission_factory.txt`, wants `?apiKey=`; feeds JSON/XML at `/V1/Affiliate/DataFeeds/<id>`): Pace Athletic (env `CF_PACE_AU_URL`); sub-ID `UniqueId`.
  - **Partnerize** (SportsShoes, account pz-rsgmedia; 10 % default, Black Friday vouchers 1 % to 2 Dec): feeds work again since 27 Nov for **UK** (`1110l312`, zip of a Google Shopping CSV, ~106k rows, ~03:00 UTC) and IT (`1011l3196`); DE/FR/ES/IE/US "not processed". Feed URLs (with key) in `_private/partnerize_sportsshoes.txt`. Feed **`ss_gb`** on branch `sportsshoes-gb` (rsg-dfs `0545876`, `1384975`), **test bench only**: 9,411 offers on 138 shoes (juniors, out of stock, non-running and "(… Width)" rows out). Links are `prf.hn` tracking links: sub-ID = path segment `/pubref:<id>/` (`subid.style="path"`). No Partnerize API keys yet.
- **Amazon Creators API quota:** 1 call/day per $0.05 of shipped revenue in the previous 30 days (+1 call/s per $4,320), 8,640/day in the first 30 days; one getItems of 10 ASINs = one call; HTTP 429 when over; **no quota headers**, so DFS counts its own calls (table `api_calls`). At the Oct–Nov revenue (~$9.7k shipped) ≈ 190k calls/day; the DFS uses ~1,550 searches + ~1,800 re-price calls a day. **Access is revoked after 30 days without sales.**
- **Amazon outside the US:** not available. The UK Associates account fails the Creators API eligibility bar (HTTP 403 `AssociateNotEligible` since ~5 Sep 2026; needs ~10 sales in 30 days); CA/DE/IT have no working setup. UK/DE prices come from the file feeds.
- WordPress fields the DFS reads (through the shoe sync): `brands` taxonomy (brand), `model` (fallback `shoe_name`), `rrp_us|gb|de|au|ca` (list prices), legacy `ds_query` (scope only; matching rules live in DFS). Table prefix `newguru_wp_`.

## Checks and tools
- Data and switches: production screens; `docker exec -i dfs python - < app/tools/check_prices.py`.
- Thumbnails: `docker exec dfs python -m dfs.images status` (counts, failed sources); `python -m dfs.images fetch <url> out.webp` (one image by hand); page check from any shell: `python3 app/tools/images/check_page.py <review URL> …` (every thumbnail loads, own copy or Amazon, bytes; exit 1 on a problem).
- Live page checks: `app/tools/pricebox/` (`chk.js`, `val.js`, `sweep.py`, `cls.js`, `clicks.js` posts real beacons, so don't run it on live). They hard-code the staging address; run from the cloud shell (Playwright).
- Click/view logs (live, from `~/public_html`): tables `newguru_wp_rsg_views` (ts, page_id, country shown, want, src c chosen / t trace / f failed / p pending, hdr = Cloudflare country, vis = first view of the visit) and `newguru_wp_rsg_clicks` (+ box, shoe, merchant, gender).
  `wp db query "SELECT COUNT(*) views, SUM(vis) visits, SUM(want<>'' AND want<>hdr) mismatch, ROUND(100*SUM(src='f')/COUNT(*),1) trace_fail_pct FROM newguru_wp_rsg_views WHERE ts>='<from>'"`
  `wp db query "SELECT merchant, country, COUNT(*) n FROM newguru_wp_rsg_clicks WHERE ts>='<from>' GROUP BY 1,2 ORDER BY n DESC"`
  Mismatch = `want<>hdr` (a visitor's own choice is not a bug). Geo-cache measurement baseline: `GO-LIVE-2026-11-10.md`.
- Server facts, restore, rebuild: `PRODUCTION-SERVER.md`. Old DFS: `ARCHITECTURE.md`, `DFS-ACCESS.md` (archive after Phase F).
