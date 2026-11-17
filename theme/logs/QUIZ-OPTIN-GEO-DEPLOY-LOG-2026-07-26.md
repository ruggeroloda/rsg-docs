# Quiz email capture — geo-conditional opt-in — DEPLOY LOG 2026-07-26

## Why
The MailerLite newsletter group `from_wizard` was drowning at the double-opt-in (DOI) step:
of ~99 quiz signups only **13 confirmed** (13 active · 82 unconfirmed · 5 unsubscribed ·
0 bounced · 0 junk). Addresses were fine and delivered — people simply weren't clicking the
DOI confirmation, because the MailerSend "your 3 picks" email already hands over the value at
submit, leaving no reason to confirm a second email.

Legal check (two independent passes): GDPR/UK-GDPR do **not** require DOI anywhere; the ICO
doesn't mention it. Both single and double opt-in are lawful — the requirement is *provable*
consent, which we already log (`created_at` + `ip` + `consent_series`). Germany/Austria are the
strict exception (UWG §7 + court precedent treats DOI as the consent-proof standard).

## Decision
Switch to **geo-conditional opt-in**, deliberately conservative:
- **EU-27 + EEA-3 (IS/LI/NO) + Switzerland = 31 countries → DOI** (`type: unconfirmed`, keeps the
  confirmation email). Strict across the whole European data-protection bloc, not just DE/AT.
- **Everyone else (US, CA, UK, rest of world, unknown) → single opt-in** (`type: active`, no
  confirmation email; instant newsletter member). UK/US/CA are the revenue markets — single
  opt-in is legal there and recovers nearly all the lost subs.

MailerLite mechanics: the per-subscriber API `type` field overrides the account DOI setting —
`type:active` skips the confirmation email regardless of account config, `type:unconfirmed`
triggers it. So this is controlled entirely in our code, no account toggle needed.

Applied to the historical 99 signups: ~74 (75%) would be single opt-in (incl. all 42 US+CA+UK),
~25 stay DOI in EU/EEA+CH.

## Changes (LIVE 2026-07-26)
Theme: `rsg_2022`. Two files, surgical:

1. **`includes/rsg-wizard-capture.php`**
   - `rsg_ml_subscribe()` gains a `$country` param; call site (line 176) passes the already-captured
     CF-IPCountry.
   - New `$rsg_doi_countries` set (EU-27 + IS/LI/NO + CH); `$type = in DOI set ? 'unconfirmed' : 'active'`.
   - `type` in the API body changed from hard-coded `'unconfirmed'` to `$type`.
   - Docblock + comments updated.

2. **`template-wizard-2024.php`** (line 1681)
   - Pre-results popup fine print now states consent explicitly: "Entering your email also
     subscribes you to our newsletter (about one email every few days). No spam, unsubscribe
     anytime. Privacy Policy." (Post-results card already says "Join the newsletter" — unchanged.)
   - Needed because single opt-in relies on clear, informed consent at point of capture.

## Verify / lint
- `ea-php83 -l` and `ea-php74 -l` clean on both files (staging + live).
- Logic tested: US/CA/GB → active; DE/FR/NL/IT/ES/NO/CH/IS → unconfirmed; TR/IN/AU/unknown →
  active; case-insensitive. DOI set size = 31.
- Live confirmed identical to staging post-deploy.

## Backups / rollback
- Live backups: `includes/rsg-wizard-capture.php.bak-20260726-100025`,
  `template-wizard-2024.php.bak-20260726-100025` (next to each file).
- Staging backups: `.bak-20260726-090725`.
- Rollback: `cp <file>.bak-20260726-100025 <file>` then purge.

## Pending manual action
- **Purge Rocket.net cache** (host edge cache) so the new capture logic + copy serve to real
  visitors. (Note: this project purges via **Rocket.net**, not Cloudflare.)

## How to measure (after purge, give it a day or two of new signups)
Group status (should see `active` climbing vs `unconfirmed`):
```
curl -s -H "X-MailerLite-ApiKey: <RSG_MAILERLITE_TOKEN>" \
  "https://api.mailerlite.com/api/v2/groups/113205636"
```
Local signups vs their ml_status / country split:
```
wp db query "SELECT country, ml_status, COUNT(*) c FROM newguru_wp_rsg_wizard_signups \
  WHERE created_at >= '2026-07-26' GROUP BY country, ml_status ORDER BY c DESC;"
```
Expect non-EU/EEA signups to log `ml_status = ok` and land as `active` in the group;
EU/EEA+CH still `unconfirmed` until they confirm.

## Not done (deliberately, follow-ups)
- The 82 already-unconfirmed subs are **not** retroactively activated (ML won't, and re-approaching
  them is a separate opt-in decision).
- Picks email (MailerSend) still has no unsubscribe — it's one-time/transactional; the recurring
  newsletter's MailerLite one-click unsubscribe honors the "unsubscribe anytime" promise. Optional
  follow-up: add an identifying footer + postal address to the picks email for the affiliate-content
  CAN-SPAM edge (a MailerSend suppression-list link would NOT touch the ML newsletter, so it's the
  wrong tool — skipped on purpose).
- DE/AT (now whole EU/EEA+CH) DOI could still be lifted via a customized confirmation email +
  on-site "check your inbox" nudge — future.
