# Comparison Schema Pass — DEPLOY LOG (2026-06-21)

**Status: ✅ DEPLOYED TO LIVE.** Frontend-invisible JSON-LD now renders on all
`comparison` pages (`single-comparison.php`). Mirrors the 2026-06-21 review schema pass.

## What shipped
Two Product nodes (the two compared shoes) + a BreadcrumbList per comparison page.
Google has no dedicated "comparison" rich result, so each shoe is emitted as an
independent `Product` (eligible for product/review/merchant rich results).

**Files changed (2, theme-only — DB/uploads untouched):**
- `includes/rsg-schema.php` — appended (additive, all `function_exists`-guarded):
  - `rsg_comparison_user_rating($pid)` — user rating normalised to a 0–5 star scale
    using the SAME math `single-comparison.php` uses (avg(rate) × multiplier; ×10 for
    `reviews_road`, ×20 otherwise → road stores rate on 0–10, others on 0–5).
  - `rsg_build_product_node($pid)` — schema.org Product for any reviewed-shoe post ID
    (brand via ACF `brand` then `brands` taxonomy; name = brand + shoe_name/model/title;
    image, category [GPS→"GPS Running Watch" else "Running Shoes"], sku/mpn = post ID,
    Weight/Heel-drop `additionalProperty`, best-effort `offers` via `rsg_schema_offer()`,
    `aggregateRating` only when >1 approved user rating).
  - `rsg_build_comparison_schema()` — returns `[product_1, product_2, breadcrumb]`.
  - `rsg_emit_comparison_schema()` — guarded on `is_singular('comparison')`, echoes one
    `<script type="application/ld+json">` in `<head>`.
- `header.php` — one line added next to the review emit call:
  `if ( function_exists('rsg_emit_comparison_schema') ) { rsg_emit_comparison_schema(); }`

## Design notes / decisions
- **Review builder left untouched.** Standalone `rsg_build_product_node()` rather than
  refactoring the review Product section (brief permitted this; safer — zero risk to the
  already-live review output).
- **aggregateRating bestRating = 5** (normalised), emitted only when count > 1 (mirrors the
  review pass's >1 guard and Google's single-self-rating guidance).
- **No embedded `review` node** on comparison Products (the editorial Review lives on the
  dedicated single-review page's Product — avoids duplicate review markup for one product
  across two URLs). This is the source of the optional "Missing field 'review'" warning.

## Verification (all passed)
- `ea-php83 -l` AND `ea-php74 -l` clean on both files (staging + live).
- 4 staging comparison URLs: valid JSON, `@types = [Product, Product, BreadcrumbList]`,
  correct brand/category/weight/heel-drop/offers/aggregateRating.
- Frontend provably unchanged: visible body-text similarity **1.000000**, **0 changed spans**
  (emit ON vs OFF render, normalising cache-buster/affiliate tokens).
- Google Rich Results Test (live): **5 valid items, 0 errors** — 2 Product snippets,
  1 Merchant listing, 1 Breadcrumbs, 1 Review snippet. Non-critical warnings only
  (optional `review` / `aggregateRating` on the lower-data shoe).
- Live re-validated post-deploy via fresh cache-busted curl: 1 ld+json block, correct types.

## Deploy details
- Method: copy 2 files staging → live, `cmp` byte-verify (both IDENTICAL), `ea-php83 -l` clean.
- **Backup:** `~/rsg_backups_predeploy_20260621_182514/` — live `header.php` + `includes/rsg-schema.php`
  pre-change, plus full theme tar `rsg_2022_theme_snapshot.tar.gz`.
- Caches: WP-Rocket file cache cleared + `wp cache flush` (Success).
- **Rollback:** restore the 2 backed-up files. New functions are self-contained, no DB deps.

## Follow-ups (user, manual)
1. **Cloudflare "Purge Everything"** in the Rocket.net panel (edge `s-maxage` ~30d) — until
   purged, old cached comparison pages won't show the new schema.
2. (Optional) re-run Rich Results Test on the live URL after the purge to confirm.
3. ⚠️ Still open from the PHP-8 deploy: rotate the SSH key (shared in plaintext); confirm
   wp-admin login (`wp-login.php` 403s external IPs).
