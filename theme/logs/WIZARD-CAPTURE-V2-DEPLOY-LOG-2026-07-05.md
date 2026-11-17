# Wizard Capture v2 — DEPLOY LOG (2026-07-05)

Scope shipped: **Phases 0 + 1 + 2** of `WIZARD-CAPTURE-V2-IMPLEMENTATION.md`
(pre-results email step + post-results card rework + endpoint hardening). **Phase 3**
(real price-drop cron) is NOT in this deploy — still owed within ~2–3 weeks (the new copy
promises price watching).

Status: **LIVE deployed 2026-07-05**, pending Cloudflare purge (see manual actions).

---

## ⚠️ MANUAL ACTIONS FOR RUGGERO (do these)
1. **Cloudflare "Purge Everything"** (Rocket.net panel). The quiz page is edge-cached ~30d;
   nothing new serves to real visitors until this is done. Several earlier deploys are also
   still awaiting a purge.
2. **Delete test subscribers** in MailerLite (`from_wizard` group): all `ruggeroloda+wiz*@gmail.com`
   created during testing (wizstg, wizbrowser, wizdmarc, wizdmarc2, wizhp). Local staging rows
   already cleaned; production got no test rows (tests hit staging endpoint).
3. **Welcome automation sender** — verify the `from_wizard` welcome/autoresponder email's
   "Who is it from?" is `newsletter@runningshoesguru.com` (NOT `ruggeroloda@gmail.com`), same
   fix as the DOI email below, or it will land in spam too. (Not yet checked.)
4. **DMARC**: leave `p=none` ~2–4 weeks, watch reports, then tighten to `p=quarantine`
   (and later `reject`).

---

## Files changed (theme `rsg_2022`, 2 files only)
- `includes/rsg-wizard-capture.php` — endpoint + table (schema v2, nonce→rate-limit, dedupe, new fields).
- `template-wizard-2024.php` — popup email step + shared JS; results-card rework + auto-submit.

Diff size vs pre-deploy baseline: capture = 41 changed lines, template = 91 changed lines
(identical on staging and live — surgical, nothing outside the intended blocks).

### Backups (rollback pairs — restore BOTH together, then purge Cloudflare)
- **Live:**
  - `~/public_html/wp-content/themes/rsg_2022/includes/rsg-wizard-capture.php.bak-20260705-1654`
  - `~/public_html/wp-content/themes/rsg_2022/template-wizard-2024.php.bak-20260705-1654`
- **Staging:**
  - `…/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022/includes/rsg-wizard-capture.php.bak-20260705-1448`
  - `…/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022/template-wizard-2024.php.bak-20260705-1448`

---

## What changed

### Part A — `rsg-wizard-capture.php`
- `RSG_WIZARD_DB_VERSION` `1`→`2`; added columns `source VARCHAR(16)`, `pick_1_id/2_id/3_id BIGINT UNSIGNED`
  (dbDelta, additive; runs once via version-option guard).
- **Removed the `wp_rest` nonce check** (it can never validate on a ~30d edge-cached page — was a
  live bug) → replaced with a **per-IP rate limit** (transient, 5/hour → 429).
- Accept `source` (whitelisted to `pre_results`/`post_results`) + `pick_1_id/2_id/3_id` (absint).
- **Same-email-same-day dedupe guard** before insert (returns `{ok,dup:true}`), TZ-correct for
  the site's `America/New_York` (`gmdate(current_time('timestamp') - DAY)`).
- Insert extended with the 4 new fields; format array kept in matching order.
- `rsg_ml_subscribe()` and the MailerLite call: **unchanged.**

### Part B — `template-wizard-2024.php`
- Popup now has a 2-state flow: spinner wrapper `#rsg-wpopup-spin` + new `#rsg-wpopup-email`
  step (email input, "Email my matches" CTA, visible skip link, privacy note).
- Both completion paths route through shared `rsgShowEmailStep()` / `rsgFinish()`; email is
  stashed in `sessionStorage['rsg_wiz_email']` then the form submits to results (navigation-safe).
