# Handover — DFS build step 3 done (7 Nov 2026)

## Start here
**Build step 3 is done** (importers, Feeds screen, scheduling, failure email). Code on GitHub `ruggeroloda/rsg-dfs` (main). **Next: build step 5 — price data + history** (see the plan doc): per shoe x gender x country the cheapest offers, daily snapshot rows, two deal signals; then step 6 WordPress on staging, step 7 production Linode + switch-over, step 8 further feeds (AWIN etc.), which are now configuration on the Feeds screen.

**The scheduler is OFF.** Ruggero turns it on with the switch at the top of the Feeds screen (http://localhost:18090/feeds via `Open RSG server tools.command`). Recommended: switch on in the evening, check the Feeds screen next morning (all five feeds green, an email only on failure). Jobs (UTC): Amazon search 02:00, Zappos 06:30, RW DE 06:45, Nike DE 07:00, Amazon re-price 08:00 / 14:00 / 20:00, Nike US 12:00 (CJ refreshes ~11:16). The Amazon nightly *search* has not yet run through the scheduler (the re-price did, 390 calls, 10 min); first full scheduled run is the real test.

## What was built (all in `app/dfs/`)
| Sub-step | What | Where |
|---|---|---|
| 3.1 | Feeds table + screen (`/feeds`) | `feedconf.py`, `templates/feeds.html` |
| 3.2 | Edit screen (`/feeds/<feed>`): source, format, column map, reading rules, sub-ID, schedule, guard; live 20-row preview (adult shoes by default) | `reader.py`, `templates/feed_edit.html`, `web.py` |
| 3.3 | Matcher reads every file through the feed configuration; output byte-identical to the old readers on the 6 Nov files (fingerprints of all matches). Old readers removed | `match.py`, `reader.py` |
| 3.4 | Downloaders (https / sftp paramiko 3.5.x / ftp), checks, 70% guard, `.rejected`, keep 3 days | `download.py` |
| 3.5 | Scheduler thread, global switch (default OFF), "Run now", retry 30 min x3, no catch-up on switch-on; jobs run as child processes one at a time | `scheduler.py`, `jobs.py` |
| 3.6 | Failure email via MailerSend (theme's token, to ruggeroloda@gmail.com), 1 per feed+job per 6 h, 15/day; test button | `alerts.py` |

## Facts and gotchas
- Server `.env` names: `CJ_NIKE_URL`, `AWIN_NIKE_DE_URL`, `RAKUTEN_ZAPPOS_URL`, `AMAZON_US_*`, `MAILERSEND_TOKEN`, `ALERT_TO`, `ALERT_FROM`. RW DE URL is public (in the feed config). No secrets in git.
- A page loaded before a container restart has a stale form token: reload it.
- Memory: a 1 GB cap shared by the web process and the one running job. Never load a whole feed into memory (a full-file comparison once got the container OOM-killed); `reader.stream` is a generator.
- The scheduler lock is `/data/scheduler.lock`; only gunicorn holds it, so `docker exec python -c "import dfs.web"` does not start a second scheduler.
- Testing with the switch ON starts any slot that is due *after* the moment you switch on; leave it OFF while testing and use "Run now".
- The matcher falls back to the newest accepted raw file when today's download is missing or refused.

## Open items
- **Ruggero:** rotate the Linode root password (printed into session output on 6 Nov and again 7 Nov); rotate the MailerSend token when the email provider is decided (the DFS failure email uses the same token).
- Nike DE: "Ultrafly Trail" misses because the German feed glues "Traillaufschuhe" (per-feed spelling rule, not built). DE offers have no list price (see DECISIONS open questions).
- Nike's CJ zip is 94 MB now vs 21 MB on 6 Nov with identical rows (compression only, not investigated).
- Add feeds (AWIN others, Puma, etc.) on the Feeds screen: 3 file formats are covered; check the preview and the guard first.
- Cleanup left for Ruggero or a session with deletion allowed: folder `dfs/_shots/`, `.git/stale-*` lock files, the rollback copies listed in HANDOVER-STEP2 after ~20 Nov.
