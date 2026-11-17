# Automations

_Last updated 2026-11-05. Status and next steps: `HANDOVER-2026-11-05.md`. The Hetzner server itself (backups, patching, security): `../hosting/hetzner/`._

- **Make.com:** 5 RSG scenarios (Insert Road/Trail/Training Shoe Airtable → WordPress; comparison → social; review pictures → Dropbox). Migration plan: `make-migration/README.md` (March 2026, not built).
- **n8n** (self-hosted on Hetzner, no extra cost): `n8n-clear-old-review-images.json` + other workflows.
- **n8n Cloud (paid, Starter €24/mo):** still runs the bank-statement import (ABN AMRO / ICS / PayPal files → accounting Airtable). The Hetzner copy failed when migrated.
- **Make's inactive accounting scenarios are still run by hand** for taxes, so Make can't be cancelled until they are ported.
- Goal: one automation platform, self-hosted n8n on Hetzner. Order: harden Hetzner → move the bank import → cancel n8n Cloud → port Make (accounting, shoe inserts) → cancel Make. Status: `HANDOVER-2026-11-05.md`.

## WordPress API for automations (11 Nov 2026)
- **Every field of every post is in the normal WordPress REST API** under `acf`: `/wp-json/wp/v2/<type>/<id>` for reviews (road/trail/training/gps, legacy review/gps), comparisons, guides, top lists, posts, pages; also users and shoe families. Default values are the stored ones (image = attachment ID, linked post = ID); add `?acf_format=standard` for formatted values (image objects with URLs, terms). Each field also has `<name>_source` (label, type, formatted value; SCF's own output).
- **Writing:** send `{"acf": {"vote": 8, "pros": [{"pro_line": "…"}]}}` in a create/update call; SCF saves the values **with** their field references, so the post is complete without opening it in wp-admin. Use this when the 3 Make insert scenarios are merged/ported (today they write 51 raw meta keys; the references appear only when the draft is saved in wp-admin).
- Published posts are public through the API (as before); drafts need the API login.
- The old `custom_fields` block (from a modified `acf-to-rest-api`, removed 11 Nov) is gone. Active scenarios never used it; **9 inactive Make scenarios** did (5 social/Buffer ones, "GPT NEW COMMENTS", "Populate_Comparison_Table", the two "Integration WordPress" ones): to revive one, change `custom_fields.x` to `acf.x` (`brand`, `professional_image` etc. need `acf_format=standard` or a lookup by ID).
