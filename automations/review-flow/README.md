# Review insert flow (Airtable → WordPress draft), n8n

_State 25 Nov 2026: built, imported **inactive** on rsgmedia n8n (`rsgReviewInsert1`, "RSG — Review Insert (Airtable to WordPress)"), staging version. Road, trail and training tested on staging 25 Nov (below). Make's three insert scenarios still run. Brief: `../../content/BRIEF-REVIEW-FLOW-N8N-2026-11-25.md`._

## Files
- `fields.json` — the field list: Airtable field ID → WordPress `acf` name, per shoe type, plus what Make sent that is dropped and why. **The only place to change a field.**
- `build_workflow.py` — writes `workflow-staging.json` / `workflow-live.json` from `fields.json` (`python3 build_workflow.py staging|live`).
- `_test-workflow-staging.json` — generated test version (`build_workflow.py staging <record id>`): reads that one record even if it has a WordPress ID, drafts on staging, writes nothing to Airtable.
- `workflow-*.json` — generated; import with `docker exec n8n n8n import:workflow --input=<file>` (same id, replaces the previous version; stays inactive).

## What it does (one workflow for road, trail, training)
1. Settings: target site (staging / live) and which status it picks (`test` on staging, `Ready to Publish` on live).
2. WordPress users with a writer role (108, two pages) → author by the reviewer's name. No match → draft by the API user (Make took the first search hit).
3. Airtable "Shoe Reviews": status = picked status and no WordPress ID (field IDs, not names).
4. Per review: type from Category (Trail / Training, else road), `acf` built from `fields.json`; skipped (not an error) when it already has a WordPress ID or has no Edited Images.
5. Create the draft (`title`, `status: draft`, `shoes_type`, `author`, `acf`, plus content/excerpt from `make_extras`) → "Make extras (temporary)" writes the plain meta Make wrote (title, description, keywords, …: title and description feed the page title and meta description through the theme's SEO module `includes/plugins/seo.php`) through the Make connector plugin key (n8n credential `rsgWpMakeKey0001`, header `iwc-api-key`; WordPress REST can't write unregistered meta) → write WordPress ID + link back to Airtable.
6. Each Edited Image: download → upload to the media library attached to the draft (title "… Review - picture N", alt/description "Picture of …", as Make).
7. Gallery: the uploaded pictures, in Airtable order, become the review's gallery (the field takes at most 19: the first 19). Swap pictures in wp-admin if needed.
8. Status → "In Wordpress".
Reviews that can't be inserted (no reviewer on the shoe in Running Shoes, reviewer not a WordPress writer, no Edited Images) are not touched and the run fails at the end with their names, so the Error Notifier mails the list (the others are inserted first).
Errors go to "RSG — Error Notifier". If a picture fails, the review keeps its WordPress ID and status "Ready to Publish": fix by hand (the flow won't create a second draft).

## Run by hand (from a Mac-linked chat)
`docker exec -e N8N_RUNNERS_BROKER_PORT=5690 -e N8N_PORT=5699 n8n n8n execute --id=rsgReviewInsert1` (the extra ports avoid a clash with the running n8n).

## Testing: never create a test record in "Shoe Reviews"
A new record fires the Airtable automation "When Review received change Status": the linked shoe in "Running Shoes" goes to "Received" (reminder emails hang off shoe status views) and the review goes to "Pictures Need Saving" (Make's Dropbox scenario + email). Test with an existing record instead: `python3 build_workflow.py staging <record id>`, import, run, then re-import `workflow-staging.json`.

## Staging test, road, 25 Nov 2026 (Pegasus 42, `recFWrRLqP3lOiM72` → staging draft 50746, compared with live 50267 on staging)
- Run OK end to end: draft, extras meta, 21 pictures attached (titles/alt as Make), Airtable not touched (last modified still 10 May).
- **No wp-admin save needed**: every value has its field reference (`_vote`, `_pros` …) and `get_fields()` returns them like on the live review.
- Same as live: model, brand, scores, rating, sizing, fit, cushioning, plate, usage, URL, list price, pros/cons (ours trimmed), who should not buy.
- Different, expected: specs not sent (Airtable has 999; live was fixed by hand); verdict / who-is-it-for differ only in line endings (`\r\n` vs `\n`) and a trailing newline; text sections on live have the photos Ruggero placed by hand; previous model, family, gallery, cut-out, compare shoes, `ds_query` are hand work (not in Airtable).
- `ds_query` is a required field: the REST call fails without it, so the flow sends it empty (Make never sent it either).
- Post content and excerpt are sent but WordPress ignores them: the review post types don't support editor/excerpt (also why they are empty on every live review).
- Author: this record has no Reviewer (lookup empty), so the draft is by the API user (ID 1). Make would have taken the first search hit.

## Staging test, trail + training, 25 Nov 2026 (Cascadia 19 `reciWIViLsRkAvTAJ` → 50770 vs live 49782; Nano X4 `recHgDXhO3CJ1KgBq` → 50769 vs live 46013)
- Both OK end to end, right post type and shoe type, authors as live (Jim Coulson 8, Josh McClurg 107), 29 + 11 pictures, Airtable not touched.
- Same as live: model, brand, pros/cons, all scores, specs, list price, verdict, who should (not) buy, fit, cushioning, sizing; trail lugs / rockplate / water resistance / "use these shoes for"; training weight lifting.
- Different because of hand edits on live after the insert: trail terrain list (live edited 16 Nov 2025), training paragraph breaks in the "good for" texts, training shop (live: Reebok via pntrac; Airtable's URL is a ShareASale link with spaces, and `?from=guru` is appended after its own `?`, as Make did).
- Extras meta written (title, description, keywords; training's two old-name "good for" keys).

## Staging test of the 25 Nov afternoon changes (Pegasus 42 + Cascadia 19 + Nano X4 together)
- Pegasus 42 (no reviewer) reported, not inserted; Cascadia 19 and Nano X4 inserted first, then the run ended with the problem message.
- Gallery: Cascadia 19 = first 19 of 29 pictures, Nano X4 = all 11; other fields unchanged after the gallery write.
- Links: Cascadia `…descpage-B19CAM3.html?from=guru`; Nano X4's ShareASale link sent unchanged.
- First try failed on the gallery limit (19): fixed. All test drafts and their 80 pictures deleted from staging.

## Differences from Make (on purpose)
- Writes through `acf` (field references stored, no wp-admin save needed: to confirm in the staging test).
- 999 / 0 / empty specs are not sent. Empty pros/cons rows are not sent.
- Running Warehouse links get `from=guru` (with `&` when the link already has `?`); other shop links are sent as they are (Make put `?from=guru` on every link, which broke ShareASale-style links).
- No author guess: a review without a reviewer, or with one who is not a WordPress writer, is reported instead of inserted (Make took the first search hit).
- Gallery filled automatically (25 Nov; Make never did, Ruggero set it by hand).
- Training: link written back as `/reviews/training/` (Make wrote `/reviews/trail/`); the two "good for" fields Make wrote under wrong names now land; URL `?from=guru` without the extra `/`.
- Trail/road taxonomies Make sent (`usage`, `lugs`, terrain…) are ACF fields: sent as `acf`.
- Everything else Make sent is still sent (Ruggero 25 Nov: don't drop things yet): `fields.json` → `make_extras`.
- Trail type: a Category value containing "Trail" (e.g. "Trail; Cushioning") counts as trail; Make's view only matched exact "Trail".
