# Quiz funnel instrumentation — DEPLOY LOG (2026-07-06)

**Status:** LIVE deployed 2026-07-06. Lint 8.3 + 7.4 clean. Verified on staging AND live
(internal `rest_do_request` smoke test: valid stage → `{"ok":true}`, junk stage → `{"ok":false}`;
counter upsert increments one row per day/stage). Live smoke-test row truncated so real data starts at 0.

**Awaiting:** user **Cloudflare "Purge Everything"** (Rocket.net panel) — the JS beacons live in the
edge-cached quiz HTML and will NOT fire for real visitors until purged. (The endpoint + table are
server-side and already work.)

---

## Why (Phase 0 of the Shoe Finder results-page redesign)
Access-log analysis 2026-07-06 showed the quiz page gets ~450–900 origin hits/day (undercount — edge
cache serves more), yet only **4 email-submit attempts in 6 days** (3×200, 1×403; the 403 was the old
pre-fix stale-nonce bug). The backend/capture path is healthy — the problem is **conversion**, and we
were blind on *where* in the funnel visitors drop. GA4 fires the stages as event *params*
(`app_name`/`screen_name` on `Wizard-Heatmap-2024`) which are almost certainly not registered as custom
dimensions, so the historical stage breakdown isn't recoverable. Decision: build **first-party
server-side funnel counters** (reliable, permanent, needed anyway to measure redesign lift).

## What changed (theme `rsg_2022`, 2 files, additive only)
- `includes/rsg-wizard-capture.php`
  - `RSG_WIZARD_DB_VERSION` `3` → `4`.
  - Install routine now also creates **`{prefix}_rsg_wizard_funnel`** (aggregate counts, **no PII**):
    `id, day DATE, stage VARCHAR(24), quiz_type VARCHAR(16), hits BIGINT`, `UNIQUE KEY (day,stage,quiz_type)`.
  - New REST route **`POST /wp-json/rsg/v1/wizard-event`** (`permission_callback => __return_true`,
    same no-nonce reasoning as `wizard-subscribe`). Handler `rsg_wizard_event_handler()`:
    whitelists `stage` ∈ {start, completed, email_shown, email_submitted, email_skipped}, optional
    `type` ∈ {road, trail, ''}, light per-IP cap (300/hr transient), then
    `INSERT … ON DUPLICATE KEY UPDATE hits = hits + 1`. Junk stages quietly return `{ok:false}`.
  - **Existing `wizard-subscribe` endpoint + `rsg_wizard_signups` table untouched.**
- `template-wizard-2024.php`
  - Global helper `window.rsgFunnel(stage)` (after `<script>`, before `$(document).ready`) — fires a
    non-blocking `navigator.sendBeacon` (fetch keepalive fallback) to the event endpoint.
  - 4 one-line calls at the **existing gtag points**: `rsgFunnel('start')`, `'completed'`,
    `'email_shown'`, and `email ? 'email_submitted' : 'email_skipped'`. gtag calls unchanged.

Note: `quiz_type` is logged as `''` in v1 (aggregate road+trail). Per-type split is a cheap fast-follow
if the funnel warrants it.

## Backups / rollback (restore BOTH files together, then purge Cloudflare)
- **Live** (tag `20260706-184857`):
  - `~/public_html/wp-content/themes/rsg_2022/includes/rsg-wizard-capture.php.bak-20260706-184857`
  - `~/public_html/wp-content/themes/rsg_2022/template-wizard-2024.php.bak-20260706-184857`
- **Staging** (tag `20260706-184453`):
  - `…/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022/includes/rsg-wizard-capture.php.bak-20260706-184453`
  - `…/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022/template-wizard-2024.php.bak-20260706-184453`
- The new table can be left in place on rollback (harmless) or dropped:
  `wp db query "DROP TABLE newguru_wp_rsg_wizard_funnel;"` and reset `wp option delete rsg_wizard_db_version`.

## How to read the funnel (after purge + a day of traffic)
```bash
wp db query "SELECT day,stage,SUM(hits) h FROM newguru_wp_rsg_wizard_funnel GROUP BY day,stage ORDER BY day,FIELD(stage,'start','completed','email_shown','email_submitted','email_skipped');"
```
Key ratios: `completed/start` (quiz-completion), `email_shown/completed`, and
`email_submitted/email_shown` (the ask's conversion) vs `email_skipped`. Cross-check `email_submitted`
against actual rows in `rsg_wizard_signups` for the same day.

## Manual action for Ruggero
1. **Cloudflare "Purge Everything"** (Rocket.net) — required for the JS beacons to reach real visitors.
