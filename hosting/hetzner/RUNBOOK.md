# Hetzner rsgmedia — runbook (n8n, Stargrid, DFS test bench, site check)

_Current state only. Checked 11 Nov 2026 ~15:40 UTC; re-checked 12 Nov ~08:40 UTC (OS, Docker, containers + ports, UFW, crons, nightly backup, test bench): no drift; `rsg-site-check.sh` changed 12 Nov (PHP check) and 14 Nov (PHP check split into its own healthchecks.io check `rsg-php`). Update in the same session as any change. History of the hardening: `archive/HANDOVER-2026-11-05.md`, `STARGRID-ADMIN-API-HARDENING.md` (archive). DFS production is a different server: `../../dfs/RUNBOOK.md`._

## The server
**rsgmedia**, Hetzner Cloud CPX32 (4 vCPU AMD / 8 GB / 160 GB), Nuremberg, `116.203.146.177`, Ubuntu 24.04.5, kernel 6.8.0-147, Docker 29.9.3. €20/mo grandfathered (new price €43.55): **never resize**. Disk 17 % used.

## What runs
| What | How | Notes |
|---|---|---|
| n8n 2.46.6 (pinned) + Postgres 16 | compose `/home/ruggero/compose/docker-compose.yml`, secrets `/home/ruggero/compose/.env` (600) | https://n8n.rsgmedia.info, own login + 2FA; DBs `n8n`, `stargrid` |
| Stargrid (playstargrid.com) | containers `stargrid-web`, `stargrid-api`, `stargrid-imggen`; host service `stargrid-gen-worker`; cron autopilot (`17 2 * * *` daily, `41 * * * *` tick), OG image (hourly), content build (`7 * * * *`), Stargrid DB dump (03:00, 7 days local) | Separate project; admin API hardening in `STARGRID-ADMIN-API-HARDENING.md`. `stargrid-pipeline.service` disabled (video posting off) |
| Nginx Proxy Manager | container; live data `/home/ruggero/compose/nginx/data` | Public 80/443 → n8n, Stargrid. Admin private |
| Portainer, Adminer | containers, `127.0.0.1` only | via tunnel |
| DFS test bench | container `dfs` (`/home/ruggero/dfs/app/docker/`), code by rsync, scheduler off, pushes only to staging | `../../dfs/RUNBOOK.md` |
| Site check | `/usr/local/bin/rsg-site-check.sh`, `/etc/cron.d/rsg-site-check` (every 5 min) | → healthchecks.io `rsg-site` (site up; also fails if this server's own backup is > 36 h old) `rsg-php` (PHP fatals) and `rsg-umami` (Umami: app heartbeat, tracker public, dashboard private, Search Console data ≤ 6 days, analyst metrics built ≤ 30 h ago; runs once `/etc/rsg-umami-check.url` exists; previous script `/usr/local/bin/rsg-site-check.sh.bak-20261116`), see `../RUNBOOK.md` |
| **Search Console loader** (16 Nov 2026) | image `rsg-gsc:1` from `~/gsc/` (source `gsc/`), cron `/etc/cron.d/rsg-gsc` 06:40 UTC, secrets `~/gsc/secrets/` + `~/gsc/.env` (600) | Writes schema `rsg` in the `umami` database; details `../../analytics/README.md` |
| **Analyst metrics** (16 Nov 2026) | image `rsg-metrics:1` from `~/metrics/` (source `metrics/`), cron `/etc/cron.d/rsg-metrics` 07:10 UTC, `~/metrics/.env` + `analyst.env` (600); SQL `rsg-sql` (`/usr/local/bin`, role `rsg_analyst`: reads all, writes only the notebook tables) and `rsg-changelog-add` (`/usr/local/bin`; called by the theme's `deploy.sh` on live deploys) | Writes `rsg.session_quality`, `metric_day`, `flag`, `page_type_map`, `metric_run` in the `umami` database; notebook `change_log`, `finding`, `finding_note`, `experiment`, `report`; details `../../analytics/README.md` "Metrics layer" + "Notebook" |
| **Umami 3.5.0** (analytics, 16 Nov 2026) | compose service `umami` (image `ghcr.io/umami-software/umami:3.5.0`, pinned), database `umami` (owner `umami`) in the shared Postgres 16; secrets `UMAMI_*` in `.env` | Public only via Nginx Proxy Manager host 7 `t.runningshoesguru.com` (Route 53 A → this server): Custom Nginx Configuration (⚙ in the host editor) exposes only `/js/main.js` (tracker), `/recorder.js`, `/api/m` (collect), `/api/record`, `/api/websites/<id>/recorder`; everything else 404 (dashboard, login, API). Dashboard/API from the server: `http://127.0.0.1:3010`. Env: `TRACKER_SCRIPT_NAME=js/main.js`, `COLLECT_API_ENDPOINT=/api/m`, `CLIENT_IP_HEADER=x-real-ip` + `SKIP_LOCATION_HEADERS=1` (16 Nov 10:33 UTC: Umami otherwise trusts client-sent `True-Client-IP` / `CF-Connecting-IP` / `CF-IPCountry` before X-Forwarded-For — 18 % of the first visits had no country and the headers could dodge `IGNORE_IP`; NPM sets X-Real-IP to the TCP peer, so only the real address counts), `TRUSTED_PROXIES=172.19.0.0/16` (the `proxy` network), `IGNORE_IP` = rsgmedia, rsg-dfs, Rocket.net (their hits answer 403), telemetry + update check off. Admin login `admin`: `_private/umami-admin.txt` → 1Password. Websites: RSG live `31037802-afa8-48d7-9ff0-badf153a16d8`, RSG staging `2d604d33-2cb6-4ed3-a7d0-359d5cf21671`. Backups: in the nightly `pg_dumpall`. Plan: `../../analytics/UMAMI-PLAN-2026-11-16.md`. **NPM regex pitfall:** a `{n}` quantifier in a location regex must be quoted, or nginx rejects the host and NPM takes it offline (16 Nov). |

## Access
- SSH `ssh -p 2222 ruggero@116.203.146.177`, key only (1Password "Hetzner" + Touch ID; Claude: `_private/claude-hetzner-key`; also `n8n@stargrid`, unused). Root and passwords off, `AllowUsers ruggero`, fail2ban.
- Private tools: double-click `Open RSG server tools.command` (= `rsg-tools.sh`) → Portainer http://localhost:19000, Adminer :18080, NPM admin :18081, DFS test bench :18090, Umami :18010 (since 16 Nov). sshd `PermitOpen` allows only 127.0.0.1:9000/8080/81/8090/3010; no socket forwarding.
- Open from outside: **80, 443, 2222** only (UFW; also `deny in on br-dfs`; 5432 only on `tailscale0`, Tailscale logged out). Port 3001 (imggen) on 127.0.0.1; 9876 closed.

## Automatic jobs
| When (UTC) | What |
|---|---|
| 03:30 daily | `/usr/local/bin/rsg-backup.sh` (`/etc/cron.d/rsg-backup`): all Postgres DBs, Docker volumes (incl. the n8n encryption key), `/home/ruggero`, system config → one age-encrypted archive (~108 MB) → `/var/backups/rsgmedia` (7) + Google Drive `Server-Backups/servers/rsgmedia/` (30 days). Log `/var/log/rsg-backup.log`. Restore tested 5 Nov. 20 Nov: the home tar accepts "file changed as we read it" (exit 1, live NPM logs) after one failed night |
| every 5 min | site check |
| daily | unattended **security** updates. **No automatic reboot**: reboot by hand when `/var/run/reboot-required` exists (none on 11 Nov) |
| hourly / daily | Stargrid jobs (table above) |

Not backed up: the DFS test bench volume `dfs_data` (production has its own backups).

## How to change things
- **n8n upgrade:** backup first (`sudo /usr/local/bin/rsg-backup.sh`), change the pinned tag in compose, `docker compose up -d n8n`, check workflows. The 2.15.0 rollback image and dump were deleted 20 Nov: for a rollback restore from the nightly backup.
- **Secrets:** only in `.env` files (600), never inline in compose. After editing an `.env`: `docker compose up -d --force-recreate <service>` (a restart doesn't reload it).
- **Reboot:** Postgres binds to the Tailscale IP → needs `net.ipv4.ip_nonlocal_bind=1` (`/etc/sysctl.d/60-docker-tailscale-bind.conf`) and docker after tailscaled (both in place; reboot tested 6 Nov).
- **NPM:** disabling a proxy host in the DB doesn't remove its conf file: check `proxy_host/*.conf`.
- Site check URLs: `/etc/rsg-site-check.url` + `/etc/rsg-php-check.url` (root, 600; previous script `/usr/local/bin/rsg-site-check.sh.bak-20261114`); PHP-error endpoint token `/etc/rsg-site-check.token` (root, 600, since 12 Nov; previous script `/usr/local/bin/rsg-site-check.sh.bak-20261112`). Test `sudo DRYRUN=1 /usr/local/bin/rsg-site-check.sh`.

## Rules in force (date = line in `../../DECISIONS.md`)
- SSH key only on 2222, no Tailscale dependency; one standing Claude key (6 Nov).
- Only 80/443/2222 open; admin tools private through the tunnel; n8n public with 2FA (OAuth, webhooks) (6 Nov).
- Secrets in `.env`, never inline (6 Nov). n8n pinned, upgrades on purpose after a backup (5 Nov).
- Nightly encrypted full backup, 7 local + 30 days on Drive; private key never on a server (5 Nov).
- Stargrid admin API: token in header only + browser password, no Docker socket; generation via job table + host worker (6 Nov).
- DFS production never runs here (9 Nov); the test bench is planned to become a stopped copy refreshed from production (ROADMAP "DFS simplification").

## Open
1. Rollback copies of the 6 Nov hardening deleted 20 Nov. Still to delete (Ruggero OK needed): `stargrid-api.bak-20261106`, `stargrid-api/.env.bak-20261106` (old token), `src/routes/admin.js.bak-*`; the 16 Nov Umami `.bak-20261116-*` files later. 18 apt updates pending (incl. Docker 29.10), not applied.
2. DNS leftovers `portainer|adminer|npm.rsgmedia.info` at SiteGround.
3. Stargrid image URLs need an HTTPS route through NPM before posting is reactivated (not a reopened 3001).
4. n8n: internal runner mode deprecated (revisit at the next upgrade).
