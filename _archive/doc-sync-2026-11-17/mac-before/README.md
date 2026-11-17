# RSG — project primer

_Last updated 2026-11-11._ **Start every RSG session here**, then read `ROADMAP.md` and the `RUNBOOK.md` of the workstream you work on. That's all you need for the current state.

**What this is:** the relaunch of RunningShoesGuru.com: cut running costs, modernise the stack, restart content on a small budget. Traffic is ~10 % of what it was (GA4 ~160k page views / 30 days, Nov 2026); revenue = Raptive ads + affiliate (mostly Amazon) + deals emails.

## Workstreams
| Workstream | Runbook (current state) | Status 11 Nov |
|---|---|---|
| Price engine (DFS) | `dfs/RUNBOOK.md` | Live since 10 Nov on rsg-dfs; old Linode retires in Phase F (~17 Nov) |
| WordPress site + theme | `theme/RUNBOOK.md` | Theme in git, deploys via `deploy.sh`; machine-readable review markup live 11 Nov; incremental rebuild next |
| Hosting, backups, DNS | `hosting/RUNBOOK.md` | Rocket.net Business plan mail pending (after the lean-staging decision) |
| rsgmedia server (n8n, Stargrid) | `hosting/hetzner/RUNBOOK.md` | Hardened, backed up |
| Automations (n8n, Make) | `automations/RUNBOOK.md` | Bank import still on paid n8n Cloud |
| Email | `email/RUNBOOK.md` | List cleanup, then platform decision |
| Content | `content/README.md` | Idea stage |
| Costs | `costs/COSTS.md` | Register |
| Analytics + analyst | `analytics/README.md` | Umami live 16 Nov; analyst metrics + notebook on rsgmedia 16 Nov |

## Servers
| Server | Runs | Access |
|---|---|---|
| Rocket.net `65.181.124.237` | WordPress live + staging | SSH from the Mac only (`theme/.ssh-rocket-key`) |
| Hetzner **rsg-dfs** `135.181.110.157` | DFS production only | SSH 2222, key only |
| Hetzner **rsgmedia** `116.203.146.177` | n8n, Stargrid, DFS test bench, site check | SSH 2222, key only |
| Linode `45.33.39.133` | Old DFS (rollback until Phase F) | read-only unless agreed |

Monitoring: healthchecks.io `rsg-dfs` (price engine) and `rsg-site` (site + backup age) and `rsg-php` (PHP fatal errors). Backups: everything to Google Drive `Server-Backups/` (encrypted; key `_private/backup-age-key.txt` + 1Password) plus Rocket.net's and Hetzner's own.

## Working rules
- **Runbooks are the current state**: any change updates the runbook of that system in the same session. A decision also gets one dated line in `DECISIONS.md` (the history: read it to know *why*, not at session start; superseded lines are marked, never deleted). Plans and open work: `ROADMAP.md`.
- **No passwords or keys in docs or git.** They live in `_private/`, the servers' `.env` files and 1Password.
- **Production changes:** theme only through git + `deploy.sh` (staging first); DFS code through GitHub after testing; never edit files on a server. Log deploys.
- **Verify against the real systems** before writing a fact down; measure before deciding.
- Keep `~/Documents` out of iCloud sync (it breaks every path).
- Folders: each workstream keeps its runbook, briefs and handovers at its top level; `logs/` = deploy logs, `_work/` = scratch, `archive/` / `_archive/` = superseded (kept, not deleted).
- Claude may purge the Rocket.net cache after a deploy.
- **Change log for the analyst:** anything that reaches the live site, ads, hosting, DFS, tracking or email gets a row in `rsg.change_log` in the same session. Theme/rsg-core live deploys are recorded by `deploy.sh`; everything else: `echo "details" | ssh -p 2222 ruggero@116.203.146.177 "rsg-changelog-add KIND claude REF 'Title'"` (KIND: deploy content seo ads hosting dfs tracking email external season). Planned items: see `analytics/README.md` "Notebook".
