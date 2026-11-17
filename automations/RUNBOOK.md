# Automations — runbook (n8n, Make, WordPress API)

_Current state only. Checked 11 Nov 2026 (n8n database read-only, Make via its API); re-checked 12 Nov (n8n 3 active / 33 inactive; Make 35 scenarios, the same 5 active). Update in the same session as any change. Plan/history: `archive/HANDOVER-2026-11-05.md`, `make-migration/README.md` (March 2026 plan, to scale down)._

## Platforms
| Platform | Where | Cost | State |
|---|---|---|---|
| **n8n (self-hosted)** | rsgmedia, https://n8n.rsgmedia.info, 2.46.6 pinned (`../hosting/hetzner/RUNBOOK.md`) | in the server's €20 | 3 active workflows |
| **n8n Cloud** (Starter) | n8n.io | €24/mo + VAT | Runs only the bank import. Cancel once that runs on the self-hosted n8n (ROADMAP "Bank import") |
| **Make** | org RSGMEDIA, eu1.make.com, Core (10k operations/mo, 15-min minimum interval) | ~$10/mo | 5 active scenarios; accounting scenarios inactive, run by hand for taxes |

## n8n self-hosted — active workflows
- **Life — GCal to Airtable Events Sync** (personal, hourly; last run 11 Nov).
- **RSG — Clear Old Review Images (Airtable Nodes)** (scheduled; last run 1 Nov).
- **RSG — Error Notifier** (error workflow).
- Inactive (33 on 12 Nov): "RSG — Full Import" (the bank import copy, never run), outgoing-invoice v1–v4 + "(Final)" + "[OLD]", invoices stage 1 ×2, 2 inactive copies of "Clear Old Review Images", "My workflow", archive-marketing-emails ×2, auto-assign clients, save review pictures to Dropbox, LifeOS ×2, 15 Stargrid workflows (posting/QA, off).

## Make — scenarios
Active (every 3 h unless noted):
- **Insert Road Shoe** (637210), **Insert Trail Shoe** (718279), **Insert Training Shoe** (1664888): Airtable → WordPress drafts (51 raw meta keys; field references appear when the draft is saved in wp-admin). Plan: merge into one, write through the `acf` REST block.
- **New Review → Save Pictures on DropBox** (633722).
- **New Comparison to Facebook and Twitter** (2191850, every 6 h, via Buffer).

Accounting, inactive, **run by hand for taxes** (keep Make until ported): "RSGMEDIA - Airtable to Outgoing Invoices" (3203531), "CREATE INCOMING FROM AIRTABLE" (3211518), "Create Outgoing Invoices" (2976810); older bank-file scenarios (ABN/ICS/PayPal from Airtable/Dropbox) superseded by n8n. 25 other inactive scenarios (social/Buffer, GPT comments, old WordPress integrations, tests); 9 of them read the removed `custom_fields` REST block (see below).

## WordPress API for automations
- Every field of every post under `acf` on `/wp-json/wp/v2/<type>/<id>` (reviews road/trail/training/gps, legacy review/gps, comparisons, guides, top lists, posts, pages, users, shoe families). Stored values by default; `?acf_format=standard` for formatted ones; `<name>_source` = label/type/formatted value.
- Writing: `{"acf": {"vote": 8, "pros": [{"pro_line": "…"}]}}` in a create/update call stores values with their field references (no wp-admin save needed).
- Drafts need the API login (Make connects through the `integromat-connector` plugin). The old `custom_fields` block is gone: a revived old scenario must read `acf.x` instead of `custom_fields.x`.
- Price data for a review is DFS-owned (`_rsg_prices`, written only by the DFS): don't touch it from automations.

## Bank import (the reason n8n Cloud still exists)
Statement files (ABN AMRO, ICS, PayPal) are downloaded by Ruggero, uploaded to the accounting Airtable, and n8n Cloud parses them into Airtable. No bank logins anywhere. The self-hosted copy ("RSG — Full Import", id `Y68pvr7eFguKWXzo`) failed when migrated. Next: export of the working Cloud flow + what failed → diff, fix, run both on the same month, compare rows → cancel n8n Cloud after a month (ROADMAP "Bank import").

## Rules in force (date = line in `../DECISIONS.md`)
- One automation platform in the end: self-hosted n8n. Cancel n8n Cloud after the bank import runs on Hetzner; Make stays until accounting + shoe inserts are ported (not urgent) (5 Nov).
- No bank logins on any server; the bank import stays file-based (11 Nov).
- Every post field available through the REST API (`acf`) for automations (11 Nov).
- Any change to fields/theme must keep the Make inserts working (or adjust them).

## Open
1. Bank import move → cancel n8n Cloud (ROADMAP "Bank import").
2. Clean the self-hosted n8n of duplicates/old versions (invoice v1–v4, duplicate "Clear Old Review Images", "My workflow") once the real ones are known.
3. Merge the 3 insert scenarios into one (port to n8n or keep in Make), write via `acf`.
4. Port the accounting scenarios, then cancel Make.
