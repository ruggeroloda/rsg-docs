# Quiz → MailerLite email capture — kickoff brief

_Started 2026-06-26. Single source of truth for the quiz email-capture build. Supersedes the capture
sections of `archive/NEWSLETTER-QUIZ-BRIEF.md` (whose recency assumptions were wrong — see below)._

Project context: `CLAUDE.md`. Strategy frame ("own the audience"): `archive/NEWSLETTER-QUIZ-BRIEF.md`.

---

## Status of the quiz thread
- **Algo fix: DONE + LIVE (2026-06-26)** — recency-decay ranking + relax-until-3 fallback in
  `template-wizard-2024.php`. See `logs/QUIZ-ALGO-DEPLOY-LOG-2026-06-26.md`. (Note: the old briefs assumed a
  hard 1-year date gate; that was dead commented code. The live path was a `WP_Query` with no recency
  weighting and no fallback — that's what we actually fixed.)
- **This build: email capture on the results page → MailerLite.** Not started.

## Decisions locked (2026-06-26)
- **Platform: MailerLite. Build against CLASSIC (legacy) FIRST** (2026-06-26 change) — user is mid
  billing/credit dispute on Classic and will migrate to the new Power plan later. Goal now: ship the
  form and **measure capture volume**. Code the integration so the API layer is swappable when we move.
  - **Classic API:** base `https://api.mailerlite.com/api/v2`, header `X-MailerLite-ApiKey: <key>`.
    Add per-group: `POST /groups/{group_id}/subscribers`; DOI via `"type":"unconfirmed"` + group DOI on.
  - **(Future) New API (Power):** base `https://connect.mailerlite.com/api`, Bearer token,
    `POST /subscribers` with `groups:[]`. Swap base URL + auth header + payload shape; field keys/groups
    carry over conceptually. (Why MailerLite at all: €1,319/yr for 50k 2-yr lock = cheapest; EU data
    residency + native GDPR decisive for EU/NL entity. beehiiv/Kit rejected: US-hosted + ~2–3× cost.)
- **Capture philosophy: give-first, no gate.** All 3 shoes show on the page regardless. The email
  module sits immediately after the podium; a quiet "✉ email me these picks" link near the header.
- **Two consents (GDPR, RSGMedia = EU/NL):** (1) email me my picks; (2) a *separate, unchecked* opt-in
  to the newsletter / 12-part "Shoe Guru" series. **Double opt-in ON.**
- **Country: stored automatically from Cloudflare `CF-IPCountry`** (ISO-2, server-side, zero friction).
- **12-part series already exists** in MailerLite (user handles the automation). We just add subscribers
  to the right group; the automation triggers on group join.
- **Welcome/picks email:** the picks are merged into the email via custom fields (see below), so the
  confirmation/welcome can say "here are your 3 shoes."

## Architecture (Classic)
```
Results page (template-wizard-2024.php)
  └─ email module (JS, fetch) ──POST──> /wp-json/rsg/v1/wizard-subscribe   (same-origin, nonce)
        └─ PHP REST handler:
             • validate email
             • read $_SERVER['HTTP_CF_IPCOUNTRY']           → quiz_country
             • map quiz answers + 3 picks                   → custom fields
             • POST https://api.mailerlite.com/api/v2/groups/{group_id}/subscribers
                  Header: X-MailerLite-ApiKey: <RSG_MAILERLITE_TOKEN>   (from wp-config.php)
                  body: { email, fields:{...}, "type":"unconfirmed", "resubscribe":false }
             • consent 2 (series) ticked → also POST to the shoe-guru-series group
             • DOI: "type":"unconfirmed" + group DOI enabled → confirmation email sent
```
- **API layer isolated in one function** (`rsg_ml_subscribe()`) so the New-API swap later is one place.
- Token lives in `wp-config.php` as a constant (`RSG_MAILERLITE_TOKEN`) — **never** in theme/git.
- Re-submitting the same email updates fields (use `resubscribe:false`; won't reactivate unsubscribed).
- Endpoint is same-origin + WP nonce; honeypot + basic rate-guard to deter bots (DOI also protects list).

---

## ⚙️ USER setup checklist (do in the MailerLite dashboard + host)

**A. Get the Classic API key** — MailerLite Classic → Integrations → **Developer API** → copy the key.
Paste it straight into `wp-config.php` (step E); don't send it in plaintext if avoidable.

**B. Turn on double opt-in** — Classic: enable **double opt-in** for the form/group flow (Subscribe
settings / per-group). We also send `type:"unconfirmed"` from the API so the confirmation email fires
and consent is logged.

**C. Create the groups** (Subscribers → Groups):
1. `shoe-finder-picks` — everyone who asks to be emailed their picks (consent 1).
2. `shoe-guru-series` — the newsletter / 12-part series opt-in (consent 2). Connect your existing
   12-part automation to trigger on **joining this group**.

**D. Create these custom fields** (Subscribers → Fields → add field; **type = Text** for all). The
key in (parentheses) is what the API writes to — name them so the key matches:
- Country code (`quiz_country`) — ISO-2, e.g. NL
- Quiz type (`quiz_type`) — road / trail
- Gender (`quiz_gender`) — male / female
- Pick 1 (`quiz_pick_1`)
- Pick 2 (`quiz_pick_2`)
- Pick 3 (`quiz_pick_3`)
- Picks links (`quiz_picks_url`) — the 3 review/shop URLs (or results URL)
- Answer profile (`quiz_profile`) — compact summary, e.g. `road|plush|some-stability|no-plate|daily_training`

(MailerLite has a built-in `country` field, but it expects a full country name; we use `quiz_country`
for the clean ISO-2 code to keep segmentation unambiguous.)

**E. Put the token in `wp-config.php`** (on the server, above the "stop editing" line):
```php
define('RSG_MAILERLITE_TOKEN', 'paste-the-token-here');
```
I'll give exact placement when we deploy; do NOT commit it anywhere.

**F. (Optional) Get the group IDs** — I can fetch them via the API once the token is in, so you don't
have to copy them by hand.

---

## Build order (mine)
1. **REST endpoint** `/wp-json/rsg/v1/wizard-subscribe` — staging first; lint 8.3 + 7.4. Reads token
   constant; if absent, fails gracefully (logs, returns friendly error) so nothing breaks pre-setup.
2. **Results-page email module** — UI after the podium: email field, 2 consent checkboxes (consent 1
   to email picks; consent 2 unchecked = series), quiet header "✉ email me these picks" link, JS fetch
   with success/error states. Hidden fields already hold the answers + picks for the payload.
3. **Light restyle** of the results area only (don't let a full redesign block this).
4. **End-to-end test on staging** → submit, confirm subscriber + fields + country land in MailerLite
   and the DOI email sends → deploy live → user purges Cloudflare → deploy log.

## Open / later
- **Picks-only automation:** consent-1 people (`shoe-finder-picks`) should get a simple "here are your
  picks" email (automation on group join, using the `quiz_pick_*` merge fields). User to create, or we
  spec it. Series (consent 2) already exists.
- Mobile sticky buy-bar + "price checked today" trust line (deferred conversion pass).
- Traffic routing into the finder once results+capture are solid (homepage CTA already points here).

## Deploy rules (unchanged, per CLAUDE.md)
Staging → lint 8.3 **and** 7.4 → verify render (`?cb=`) → diff live-vs-new → back up live → upload →
verify live → user purges Cloudflare → write/append a deploy log. Theme files only; never edit live
directly; token only in `wp-config.php`.
