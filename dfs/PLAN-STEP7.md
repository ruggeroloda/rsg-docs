# DFS step 7 — production + switch-over: plan (9 Nov 2026, for approval)

Decisions this plan rests on: `../DECISIONS.md` 9 Nov (launch feeds, production server, GPS). Rules: live theme = patch the live files (never copy staging over), backup first, lint `ea-php83 -l` + `ea-php74 -l`, box-off page identical, checks in `app/tools/` (pricebox/, go/), log everything (`DEPLOY-LOG-STEP7.md`), purge Rocket.net after live deploys. One phase at a time, result shown, wait for Ruggero.

## Measured for sizing (Hetzner test container, 8–9 Nov)
- Memory peak **131 MB** (cgroup `memory.peak`, file cache included) over a full re-match of all 4 file feeds (Zappos 770k rows) + the nightly Amazon search (1,604 calls, 46 min); idle 45 MB; CPU ~0.
- Data 1.1 GB: `dfs.sqlite` 84 MB (+ history, ~1 GB/yr at full scale), raw files 415 MB (3 days), `spike.sqlite` 435 MB (not carried over), backups ~100 MB. Image 239 MB.

## As built (added 11 Nov 2026)
Status: **A–E done (live 10 Nov)**; F (retire old DFS) from ~17 Nov; G (docs) done 11 Nov except the GitHub/after-go-live items. Differences between this plan and what was built (details in `DEPLOY-LOG-STEP7.md`, decisions in `../DECISIONS.md`):
- Server: Ubuntu **26.04** LTS (the image Hetzner gave), not 24.04. **No Hetzner Cloud Firewall** (UFW only, 2222). Container memory cap **1 GB** (compose unchanged), not 512 MB.
- Heartbeat: a cron every 30 min that checks container, disk, backup age, scheduler and the last job, not a ping after each build/push.
- Monthly health check: **emailed by the server** (cron + MailerSend), not a scheduled Claude task.
- Countries: **GB switched on 10 Nov** (Nike UK, Hoka UK, Puma UK), not only US + DE.
- Phase E GPS: the old boxes were replaced by a **"Check price on Amazon" button** per country (10 Nov), not just hidden.
- Phase C: files were first extracted with a tar (mode problem, fixed); rule since then: copy files one by one (`GO-LIVE-2026-11-10.md`).

## Phase A — launch feeds (Hetzner + staging; no production needed) — ~2–3 h
1. Feed configs on the Feeds screen, with the 20-row preview: **Running Warehouse US** (public CSV, same format as RW DE, `from=guru`), **Hoka US** (Rakuten FTP, same login as Zappos, Rakuten "mp" format → own column map), **Puma US** (CJ SFTP, same login as Nike US, same Google-Shopping format). Paths from the old `datafeeds` table (read-only). No new secrets. Sub-IDs: Rakuten `u1`, CJ `sid`; RW fixed.
2. Schedule slots next to the existing ones (UTC): RW US 06:50, Hoka US 06:35, Puma US 12:10.
3. Download + match each; report: shoes/offers gained, wrong matches found in a spot check (Shoes screen preview), above-list/variant drops. Fix rules only where clearly wrong.
4. Logos for Hoka + Puma (`app/tools/logos/optical.py`), staging `images/merchants/`, `merchant_logos` rows, version bump.
5. Build + push to staging; `chk.js`/`val.js` on a page with each new merchant; sweep subset. Log + commit.

