# Deploy Log — Guides + Reviews-hub schema / structure / canonical pass
**Date:** 2026-06-21 · **Env:** Rocket.net (`131.153.143.194`, user `jfx9wij`) · **Theme:** `rsg_2022`
**Status:** ✅ Deployed to live. ⚠️ One manual step outstanding: **Cloudflare "Purge Everything"** (see §7).

---

## 1. What shipped (two passes, deployed together)

### Pass A — Guide pages (`post_type=guide`, 15 posts, `single-guide.php`)
Additive, frontend-invisible **JSON-LD** + a pixel-identical **heading re-level**.
- **Schema** (`includes/rsg-schema.php`): new `rsg_build_guide_schema()` + `rsg_emit_guide_schema()`. Emits, per guide, a connected graph:
  - **Article** — headline, author (Person), `datePublished`/`dateModified`, **publisher Organization** carrying RSG's independence/USP signals (description "independent, unbiased, expert reviews… since 2009", `slogan`, `knowsAbout`, `foundingDate`).
  - **ItemList** (`mainEntity`, `ItemListOrderDescending`) of the picks — each a **Product** via the reused `rsg_build_product_node()` (brand, image, specs, offers, aggregateRating) **plus an editorial `Review`** (the shoe's RSG score + reviewer + verdict) so every pick is rich-result valid.
  - **BreadcrumbList** (Home → Guides → guide) and **FAQPage** (from the guide's `faq` ACF repeater).
  - Coverage: 13/15 guides get full schema; `best-gps-running-watches` (no `review_sections_2023` rows) and `best-mizuno-running-shoes` (winner not a published review) emit nothing by graceful design.
- **Headings** (`single-guide.php`): 9 swaps, all pixel-identical via the theme's `.hN` utility classes (which render identically to the `<hN>` tags; the guide template uses no `.entry-content`/`.easy-reading-2019` scoped margin rules, verified):
  - `<h6>Why you can trust us</h6>` → `<h2 class="h6">` (E-E-A-T trust block lifted out of the lowest level)
  - 8× per-pick sub-labels (In short / Pros / Cons / Who should buy it / Who should not buy it / Main Specs / Rating breakdown / Team review) `<h6>` → `<h4 class="h6">` (proper nesting under the `<h3>` shoe name)

### Pass B — Reviews hub + faceted brand/family pages (`reviews_search.php`, Page 589)
- **Canonical change** (`functions.php`, `RSG_rel_canonical()`): curated **family pages** `/reviews/{brand}/{family}/` are now **self-canonical (indexable)** instead of rolling up to `/reviews/{brand}/all/`. This captures the previously-forfeited "{brand} {family}" query intent (e.g. **Nike Pegasus**, 23 reviews) across the **27 `shoes_family` hubs**. The `shoes_family` taxonomy is the editorial gate (only important model lines get a family), so no thin facets are opened. Brand-only, type-only, brand+type, and base behaviour are **unchanged**.
- **Schema** (`includes/rsg-schema.php`): new `rsg_reviews_facet()` + `rsg_build_reviews_schema()` + `rsg_emit_reviews_schema()`. On **self-canonical views only** (mirrors `RSG_rel_canonical()`), emits a **CollectionPage → ItemList** of the listed reviews (lightweight ListItem: position/url/name) + a **BreadcrumbList** with facet-appropriate depth (Home → Reviews → Brand → Family). No schema on consolidated/duplicate views (verified absent on `/reviews/nike/trail/`).
- **Template** (`reviews_search.php`): one output-free line exposing the results query to the builder (`$GLOBALS['rsg_reviews_query']`). Frontend-invisible.
- **`header.php`**: added `rsg_emit_guide_schema()` and `rsg_emit_reviews_schema()` calls next to the existing review + comparison emitters.

---

## 2. Files changed (5)
| File | Change | Diff |
|---|---|---|
| `includes/rsg-schema.php` | guide + reviews builders/emitters (+ shared `rsg_publisher_org`, `rsg_build_editorial_review`) | +372 / −0 |
| `header.php` | 2 emit calls | +2 / −0 |
| `single-guide.php` | 9 heading re-levels | +9 / −9 |
| `functions.php` | `RSG_rel_canonical()` family-page branch | +13 / −3 |
| `reviews_search.php` | expose results query (invisible) | +4 / −0 |

---

## 3. Verification (pre-deploy, on staging)
- **PHP lint:** `ea-php83 -l` AND `ea-php74 -l` clean on all 5 files.
- **Frontend provably unchanged:** visible-text similarity **1.0000, 0 changed spans** vs live — Hoka guide and `/reviews/nike/pegasus/`. (Only `<head>` JSON-LD + the canonical line differ.)
- **Schema valid:** JSON parses; guides validated in Google Rich Results Test → 6/6 Products valid after adding the editorial Review (fixed the one Stinson-7 "needs offers/review/aggregateRating" critical).
- **Canonical correctness (per facet):** base, `/{brand}/all/`, `/all/{type}/`, `/{brand}/{family}/` → self; `/{brand}/{type}/` → rolls up to `/{brand}/all/`.
- **Schema gating:** present on the 4 self-canonical views, **absent** on non-canonical `/nike/trail/`.

## 4. Post-deploy (live) confirmation
- All 5 files copied staging→live, `cmp` **byte-identical**, `ea-php83`+`ea-php74` clean on live.
- Live `…/guide/best-hoka-running-shoes/` → Article + ItemList + 6 Product + BreadcrumbList + FAQPage. `cf-cache-status: MISS`.
- Live `…/reviews/nike/pegasus/` → canonical = **self** (now indexable); CollectionPage + ItemList + BreadcrumbList.

## 5. Caches purged
- WP-Rocket file cache removed (`wp-content/cache/wp-rocket/*`) + `wp cache flush` (object cache). ✅

## 6. Backups
- **Server:** `~/rsg_backups_predeploy_20260621-213334/` — live originals of all 5 files **+ `rsg_2022_theme_snapshot.tgz`** (full live-theme tarball).
- **Project folder:**
  - `schema-pass/predeploy-backups-20260621/` — pristine **live originals** (pre-deploy).
  - `schema-pass/deployed-20260621/` — the **deployed** versions.

## 7. Outstanding manual step
**Cloudflare → "Purge Everything"** in the Rocket.net panel. The origin renders correctly (verified with cache-bust), but edge-cached URLs will serve old HTML until the CDN is purged.

## 8. Rollback
Restore the 5 files from `~/rsg_backups_predeploy_20260621-213334/` (or untar the snapshot), then purge caches. All additions are self-contained and feature-guarded (`is_singular('guide')`, `is_page_template('reviews_search.php')`); the canonical change is one isolated branch in `RSG_rel_canonical()`. No DB changes.

## 9. Deferred / not done
- **Reviews-card heading cleanup** (dedupe the responsive-twin `<h4>` shoe names; de-head the Weight/Drop/Category `<h6>` labels). The card is rendered by the **shared** `print_review_block_2020()` in `includes/widgets/widgets.php`, also used by guides/best/top — so it needs cross-page visual re-verification before shipping. Method is known (swap to `div.h4` / `h3.h4` / `div.h6` with matching margin utilities, pixel-identical).
- Data nits surfaced (not blocking): doubled brand in some model names (e.g. "Hoka One One HOKA Gaviota 5"); two `robots` meta tags + obsolete `noodp,noydir` on reviews pages.

## 10. Follow-on opportunity (now unlocked)
The 27 family hubs are newly indexable. To maximise them: a sentence of unique intro copy per family hub, and monitor Search Console for "{brand} {family}" impressions over the coming weeks.
