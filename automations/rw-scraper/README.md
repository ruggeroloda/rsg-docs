# Running Warehouse scraper — runbook

_Current state. Checked on the OptiPlex 25 Nov 2026. This folder is the source of the code; the OptiPlex runs a copy._

## What it does
| Script | When (OptiPlex cron, local time) | Does |
|---|---|---|
| `RW-2025.py` | 06:00 and 18:00 | Reads Running Warehouse US "Men's new arrivals"; each model not yet in Airtable "Running Shoes" (base RSG Product Testing) becomes a row: name, brand, price on that day (`MSRP`), stock photos (1462 px), specs, best use / cushioning / surface / stability, link, status "New Release" (spikes: "IGNORE SHOE") |
| `metadata_updater.py` | 12:00 | Re-visits rows of the last 8 weeks that lack a spec, a characteristic or the description and fills what the page now has |
| `rw_common.py` | — | Shared: page loading (headless Chrome, selenium-stealth), spec parsing, settings, alarm |

A missing spec stays **empty** (until 25 Nov 2026 the scripts wrote 999; `is_missing` still treats 999 as missing).

## Where
- OptiPlex (home server, `192.168.68.79`, user `ruggero`): `~/code/RW-info/`, virtualenv `venv/`, logs `logs/new-arrivals.log`, `logs/updater.log`.
- It stays at home (Ruggero 25 Nov): Running Warehouse answers plain server requests with 406; the browser from a home connection passes.
- Settings: `~/code/RW-info/.env` (mode 600, not in git): `AIRTABLE_PAT`, `HC_URL` (healthchecks.io ping URL; empty = no alarm).
- Access: the standing Claude key (`_private/claude-hetzner-key`) is in the OptiPlex's `authorized_keys` since 25 Nov; only from a chat linked to the Mac while the Mac is at home. Copy the key to the shell's home with mode 600 first.

## Deploy
Edit here, commit, then copy the three `.py` files to `~/code/RW-info/` (scp) and run `venv/bin/python metadata_updater.py --dry-run --limit 5`. Backups of the pre-25-Nov scripts: `*.bak-20261125` on the OptiPlex; old crontab `logs/crontab.bak-20261125`.

## Alarm
`metadata_updater.py` pings `HC_URL` after each daily run; `/fail` when: the newest complete row no longer yields specs (page layout changed), 5+ pages to check and none loads (blocked), or a crash. `RW-2025.py` pings only on failure: no links on the arrivals page, no new model readable, or a crash.

## Useful
- Backfill: `venv/bin/python metadata_updater.py --since 2026-01-01` (no ping; 15 pages a run, 20–40 s apart). **Never faster:** on 25 Nov a run of ~40 pages in 6 minutes made Running Warehouse stop answering the OptiPlex.
- Spec format on the page since 2025: `<li><strong>Weight:</strong><span> 10.7 oz | 303 g</span></li>` in `#product_sizing` (labels Weight, Heel Stack, Forefoot Stack, Heel-Toe Offset).

## Open
0. 25 Nov 09:30: Running Warehouse does not answer the OptiPlex's browser after the fast backfill (30 of 219 rows of 2026 done). Re-test with one page before the next run; then backfill 15 rows a day. Plain requests (no browser) get HTTP 406, so the arrivals list is now read with the browser too; new rows had stopped on 14 Nov.
1. `HC_URL` is empty until Ruggero creates the healthchecks.io check `rsg-scraper` (period 1 day, grace 12 h).
2. Rows from 2023–2025 still carrying 999 (250, of which 82 published) are not backfilled; many pages are gone.
3. EUR / AUD launch prices: Running Warehouse Europe and Australia use other page addresses than the US site (US link answers 404 there). Proposed source: the RW EU / RW AU feeds the price engine downloads nightly, retried until the model appears. Not built.
4. Only the men's arrivals page is read.
