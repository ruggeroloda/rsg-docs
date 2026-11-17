# Next session: Shoe Finder quiz → emailing offers for the picked shoes

_Written 15 Nov 2026 at the end of the guides-cleanup chat (`../content/GUIDES-CLEANUP-2026-11-15.md`), as the starting brief for a new chat. Facts checked on live 15 Nov; thoughts marked as Claude's view are not decisions._

## Goal (Ruggero, 15 Nov)
Email quiz takers offers for the shoe(s) the quiz picked for them (one, or all three). Related ideas raised in the same chat: a "deals" page and "deals" emails; possibly DFS prices for shoes RSG didn't review (brand lineup pages).

## What already exists
- **Quiz capture** (`template-wizard-2024.php`, `includes/rsg-wizard-capture.php`): `POST /wp-json/rsg/v1/wizard-subscribe` → table `newguru_wp_rsg_wizard_signups` + MailerLite group `from_wizard` (country only stored there). EU-27/EEA/CH double opt-in, everyone else single opt-in. "Your 3 picks" email via **MailerSend** at submit (free plan, 100 mails/day). Funnel counters in `newguru_wp_rsg_wizard_funnel`.
- **The table already stores the picks by post ID** (`pick_1_id`…`pick_3_id`, plus names), `gender`, `country`, `quiz_type` (road/trail), `profile`, `consent_picks`, `consent_series`, `ml_status`, `email_status`.
- **Numbers (live, 15 Nov):** 597 sign-ups since 5 Jul 2026 (~130/month; Nov so far 50), 576 unique emails; road 556, trail 41; all with consent_picks + consent_series; pick IDs on all but 1. **Countries: US 115, IN 56, GB 54, GR 35, CA 29, AU 24, NL 23, TR 20, IE 18, BR 16, DE 12, ID 12…** → most quiz takers are outside the countries DFS has feeds for.
- **DFS** (`../dfs/RUNBOOK.md`): offers per shoe (= WordPress review ID) × gender × country; feeds for US, DE, GB (switched off on the site until SportsShoes), AU; countries without a feed fall back to US offers. Amazon = US only (Creators API; UK account not eligible). **Daily price history** (22:00 UTC snapshot: cheapest offer per shoe × gender × country × merchant, 2 years kept). **Deal signals designed, not built** (DECISIONS 6 Nov): ≥20% below list price; or lowest in 90 days and ≥10% below the 30-day median.
- **Email:** MailerLite ~50k subscribers (~35k active, ~20k US), ~$1,058/yr; deals emails since 16 Oct (Zappos). List cleanup not started; platform decision open (MailerLite vs EmailOctopus vs Listmonk+SES; wants smart list management + create/send via API).

## Rules already in force
- Amazon links in email only to opted-in subscribers; **never Amazon prices in emails**; check AWIN/CJ advertisers allow email traffic (`RUNBOOK.md`).
- Quiz copy currently **promises price-drop alerts that don't exist** (theme RUNBOOK gap 8 / email RUNBOOK open 4): build them or cut the copy — this project is the "build" option.
- Email link sub-ID ("email" in the network sub-ID) planned 6 Nov, not built (DFS RUNBOOK gap 6) — needed to measure what these emails earn.

## Claude's view from the guides chat (to challenge, not decided)
- **Don't extend DFS to unreviewed shoes yet.** "Shoe" in DFS = a review ID; adding a catalog of untested shoes is a few days to build but a permanent rule-QA burden every model season (Ruggero's time is the constraint), weak list prices (feed list prices only), more Amazon calls on a revenue-tied quota. Deals and quiz-offer emails can run on the ~300 reviewed shoes; older reviewed models are where the deep discounts are, and "deals on shoes we actually tested" fits the trust positioning.
- **Quiz offers fit the existing data exactly:** each sign-up has pick IDs + gender + country → DFS offers for those shoes. The open design questions are mostly about countries without feeds and Amazon's no-price rule.
- Build the deal signals first (they serve the deals page, deals emails and quiz alerts alike), then the sending.
- Email may be the channel to move first: search traffic is ~10% of what it was, and Black Friday is close.

## Open questions for the new chat
1. Trigger: price-drop alert (only when a pick hits a deal signal) vs a scheduled digest ("your 3 picks this week") vs both. One shoe or all three?
2. Countries: what does a reader in IN/GR/TR/BR get (US offers? Amazon search link? nothing)? US fallback prices are misleading outside the US.
3. Amazon: no prices in email → Amazon offers can only appear as "check price" links.
4. Sending system: MailerLite (custom fields + automation) vs MailerSend (transactional, 100/day free) vs the platform still to be chosen. Volume is small (~600 people, ~130 new/month).
5. Freshness: a price in an email is stale the moment it is read → "from $X today" + link to the review's live price box?
6. Consent wording for the existing 597 (consent_picks / consent_series — what exactly did they agree to?).
7. Unsubscribe / frequency cap; stop alerts when a shoe has no offers or a newer version replaces it.
8. Measurement: sub-ID `email-…` on network links, click logging (the site already logs every affiliate click, `rsg-dfs-clicks.php`).

## Where to look
`RUNBOOK.md` (email), `../theme/RUNBOOK.md` ("Shoe Finder quiz"), `../theme/MAILERLITE-CAPTURE-BRIEF.md`, `../dfs/RUNBOOK.md`, `../DECISIONS.md` (DFS 6–7 Nov: history, deal signals, sub-IDs), `../automations/`.
