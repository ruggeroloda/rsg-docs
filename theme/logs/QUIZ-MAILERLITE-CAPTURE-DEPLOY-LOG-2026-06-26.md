# Quiz → MailerLite email capture — deploy log 2026-06-26

**Status:** LIVE 2026-06-26. Lint 8.3 + 7.4 clean. End-to-end verified on staging AND live.
**Awaiting:** user Cloudflare "Purge Everything" (edge still serving old results HTML to visitors).
Brief: `MAILERLITE-CAPTURE-BRIEF.md`. Platform: **MailerLite Classic** (new/Power later).

## What shipped
A give-first email-capture module on the Shoe Finder results page (`/best-running-shoes-wizard/`),
backed by a WP REST endpoint that logs every submission locally and pushes the subscriber to
MailerLite Classic with double opt-in.

### Files (theme `rsg_2022`)
- **NEW `includes/rsg-wizard-capture.php`** — table installer + REST route `rsg/v1/wizard-subscribe`
  + `rsg_ml_subscribe()` (Classic API, isolated for a one-spot swap to the New API later).
- **`functions.php`** — one line: `require_once('includes/rsg-wizard-capture.php');` (after share_count).
- **`template-wizard-2024.php`** — email module inserted after the podium row (before the affiliate
  disclosure). Light white card: email field + "Email me my picks" (btn-warning), unchecked
  "Shoe Guru series" opt-in, DOI + Privacy Policy notice, honeypot, inline success/error. Collects
  picks from `$reviews_the_query->posts`, builds an answer `profile`, outputs a `wp_rest` nonce.
- **`wp-config.php` (live + staging)** — 3 constants:
  `RSG_MAILERLITE_TOKEN`, `RSG_ML_GROUP_PICKS=113205636` (from_wizard), `RSG_ML_GROUP_SERIES=''`.

### Behavior
- POST `/wp-json/rsg/v1/wizard-subscribe` (same-origin nonce, honeypot). Validates email; requires a
  consent; reads country from **Cloudflare `CF-IPCountry`** (ISO-2).
- **Local log first** → `{prefix}_rsg_wizard_signups` (email, country, type, gender, consents, 3 picks,
  profile, ml_status, ip, created_at). This is the reliable capture count, independent of MailerLite.
- **MailerLite**: `POST /api/v2/groups/113205636/subscribers`, header `X-MailerLite-ApiKey`,
  `type:"unconfirmed"` → DOI confirmation email (DOI enabled account-wide via API 2026-06-26).
- **Scope now (user decision 2026-06-26): store ONLY country in MailerLite**, written to MailerLite's
  **built-in `country` field** (no custom-field setup needed). All other quiz data stays in the local
  table. To push full data later: create the custom fields (see brief) and extend the `$fields` array
  in `rsg_ml_subscribe()`'s caller.

## Verification
- Staging + live: page renders HTTP 200, no PHP errors; module present; picks/profile/nonce in markup.
- Live POST → `{ok:true}`, local row written, MailerLite subscriber created (`ml_status=ok`) with the
  country field populated. (Country reads `US` in server-side tests because Cloudflare overrides a
  spoofed header with the origin IP; real visitors get their true country.) All test records deleted.
- `rsg_ml_subscribe()` returns `skipped_no_token`/`skipped_no_group` gracefully if unconfigured.

## Backups / rollback (live, tag .bak-202606261822)
- `functions.php.bak-202606261822`, `template-wizard-2024.php.bak-202606261822`,
  `wp-config.php.bak-202606261822`. To remove the new file: delete `includes/rsg-wizard-capture.php`
  and its `require_once` line. (Earlier same-day backups exist from the algo deploy.)

## ⚠️ USER ACTIONS
1. **Cloudflare "Purge Everything"** (Rocket.net) — required for visitors to see the form.
2. **Rotate the MailerLite API key** at some point — it was shared in plaintext (now only in wp-config).
3. (Optional) ignore/delete the test confirmation emails sent to `ruggeroloda+…@gmail.com` during QA.
4. (Later) create the 8 custom fields if/when you want full quiz data in MailerLite (now: country only).

## Update 2026-06-26 (later) — switched to SINGLE opt-in
Reasoning (user): a picks-only email you can't market to is near-worthless + GDPR liability, so the
form should be a clean newsletter signup. **Picks stay free on the page (give-first); the email box IS
the newsletter join.** Changes shipped live:
- Module: removed the second checkbox + "picks-only" framing → one honest opt-in ("get your picks +
  join the Shoe Guru series"), DOI confirmation copy. Success msg now says "check your inbox to confirm".
- Endpoint: everyone who submits joins **one** group via new constant **`RSG_ML_GROUP_SUBS=113205636`**
  (from_wizard). `rsg_ml_subscribe($email,$fields)` simplified to a single group. Old
  `RSG_ML_GROUP_PICKS/SERIES` constants removed from wp-config (live + staging).
- No second group needed (user decision): from_wizard = all subscribers; their MailerLite automation
  moves confirmed subs to the autoresponder.

**DOI gotcha confirmed:** addresses on the sending domain `@runningshoesguru.com` are auto-`active`
(MailerLite skips DOI for same-domain) — that's why early tests got no confirmation email. **External
addresses correctly come back `unconfirmed` and get the DOI email** (verified live with a mailinator
address). `resubscribe:false` means previously-unsubscribed contacts are NOT resurrected (no email).
Backups for this change tagged `.bak-202606261914` (live).

## Measuring capture
Count rows in `{prefix}_rsg_wizard_signups` (most reliable), or watch the `from_wizard` group in
MailerLite (confirmed subscribers only, since DOI is on). I can build a quick live artifact/dashboard
of capture volume on request.
