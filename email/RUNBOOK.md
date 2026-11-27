# Email — runbook (MailerLite, MailerSend, sign-up forms)

_Current state only. Deals emails + MailerLite access updated 27 Nov 2026; rest checked 11 Nov 2026 (site code + DNS; no MailerLite access from here). Update in the same session as any change. Strategy and platform options: `README.md`._

## Services
| Service | Used for | Facts |
|---|---|---|
| **MailerLite** (Classic API, account 693769) | Newsletter + deals emails (since 16 Oct 2026, Zappos) | **39,252 active** (26 Nov). ~$1,058/yr (to confirm on the invoice). Quiz sign-ups → group `from_wizard` (`RSG_ML_GROUP_SUBS`). API key = `RSG_MAILERLITE_TOKEN` in live `wp-config.php`: Claude reads it on Rocket.net from a Mac-linked chat (never printed) to read lists, create/update **drafts** and groups, delete its own drafts on request; **never schedules or sends** (Ruggero does). Classic API can't change a draft's subject or recipients |
| **MailerSend** (free plan, 100 mails/day) | Transactional: quiz "your 3 picks" email, DFS failure emails, rsg-dfs monthly report, weekly WordPress PHP-error digest (since 12 Nov, Mondays, only when errors were logged; `../theme/RUNBOOK.md` "PHP error log") | Sender `newsletter@runningshoesguru.com`. **One token shared** by the theme (`RSG_MAILERSEND_TOKEN`) and both DFS servers (`MAILERSEND_TOKEN`): to split per system at the key rotation (ROADMAP "Rotate exposed keys") |

DNS (Route 53): SPF `include:_spf.mailersend.net a mx include:_spf.mlsend.com ~all`, DKIM `mlsend2._domainkey`, return path `mta` → mailersend.net, DMARC `p=none` (reports to dmarc@runningshoesguru.com). Mailbox: Google Workspace.

