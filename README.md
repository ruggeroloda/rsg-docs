# RSG — project primer

_Last updated 2026-11-26 (workstream table cut to one line each; the history is in `DECISIONS.md`)._ **Start every RSG session here** (after `git pull`), then read `ROADMAP.md` and the `RUNBOOK.md` of the workstream you work on. That's all you need for the current state.

**Strategy (22 Nov 2026, `STRATEGY.md`):** (1) the reader gets the answer and doesn't return to Google; (2) revenue per 1,000 views (shop clicks plus Raptive) goes up, scroll depth is not a goal; (3) the undecided reader opens a second page, ideally a comparison. Every change says which goal it serves; scoreboard and change log are in `STRATEGY.md`.

**What this is:** the relaunch of RunningShoesGuru.com: cut running costs, modernise the stack, restart content on a small budget. Traffic is ~10 % of what it was (GA4 ~160k page views / 30 days, Nov 2026); revenue = Raptive ads + affiliate (mostly Amazon) + deals emails.

## Workstreams
| Workstream | Runbook (current state) | State (26 Nov) |
|---|---|---|
| Price engine (DFS) | `dfs/RUNBOOK.md` | Live on rsg-dfs since 10 Nov: 31 feeds, 11 countries, 329 of 429 reviews with offers. Newest: Road Runner Sports US + New Balance DE/IT/GB (26 Nov). Open: retire the old Linode (Phase F), outdated US list prices |
| WordPress site + theme | `theme/RUNBOOK.md` | Reskin live since 15 Nov; live = theme `0019202` (26 Nov, all PHP warnings fixed). Staging runs branch `categories` (shoe categories, live 8–10 Dec). Freeze Fri 27 – Mon 30 Nov |
| Hosting, backups, DNS | `hosting/RUNBOOK.md` | Rocket.net Business (paid to 24 Nov 2027), new server since 24 Nov; weekly WordPress backup to Drive fixed 24 Nov; Redis crash 25 Nov (ticket parked) |
| rsgmedia server (n8n, Stargrid) | `hosting/hetzner/RUNBOOK.md` | Hardened, backed up; also the DFS test bench and the analyst database |
| Automations (n8n, Make) | `automations/RUNBOOK.md` | Review inserts move Make → n8n Tue 1 Dec (built, tested on staging); bank import still on paid n8n Cloud; Running Warehouse scraper on the OptiPlex fixed 25 Nov |
| Email | `email/RUNBOOK.md` | Black Friday email 1 sent 24 Nov (US); email 2 Fri 27 Nov, Cyber Monday 30 Nov; then list cleanup → platform decision |
| Content | `content/README.md` | Shoe categories on staging (live 8–10 Dec); review flow agreed (Airtable + one n8n flow); review format waits for Ruggero's six decisions; 14 shoes to assign; prune from the week of 7 Dec |
| Costs | `costs/COSTS.md` | Register |
| Analytics + analyst | `analytics/README.md` | Umami since 16 Nov; revenue data in `rsg` (Raptive daily, CJ, Rakuten, Awin, Impact, Amazon); GA4 + Search Console history; baseline + "head to head" test read 8 Dec |

## Servers
| Server | Runs | Access |
|---|---|---|
| Rocket.net `194.39.148.145` | WordPress live + staging | SSH from the Mac only (`theme/.ssh-rocket-key`) |
| Hetzner **rsg-dfs** `135.181.110.157` | DFS production only | SSH 2222, key only |
| Hetzner **rsgmedia** `116.203.146.177` | n8n, Stargrid, DFS test bench, site check | SSH 2222, key only |
| Linode `45.33.39.133` | Old DFS (rollback until Phase F) | read-only unless agreed |
| OptiPlex (home, `192.168.68.79`) | Running Warehouse scraper → Airtable | standing Claude key, only from a chat linked to the Mac at home (`automations/rw-scraper/README.md`) |

Monitoring: healthchecks.io `rsg-dfs` (price engine) and `rsg-site` (site + backup age) and `rsg-php` (PHP fatal errors). Backups: everything to Google Drive `Server-Backups/` (encrypted; key `_private/backup-age-key.txt` + 1Password) plus Rocket.net's and Hetzner's own.

## Working rules
- **Runbooks are the current state**: any change updates the runbook of that system in the same session. A decision also gets one dated line in `DECISIONS.md` (the history: read it to know *why*, not at session start; superseded lines are marked, never deleted). Plans and open work: `ROADMAP.md`.
- **No passwords or keys in docs or git.** They live in `_private/`, the servers' `.env` files and 1Password.
- **Production changes:** theme only through git + `deploy.sh` (staging first); DFS code through GitHub after testing; never edit files on a server. Log deploys.
- **Verify against the real systems** before writing a fact down; measure before deciding.
- Keep `~/Documents` out of iCloud sync (it breaks every path).
- Folders: each workstream keeps its runbook, briefs and handovers at its top level; `logs/` = deploy logs, `_work/` = scratch, `archive/` / `_archive/` = superseded (kept, not deleted).
- Claude may purge the Rocket.net cache after a deploy.
- **Docs = the private git repo `ruggeroloda/rsg-docs`** (this folder). On the Mac it is `~/Documents/claude/projects/rsg/`; a session without the Mac (e.g. started on the phone) attaches the repo with push access and clones it from GitHub. Mac access: deploy key `_private/github-rsg-docs-deploy-key` (write, this repo only), on the Mac also at `~/.ssh/github-rsg-docs` (`chmod 600`). The repo's `core.sshCommand` lists both (`ssh -i ~/.ssh/github-rsg-docs -i _private/github-rsg-docs-deploy-key -o IdentitiesOnly=yes`), so a Mac-linked chat's device shell uses the key in place, no copy (since 21 Nov; the "not accessible" warning for the missing one is harmless), plus `-o UserKnownHostsFile=.git/github_known_hosts -o StrictHostKeyChecking=yes` (GitHub's ED25519 key `SHA256:+DiY3wvvV6TuJJhbpZisF/zLDA0zPMSvHdkr4UvCOqU`, checked against api.github.com/meta; since 24 Nov: every device shell starts with no known hosts, which made `git pull` fail as "access rights"). There git needs delete permission on the folder once per session (its lock files). **Start of a session:** `git pull`. **End of a session (close-out):** runbook of the system you changed, one line in `DECISIONS.md` per decision, `ROADMAP.md`, handovers whose work shipped get a **DONE** line (or move to the workstream's `archive/`), then `git commit` + `git push`, then copy `README.md` and `ROADMAP.md` to the claude.ai project. Code lives in its own repos (`rsg-theme`, `rsg-dfs`); secrets never go in git (`.gitignore`).
- **The claude.ai project holds generated copies only**: `README.md` and `claude/ROADMAP.md`, refreshed at close-out. Never edit docs in the project; the repo is the only place docs change.
- **One chat per code repo at a time.** A second chat on the same repo works in its own `git worktree` and deploys only its own commits.
- **Change log for the analyst:** anything that reaches the live site, ads, hosting, DFS, tracking or email gets a row in `rsg.change_log` in the same session. Theme/rsg-core live deploys are recorded by `deploy.sh`; everything else: `echo "details" | ssh -p 2222 ruggero@116.203.146.177 "rsg-changelog-add KIND claude REF 'Title'"` (KIND: deploy content seo ads hosting dfs tracking email external season). Planned items: see `analytics/README.md` "Notebook".
