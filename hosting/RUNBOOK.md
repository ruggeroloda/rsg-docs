# Hosting — runbook (Rocket.net account, backups outside it, DNS, old Linode, server overview)

_Current state only. Checked 11 Nov 2026 ~15:40 UTC. Update in the same session as any change. Why: `../DECISIONS.md`. Ticket history: `ROCKET-BANDWIDTH-HANDOVER.md` (archive once the plan is confirmed). WordPress itself (caches, deploys, plugins): `../theme/RUNBOOK.md`. The two Hetzner servers: `hetzner/RUNBOOK.md` (rsgmedia), `../dfs/RUNBOOK.md` (rsg-dfs)._

## Servers at a glance
| Server | What | Runbook |
|---|---|---|
| Rocket.net `194.39.148.145` (`dal11.onrocket.com`, since 24 Nov) | WordPress live + staging; PHP `short_open_tag` **Off** here (web + CLI; `php_flag` in `.htaccess` is ignored, tested 24 Nov): theme code uses `<?php` only (old `<?` tags broke old reviews + articles 07:22–11:08 UTC 24 Nov) | `../theme/RUNBOOK.md` |
| Hetzner **rsg-dfs** `135.181.110.157` (CAX11, Helsinki) | DFS production only | `../dfs/RUNBOOK.md`, `../dfs/PRODUCTION-SERVER.md` |
| Hetzner **rsgmedia** `116.203.146.177` (CPX32, Nuremberg) | n8n, Stargrid, DFS test bench, site check | `hetzner/RUNBOOK.md` |
| Linode `45.33.39.133` | Old DFS (rollback) until Phase F | below |

