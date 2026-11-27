# Running Warehouse scraper — runbook

_Current state. Checked on the OptiPlex 25 Nov 2026. This folder is the source of the code; the OptiPlex runs a copy._

## What it does
| Script | When (OptiPlex cron, local time) | Does |
|---|---|---|
| `RW-2025.py` | 06:00 and 18:00 | Reads Running Warehouse US "Men's new arrivals"; each model not yet in Airtable "Running Shoes" (base RSG Product Testing) becomes a row: name, brand, price on that day (`MSRP`), stock photos (1462 px), specs, best use / cushioning / surface / stability, link, status "New Release" (spikes: "IGNORE SHOE") |
| `metadata_updater.py` | 12:00 | Re-visits rows of the last 8 weeks that lack a spec, a characteristic or the description and fills what the page now has; skips "IGNORE SHOE" rows (25 Nov) |
| `rw_common.py` | — | Shared: page loading (headless Chrome, selenium-stealth), spec parsing, settings, alarm |

A missing spec stays **empty** (until 25 Nov 2026 the scripts wrote 999; `is_missing` still treats 999 as missing).

## Where
- OptiPlex (home server, `192.168.68.79`, user `ruggero`): `~/code/RW-info/`, virtualenv `venv/`, logs `logs/new-arrivals.log`, `logs/updater.log`.
- It stays at home (Ruggero 25 Nov): Running Warehouse answers plain server requests with 406; the browser from a home connection passes.
- Settings: `~/code/RW-info/.env` (mode 600, not in git): `AIRTABLE_PAT`, `HC_URL` (healthchecks.io ping URL; empty = no alarm).
- Access: the standing Claude key (`_private/claude-hetzner-key`) is in the OptiPlex's `authorized_keys` since 25 Nov; from a chat linked to the Mac: at home on `192.168.68.79`, and since 27 Nov also away through Tailscale (`100.112.142.127`, the Mac's Tailscale must be on). Copy the key to the shell's home with mode 600 first. sudo needs Ruggero's password. Access and security of the machine: `../../hosting/optiplex/RUNBOOK.md`.

## Deploy
Edit here, commit, then copy the three `.py` files to `~/code/RW-info/` (scp) and run `venv/bin/python metadata_updater.py --dry-run --limit 5`. Backups of the pre-25-Nov scripts: `*.bak-20261125` on the OptiPlex; old crontab `logs/crontab.bak-20261125`.

## Alarm
`metadata_updater.py` pings `HC_URL` after each daily run; `/fail` when: the newest complete row no longer yields specs (page layout changed), 5+ pages to check and none loads (blocked), or a crash. `RW-2025.py` pings only on failure: no links on the arrivals page, no new model readable, or a crash.

## Useful
- Backfill: `venv/bin/python metadata_updater.py --since 2026-01-01` (no ping; 15 pages a run, 20–40 s apart). **Never faster:** on 25 Nov a run of ~40 pages in 6 minutes made Running Warehouse stop answering the OptiPlex.
- Specific rows now: `venv/bin/python metadata_updater.py --ids recA,recB` (any age, no ping; added 25 Nov, used for the 5 shoes kept after the Running Shoes cleanup: all 5 filled; log `logs/ids-20261125.log`). Backup of the version before: `metadata_updater.py.bak-20261125b`.
- Spec format on the page since 2025: `<li><strong>Weight:</strong><span> 10.7 oz | 303 g</span></li>` in `#product_sizing` (labels Weight, Heel Stack, Forefoot Stack, Heel-Toe Offset).

## Open
0. Backfill of the 2026 rows: Running Warehouse answered again by 12:00 on 25 Nov (noon run at the slow pace: 14 of 15 pages loaded, 10 rows updated). The noon job only looks back 8 weeks, so a **temporary cron line** (15:30 daily, `--since 2026-01-01`, 15 pages, log `logs/backfill.log`) finishes the older rows in about two weeks: remove it when the log says 0 rows. **Update 25 Nov 15:20:** after the Running Shoes cleanup (318 rows to IGNORE) and with IGNORE rows skipped, only 12 rows of 2026 are left (mostly Best Use / Surface, which may never appear on the page), so one or two runs finish it: remove the line after 26 Nov. The rewritten new-arrivals script (list read through the browser) has its first run at 18:00 on 25 Nov: check `logs/new-arrivals.log`.
1. `HC_URL` is empty until Ruggero creates the healthchecks.io check `rsg-scraper` (period 1 day, grace 12 h).
2. Rows from 2023–2025 still carrying 999 (250, of which 82 published) are not backfilled; many pages are gone.
3. EUR / AUD launch prices: Running Warehouse Europe and Australia use other page addresses than the US site (US link answers 404 there). Proposed source: the RW EU / RW AU feeds the price engine downloads nightly, retried until the model appears. Not built.
4. Only the men's arrivals page is read, and Running Warehouse pre-orders don't appear on it (found 25 Nov: Gel Nimbus 29 + Gel Cumulus 29 were on pre-order and missing). Those two were added by hand and filled with a one-off copy of the new-row logic (`/tmp/rsgfill/fill.py` on the OptiPlex; log `logs/ids-20261125-evening.log`). Wave Rider 30's link pointed at a gone unisex page: now `descpage-MIZWR00.html`.
