# Email

_Last updated 2026-11-11._ **Current state (services, sign-up paths, DNS, rules): `RUNBOOK.md`.** This file: strategy and platform options.

- **MailerLite:** ~50k subscribers, **~35k active, ~20k in the US** (Ruggero, 11 Nov). Cost **~$1,058/yr (~$88/mo)**, Ruggero's figure, to confirm on the invoice. Fed by the Shoe Finder quiz (geo-conditional opt-in) and older signups. Theme integration: `../theme/MAILERLITE-CAPTURE-BRIEF.md`.
- **MailerSend:** transactional "your 3 picks" quiz email.
- **Deals emails** started 16 Oct 2026 (Zappos). Compliance: Amazon links in email only to opted-in subscribers; never Amazon prices in emails; check AWIN/CJ advertisers allow email traffic.

### List cleanup (do first)
1. Active = opened **or** clicked in the last 12 months. (Apple Mail inflates opens, so "inactive" usually is.)
2. Send the rest one re-engagement email ("Still want running-shoe deals? Click here").
3. Export, then remove non-responders.

### Platform options (after cleanup)
_List prices below are from 5 Nov estimates and do not match the ~$1,058/yr actually paid: re-check them (and the plan MailerLite bills) before deciding. 9 Nov: Ruggero wants smart list management and creating + sending emails via API._

| Option | ~50k contacts | ~20k contacts | Notes |
|---|---|---|---|
| MailerLite | $319–389/mo | $159–219/mo | Current; easiest |
| EmailOctopus | $178/mo | less | Simpler, cheaper |
| Listmonk (self-hosted, Hetzner) + Amazon SES | ~$5–20/mo | ~$2–8/mo | ~1 day setup; you manage bounces/complaints; SES suspends on high bounce rates, so clean first |
Current MailerLite cost: ~$1,058/yr (to confirm), in `../costs/COSTS.md`.

Automations (n8n, Make) are in `../automations/`.