## Rocket.net account
- Plan: **Managed Business, annual billing, since 12 Nov 2026** (ticket 122395, Rocket.net's mail of 12 Nov): $1,000/yr (~$83/mo), first charge **25 Nov 2026**; no storage add-on; the bot and geo rules were not affected. Was custom Expert (~$370/mo). Extra storage $2/GB/mo; bandwidth overage $0.08/GB (overage from crawlers/malicious traffic not charged).
- Business = 40 GB storage, 300 GB bandwidth. Used (5 Nov): 53 GB (live uploads 25 GB + staging's full copy 25 GB + ~1.3 GB leftovers). Staging is always a full copy (Rocket.net can't exclude uploads); a lean staging (uploads served from live) frees ~25 GB. **11 Nov:** staging refreshed from live by our own script (not Rocket's re-sync) and serves missing uploads from live (`.htaccess` 302 rule, `../theme/RUNBOOK.md` "Refresh staging"); its 25 GB uploads copy was deleted the same evening (Ruggero OK; only files identical on live, 403,463 files; 49 staging-only files kept). Account now **~27 GB** (fits Business 40 GB without the add-on).
- Bandwidth (30 days to 5 Nov): 366 GB, 14.1M requests, 68.6 % from the edge; real visitors ≈ 110–150 GB.
- **Geo-caching rule:** the runningshoesguru.com part switched **off** 11 Nov ~08:45 UTC (ticket 122395); can be re-added on request. Measurement vs baseline: `../dfs/GO-LIVE-2026-11-10.md` (ROADMAP "Geo measurement").
- **Bot rules** (ticket 121944, 9 Nov): Rocket.net blocks IPs 34.232.137.97 + 184.72.84.161 and user agents with Bytespider / SeekportBot / Baiduspider (expressions never sent back). `robots.txt` (physical file `~/public_html/robots.txt`) also disallows `/go/`, SeekportBot, Bytespider, MJ12bot, DotBot, PetalBot, AhrefsBot, SemrushBot.
- **Managed Challenge** on the two networks behind the 13 Nov headless-bot wave (AS150436 BytePlus, AS63199 CDS Global Cloud), asked 14 Nov, enabled by Rocket.net 16 Nov (ticket 122395). Effect on bot share: Umami / `analytics/README.md`.
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
- **`rsg-site`** = is the site up (ping URL `/etc/rsg-site-check.url`): edge page, fresh origin render, price box on a review, an old review + an article at the origin (HTTP 200, since 24 Nov: `/review/salomon-speedcross-5-review/`, `/content/alternatives-to-the-asics-nimbus-26/`; previous script `.bak-20261124b`), off-site WordPress backup age (< 8 days) and size (newest db file ≥ 1 MB, since 24 Nov), rsgmedia's own nightly backup (< 36 h).
- **`rsg-php`** = PHP health (since 14 Nov; ping URL `/etc/rsg-php-check.url`): **no fatal error in the last 60 min and logging on** (endpoint `/wp-json/rsg/v1/php-errors`, token `/etc/rsg-site-check.token` = WP option `rsg_php_errors_token`; details theme `RUNBOOK.md` "PHP error log"). A single fatal keeps it DOWN for up to 60 min. Split off from `rsg-site` 14 Nov because one scanner request to `/wp-admin/setup-config.php` (13 Nov 22:10 UTC) made `rsg-site` read "down" for 55 min while the site was fine.
- Test: `sudo DRYRUN=1 /usr/local/bin/rsg-site-check.sh`.
- **An alert during or after maintenance is checked, never assumed to be the maintenance** (24 Nov: the `rsg-php` alert at 08:25 local was a real outage, read as the server move).

## After any server change (move, PHP version, PHP settings, plan change)
Run all of it right after Rocket.net (or we) change the server, and again ~1 h later:
1. **One page per template, bypassing the cache** (`?cb=<random>`, expect 200 and no raw `<?` in the HTML): homepage, new review (`/reviews/road/…`), trail review, GPS review (`/reviews/gps/garmin-forerunner-265/`), old review (`/review/…`), dated post (`/2012/10/…`), article (`/content/…`), comparison, `/top/` list, `/reviews/` listing, search results (`/?s=pegasus`), Shoe Finder; the same on staging.
2. **PHP error log** (`~/rsg-logs/php-errors-live.log` + `-staging.log`): no new Parse/Fatal lines since the change.
3. **PHP settings vs the old server**: `ea-php83 -i | grep -E "short_open_tag|memory_limit|max_execution_time|upload_max_filesize|post_max_size"`; code check of the whole theme with the server's real settings (`ea-php83 -l`, as `deploy.sh` does: a lint failure on files you didn't touch = the server changed).
4. **Site check by hand**: `sudo DRYRUN=1 /usr/local/bin/rsg-site-check.sh` on rsgmedia (all three ok).
5. Cron, backups, SSH key, outbound IP (allow-lists: rsgmedia `IGNORE_IP`, DFS push), WP-CLI, `deploy.sh` host — as in the 24 Nov move checks (Open 8).
- `.htaccess` (live, top, block `rsg-block-installer`, 14 Nov): `wp-admin/setup-config.php` and `wp-admin/install.php` are refused (answer 404 via WordPress); backup `~/rsg_deploy_backups/htaccess-before-installer-block-20261114-044425`.

## DNS and domains
- runningshoesguru.com: **AWS Route 53**. Mail MX = Google Workspace. SPF `include:_spf.mailersend.net a mx include:_spf.mlsend.com ~all`; DKIM `mlsend2._domainkey` (MailerLite/MailerSend); `mta` → mailersend.net (return path); DMARC `p=none`, reports to dmarc@runningshoesguru.com.
- rsgmedia.info (n8n, server names): nameservers at **SiteGround**. Leftover records `portainer|adminer|npm.rsgmedia.info` to delete.

## Old Linode (until DFS Phase F)
"DFS-PERFORMANCE", Linode 4 GB ($24 + $5 backups), Ubuntu 18.04, `45.33.39.133`, SSH port 2200 (`../dfs/DFS-ACCESS.md`, read-only unless agreed). Feeds nothing since 10 Nov; `dstudio` on live still calls it hourly. Phase F (from ~17 Nov, `../dfs/PLAN-STEP7.md`): confirm no callers 7 days, `/go/` stub, deactivate `dstudio`, power off 1 week, cancel; remove the Linode Managed monitor (~3.4k hits/day on the site) and the `dfs-mon` cron.

## Rules in force (date = line in `../DECISIONS.md`)
- Stay on Rocket.net; Business yearly, no storage add-on (agreed 11 Nov, in place 12 Nov); geo rule off (11 Nov).
- Keep ad-industry and search/AI crawlers; block/challenge only scrapers and low-value bots (5–6 Nov).
- Off-server backups go to Google Drive `Server-Backups/<type>/<source>/` (until 11 Nov: `Backups/`), age-encrypted (one key pair, private key never on a server); Dropbox/OneDrive not used (5 Nov). WordPress copy outside Rocket.net on Drive (11 Nov).
- No Rocket.net key on Hetzner; no Hetzner key on Rocket.net (6 Nov).
- Measure before deciding (bandwidth, storage, geo).

## Open
1. ~~Business plan~~ done 12 Nov (Rocket.net answered 12 Nov; $1,000 on 25 Nov). Left: check the 25 Nov invoice says $1,000 and how the unused Expert time was credited (Ruggero asked on 11 Nov; the 12 Nov answer does not say).
2. Geo measurement after a few days (edge share up, mismatches ~0, clicks per 1,000 views unchanged).
3. Phase F (Linode) from ~17 Nov.
4. Delete SiteGround DNS leftovers.
5. After 11 Dec 2026: delete `Backups/servers/` and `Backups/sites/` in Drive by hand (old-login copies; the new login can't see or prune them). Keep `Backups/nas/`. Arq's Mac backups on the same Drive are untouched by all of this.
6. Parked: hotlink protection, top lists by bytes, ASN challenge (bandwidth overage is cheap).
7. **Server stalls on Rocket.net** (ticket 123597). **19 Nov 10:40–10:44 UTC**: almost no requests completed (9–15/min vs ~70), static files stalled too; Redis socket read errors 10:42:59 + 10:44:23; 49 HTTP 500 on recovery; shorter slowdown 13:19–13:22. Rocket.net (Raouf Saada, 20 Nov): their infrastructure maintenance + security patches, nothing to change on our side; ticket closed 20 Nov. **21 Nov 13:29:30–13:32:00 UTC (Saturday)**: same pattern (1–6 requests per 10 s vs 15–30, static files too, 13:30 site check got no answer); no 500s, no PHP or Redis errors, no job of ours, normal traffic before; no other stall minutes 08–22 UTC since 19 Nov. Ruggero reopened the ticket 21 Nov ~14:50 UTC (same cause as 19 Nov? anything on our side?). Next: read their answer; if stalls keep repeating, send them the new times.
8. ~~Server transfer~~ done 24 Nov 07:24 UTC (Rocket.net, ticket 123597; zero downtime): new server `dal11.onrocket.com`, IP `194.39.148.145` (inbound SSH and outbound); old `65.181.124.237` / `dal20` gone. Checked 07:33 UTC: live + staging 200, SSH key works, crontab (backups Sun 04:10/04:40, wp-cron) and Redis socket unchanged, 32 cores. Old DFS (Linode) allowlist doesn't know the new IP: harmless, Phase F.
9. ~~Weekly WordPress backup failing~~ fixed 24 Nov (Ruggero's OK): cron's PATH had no `/usr/local/bin`, so `wp db export` wasn't found and `rclone rcat` uploaded 220-byte empty files on 15 + 22 Nov (deleted 24 Nov). Script PATH line fixed (server = repo, md5 `37cf2d1b…`; old copy `~/rsg-backup/rsg-wp-backup.sh.bak-20261124`); manual run 24 Nov 07:36 UTC OK (db 26 MB, files 28 MB); site check now also needs the newest db backup ≥ 1 MB. Next automatic run: Sun 29 Nov 04:10 UTC.
10. **Redis crash 25 Nov 21:07:26 UTC (caused by Claude):** Redis (`~/redis/`, 6.2.24, socket `~/redis/redis.sock`, 1 GB allkeys-lru, no persistence; **db 0 = live, db 3 = staging** object cache, Object Cache Pro) segfaulted during a bulk `STRLEN` scan of db 3 (crash in `stringObjectLen`, key object type/encoding/refcount 0 = corrupted). It restarted itself at 21:07:31; both caches emptied; live answered 500 to 7 requests 21:07:27–30, none since; report in `~/redis/redis_log`. Rule: **no bulk or scan commands against this Redis** (read single keys only). Same db 3 held 109 empty `comment_meta` entries written in one burst ~24 Nov 11:02 UTC (from remaining TTL; writer not found), which made reviews fatal on staging; the theme now guards against that (`../theme/RUNBOOK.md`). Ticket to Rocket.net drafted 25 Nov, **parked** (Ruggero). Rocket.net SSH host key ED25519 `SHA256:GAK7C9gErxmxBWYSxcOZMSvCASRgE/KBCgfI1+2S68Q` (seen 25 Nov from the Cowork VM).