## Where sign-ups come from
1. **Shoe Finder quiz** (`../theme/RUNBOOK.md`): `POST /wp-json/rsg/v1/wizard-subscribe` → local table `newguru_wp_rsg_wizard_signups` (reliable count) + MailerLite. EU-27/EEA/CH double opt-in, everyone else single opt-in. Picks email via MailerSend at submit. Funnel counters in `newguru_wp_rsg_wizard_funnel`.
2. No other form on the site: the old AWeber footer form had been commented out for years (dead markup removed 11 Nov). A footer/sidebar sign-up would be new work (reuse the quiz's capture endpoint).
3. Running guide PDFs are used as sign-up incentives (keep them).
- Old sign-up data of removed plugins (OptinMonster 14,616 rows, Newsletter plugin 302, Gravity Forms 35): deleted from the database 11 Nov; encrypted copy `Server-Backups/sites/rsg/archive/rsg-old-signup-tables_2026-11-11.sql.gz.age`.

## Subscriber country (checked 26 Nov 2026, all 39,252 active)
- Two fields: the **Country** text field (US 8,231, UK 2,549, AU 1,464, CA 1,046, empty 22,025) and MailerLite's own **location** (`country_id` in the API; Gleam giveaways fill it): 465 = US, 21 = UK, 95 = AU, 135 = CA (codes found by cross-tab). City/State are empty whenever Country is. Saved segments "UK subscribers" (6,835) / "Australia" (2,373) / "USA subscribers" use rules the API doesn't show.
- **Country groups** (Ruggero 26 Nov: "USA = not explicitly from somewhere else"): Country field decides when filled; when empty, location decides; both empty = USA. Groups **BF2026 – USA** `113262342` (21,316), **BF2026 – UK** `113262347` (3,681), **BF2026 – AU** `113262348` (2,103). Static: new subscribers are not added. Refresh = re-run `tools/mailerlite-country-groups.php` (adds newcomers, skips members; ~8–10 min, run it the evening before a send). Email 1 (24 Nov) went to 30,766 = US + empty Country, ~8,600 of them located elsewhere.

## Rules in force
- Amazon links in email only to opted-in subscribers; never Amazon prices in emails; check AWIN/CJ advertisers allow email traffic. Allowed (checked): Nike US (CJ), Nike UK + Hoka UK (Awin, Ruggero 27 Nov), New Balance EMEA (Impact contract has no email restriction). Not confirmed: adidas AU (Awin).
- **Every link carries our sub-ID in its own network's parameter**, chosen by link domain (CJ `sid`, Rakuten `u1`, Awin `clickref`, Impact `subId1`, Commission Factory `UniqueId`, Partnerize path `/pubref:<id>/`, AvantLink `ctc`); the build stops on an unknown domain. Before scheduling: list every link's host + sub-ID parameter. (27 Nov: the AU email went out with `sid=` on Commission Factory links, so no sub-ID on its 6 Pace Athletic buttons.)
- **Zappos pays 0 % on items ≥ 30 % below its original price** (Rakuten offer "10 % Content Baseline", seen 27 Nov): keep Zappos in deals emails but not leading; lead with shops that pay on discounts (Nike, HOKA, Road Runner Sports, Running Warehouse, adidas, Brooks).
- Look: a short personal note from Ruggero at the top, no blue hero block (Ruggero 26 Nov).
- DOI only where required (EU/EEA/CH); single opt-in elsewhere, with the newsletter stated at the point of sign-up (26 Jul).
- Clean the list before choosing a platform; wanted: smart list management and create/send through an API (9 Nov).

## Deals emails: data (23 Nov 2026)
- Offers: view `rsg.deals_email_offer` in the `umami` database on rsgmedia (Awin, Rakuten, CJ, Impact; filter rules in `../analytics/README.md` "Offers"). Shoe deals: DFS `price_history` on rsg-dfs (US, non-Amazon, ≥ 25 % below list). Never Amazon prices in email.
- Link sub-ID per email: `email-<send date>-<nn>` (Rakuten `u1` before `murl`, CJ `sid`, Awin `clickref`, Impact `subId1`; Running Warehouse can't carry one).
- Shoe prices on the send day: DFS production view `offers` (`/data/dfs.sqlite` in container `dfs` on rsg-dfs, read-only), latest `day`, country US, non-Amazon, cheapest per shoe + gender. `price_history` lags a day.
- Shoe photos: the review's `…white-background-300x225` cut-out from the site's uploads (each shoe's review page). Before picking a shoe, read the review's verdict: no shoe our review is negative about.
- Build (Mac, `_work/deals-email/`, not in git): `build_20261124.py` (data + sub-IDs → json), `render_20261124_v3.py <logo URL> <out.html>` (branded HTML: navy logo bar, blue hero, yellow buttons `#FFC83D`, photo above text, codes, footer with affiliate note). Logo: `assets/rsg-logo-white-550.png`, uploaded to MailerLite's file manager (the site's logo is SVG, which email apps don't show).
- Sent: **24 Nov 2026 ~13:55 UTC, email 1, US segment** (subject "Black Friday came early: Pegasus 42 from $78, Evo SL $93"; 30,766 recipients; change log row 146).
- **Black Friday 27 Nov (build `_work/deals-email/`: `export_offers.py` on rsg-dfs → `build_20261127.py` → `render_20261127.py`; drafts created/updated by API):**

| Email | MailerLite | Group | Send (Amsterdam) | Sub-IDs |
|---|---|---|---|---|
| AU | 64234550 | BF2026 – AU | sent 27 Nov 07:00 | `email-20261127-43…53` (Pace Athletic links without sub-ID, see rules) |
| GB | 64234548 | BF2026 – UK | 27 Nov 09:45 (08:45 London) | `email-20261127-25…42` |
| US A | 64234545 | BF2026 – USA | 27 Nov 14:00 | `email-20261127-60…88` |
| US B (same offers, "ends tonight") | 64234553 | BF2026 – USA (everyone, Ruggero) | Sat 28 Nov 02:00 | `email-20261128-01…29` |

Canada skipped (only 2 MEC deals, no store offers). Next: **Cyber Monday 30 Nov, US only** (BF2026 – USA; refresh the group Sunday evening; codes PUMA CHEER, ASICS CYBER, Zappos, Under Armour BLACKFRIDAY/EXTRA15, Finish Line; re-check Nike).

## Open
1. List cleanup: active = opened or clicked in 12 months → one re-engagement email → remove non-responders (ROADMAP "Email list cleanup").
2. Platform decision after the cleanup: MailerLite vs EmailOctopus vs Listmonk + SES; confirm MailerLite's real plan/price first (ROADMAP "Email list cleanup, then platform decision").
3. Decide whether the site gets a footer/sidebar sign-up (new work).
4. Quiz copy promises price-drop alerts that don't exist (build or cut).
5. Separate MailerSend tokens per system (ROADMAP "Rotate exposed keys").
6. Subscriber country: MailerLite already has two country fields (above); `SUBSCRIBER-COUNTRY-2026-11-24.md` options 2–3 (locate on click, ask) still open for the 22k with neither.
7. Read results per sub-ID ~1 Dec (emails 1, 27 Nov AU/GB/US A/B): clicks in MailerLite, orders in `rsg` (Rakuten, CJ, Awin, Impact; Commission Factory + AvantLink from 1 Dec).
