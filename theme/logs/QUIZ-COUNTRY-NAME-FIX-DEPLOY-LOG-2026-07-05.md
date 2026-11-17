# Quiz MailerLite country-name fix — deploy log (2026-07-05)

## Problem
The Shoe Finder wizard email capture pushed the subscriber country to MailerLite's
built-in `country` field as an **ISO-3166 alpha-2 code** (`US`, `CA`) taken straight
from the Cloudflare `CF-IPCountry` header. But every pre-existing subscriber (Gleam-era
imports + MailerLite's own geolocation) stores that field as a **full country name**
(`United States`, `United Kingdom`, `Canada`, …). Verified by scanning ~1,800 live
subscriber records via the Classic API: 528 non-empty Country values, **all full names,
zero ISO codes** — the wizard was the only source writing codes.

Consequence: country segments keyed on `Fields → Country Equals "United States"` did
**not** catch wizard signups (`"US" != "United States"`), so wizard subscribers only
landed in country segments if their engagement-derived **Location** (`country_id`,
separate field, not API-writable) happened to resolve. This is what made the user's two
country fields look "alternately populated."

## Fix
`includes/rsg-wizard-capture.php`:
- Added helper `rsg_wizard_country_name( $iso )` — maps ISO-2 → full country name using
  MailerLite's naming convention (spellings for the top ~40 countries verified against
  the actual live data). Returns `''` for empty / unknown / non-country CF codes
  (`EU`, `AP`, `A1`, `XX`, `T1`), so we never send junk. Case-insensitive.
- Line 156 changed:
  - before: `$fields = array( 'country' => $country );`
  - after:  `$fields = array( 'country' => rsg_wizard_country_name( $country ) );`
- **Local table `{prefix}_rsg_wizard_signups` is unchanged** — it still stores the raw
  ISO-2 code (cleaner there). Conversion happens only on the MailerLite push.

## Verification
- Lint clean on **PHP 8.3 and 7.4** (staging and live).
- Functional test: `US`→United States, `CA`→Canada, `GB`→United Kingdom, `NL`→Netherlands,
  `TR`→Turkey, `DE`→Germany, `AU`→Australia; `''`/`XX`/`EU`/`AP`→`''`; lowercase handled.
- Diff live-vs-new was surgical (one line + appended helper only).

## Deploy flow (followed)
Edit on staging → lint 8.3 + 7.4 → functional test → diff live vs new → back up live →
copy to live → lint live → verify. Staging and live were identical before deploy.

## Backups (rollback)
- Staging: `…/jfx92wmcr3-staging.onrocket.site/…/includes/rsg-wizard-capture.php.bak-20260705-153932`
- Live:    `…/wp-content/themes/rsg_2022/includes/rsg-wizard-capture.php.bak-20260705-154013`
- Rollback = restore the live `.bak-20260705-154013` and purge Cloudflare.

## ⚠️ Pending user action
- **Cloudflare "Purge Everything"** (Rocket.net panel) — required for the new PHP to serve.
  (Affects only new wizard submissions going forward; no cached HTML change, but keep the
  standing purge habit.)

## Not done yet (optional follow-up)
- **Backfill existing wizard rows already in MailerLite with ISO codes.** Only the wizard
  ever wrote codes; currently 2 such records (one active real subscriber = `CA`, one
  unsubscribed test = `US`). Can PATCH the active one's Country field `CA`→`Canada` via API
  on request. Updating a field does not change subscription status.

## Note (unrelated, surfaced during this session)
- Wizard send uses `'resubscribe' => false` — a previously-unsubscribed person who redoes
  the wizard is **not** reactivated (stays unsubscribed, gets no email). Intentional /
  best-practice. Changeable to `true` if a fresh wizard opt-in should override an old
  unsubscribe (not recommended — deliverability risk).
