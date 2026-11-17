# Email — runbook (MailerLite, MailerSend, sign-up forms)

_Current state only. Checked 11 Nov 2026 (site code + DNS; no MailerLite access from here). Update in the same session as any change. Strategy and platform options: `README.md`._

## Services
| Service | Used for | Facts |
|---|---|---|
| **MailerLite** (Classic API) | Newsletter + deals emails (since 16 Oct 2026, Zappos) | ~50k subscribers, ~35k active, ~20k in the US (Ruggero, 11 Nov). ~$1,058/yr (to confirm on the invoice). Quiz sign-ups → group `from_wizard` (`RSG_ML_GROUP_SUBS`), country only stored there |
| **MailerSend** (free plan, 100 mails/day) | Transactional: quiz "your 3 picks" email, DFS failure emails, rsg-dfs monthly report, weekly WordPress PHP-error digest (since 12 Nov, Mondays, only when errors were logged; `../theme/RUNBOOK.md` "PHP error log") | Sender `newsletter@runningshoesguru.com`. **One token shared** by the theme (`RSG_MAILERSEND_TOKEN`) and both DFS servers (`MAILERSEND_TOKEN`): to split per system at the key rotation (ROADMAP "Rotate exposed keys") |

DNS (Route 53): SPF `include:_spf.mailersend.net a mx include:_spf.mlsend.com ~all`, DKIM `mlsend2._domainkey`, return path `mta` → mailersend.net, DMARC `p=none` (reports to dmarc@runningshoesguru.com). Mailbox: Google Workspace.

## Where sign-ups come from
1. **Shoe Finder quiz** (`../theme/RUNBOOK.md`): `POST /wp-json/rsg/v1/wizard-subscribe` → local table `newguru_wp_rsg_wizard_signups` (reliable count) + MailerLite. EU-27/EEA/CH double opt-in, everyone else single opt-in. Picks email via MailerSend at submit. Funnel counters in `newguru_wp_rsg_wizard_funnel`.
2. No other form on the site: the old AWeber footer form had been commented out for years (dead markup removed 11 Nov). A footer/sidebar sign-up would be new work (reuse the quiz's capture endpoint).
3. Running guide PDFs are used as sign-up incentives (keep them).
- Old sign-up data of removed plugins (OptinMonster 14,616 rows, Newsletter plugin 302, Gravity Forms 35): deleted from the database 11 Nov; encrypted copy `Server-Backups/sites/rsg/archive/rsg-old-signup-tables_2026-11-11.sql.gz.age`.

## Rules in force
- Amazon links in email only to opted-in subscribers; never Amazon prices in emails; check AWIN/CJ advertisers allow email traffic.
- DOI only where required (EU/EEA/CH); single opt-in elsewhere, with the newsletter stated at the point of sign-up (26 Jul).
- Clean the list before choosing a platform; wanted: smart list management and create/send through an API (9 Nov).

## Open
1. List cleanup: active = opened or clicked in 12 months → one re-engagement email → remove non-responders (ROADMAP "Email list cleanup").
2. Platform decision after the cleanup: MailerLite vs EmailOctopus vs Listmonk + SES; confirm MailerLite's real plan/price first (ROADMAP "Email list cleanup, then platform decision").
3. Decide whether the site gets a footer/sidebar sign-up (new work).
4. Quiz copy promises price-drop alerts that don't exist (build or cut).
5. Separate MailerSend tokens per system (ROADMAP "Rotate exposed keys").