## Phase B — production server (Tue/Wed, after Ruggero orders it) — ~half a day
Ruggero: order the server — **updated 9 Nov: Hetzner CAX11 (ARM Ampere, 2 vCPU, 4 GB, 40 GB) in Helsinki** (only low-cost type available; ARM checked: python:3.12-slim + all packages + host tools exist for arm64; the image is built on the server), Ubuntu 24.04, name `rsg-dfs`, Hetzner Backups ON, his SSH key + Claude's standing key (`_private/claude-hetzner-key.pub`). Claude:
1. Harden as Hetzner: SSH key-only on 2222, `AllowUsers`, fail2ban, UFW (2222 only), Hetzner Cloud Firewall (same), unattended **security-only** updates (no release upgrades) with automatic reboot in a window with no feed/Amazon job (pick from the schedule), 1 GB swap file, timezone UTC. **Reboot test** (container back by itself, scheduler running).
2. Docker; clone `rsg-dfs` with a **read-only** deploy key; same compose/hardening (read-only root, non-root, caps dropped, memory cap 512 MB), `dfs-firewall` as on Hetzner, Shoes screen on 127.0.0.1 only + a tunnel entry in `rsg-tools.sh` (production on its own local port, e.g. 18091).
3. Data: Hetzner scheduler OFF → sqlite backup copy of `dfs.sqlite` (rules, blocks, overrides, Amazon pool, history, logos) → production. `.env` copied; `WP_PUSH_*` filled in Phase C.
4. Backups: nightly `sqlite3 .backup` + `.env` → age-encrypted → Google Drive `Backups/servers/dfs/` (keep 30) + Hetzner backups (7). **Restore test** from the Drive copy (decrypt, open, row counts match). **Rebuild runbook** `dfs/PRODUCTION-SERVER.md`: new server → running DFS in ~1 h (written while building, so Phase B is its first test).
5. Heartbeat (to confirm): free healthchecks.io check pinged after each successful build/push; email if silent for 8 h (covers box/container down, which the failure emails can't). **Confirmed 9 Nov.** The ping also fails/alerts when disk > 80 % or the newest Drive backup is > 36 h old.
6. Production scheduler ON, **push OFF**; one full cycle of feeds runs. Hetzner stays the test bench (scheduler OFF; "Run now" + push to staging when needed).
7. **Monthly health check** (agreed 9 Nov): a scheduled task, 1st of each month, that checks pending/applied updates and last reboot, backup age + a test decrypt, disk, heartbeat history, failure emails of the month, container restarts; sends Ruggero a short report. Set up once the server runs.
8. **Scope rule:** this server runs the DFS only (no n8n, Stargrid, WordPress or other tools) — logged in DECISIONS.

## Phase C — live WordPress, invisible (box off everywhere) — ~half a day
1. Backups: every live file touched, `*.bak-dfs7-<ts>` next to it + a tarball in `~/rsg_backups_dfs7_<ts>/`.
2. mu-plugins `rsg-dfs-receiver.php` + `rsg-dfs-clicks.php` to live; role `rsg_dfs_pusher`, user `dfs-push`, application password → production `.env` (`WP_PUSH_URL|USER|APP_PASSWORD`, live URL).
3. `images/merchants/` to the live theme; `merchant_logos` URLs → live (`?v=` bump).
4. Theme: copy the 3 new files (`includes/rsg-dfs-prices.php`, `css/rsg-dfs.css`, `js/rsg-dfs.js`); **patch** the existing live files with the 6.3 edit scripts (`_work/step6.3/…`): `functions.php`, `includes/rsg-schema.php`, `single-reviews_{road,trail,training}.php`, `single-comparison.php`, `includes/comparison-redesign-block.php`, `single-guide.php`, `single-top.php`, `includes/widgets/widgets.php`, `includes/shortcodes.php`, `include-single-review.php`, `include-single-review-trail.php`, `template-parts/guide-boxes.php`, `comments-full-width.php`, `sidebar-review.php`. Plus one line in `rsg_prices_on()`: site option `rsg_prices_default` (the switch for Phase E). Proof before upload: each patched live file = the staging file (both carry the same non-DFS fixes); any difference explained. Lint 8.3 + 7.4.
5. **Box off = before** on live: `tagdiff.py` on ~15 pages (each template), `sweep.py` live with no parameter and `?dfs=0`.
6. First full push to live (all shoes, ~4 min, one purge).
7. Live `?dfs=1` checks: `chk.js`, `val.js`, `sweep.py` (live URL list), `clicks.js` (shops blocked; test rows deleted after). Purge.

## Phase D — side by side (2–3 days)
- Production push to live ON (prices refresh ~4×/day, purge after each push). Pages still show the old DFS by default.
- Daily: feed runs, alerts, push runs; compare old vs new on ~30 shoes (`?dfs=0` vs `?dfs=1`): offers, prices, links land (one click per network on live).

## Phase E — switch
1. `wp option update rsg_prices_default 1` + purge. GPS: the broken old boxes hidden (small guarded edit in the GPS templates, prepared in Phase C, box off = before checked).
2. Sweep live without parameter; PHP error log; 24 h watch: 500s, click log rows by shop/box, Amazon report next day.
3. Rollback = `wp option delete rsg_prices_default` + purge (old DFS still running).

## Phase F — retire the old DFS (after ~1 week stable)
1. Nothing calls the old DFS: inventory any unguarded `dstudio()` call, then deactivate the dstudio plugin; Linode access log shows no callers for 7 days.
2. `/go/` stub live (backup `~/public_html/go/index.php`, `go-check.sh https://www.runningshoesguru.com`, purge).
3. Old Linode **powered off** (kept 1 week as the rollback), then cancelled; Linode Managed monitor + `dfs-mon` removed. Linode account closed if nothing else is on it.
4. Rotate the network logins now living in the new `.env` (Rakuten FTP, CJ SFTP, AWIN key; Impact when used) and the MailerSend token.

## Phase G — docs and after go-live
- `HANDOVER-STEP7.md`, DECISIONS, CONTEXT (production server facts), README status, GitHub commit.
- After go-live: AWIN Click References + CJ/Rakuten sub-IDs on the first commissions; step 7b GPS via DFS (fixed ASINs, US); step 8 more feeds; then the theme rebuild.
