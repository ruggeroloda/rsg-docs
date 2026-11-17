# Make.com → n8n Migration

**Started:** 2026-03-15
**Status:** In progress — architecture decided, building not started
**n8n instance:** https://n8n.rsgmedia.info
**Make org:** RSGMEDIA (org 298480, team 183873)

---

## Overview

Migrating all active Make.com scenarios to self-hosted n8n on Hetzner. Make.com is being divested.

**5 active Make scenarios to migrate:**

| # | Scenario | Make ID | Priority | Status |
|---|----------|---------|----------|--------|
| 1 | Insert Road Shoe | 637210 | 🔴 Critical | Not started |
| 2 | Insert Trail Shoe | 718279 | 🔴 Critical | Not started |
| 3 | Insert Training Shoe | 1664888 | 🔴 Critical | Not started |
| 4 | New Comparison to Facebook and Twitter | 2191850 | Medium | Not started |
| 5 | New Review → Save Pictures on DropBox | 633722 | Medium | Not started |

**Already in n8n (do not migrate):**
- RSG — Clear Old Review Images (Airtable Nodes) — active ✅
- GCal → Airtable Events Sync — active ✅

---

## Insert Shoe Flows — Deep Analysis

### Common Structure (Road / Trail / Training are 95% identical)

All three run on a **3-hour schedule** and follow the same 11-step flow:

```
1. Airtable search      → fetch up to 5 records from type-specific view
2. Loop (BasicFeeder)   → iterate over each record
3. WP searchUsers       → look up reviewer name → get author ID
4. WP createPost        → create CPT with 40–58 custom fields (DRAFT)
5. WP getPost           → read back post to get generated slug
6. Airtable update      → write WP post ID + URL back to record
7. Loop (BasicFeeder)   → iterate over "Edited Images" (Airtable attachments)
8. HTTP GET             → download image binary from URL
9. WP createMediaItem   → upload image, attach to post
10. WP updateMediaItem  → fix image title
11. Airtable update     → set status field → "In Wordpress"
```

### Differences Between the 3 Flows

| | Road | Trail | Training |
|---|---|---|---|
| Make scenario ID | 637210 | 718279 | 1664888 |
| WP custom post type | `reviews_road` | `reviews_trail` | `reviews_training` |
| WP content placeholder | `ruggero_road_content` | `ruggero_trail_content` | `ruggero_training_content` |
| `shoes_type` taxonomy term ID | `781` | `235` | `746` |
| Extra taxonomies | `usage` | `lugs`, `rockplate`, `water_resistance`, `terrain`, `usage_trail` | none extra |
| Meta field count | ~50 | 58 | 56 |
| Airtable view | `viwV6Hi239mKziRa9` | `viwX08J62cKrTcuqD` | `viwVjDbH2BZelkKc4` |
| Airtable base | `appLoQNoAydB65Vop` | `appLoQNoAydB65Vop` | `appLoQNoAydB65Vop` |
| Airtable table | `Shoe Reviews` | `Shoe Reviews` | `Shoe Reviews` |

### Airtable Write-Back (step 6 — Road example)
- Field `fldQCzHQLrN67sId7` ← WP post ID (`{{59.id}}`)
- Field `fldkZWEFdJERBOyKZ` ← Post URL (`https://www.runningshoesguru.com/reviews/road/{{slug}}/`)

### Airtable Final Status Update (step 11)
- Field `fldLD67rLdsVTveC3` ← `"In Wordpress"`

---

## Critical Technical Finding: WordPress Plugin

The flows use custom post types and **40–58 custom fields** managed by a custom WordPress plugin on runningshoesguru.com.

### What works via REST API
- All 3 CPTs are exposed: `/wp-json/wp/v2/reviews_road`, `/wp-json/wp/v2/reviews_trail`, `/wp-json/wp/v2/reviews_training` ✅
- Custom fields are accessible as **top-level properties** in REST responses (not nested under `meta`)
- Fields are registered via `register_rest_field()`, NOT `register_meta()`

### Why n8n's native WordPress node won't work
1. It only supports `post` and `page` types — not custom post types
2. Its meta handling targets `register_meta()` fields — these are `register_rest_field()` fields
3. Custom fields would be silently ignored

### Solution: Use HTTP Request nodes throughout
POST directly to `/wp-json/wp/v2/reviews_road` (etc.) with a JSON body where all custom fields are top-level keys. Full control, no abstraction layer issues.

---

## Road Shoe — Full Meta Fields Reference

These are the fields sent in the `createPost` call. Trail and Training share most of these plus type-specific extras.