- Results card: dropped `data-nonce`; added `data-pickid1/2/3`; re-copied around the price-watch
  hook ("Want us to watch prices on these 3?"); removed the `X-WP-Nonce` fetch header; payload now
  sends `source:'post_results'` + pick IDs.
- New auto-submit block: on results load, a pending `sessionStorage` email is POSTed as
  `pre_results` (keepalive) → card flips to green "check your inbox" state; key cleared; failures
  leave the card usable as a manual fallback. gtag funnel events added
  (`EmailStep-Shown/Submitted/Skipped`).

---

## Verification

**Lint:** `ea-php83 -l` + `ea-php74 -l` clean on both files (staging AND live).

**DB:** columns `source, pick_1_id, pick_2_id, pick_3_id` confirmed present on both staging and
live `newguru_wp_rsg_wizard_signups` (DESCRIBE / SHOW COLUMNS). db_version = 2.

**Staging functional (full):**
- Endpoint: valid `pre_results` → 1 row w/ source + pick IDs + `ml_status=ok`; dedupe → `dup:true`
  no 2nd row; honeypot → accepted, no row; invalid email → 422; rate limit → 429 after 5/IP/hr.
- Browser (Chrome): complete quiz → popup → spinner ~1.8s → email step (skip visible) → submit →
  results auto-POST `pre_results` → green confirm; reload does NOT re-fire, no duplicate row.
- Test rows cleaned afterward.

**Live render (`?cb=`, canonical www host):** quiz page shows `#rsg-wpopup-email`/`#rsg-wpopup-spin`
+ `rsgShowEmailStep`; results page shows new card title, `data-pickid1`, auto-submit block, and
**no** `data-nonce`; **zero** PHP errors/warnings on either. (Note: apex 301s to `www.` — verify on
`https://www.runningshoesguru.com/...`.)

---

## Deliverability side-fix (root cause of the 0/800 v1 conversions was also this)
The DOI confirmation emails were landing in **Spam**. Root cause found via headers: the
**API double-opt-in confirmation email was sent From `ruggeroloda@gmail.com`** (a free Gmail),
so DMARC evaluated `gmail.com`, DKIM signed `d=mlsend.com` → **`dmarc=fail`** → spam. Fixes applied
2026-07-05:
- **Route 53:** added `_dmarc` TXT = `v=DMARC1; p=none; rua=mailto:dmarc@runningshoesguru.com`;
  tightened apex SPF `?all`→`~all` (kept `google-site-verification`). DKIM `ml._domainkey` already present.
- **MailerLite:** Account settings → Subscribe settings → **Double opt-in for API and integrations**
  → confirmation email **sender changed to `newsletter@runningshoesguru.com`** (was `ruggeroloda@gmail.com`);
  subject/body rewritten around the "confirm to get your matches + price watch" hook.
- **Verified by header:** `dkim=pass header.i=@runningshoesguru.com`, `dmarc=pass header.from=runningshoesguru.com`,
  From aligned; email moved **out of Spam → Promotions tab** (normal, healthy placement).

---

## Rollback
Restore the two `.bak-20260705-1654` files on live, then Cloudflare Purge Everything. DB columns
are additive — leave them (harmless). The old nonce-checking handler and old template markup are a
matched pair, so roll both back together. DNS/MailerLite deliverability changes are independent and
should be kept regardless.

## Follow-ups / open
- **Phase 3** (weekly price-drop cron + ML notify) owed within ~2–3 weeks — the copy promises it.
- 7-day check-in: `SELECT source, COUNT(*) FROM newguru_wp_rsg_wizard_signups GROUP BY source;`
  vs results-page loads in origin access logs. Target ≥2% of completions (>0 already beats v1).
- Optional MailerLite "Domain alignment" (branded return-path/tracking) — further deliverability
  boost, not required (DKIM alignment already carries DMARC).
- Existing pending: rotate SSH key, MySQL password, **MailerLite API key**.
