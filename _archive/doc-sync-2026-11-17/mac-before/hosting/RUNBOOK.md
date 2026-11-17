# Hosting — runbook (Rocket.net account, backups outside it, DNS, old Linode, server overview)

_Current state only. Checked 11 Nov 2026 ~15:40 UTC. Update in the same session as any change. Why: `../DECISIONS.md`. Ticket history: `ROCKET-BANDWIDTH-HANDOVER.md` (archive once the plan is confirmed). WordPress itself (caches, deploys, plugins): `../theme/RUNBOOK.md`. The two Hetzner servers: `hetzner/RUNBOOK.md` (rsgmedia), `../dfs/RUNBOOK.md` (rsg-dfs)._

## Servers at a glance
| Server | What | Runbook |
|---|---|---|
| Rocket.net `65.181.124.237` (`dal20.onrocket.com`) | WordPress live + staging | `../theme/RUNBOOK.md` |
| Hetzner **rsg-dfs** `135.181.110.157` (CAX11, Helsinki) | DFS production only | `../dfs/RUNBOOK.md`, `../dfs/PRODUCTION-SERVER.md` |
| Hetzner **rsgmedia** `116.203.146.177` (CPX32, Nuremberg) | n8n, Stargrid, DFS test bench, site check | `hetzner/RUNBOOK.md` |
| Linode `45.33.39.133` | Old DFS (rollback) until Phase F | below |

