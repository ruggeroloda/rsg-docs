# RSG — project primer

_Last updated 2026-11-19._ **Start every RSG session here** (after `git pull`), then read `ROADMAP.md` and the `RUNBOOK.md` of the workstream you work on. That's all you need for the current state.

**What this is:** the relaunch of RunningShoesGuru.com: cut running costs, modernise the stack, restart content on a small budget. Traffic is ~10 % of what it was (GA4 ~160k page views / 30 days, Nov 2026); revenue = Raptive ads + affiliate (mostly Amazon) + deals emails.

## Workstreams
| Workstream | Runbook (current state) | Status 19 Nov |
|---|---|---|
| Price engine (DFS) | `dfs/RUNBOOK.md` | Live since 10 Nov on rsg-dfs; 25 feeds, countries US, CA, GB, DE, FR, IT, ES, IE, GR, SE, AU (18 Nov: Impact US brand shops + Running Room CA); old Linode retires in Phase F (due now) |
| WordPress site + theme | `theme/RUNBOOK.md` | Reskin live 15 Nov; homepage, `/top/`, guides, `/reviews/` hub, list images live 17 Nov; one-width price copies + lists picks table + deploy guard + phone price bar G + lists/guides shoe box + review top on phones live 18 Nov; site search routing + `/reviews/` listings reskin + 41 reviews tagged into families live 19 Nov; live = staging = `04479d8`; GitHub `main` = `d26aea5` (adds only a deploy.sh change, not on the server) |
| Hosting, backups, DNS | `hosting/RUNBOOK.md` | Rocket.net Business annual since 12 Nov ($1,000 on 25 Nov); bot challenge on 2 networks since 16 Nov; server stall 19 Nov reported, waiting for their answer |
| rsgmedia server (n8n, Stargrid) | `hosting/hetzner/RUNBOOK.md` | Hardened, backed up |
| Automations (n8n, Make) | `automations/RUNBOOK.md` | Bank import still on paid n8n Cloud |
| Email | `email/RUNBOOK.md` | List cleanup, then platform decision |
| Content | `content/README.md` | Idea stage; old-article prune ~22 Nov; SEO checklist with Ruggero: comparison refresh 5 of 39 live 18 Nov |
| Costs | `costs/COSTS.md` | Register |
| Analytics + analyst | `analytics/README.md` | Umami live 16 Nov; analyst metrics + notebook on rsgmedia; Ruggero asks for the analysis in the project (not scheduled) |

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
- **Docs = the private git repo `ruggeroloda/rsg-docs`** (this folder). On the Mac it is `~/Documents/claude/projects/rsg/`; a session without the Mac (e.g. started on the phone) attaches the repo with push access and clones it from GitHub. Mac access: deploy key `_private/github-rsg-docs-deploy-key` (write, this repo only), copied to `~/.ssh/github-rsg-docs` with `chmod 600`; the repo's `core.sshCommand` points there. **Start of a session:** `git pull`. **End of a session (close-out):** runbook of the system you changed, one line in `DECISIONS.md` per decision, `ROADMAP.md`, handovers whose work shipped get a **DONE** line (or move to the workstream's `archive/`), then `git commit` + `git push`, then copy `README.md` and `ROADMAP.md` to the claude.ai project. Code lives in its own repos (`rsg-theme`, `rsg-dfs`); secrets never go in git (`.gitignore`).
- **The claude.ai project holds generated copies only**: `README.md` and `claude/ROADMAP.md`, refreshed at close-out. Never edit docs in the project; the repo is the only place docs change.
- **One chat per code repo at a time.** A second chat on the same repo works in its own `git worktree` and deploys only its own commits.
- **Change log for the analyst:** anything that reaches the live site, ads, hosting, DFS, tracking or email gets a row in `rsg.change_log` in the same session. Theme/rsg-core live deploys are recorded by `deploy.sh`; everything else: `echo "details" | ssh -p 2222 ruggero@116.203.146.177 "rsg-changelog-add KIND claude REF 'Title'"` (KIND: deploy content seo ads hosting dfs tracking email external season). Planned items: see `analytics/README.md` "Notebook".