```
model, cons, cons_0_cons_line, cons_1_cons_line, cons_2_cons_line,
pros, pros_0_pro_line, pros_1_pro_line, pros_2_pro_line, pros_3_pro_line, pros_4_pro_line,
vote, weight_oz, heel_drop_mm, heel_mm, toe_mm,
purchased_from_name, purchased_from_url,
comfort, build_quality, upper_rate, sole_unit_rate,
landing, transition, toe_off, traction, durability, value_price,
who_is_it_for, verdict, who_should_buy_it, who_should_not_buy_it_,
introduction, first_impressions, sole_unit, upper, conclusions,
rrp_us, sizing, cushioning_type, cushioning_amount,
heel_fit, midfoot_fit, toebox_fit, flexibility, stability,
carbon_plate, brand, title, description, keywords
```

Trail adds: `protection`, `durability_section`, `responsiveness_speed`, `comfort_and_fit`, `lugs`, `rockplate`, `water_resistance`

Training replaces running-specific fields with: `good_for_running`, `good_for_hiit__crossfit`, `good_for_weight_lifting`, `good_for_dance_classes`, `good_for_general_gym_use`, `good_for_other_types_of_training`

---

## Architecture Decisions

### Trigger strategy: Hybrid (webhook + scheduled reconciliation)
- **Primary:** Airtable Automation → POST to n8n webhook when record status changes to "ready to publish" → instant processing
- **Secondary:** Scheduled cron every 3h to catch any records that slipped through (webhook delivery failure etc.)
- **Why:** Current 3h polling = up to 3h publication delay and wasted API calls on empty runs

### One parameterised workflow (not three)
Road, Trail, and Training are 95% identical. Build one workflow with a shoe-type config lookup at the top (CPT endpoint, Airtable view ID, taxonomy term ID, extra fields). Easier to maintain, single place to fix bugs.

### HTTP Request nodes for all WordPress interaction
The native n8n WordPress node only supports `post`/`page` and won't send custom fields correctly (they're `register_rest_field()` not `register_meta()`). All WP calls are plain HTTP Requests to the REST API.

### AI enrichment as a toggleable gate
Add an AI enrichment step (Claude or GPT) between the Airtable fetch and the WP POST. Controlled by a boolean flag. When enabled: fix grammar/punctuation, add hyperlinks to brand/model mentions. When disabled: pass through unchanged. Non-blocking — if it fails, continue without enrichment.

### Image pipeline: Cloudinary + human approval + Luminar Neo fallback
Replacing Imaginary with Cloudinary (free tier, ~2 credits/month for this volume). Full pipeline:

**Step 1 — Cloudinary processing (automatic)**
- Upload original from Airtable to Cloudinary
- Generate 3 transformation versions per image:
  - Horizontal 3:2 @ 1200×800 (`c_fill,g_auto:subject,ar_3:2,w_1200/e_improve/e_vibrance:30/e_sharpen:50`)
  - Vertical 4:5 @ 1080×1350 (`c_fill,g_auto:subject,ar_4:5,w_1080/e_improve/e_vibrance:30/e_sharpen:50`)
  - Square 1:1 @ 1080×1080 (`c_fill,g_auto:subject,ar_1:1,w_1080/e_improve/e_vibrance:30/e_sharpen:50`)
- Write all 3 Cloudinary preview URLs back to Airtable fields (`fld_img_preview_h`, `fld_img_preview_v`, `fld_img_preview_sq`)
- Set `Image Status` = "Pending Review"

**Step 2 — Human approval (Airtable gallery view)**
- Ruggero reviews in a dedicated Airtable gallery view showing original vs processed versions
- Sets `Image Status` = "Approved" or "Manual"
- "Manual" = process in Luminar Neo, re-export, re-upload attachment to Airtable, then set to Approved

**Step 3 — Publish to WordPress (triggered by approval)**
- Second n8n workflow watches for `Image Status = Approved`
- Downloads transformed images from Cloudinary URLs
- Uploads all 3 versions to WP media library, attached to the post
- Meaningful alt text built from shoe metadata (not generic "Picture of X")

**Luminar Neo fallback:** desktop editing for images Cloudinary handles poorly. Ruggero exports from Luminar Neo → re-uploads to Airtable → marks Approved → triggers Step 3 as normal.

**Cloudinary transformation to use (settled after testing):** TBD — finalise after visual testing session.
**Credentials:** Cloud name `dml4dfz6g`, in n8n as `RSG Cloudinary` (to be added).

### Idempotency: check WP Post ID before creating
At the start of each record's processing, check if `fldQCzHQLrN67sId7` (WP Post ID field in Airtable) is already set. If yes, skip. Prevents duplicate posts on re-run.

### Error handling: per-record, non-blocking
Individual record failures get logged to an Airtable `_error_log` field and processing continues to the next record. If >50% of a batch fails, the workflow stops and sends a Slack/email alert. Uncaught exceptions trigger a workflow-level error notification.