## Rocket.net account
- Plan today: **custom Expert (~$370/mo)**. Agreed in principle (ticket **122395**, 10 Nov): **Business yearly $83/mo + 15 GB extra storage $30/mo = $113/mo**, in place, no migration. Confirmation mail **not sent yet**: decide lean staging first (ROADMAP "Lean staging"). Extra storage $2/GB/mo; bandwidth overage $0.08/GB (overage from crawlers/malicious traffic not charged).
- Business = 40 GB storage, 300 GB bandwidth. Used (5 Nov): 53 GB (live uploads 25 GB + staging's full copy 25 GB + ~1.3 GB leftovers). Staging is always a full copy (Rocket.net can't exclude uploads); a lean staging (uploads served from live) frees ~25 GB. **11 Nov:** staging refreshed from live by our own script (not Rocket's re-sync) and serves missing uploads from live (`.htaccess` 302 rule, `../theme/RUNBOOK.md` "Refresh staging"); its 25 GB uploads copy was deleted the same evening (Ruggero OK; only files identical on live, 403,463 files; 49 staging-only files kept). Account now **~27 GB** (fits Business 40 GB without the add-on).
- Bandwidth (30 days to 5 Nov): 366 GB, 14.1M requests, 68.6 % from the edge; real visitors ≈ 110–150 GB.
- **Geo-caching rule:** the runningshoesguru.com part switched **off** 11 Nov ~08:45 UTC (ticket 122395); can be re-added on request. Measurement vs baseline: `../dfs/GO-LIVE-2026-11-10.md` (ROADMAP "Geo measurement").
- **Bot rules** (ticket 121944, 9 Nov): Rocket.net blocks IPs 34.232.137.97 + 184.72.84.161 and user agents with Bytespider / SeekportBot / Baiduspider (expressions never sent back). `robots.txt` (physical file `~/public_html/robots.txt`) also disallows `/go/`, SeekportBot, Bytespider, MJ12bot, DotBot, PetalBot, AhrefsBot, SemrushBot.
- The rules block only 2 AWS IPs and 3 user agents, so our own servers need no exemption: DFS pushes (135.181.110.157) and the site check (116.203.146.177) work daily.
- **Never block or challenge:** search and AI-answer crawlers (Googlebot, bingbot, GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot…), ad crawlers (AmazonAdBot, proximic, CriteoBot, Google ad crawlers, meta) — Raptive revenue. Must keep working: `/wp-json/*` (DFS pushes from 135.181.110.157; Make via the integromat connector), `/ads.txt`, `/robots.txt`, sitemaps. New rules: few at a time, Rocket.net proposes before applying, broad rules only as managed challenge in log-only mode first.
- Rocket.net backups: automated daily, 30 kept (panel → Backups).
- Contacts in tickets: Mehedi Hassan (122395), Andre Correa (121944).

## WordPress backup outside Rocket.net (since 11 Nov)
- Where: Google Drive (ruggeroloda@gmail.com) `Server-Backups/sites/rsg/` — `db/rsg-db_<stamp>.sql.gz.age`, `files/rsg-files_<stamp>.tar.gz.age` (wp-config.php, .htaccess, wp-content without uploads/cache), `uploads/rsg-uploads-<year>_<stamp>.tar.age` (one tar per year folder) + `rsg-uploads-other_<stamp>.tar.age` (loose files; caches `cache`, `dfs-images-cache`, `amazon-api-cache`, `minit`, `fvm` left out). Everything age-encrypted.
- When: Sundays, 04:10 UTC db + files (kept 60 days), 04:40 UTC uploads: past years only if missing on Drive, current + previous year + loose files every week (2 newest kept). Single files were tried first: Drive creates ~0.7 files/s, so 275k files would take days; per-year tars upload at ~15 MB/s. Crontab of `jfx9wij` on Rocket.net; script `~/rsg-backup/rsg-wp-backup.sh` (source `rocket/rsg-wp-backup.sh`), tools `~/bin/rclone` 1.68.2 + `~/bin/age` 1.2.1, Drive login `~/.config/rclone/rclone.conf` (same as the Hetzner servers: own OAuth client "rclone-backups", scope `drive.file`: sees only files it created), public key `~/rsg-backup/age-recipient.txt`. Log `~/rsg-backup/backup.log` (+ `uploads-errors.log`).
- Size 11 Nov: db 30 MB (after the table cleanup), files 50 MB, uploads ~25 GB in 19 tars.
- One-off archive: `Server-Backups/sites/rsg/archive/` (old sign-up tables, 11 Nov).
- Watched by: the `rsg-site` check (fails if the newest db backup is ≥ 8 days old).
- **Restore** (Mac, private key `_private/backup-age-key.txt` / 1Password): download the file from Drive, then
  `age -d -i backup-age-key.txt rsg-db_<stamp>.sql.gz.age | gunzip > rsg.sql`, then on Rocket.net (Mac shell): `scp` the file up → in the webroot `wp db export ~/rsg_deploy_backups/before-restore-<stamp>.sql` (safety copy) → `wp db import rsg.sql` (MariaDB dump; first line is a MariaDB sandbox comment) → `wp cache flush` (Redis object cache) → `wp eval 'rocket_clean_domain();' && wp cdn purge` → check a few pages. Site files:
  `age -d -i backup-age-key.txt rsg-files_<stamp>.tar.gz.age | tar -xz`; uploads: `age -d -i backup-age-key.txt rsg-uploads-<year>_<stamp>.tar.age | tar -x` inside `wp-content/uploads/`. Tested 11 Nov (103 tables, complete dump; 5,613 files incl. wp-config.php; uploads 2009 tar = 1,446 entries, same as the server). First full uploads run on the new login: 19 tars, 23.1 GiB, 9 min, no failures.
- **Drive login (all three servers, since 11 Nov):** own Google OAuth client "rclone-backups" (Desktop) in Cloud project `quickstart-1593179204049` (created by Google's Drive quickstart in June 2020; External, In production, scope `drive.file`). Credentials + token: `_private/google-oauth-rsg-backups.txt`, `_private/rclone-gdrive-rsg-backups.conf`. Replaced rclone's shared client, which hit `rateLimitExceeded` (uploads 2014–2022 failed); with it a 104 MB backup took ~3 min, now ~10 s. Root folder **`Server-Backups/`** (the new login can't see the old `Backups/`, which keeps the NAS copy). Previous configs kept as `rclone.conf.old-client-20261111` on each server.
- Backups folder rules: `hetzner/Backups-README.txt` (also on Drive as `Server-Backups/README.txt`): never create folders there by hand.

## Site check
Two healthchecks.io checks (Ruggero's account, same as the DFS heartbeat; alerts go to that account's notification channels, email by default — not checked from here), both pinged every 5 min by `/usr/local/bin/rsg-site-check.sh` on rsgmedia (`/etc/cron.d/rsg-site-check`, source `hetzner/rsg-site-check.sh`); each check runs in parallel with one retry after 30 s:
- **`rsg-site`** = is the site up (ping URL `/etc/rsg-site-check.url`): edge page, fresh origin render, price box on a review, off-site WordPress backup age (< 8 days), rsgmedia's own nightly backup (< 36 h).
- **`rsg-php`** = PHP health (since 14 Nov; ping URL `/etc/rsg-php-check.url`): **no fatal error in the last 60 min and logging on** (endpoint `/wp-json/rsg/v1/php-errors`, token `/etc/rsg-site-check.token` = WP option `rsg_php_errors_token`; details theme `RUNBOOK.md` "PHP error log"). A single fatal keeps it DOWN for up to 60 min. Split off from `rsg-site` 14 Nov because one scanner request to `/wp-admin/setup-config.php` (13 Nov 22:10 UTC) made `rsg-site` read "down" for 55 min while the site was fine.
- Test: `sudo DRYRUN=1 /usr/local/bin/rsg-site-check.sh`.
- `.htaccess` (live, top, block `rsg-block-installer`, 14 Nov): `wp-admin/setup-config.php` and `wp-admin/install.php` are refused (answer 404 via WordPress); backup `~/rsg_deploy_backups/htaccess-before-installer-block-20261114-044425`.

## DNS and domains
- runningshoesguru.com: **AWS Route 53**. Mail MX = Google Workspace. SPF `include:_spf.mailersend.net a mx include:_spf.mlsend.com ~all`; DKIM `mlsend2._domainkey` (MailerLite/MailerSend); `mta` → mailersend.net (return path); DMARC `p=none`, reports to dmarc@runningshoesguru.com.
- rsgmedia.info (n8n, server names): nameservers at **SiteGround**. Leftover records `portainer|adminer|npm.rsgmedia.info` to delete.

## Old Linode (until DFS Phase F)
"DFS-PERFORMANCE", Linode 4 GB ($24 + $5 backups), Ubuntu 18.04, `45.33.39.133`, SSH port 2200 (`../dfs/DFS-ACCESS.md`, read-only unless agreed). Feeds nothing since 10 Nov; `dstudio` on live still calls it hourly. Phase F (from ~17 Nov, `../dfs/PLAN-STEP7.md`): confirm no callers 7 days, `/go/` stub, deactivate `dstudio`, power off 1 week, cancel; remove the Linode Managed monitor (~3.4k hits/day on the site) and the `dfs-mon` cron.

## Rules in force (date = line in `../DECISIONS.md`)
- Stay on Rocket.net; Business yearly + 15 GB (9–10 Nov); geo rule off (11 Nov).
- Keep ad-industry and search/AI crawlers; block/challenge only scrapers and low-value bots (5–6 Nov).
- Off-server backups go to Google Drive `Server-Backups/<type>/<source>/` (until 11 Nov: `Backups/`), age-encrypted (one key pair, private key never on a server); Dropbox/OneDrive not used (5 Nov). WordPress copy outside Rocket.net on Drive (11 Nov).
- No Rocket.net key on Hetzner; no Hetzner key on Rocket.net (6 Nov).
- Measure before deciding (bandwidth, storage, geo).

## Open
1. Business plan mail **sent 11 Nov** (Business yearly, no storage add-on, ticket 122395): wait for Rocket.net's answer (start date/credit, staging in the 40 GB, bot rules kept, geo rule off), then update this file and `../costs/COSTS.md` (ROADMAP "Rocket.net → Business", "Lean staging").
2. Geo measurement after a few days (edge share up, mismatches ~0, clicks per 1,000 views unchanged).
3. Phase F (Linode) from ~17 Nov.
4. Delete SiteGround DNS leftovers.
5. After 11 Dec 2026: delete `Backups/servers/` and `Backups/sites/` in Drive by hand (old-login copies; the new login can't see or prune them). Keep `Backups/nas/`. Arq's Mac backups on the same Drive are untouched by all of this.
6. Parked: hotlink protection, top lists by bytes, ASN challenge (bandwidth overage is cheap).
