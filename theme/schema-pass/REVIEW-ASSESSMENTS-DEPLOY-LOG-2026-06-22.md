# Deploy Log — Review tester-assessment properties (Size/Fit · Cushioning & ride · Usage)
**Date:** 2026-06-22 · **Env:** Rocket.net (`131.153.143.194`, user `jfx9wij`) · **Theme:** `rsg_2022`
**Status:** ✅ Deployed to live. ⚠️ One manual step outstanding: **Cloudflare "Purge Everything"** (see §7).

---

## 1. What shipped
Additive, **frontend-invisible** JSON-LD. Adds the editorial **tester judgments** — the
on-page "Size/Fit", "Cushioning & ride" and "Usage" panels — into the structured data so
search bots and LLMs read them as **RSG's expert opinion**, not manufacturer spec.

The values are placed in an `additionalProperty` array **inside the `Review` node** (which
already carries `author` = the reviewer and `publisher` = RSG). That nesting is the signal
that these are assessments, not objective product attributes. **Text values only** (the
controlled-vocabulary label as stored, `ucfirst()`'d) — no numeric scale encoded, by decision.

Each entry is a `PropertyValue` with a `propertyID` grouping it back to its on-page section:
`size-fit`, `cushioning-ride`, or `usage`.

### Per-type coverage (fields differ by post type — verified, not assumed)
| Post type | Fields emitted | Notes |
|---|---|---|
| `reviews_road` | sizing, heel_fit, midfoot_fit, toebox_fit, cushioning_type, cushioning_amount, stability, flexibility, usage | The clean baseline. 9 props on the sample. |
| `reviews_training` | same nine field names as road | Some fields often blank (e.g. `cushioning_type`, `usage`) → skipped. 7 props on the sample. |
| `reviews_trail` | the eight fit/ride fields **+ `lugs`**, and usage via **`usage_these_shoes_for`** **+ `good_for_running`** | Trail uses a *different* usage field name and a *different* ACF storage shape (array of `{value,label}` objects). 20 props on the sample. |
| `reviews_gps` | none | Watches don't populate these fields → no block emitted, by design. 0 props. |

### Field storage quirks handled
- **`sizing`** is a radio that stores a numeric key (`-2`…`+2`); the builder resolves it to the
  choice label (e.g. `1` → "Buy half size bigger"). The other radios store the label directly.
- **Usage** fields are checkboxes. Road/training `usage` returns plain label strings; trail's
  `usage_these_shoes_for` / `good_for_running` return `{value,label}` object arrays. A single
  normalizer (`rsg_assessment_usage_values()`) handles both and emits one `PropertyValue` per
  selected value.

---

## 2. Files changed (1)
| File | Change | Diff |
|---|---|---|
| `includes/rsg-schema.php` | 4 new functions + 1 call line inside `rsg_build_review_schema()` | +110 / −0 |

New functions (all `function_exists`-guarded, same pattern as the rest of the file):
`rsg_review_assessment_map()`, `rsg_assessment_label()`, `rsg_assessment_usage_values()`,
`rsg_build_review_assessments()`. The call, inserted right after the `reviewRating` block and
before the Product node is assembled:

```php
$assessments = rsg_build_review_assessments( $pid );
if ( ! empty( $assessments ) ) { $review['additionalProperty'] = $assessments; }
```

No changes to `header.php`, `functions.php`, or any template. The emitter path
(`rsg_emit_review_schema()` in `header.php`) is unchanged — this only enriches the Review
payload it already prints.

---

## 3. Verification (pre-deploy, on staging)
- **PHP lint:** `ea-php83 -l` AND `ea-php74 -l` clean.
- **Builder logic proven against live data** via a throwaway `wp eval-file` harness on one real
  post of each type → road 9 / training 7 / trail 20 / gps 0 props, all quirks parsed correctly.
- **Rendered staging pages** (cache-busted) parse valid JSON-LD with the new
  `Review.additionalProperty`: road 9, trail 20, training 7, gps 0.
- **Schema validated** in the schema.org validator (pasted JSON-LD) → passes. Expect a
  non-blocking note that `additionalProperty` on `Review` is outside Google's core Review spec;
  harmless — it does not affect the star-rating rich result (driven by `reviewRating`), and
  these fields are for AI/LLM legibility, not a Google rich-result type.
- **Frontend unchanged by construction:** the change only adds to the `<head>` JSON-LD; no page
  body markup touched.

## 4. Post-deploy (live) confirmation
- Live `includes/rsg-schema.php` diffed against the pre-deploy live original → **only** the 4
  functions + 1 call line differ (surgical; no live-only divergence clobbered).
- `ea-php83 -l` + `ea-php74 -l` clean on live.
- Cache-busted fetch of production URLs, all JSON-LD parses valid:
  - road `…/reviews/road/asics-gel-cumulus-28-review/` → 9 props
  - trail `…/reviews/trail/brooks-cascadia-19-review/` → 20 props
  - training `…/reviews/training/reebok-nano-x4-review/` → 7 props
  - gps `…/reviews/gps/garmin-forerunner-165/` → 0 (no block)

## 5. Caches
- Verified at origin with a `?cb=` cache-buster. Edge/page cache not force-purged yet — see §7.

## 6. Backups
- **Server (live, pre-deploy):** `~/public_html/wp-content/themes/rsg_2022/includes/rsg-schema.php.bak-20260622-142155`
- **Server (staging, pre-edit):** `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022/includes/rsg-schema.php.bak-20260622-055641`
- **Project folder:**
  - `schema-pass/predeploy-backups-20260622/includes/rsg-schema.php` — pristine **live original** (pre-deploy).
  - `schema-pass/deployed-20260622/includes/rsg-schema.php` — the **deployed** version.
  - `schema-pass/rsg-assessments-proposed.php` — the standalone helper block (reference copy).

## 7. Outstanding manual step
**Cloudflare → "Purge Everything"** in the Rocket.net panel. Origin renders correctly (verified
with cache-bust), but edge-cached review URLs will serve old HTML until the CDN is purged.

## 8. Rollback
Single file, no DB changes. Restore the live original and purge caches:
```bash
S=~/public_html/wp-content/themes/rsg_2022/includes/rsg-schema.php
cp -p "$S.bak-20260622-142155" "$S"
ea-php83 -l "$S"   # sanity
```
(Or copy `schema-pass/predeploy-backups-20260622/includes/rsg-schema.php` back up.) The addition
is self-contained and guarded; removing it leaves yesterday's schema exactly as it was.

## 9. Deferred / next opportunities
- **Front-end redesign (NOT shipped).** Mockups exist (desktop + mobile): an "Our tester's
  assessment" header to frame the panel as opinion, spectrum scales for the ordinal values, and
  "Best for" chips for usage. Keeps the page palette (`#1C2226` panel, `#353F46` dividers,
  `#2189E1` accent, proxima-nova). One CSS media query for the mobile restack. Awaiting go-ahead.
- **Trail `good_for_running`** contributes ~9 of trail's 20 props (every terrain checked). Valid,
  but if it reads heavy, drop it from `rsg_review_assessment_map()` and keep `usage_these_shoes_for`.
- **Comparison posts** could reuse these per-shoe assessments + the expert score + who-should-buy
  as an at-a-glance matrix. The comparison template already pulls several of these fields.
