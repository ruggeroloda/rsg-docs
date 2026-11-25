# Brief for a new chat: review inserts from Make to n8n (25 Nov 2026)

**Goal:** one workflow on the self-hosted n8n (rsgmedia) replaces Make's "Insert Road Shoe" (637210), "Insert Trail Shoe" (718279), "Insert Training Shoe" (1664888). Then "New Review → Save Pictures on DropBox" (633722). Ruggero wants this now.

**Read first:** `content/REVIEW-PROCESS-2026-11-25.md` (how it works, direction agreed), `automations/RUNBOOK.md`, `automations/make-migration/README.md` (March analysis: the 11 steps, the three differences, the field names; its Cloudinary image plan and "top-level fields" finding are **outdated**: photos stay manual, and fields are written through the `acf` block since 11 Nov).

## Order
1. **Like-for-like port, road first.** Read the three Make blueprints (Make connector, read-only) and write the field list `automations/review-flow/fields.json` (Airtable field ID → WordPress `acf` name, per shoe type). Workflow: Airtable view "Road Reviews Ready" → author lookup → create draft with `{"acf": {...}}` → write post ID + link back → upload "Edited Images" → status "In Wordpress". Skip a record that already has a WordPress ID.
2. **Test on staging only**: point the workflow at the staging site and re-insert an existing Airtable review (copy the record, status "Ready to Publish"); compare the draft's `acf` values with the live review made by Make. Check there whether the draft needs a save in wp-admin (runbook says yes, Ruggero says never).
3. Trail and training through the same workflow (config per type).
4. Switch: Make scenarios off, n8n on against live **only with Ruggero's "deploy to live"**; keep the Make scenarios (inactive) for a month.
5. After that, in this order: the Dropbox photo scenario; one Airtable form instead of three; the missing shoe fields in Airtable (previous model, family, release date, list prices); check script (Airtable fields vs field list vs WordPress fields); the new review format's fields once Ruggero has decided (`content/REVIEW-FORMAT-2026-11-22.md`, six decisions); then the skill for "add a review field".

## Facts checked 25 Nov
- Airtable base `appLoQNoAydB65Vop`: the Airtable connector reads it; table "Shoe Reviews" `tblwEW5UcK5zbAvYi`, status field `fldLD67rLdsVTveC3`, WordPress ID `fldQCzHQLrN67sId7`, link `fldkZWEFdJERBOyKZ`.
- n8n already holds an Airtable token and a WordPress application password (credential IDs in the March doc; verify they still work).
- WordPress returns 60 fields under `acf` for a road review (e.g. post 50267); writing `{"acf": {...}}` stores values with their field references (`automations/RUNBOOK.md`).
- Specs in Airtable are 999 or empty on many rows: never send 999 to WordPress (empty instead).
- No review is waiting: all 704 are "In Wordpress", the last from 30 Apr 2026.

## Rules
Staging first; nothing on live without "deploy to live". Black Friday week (27–30 Nov): no live changes. Price data (`_rsg_prices`) is never touched by automations.
