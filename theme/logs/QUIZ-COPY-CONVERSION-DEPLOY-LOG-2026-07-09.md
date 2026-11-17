# Deploy log — Shoe Finder email-ask copy rework (conversion Phase 1a)

_Date: 2026-07-09. Author: session. Status: LIVE (pending Cloudflare purge + MailerLite DOI edit)._

## Why
Funnel (`rsg_wizard_funnel`, live since 2026-07-06) over 3 days: **99 starts → 86 completed (87%) →
86 email_shown → 5 submitted (~6%) / 74 skipped (86%)**. Quiz completion is strong; the leak is
entirely at the email ask. Diagnosis confirmed on real sample → Phase 1 targets the ask, not the quiz.

Also fixed a structural flaw: the transactional picks email (MailerSend, 100% delivery) removed any
reason to click the MailerLite DOI confirmation → subs never confirm → newsletter never grows.
Fix = plan **B (two-tier value)**: picks delivered immediately (service message, GDPR-fine); the
recurring newsletter is reserved behind DOI confirmation, and both the picks email and the DOI email
now sell that confirmation. DOI preserved (GDPR — single opt-in rejected).

## Changes (theme files only; copy + one email block, no architecture change)
**`template-wizard-2024.php`**
1. Gate promise (`#rsg-wpopup-email`): dropped the unbuilt "watch prices / tell you when one drops"
   promise → picks + honest tester advice ("500+ shoes", ~one email every few days, unsubscribe).
2. Gate: added social-proof line — "Join 45,000+ runners who find their next shoe with us."
3. Gate button: "Email my matches" → **"Email me my 3 picks"**.
4. Gate skip link: de-emphasised (smaller `.8rem`, lighter `#9aa3ac`) — "No thanks, just show my results".
5. Results card (`.rsg-emailcard`): retitled "Get honest shoe advice from our testers", 45k/500+
   social proof, button → **"Join the newsletter"** (differentiated from the modal ask).

**`includes/rsg-wizard-capture.php`** (`rsg_ms_picks_html()`)
6. Added a "One quick step — check your inbox" block after the pick cards, priming the MailerLite DOI
   confirmation email (plan B, Lever 2). No price-watch language anywhere (email was already clean).

All price-drop-alert promises removed everywhere (feature not built).

## Deploy
- Edited on **staging** first (byte-exact replacement script, each replacement asserted count==1).
- Lint **8.3 and 7.4**: clean, both files, staging + live.
- Confirmed **live == staging pre-edit (no drift)** before applying → same script applied to live →
  **live == staging** after.
- Verified new copy renders on staging URL and on the **live origin** (`--resolve …:127.0.0.1`, cb param).

## Backups / rollback
- Live: `~/public_html/wp-content/themes/rsg_2022/template-wizard-2024.php.bak-20260709-064834`
  and `.../includes/rsg-wizard-capture.php.bak-20260709-064834`.
- Staging: matching `*.bak-20260709-064304`.
- Rollback: `cp <file>.bak-20260709-064834 <file>` on live, then purge Cloudflare.

## Pending manual (USER)
1. **Cloudflare "Purge Everything"** (Rocket.net panel) — required before real visitors see the new copy.
2. **MailerLite DOI confirmation email rewrite (Lever 1)** — dashboard only. Copy provided in-session:
   subject "One click to start getting our honest shoe advice", benefit-led body + button
   "Confirm & join the newsletter". Without this, Lever 2 (the picks-email nudge) points at a generic
   confirm page.

## Measure
Baseline (pre-change, 3 days): submit ~6% of email_shown, skip 86%. After purge, watch
`rsg_wizard_funnel` for `email_submitted / email_shown` lift (see funnel query in
`QUIZ-FUNNEL-INSTRUMENTATION-DEPLOY-LOG-2026-07-06.md`). Also watch `rsg_wizard_signups` count and,
in MailerLite, the DOI **confirmation rate** (the plan-B target).
