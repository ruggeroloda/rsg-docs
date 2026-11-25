# Review process: how it works today and what changes (25 Nov 2026)

_Read from the real systems on 25 Nov 2026: Airtable base "RSG Product Testing" (`appLoQNoAydB65Vop`), Make, WordPress REST, the OptiPlex. Decisions: `../DECISIONS.md` 25 Nov._

## Today
1. **Scraper** (OptiPlex, `../automations/rw-scraper/`) adds new Running Warehouse models to Airtable "Running Shoes" (2,245 rows: 1,146 published, 755 ignored, 165 "New Release", 154 "only overview needed").
2. **Ruggero** triages in Airtable: priority, ignore, tester (table Reviewers: 50, 18 active), status (requested, sent, in testing, received…).
3. **Tester** fills one of three Airtable forms (road, trail, training) → table "Shoe Reviews" (97 fields; ~20 are lookup + formula copies made for Make). 704 reviews, all "In Wordpress"; last one 30 Apr 2026, 8 in 2026; one tester wrote 35 of the 83 since 2025.
4. **Photos**: Make "New Review → Save Pictures on DropBox" copies the form's photos to Dropbox and mails Ruggero → Luminar Neo → he uploads them to "Edited Images" → status "Ready to Publish". An n8n job later deletes the originals in Airtable.
5. **Make** "Insert Road / Trail / Training Shoe" (every 3 h): creates the WordPress draft with ~50 hand-mapped fields, uploads the edited images, writes the post ID back, status "In Wordpress".
6. **wp-admin**: Ruggero completes what Airtable does not have (previous model, family, release date, cut-out image, gallery, video, non-US list prices) and publishes.

## Findings
- Specs: Airtable said 999 where WordPress has the real values (Pegasus 42: 999 vs 10.3 oz / 38 / 28 mm): specs were fixed by hand in WordPress. Cause and fix: the scraper runbook.
- A new review field means: Airtable field, three forms, three Make scenarios, the WordPress field, the template.
- "How we tested" and "Unboxing Video" exist in Airtable, are empty on recent reviews and are not sent to WordPress.
- Checked 25 Nov on staging: drafts written through `acf` need no save in wp-admin (every value has its field reference).

## Direction agreed (nothing built)
1. Airtable stays as intake and staging: form, photos, Ruggero's edits, fields usable for social / video automations.
2. One form with the trail and training questions shown by shoe type.
3. One n8n flow instead of the three Make scenarios, driven by a field list in this repo (Airtable field → WordPress field), writing through the `acf` block.
4. A field change is one request to Claude (Airtable field, form question, list line, WordPress field, template) plus a check script that compares the three. Skill after the first real change.
5. Specs have one home; no placeholder values reach a draft.
6. Photo editing stays manual. Improve only the plumbing (one folder in, one step back) and give testers a one-page photo guide with the new tester's brief.

Considered and dropped: testers writing straight into WordPress drafts (one definition per field, but no staging step for the photos); one shared shoe record in WordPress (its payoff was pages for unreviewed shoes, which were the thin `/overview/` pages).

## Shoe facts: where they live
Airtable owns a shoe's facts until the review is published (scraper, Ruggero's corrections, one flow sends shoe + review to WordPress). After publishing, WordPress owns the published copy (the price engine reads list prices there). Airtable is not updated afterwards. To make it hold: add previous model, family, release date and the list prices per currency to "Running Shoes"; fix shoe facts in Airtable before the insert, never in the draft.

## Depends on
The review format decisions (`REVIEW-FORMAT-2026-11-22.md`, six decisions) define the new fields; the form and the flow are built after them.

## 25 Nov afternoon
- Flow built and tested on staging (`../automations/review-flow/README.md`); switch on live planned 1 Dec.
- "Running Shoes" cleaned up (Ruggero): 6 shoes kept as New Release / High (Novablast 6, Ghost 18, Clifton 11, Kayano 33, Pegasus Plus 2, Winflo 12 added by hand from nike.com), 318 open rows to IGNORE SHOE (before/after: `RUNNING-SHOES-CLEANUP-2026-11-25.csv`), New Balance More v5 (Michael, in testing since 2024) closed. Bondi 10 not released (HOKA US still lists the Bondi 9): not added. IGNORE SHOE keeps meaning "never" (Ruggero); a shoe due a replacement within a couple of months is not reviewed.
- Reviewer reminders (4 / 8 / 12 weeks) and the Sunday Weekly Digest switched off by Ruggero.
