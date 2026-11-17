# RSG — project primer

_Last updated: 2026-11-11._ Start every RSG chat here, then read the README of the workstream you're working on._

**What this is:** relaunch of RunningShoesGuru.com (RSG): cut running costs, modernise the stack, and restart content on a small budget. Traffic is ~10% of what it was; running costs roughly match declining revenue.

## Workstreams

| # | Workstream | Folder | Status (11 Nov) | Next step |
|---|---|---|---|---|
| 1 | Costs | `costs/` | Register updated 11 Nov; a few costs still unknown | Fill the unknowns (MailerSend, Airtable, rsg-dfs from the first Hetzner invoice) |
| 2 | Hosting (Rocket.net, Linode, Hetzner) | `hosting/` | Bot blocks live (6/9 Nov). **Geo-cache rule switched off 11 Nov ~08:45 UTC** (one shared HTML copy; measuring vs the baseline in `dfs/GO-LIVE-2026-11-10.md`). Rocket.net Business yearly + 15 GB ($113/mo) agreed in principle 10 Nov, not yet confirmed. Server leftovers cleaned 11 Nov (archive `_private/server-cleanup-2026-11-11/`) | Decide lean staging → send the Business plan mail; check the geo measurement after a few days |
| 3 | DFS (price engine) | `dfs/` | **LIVE since 10 Nov** (step 7 Phase E): the new price box is the default on the live site; production server **rsg-dfs** (Hetzner CAX11, Helsinki) runs the feeds and pushes changed prices to WordPress after every run. rsgmedia = test bench. Old Linode still running as the rollback. Code: GitHub `ruggeroloda/rsg-dfs` (private) | Phase F (retire the old DFS, from ~17 Nov), step 8 (more feeds), step 7b (GPS prices). Start from `dfs/HANDOVER-STEP7.md` + `dfs/GO-LIVE-2026-11-10.md` |
| 4 | Theme (`rsg_2022`) | `theme/` | **In git since 11 Nov**: private GitHub `ruggeroloda/rsg-theme`, working copy `theme/rsg_2022/`, deploys only via `deploy.sh` (staging + live deployed from git 11 Nov). Primer `theme/CLAUDE.md`. 10 Nov: DFS price box + GPS Amazon button live | Dead-code cleanup; then the **incremental in-place rebuild** (`theme/THEME-REBUILD-NEXT-STAGE.md`) |
| 5 | Content & distribution | `content/` | Idea stage | Review-format research, then the restart plan (`content/README.md`) |
| 6 | Email | `email/` | MailerLite (~$1,058/yr, to confirm; ~50k subscribers, ~35k active, ~20k in the US); deals emails started 16 Oct | List cleanup; decide the email platform |
| 7 | Automations (n8n, Make) | `automations/` | Paid n8n Cloud still runs the bank import; Make still needed for accounting | Move the bank import (step 4, unblocked): `automations/HANDOVER-2026-11-05.md` |

Priority order and savings: `ROADMAP.md`. Facts about every system: `CONTEXT.md`. Settled decisions: `DECISIONS.md`. (In the claude.ai Project these three live under `claude/`.)

## Servers at a glance
| Server | What runs there | Access |
|---|---|---|
| Rocket.net `65.181.124.237` | WordPress live + staging | SSH key `theme/.ssh-rocket-key`, from the Mac (device shell) only |
| Hetzner **rsg-dfs** `135.181.110.157` (CAX11, Helsinki) | DFS production only | SSH 2222, key-only; screens: `dfs/production/Open DFS production.command` → http://localhost:18091. Facts: `dfs/PRODUCTION-SERVER.md` |
| Hetzner **rsgmedia** `116.203.146.177` (CPX32, Nuremberg) | n8n, Stargrid, DFS test bench | SSH 2222, key-only; Portainer/Adminer/NPM/DFS test screens: `hosting/hetzner/Open RSG server tools.command` (DFS → http://localhost:18090) |
| Linode `45.33.39.133` | Old DFS (rollback until Phase F) | Read-only unless agreed (`dfs/DFS-ACCESS.md`) |

## Key links
- DFS Rebuild Plan (doc, with "Challenge review" tab; history, current state is in `dfs/`): https://claude.ai/artifact/FNBUHjND581QjFN8t2p7KP
- DFS Shoes preview (6 Nov snapshot, superseded by the live Shoes screen): https://claude.ai/artifact/JoSr5Rj3ZF1LhaCv7u5nXN
- DFS Architecture page (4 Nov "today vs target" diagram, history): https://claude.ai/artifact/RtUQYyJ7tYyCDLSm3vT5k3 (local copy `dfs/dfs-architecture.html`)

## Working rules
- **One chat per workstream.** Read this file + that workstream's README first.
- **Log decisions** in `DECISIONS.md` (dated, one line each, date order; mark replaced lines "→ Superseded" instead of deleting) so settled questions aren't reopened.
- **Update `CONTEXT.md`** when a fact changes (plan, price, IP, server). Stale notes caused several wrong turns.
- **No passwords or keys in docs.** They live in `_private/` and in the theme's gitignored `.ssh-rocket-key` / `.credentials-local.md`; server secrets in each server's `.env`. Goal: move all of them to 1Password.
- **Backups:** both Hetzner servers back themselves up nightly (age-encrypted) to Google Drive `Server-Backups/servers/rsgmedia/` (03:30 UTC) and `Server-Backups/servers/dfs/` (03:15 UTC). `Backups/` is the root for any future backup; rules in `hosting/hetzner/Backups-README.txt`. Decryption key: `_private/backup-age-key.txt` (+ 1Password).
- **Hetzner access:** SSH key only on port 2222 (your 1Password key; Claude's standing key `_private/claude-hetzner-key`). Only 80/443/2222 open on rsgmedia, only 2222 on rsg-dfs.
- **Keep `~/Documents` out of iCloud Desktop & Documents sync**: it moves this folder and breaks every path (happened 5 Nov, undone).
- **Cache purge (live):** Claude may purge itself after a deploy: `cd ~/public_html && wp eval 'rocket_clean_domain();'` + `wp cdn purge` (Rocket.net "Purge Everything"). After CSS/JS changes also clear WP Rocket's used CSS and refresh its minified copy (`wp-content/cache/min/`), see `dfs/GO-LIVE-2026-11-10.md` → Lessons.
- **Production:** never edit theme files on a server. Theme changes = commit in `theme/rsg_2022/` → `./deploy.sh staging` → check → `./deploy.sh live` (same commit; see `theme/CLAUDE.md`). No credentials in any repo (theme: `SECRETS.md`). DFS code changes: test on rsgmedia, then GitHub → rsg-dfs (`dfs/PRODUCTION-SERVER.md`).
- **Folder layout:** each workstream keeps its primer/briefs/handovers at its top level; `logs/` = dated deploy logs (theme: `theme/logs/`; DFS: `dfs/DEPLOY-LOG-*.md`), `_work/` = scratch files, scripts and rollback copies from past changes, `archive/` or `../_archive/` = superseded material (kept, not deleted).
- Measure before deciding: actual measurements (Linode RAM, bandwidth, page weight, geo lookups) overturned several assumptions.