---

## Build Plan

### Prerequisites (must do before building)
- [ ] **Add WordPress Application Password to n8n** — Make uses WP connection ID 695541. Need to generate an App Password in WP Admin → Users → Profile → Application Passwords, then add as HTTP Header Auth credential in n8n (`Authorization: Basic base64(user:app_password)`)
- [ ] **Confirm author ID for Ruggero** — Make does a `searchUsers` call to resolve reviewer name → WP user ID. In n8n we can do the same via HTTP Request to `/wp-json/wp/v2/users?search={name}` or hardcode known author IDs if stable.

### Build phases
1. **Phase 1 — Foundation:** Build Road Shoe workflow, test end-to-end (text fields only, no images yet)
2. **Phase 2 — Image pipeline:** Cloudinary processing → Airtable preview URLs → approval view → WP upload (all 3 versions)
3. **Phase 3 — Parameterise:** Add shoe-type config, make one workflow handle all 3 types
4. **Phase 4 — Smart triggers:** Set up Airtable automation webhook + reconciliation cron
5. **Phase 5 — AI enrichment:** Add optional grammar/hyperlinking gate (Claude API)
6. **Phase 6 — Error handling:** Per-record error trapping, Airtable error log, Slack alerts

### n8n Node Plan (final architecture)
```
Webhook Trigger (Airtable automation)  OR  Schedule Trigger (3h reconciliation)
  → Code: load shoe-type config (CPT, view ID, taxonomy ID)
  → HTTP: Airtable search (filtered by view, status = "ready")
  → Split in Batches (up to 5)
    → IF: WP Post ID already set? → skip (idempotency)
    → [Optional] Code: AI enrichment gate
      → HTTP: Claude/GPT API (grammar + hyperlinks)
    → HTTP: WP GET /users?search={reviewer}  (resolve author ID)
    → HTTP: WP POST /wp-json/wp/v2/{cpt}  (JSON body, all custom fields)
    → HTTP: WP GET /wp-json/wp/v2/{cpt}/{id}  (read back slug)
    → HTTP: Airtable PATCH  (write WP post ID + URL)
    → Split in Batches (loop over Edited Images)
      → HTTP: GET image binary from Airtable
      → HTTP: Imaginary resize+compress
      → HTTP: WP POST /wp-json/wp/v2/media  (Content-Disposition header)
      → HTTP: WP PATCH /wp-json/wp/v2/media/{id}  (attach to post, set alt text)
    → HTTP: Airtable PATCH  (set status = "In Wordpress")
  → Error trap: log failures → notify on threshold
```

### Credentials needed in n8n
| Credential | Type | Status |
|---|---|---|
| Airtable PAT | airtableTokenApi | ✅ Already in n8n (id: `4oQJXi2Yfbu7VWsD`) |
| WordPress App Password | httpBasicAuth | ✅ Added (id: `2rc23hiXxgr0E9qT`, user: ruggero) |
| Cloudinary | httpBasicAuth or Code | ❌ To be added (cloud: `dml4dfz6g`) |

---

## Make Scenarios — 30 Inactive (archived, do not migrate)

These are all inactive. Most are old experiments, accounting flows, and social media tests. Kept here for reference in case anything needs to be salvaged.

`ABN from AirTable`, `ACCOUNTING - ABNAMRO`, `ACCOUNTING - ICS`, `ACCOUNTING - PayPal`, `Comparisons update`, `CREATE INCOMING FROM AIRTABLE`, `Create Outgoing Invoices`, `Create_Overview`, `Debug Wordpress Custom Field Array`, `Giulio - Insert Road Shoe`, `GPT Comments`, `GPT NEW COMMENTS`, `Integration WordPress` (×4), `Integration WordPress HTTP`, `New ABN from AirTable`, `New Content Post to Buffer`, `New Road Review to Buffer`, `New Road Review to SOCIAL`, `New Road Review to Social Media`, `New Shoe in Airtable - upload picture to wordpress`, `New Trail Review to Buffer`, `New Training Review to Buffer`, `PayPal from AirTable`, `Populate_Comparison_Table`, `RSGMEDIA - Airtable to Outgoing Invoices`, `RSS to Airtable to Buffer (with AI) - WIP`, `Updated Comparison to Facebook and Twitter`

---

## Notes & Decisions

- **2026-03-15**: Initial analysis complete. Decision: use HTTP Request nodes (not WP node) for all WordPress interactions.
- The `content` field value (`ruggero_road_content` etc.) is a static plugin template name — pass as-is in POST body.
- Images loop: Airtable `Edited Images` field returns attachment objects with `.url` and `.filename` properties.
- Make ran up to 5 records per execution to stay within operation limits. n8n has no such constraint but keep batching for safety.
