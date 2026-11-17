# DFS production server — facts, operations, rebuild, restore

_Built 9 Nov 2026 (step 7 Phase B). Deploy log: `logs/DEPLOY-LOG-STEP7.md`. Decisions: `../DECISIONS.md` 9 Nov._

## Facts
Current server facts (hardware, OS, access, container, switches): `RUNBOOK.md` → "Where it runs", "Access". This file keeps the operations detail, restore and rebuild.

## Automatic operations
| What | How | When (UTC) |
|---|---|---|
| Security updates | unattended-upgrades, security pockets only (no release upgrades); `52unattended-upgrades-local` | daily; **reboot 04:30 if needed** (after the 02:00 Amazon search, before the 06:30 feeds; missed slots retried for 3 h) |
| Backup | `/usr/local/bin/dfs-backup.sh` (cron `/etc/cron.d/dfs-backup`): sqlite online backup + integrity check, `.env`, config → age-encrypted (public key only on the server) → `/var/backups/dfs` (7) + Google Drive `Server-Backups/servers/dfs` (30 days). Log `/var/log/dfs-backup.log` | 03:15 |
| Heartbeat | `/usr/local/bin/dfs-heartbeat.sh` (cron every 30 min) → healthchecks.io check `rsg-dfs` (Ruggero's account, period 1 h, grace 7 h; URL in `/etc/dfs-heartbeat.url`). OK only if: container running, disk < 80 %, Drive backup < 36 h, scheduler on, a DFS job ok in the last 8 h; else a `/fail` ping with the reason. `DRYRUN=1` prints. | every 30 min |
| Monthly report | `/usr/local/bin/dfs-monthly-report.sh` → email via the DFS MailerSend sender: updates, reboots, Docker, disk, backups on Drive (+ newest identical), job ok/failed counts, switches, fail2ban. `REPORT_PRINT=1` prints. | 1st of month 07:30 |
| Job failures | the DFS's own failure emails (unchanged) | per job |

## Deploy a code change
Test on rsgmedia first. Then here: `cd ~/dfs/app && git pull && docker compose -f docker/compose.yml up -d --build` (no job running: check the Feeds screen). Rollback: `git checkout <previous commit>` + same command.

## Restore (test it by hand a few times a year)
1. Get an archive: Google Drive `Server-Backups/servers/dfs/dfs_<stamp>.tar.zst.age` (or `/var/backups/dfs/` on the server).
2. Decrypt where the private key is (Mac, `_private/backup-age-key.txt`, also in 1Password): `age -d -i backup-age-key.txt dfs_<stamp>.tar.zst.age | zstd -d | tar -x` → `dfs.sqlite`, `dfs.env`, `etc.tar`, `git_commit.txt`.
3. Check: `sqlite3 dfs.sqlite "PRAGMA integrity_check"` → ok. (Tested 9 Nov from the Drive copy: ok; feeds 11, rules 429, blocks 2, amazon_pool 5022, price_json 429, logos 11.)
4. Into a running server: `docker compose -f docker/compose.yml stop`, copy `dfs.sqlite` into the volume (owner 10001, mode 600, remove old `-wal`/`-shm`), `up -d`.

## Rebuild from scratch (~1 h)
1. Hetzner: CAX11 (or any ≥2 GB), Ubuntu LTS, both SSH keys, Backups ON. (New IP → update this file, `dfs-production.sh`, the healthchecks note, and the Rocket.net exemption if any.)
2. As root: `apt full-upgrade`; install `fail2ban ufw sqlite3 age rclone zstd git curl docker.io docker-compose-v2`; user `ruggero` (sudo NOPASSWD, keys from root); reboot.
3. From the backup's `etc.tar`: restore `/etc/ssh/sshd_config.d/ssh-hardening.conf`, `/etc/fail2ban/jail.local`, `/etc/apt/apt.conf.d/52unattended-upgrades-local`, `/etc/cron.d/dfs-*`, `/etc/rsg-backup/age-recipient.txt`, `/etc/sudoers.d/90-ruggero`, `/usr/local/bin/dfs-*.sh`, `dfs-firewall.*`. `ufw allow 2222/tcp; ufw enable`; restart ssh (keep a session open until 2222 works). Copy root's rclone config from rsgmedia (`/root/.config/rclone/rclone.conf`). `/etc/dfs-heartbeat.url` (healthchecks.io → check → ping URL).
4. New read-only deploy key on GitHub; `git clone git@github.com:ruggeroloda/rsg-dfs.git ~/dfs/app`; `.env` from the backup (mode 600); `docker compose -f docker/compose.yml build`; load `dfs.sqlite` into volume `dfs_data` (Restore step 4); `systemctl enable --now dfs-firewall`; `up -d`.
5. Check: `DRYRUN=1 dfs-heartbeat.sh` OK, Shoes screen through the tunnel, one manual backup run.

## Follow-ups
- ~~`dfs/alerts.py` linked to the test-bench port~~ done 11 Nov: `DFS_WEB_URL=http://localhost:18091` in `.env` (commit `658f5c9`).
- Optional: a Hetzner Cloud Firewall (2222 only) as a second layer in front of UFW. Not set up (Ruggero, 11 Nov).
