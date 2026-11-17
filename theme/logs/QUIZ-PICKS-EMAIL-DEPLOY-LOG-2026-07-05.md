# Quiz → "your 3 picks" email via MailerSend — deploy log 2026-07-05

**Status:** LIVE 2026-07-05. Lint 8.3 + 7.4 clean. End-to-end verified on staging AND live
(live send returned HTTP 202 `sent`; test landed in Gmail **Promotions**, not spam).
**Awaiting:** user Cloudflare "Purge Everything" (unrelated to sending, but the quiz template edits
from earlier deploys still need it) + MailerSend API-key rotation (shared in plaintext).

## Why this exists
The quiz captured emails (local table + MailerLite) but the 3 picks were never emailed. A MailerLite
automation could not do it cleanly: with DOI on, automations only fire **after** a subscriber confirms,
so anyone who wanted their picks but didn't confirm would get nothing. Solution: send the picks as a
**transactional** email from WordPress, immediately, independent of DOI — it reaches 100% of submitters.
MailerLite still handles the newsletter DOI + 12-part series, unchanged.

## Sender platform: MailerSend (MailerLite's transactional arm)
Chosen so the send rides an **authenticated, reputation-bearing** channel (never raw `wp_mail`, which
would spam). Domain `runningshoesguru.com` verified in MailerSend 2026-07-05:
- **SPF** merged (one record): `v=spf1 include:_spf.mailersend.net a mx include:_spf.mlsend.com ~all`
  (kept the existing MailerLite `_spf.mlsend.com`; Google untouched).
- **DKIM** CNAME `mlsend2._domainkey` → `mlsend2._domainkey.mailersend.net`.
- **Return-Path** CNAME `mta` → `mailersend.net`.
- DNS is in **AWS Route 53** (Cloudflare only proxies in front). Records confirmed live via `dig`.
- Free plan (500/mo, 100/day), card on file, no charge. **From/Reply-To = `newsletter@runningshoesguru.com`**
  (no mailbox needed to send; `newsletter@` already exists in Google Workspace so replies are received).
  From/Reply-To are set in code, not in the MailerSend dashboard.

## What shipped (theme `rsg_2022`, one file)
**`includes/rsg-wizard-capture.php`** — added, all isolated so the sender can be swapped in one place:
- Config constants (safe empty/default): `RSG_MAILERSEND_TOKEN`, `RSG_MS_FROM`, `RSG_MS_FROM_NAME`,
  `RSG_MS_REPLYTO`.
- `rsg_ms_send_picks($email,$picks,$quiz_type,$gender,$profile)` → POST `https://api.mailersend.com/v1/email`
  (Bearer token; `from`/`to`/`reply_to`/`subject`/`html`/`text`). 202 ⇒ `sent`, else `ms_http_<code>`/`ms_err`.
- `rsg_ms_build_pick_cards()` — pulls each pick's **title, permalink, featured image** from the review
  post ID (falls back to the passed name; skips the image column if no thumbnail).
- `rsg_ms_profile_chips()` — maps the saved quiz answers (quiz_type + gender + the `profile` string)
  to readable chips (Road, Men's, Plush cushioning, Daily training, Some stability, etc.). Unknown
  keys/values are skipped, so a quiz change can't produce gibberish.
- `rsg_ms_picks_html()` / `rsg_ms_picks_text()` — the email itself (see design below).
- **Handler wiring:** after the local-log + MailerLite steps, `rsg_wizard_subscribe_handler()` calls
  `rsg_ms_send_picks(...)` and stores the result in a new **`email_status`** column. Best-effort:
  the endpoint always returns `ok:true`, so a mail hiccup never blocks the form.
- **DB:** `RSG_WIZARD_DB_VERSION` bumped `2 → 3`; adds `email_status VARCHAR(40)` via dbDelta.

**`wp-config.php` (live + staging):** added `define('RSG_MAILERSEND_TOKEN', 'mlsn.…');` after
`RSG_ML_GROUP_SUBS`.

## Email design (v2, matched to the live site)
Table-based, inline styles (email-client safe). Uses the site's OWN design language, not generic:
- **Header:** real RSG logo (`images/logo.png`, 188px) centered on white.
- **Palette:** dark `#1c2226` text, blue `#2189E1` accents/links (the theme's link colour), amber
  `#ffbf1f` CTA (matches `btn-warning`). (v1's orange was replaced — it read "Amazon-ish".)
- **Conversational** greeting + intro ("no freebies, no bias").
- **"You told us"** recap panel: the runner's quiz answers as blue chips.
- **3 shoe cards:** photo + "Match #n" (blue) + name + amber "See review & best price" button → review.
- **Footer:** the site's real "we buy every shoe at retail / reader-supported / Amazon Associate"
  disclosure + a why-you're-receiving line.
- Subject: "Your 3 running shoe matches from the Shoe Finder". Plain-text alternative included.
- **Greeting by name: deferred** (user decision 2026-07-05) — would need a name field on the capture
  form; greets "Hi there," for now. Future: per-shoe verdict / best price / retailer in each card.

## Verification
- Staging + live lint clean (ea-php83 **and** ea-php74).
- Live: `rsg_wizard_capture_install()` ran → `db_version=3`, `email_status` column confirmed present.
- Live send (real review IDs + sample road profile) → `sent` (HTTP 202), using live logo + live URLs.
- Deliverability: test landed in Gmail **Promotions**, no spam. (SPF/DKIM/Return-Path all pass.)

## Backups / rollback (live, tag .bak-202607051948)
- `~/public_html/wp-config.php.bak-202607051948`
- `~/public_html/wp-content/themes/rsg_2022/includes/rsg-wizard-capture.php.bak-202607051948`
- Staging capture backups: `.bak-202607051934` (v1) and a later v2 tag.
- **Full rollback:** restore the capture `.bak` (removes all MailerSend code); optionally remove the
  `RSG_MAILERSEND_TOKEN` line from wp-config. The `email_status` column can stay (harmless). MailerLite
  + local-log capture are untouched by this change.
- **Disable sending only (keep code):** blank `RSG_MAILERSEND_TOKEN` in wp-config → `skipped_no_token`,
  nothing else breaks.

## ⚠️ USER ACTIONS
1. **Cloudflare "Purge Everything"** (Rocket.net) — clears edge cache. (The send is server-side and
   already works; this is for the accumulated template/HTML deploys.)
2. **Rotate the MailerSend API key** — shared in plaintext 2026-07-05; now only in `wp-config.php`
   (`RSG_MAILERSEND_TOKEN`) and the gitignored `.credentials-local.md`. Re-key when convenient and
   update the constant.
3. (Optional) delete the 3 QA test emails sent to `ruggeroloda@gmail.com`.
4. MailerSend free plan = **500/mo, 100/day**. Well above expected quiz volume; watch if traffic grows.

## Measuring
`{prefix}_rsg_wizard_signups.email_status` per row: `sent` / `ms_http_<code>` / `ms_err` /
`skipped_no_token` / `skipped_no_picks`. (Local log remains the reliable capture count; `ml_status`
tracks the newsletter DOI.)
