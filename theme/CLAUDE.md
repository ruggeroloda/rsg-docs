# Theme workstream — primer

Part of the RSG project (`../README.md`). This folder = the WordPress site and its theme `rsg_2022` (www.runningshoesguru.com on Rocket.net).

**Read `RUNBOOK.md` first**: what runs where, access, how to deploy, caches, features, rules in force, open items. It is checked against the servers and is the current state. Plan for the rebuild: `THEME-REBUILD-NEXT-STAGE.md`.

Working rules (detail in the runbook):
- The theme is in git (`rsg_2022/`, GitHub `ruggeroloda/rsg-theme`, `main` = live). Never edit theme files on a server. Deploy with `./deploy.sh staging` → check → `./deploy.sh live` (same commit).
- No credentials in git (`rsg_2022/SECRETS.md`; `gitleaks git .` clean before a push).
- Rocket.net SSH only from the Mac's shell; read live files, not old local copies.
- After CSS/JS changes on live: WP Rocket used CSS + minified copy, then `wp cdn purge` (Claude may purge).
- Any change: update `RUNBOOK.md` in the same session, log decisions in `../DECISIONS.md`.

The long primer that lived here until 11 Nov 2026 (feature history, June–Nov deploy notes, doc map): `archive/CLAUDE-until-2026-11-11.md`.
