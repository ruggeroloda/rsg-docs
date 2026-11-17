# NEXT SESSION BRIEF — Comparison schema pass (then guides)

**Goal:** add valid, **frontend-invisible** JSON-LD structured data to **comparison** pages
(`single-comparison.php`), exactly like the 2026-06-21 review schema pass did for road/trail/gps/training.
Then repeat for **guide** pages (separate design — see end). Looks/markup on the page must not change.

> This is a SEO/structured-data pass only. PHP 8.3 is already live and stable (see
> `theme/logs/DEPLOY-LOG-2026-06-21.md`); comparison + guide templates already passed the 8.3 fatal-hardening,
> so this is purely additive schema work.

---

## How to connect (do first)
```bash
# SSH key = ../.ssh-rocket-key (gitignored). In the Linux sandbox copy it to outputs and chmod:
cp "<rsg-redesign>/.ssh-rocket-key" /sessions/<session>/mnt/outputs/.rsgkey && chmod 600 /sessions/<session>/mnt/outputs/.rsgkey
ssh -i /sessions/<session>/mnt/outputs/.rsgkey -p 22 -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null jfx9wij@131.153.143.194
```
- Host/user/port: `131.153.143.194` · `jfx9wij` · `22` · key-based.
- **Live theme:** `~/public_html/wp-content/themes/rsg_2022`
- **Staging theme:** `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022`
- **ALL WORK ON STAGING FIRST.** Never edit live except via the verified deploy step.
- PHP lint bins: 8.3 = `/opt/cpanel/ea-php83/root/usr/bin/php -l` ; 7.4 = `/opt/cpanel/ea-php74/root/usr/bin/php -l`
  (theme must stay clean on BOTH — it's forward-compatible and we keep it that way).
- Remote bash quirk: **process substitution `< <(...)` fails** over `ssh 'bash -s'`; read lists from a
  temp file instead (`done < /tmp/file`).
- ⚠️ Staging is `robots: Disallow /` → validate staging URLs in Google Rich Results Test **Code mode**
  (paste rendered HTML), or validate the live URL after deploy.

## The existing schema machinery (reuse it)
File: `includes/rsg-schema.php` (already live). Pattern = build PHP arrays → `wp_json_encode()` →
echo one `<script type="application/ld+json">` in `<head>` (valid by construction). It is wired by:
- one `require_once` in `functions.php`, and
- a call in `header.php` to `rsg_emit_review_schema()` (guarded by `is_singular([review types])`).

Reusable helpers already in that file: `rsg_schema_brand_name()`, `rsg_schema_image_url()`,
`rsg_schema_offer($pid)` (best-effort DFS Offer, never fatal), `rsg_build_review_faq()`. The local
reference copy is `rsg-schema.php` in this folder; the canonical doc is `SCHEMA-PASS-2026-06-21.md`.

---

## Comparison page data (already available in `single-comparison.php`, top ~70 lines)
- `$first_shoe_ID`, `$second_shoe_ID` = `get_field('first_shoe'|'second_shoe')` (the two reviewed shoes).
- Per shoe: brand = `get_the_terms($id,'brands')`; name = `shoe_name` meta or `get_field('model',$id)`;
  vote = `stars` meta or `get_field('vote',$id)` (X/10); weight = `weight`/`weight_oz`;
  heel drop = `heeldrop`/`heeldrop_mm`/`heel_drop_mm`; rrp = `rrp` meta or `get_geo_rrp($id)`;
  image = `get_field('professional_image',$id)`; permalink = `get_permalink($id)`.
- Title: `$vs_title = "$shoe_name_1 vs $shoe_name_2"`. User rating computed from approved comments meta `rate`.
- **Current schema on comparison pages = NONE** (confirmed: 0 `application/ld+json` blocks live).

## Recommended schema design (comparison)
Google has no dedicated "comparison" rich result, so emit the two shoes as **two `Product` nodes**
(each eligible for product/review rich results) + a `BreadcrumbList` for the comparison URL. Per Product:
`name` (brand + model), `brand`, `image`, `category` ("Running Shoes"; GPS → "GPS Running Watch"),
`sku`/`mpn` = shoe post ID, `aggregateRating` (from the page's existing per-shoe user-rating calc, when
>1 ratings), `additionalProperty` for Weight (oz) / Heel drop (mm) when >0, and best-effort `offers` via
`rsg_schema_offer($shoeID)`. Breadcrumb: Home → Comparisons (`/comparison/`) → "<X> vs <Y>" (permalink).
Build a `rsg_build_product_node($pid)` helper (factor it out of the review builder's Product section so
review + comparison share one code path), then `rsg_build_comparison_schema()` returns
`[product_1, product_2, breadcrumb]`.

## Files to change (staging) — keep it minimal, mirror the review pass
1. `includes/rsg-schema.php` — ADD `rsg_build_product_node($pid)`, `rsg_build_comparison_schema()`,
   and `rsg_emit_comparison_schema()` (guard `is_singular('comparison')`, no-op otherwise). Reuse the
   existing offer/brand/image helpers. (Optional refactor: have the review builder call the shared
   `rsg_build_product_node()` — only if it stays byte-identical in output; otherwise leave review code as-is.)
2. `header.php` — add a `rsg_emit_comparison_schema();` call right next to the existing
   `rsg_emit_review_schema();` call.

## Verification checklist (same bar as the review pass)
- [ ] `ea-php83 -l` AND `ea-php74 -l` clean on both changed files.
- [ ] Render 3-4 staging comparison URLs; extract the `ld+json` block and **`python3 -m json.tool`** = valid;
      confirm `@type`s = `[Product, Product, BreadcrumbList]`.
- [ ] **Frontend provably unchanged**: visible-text similarity = 1.0000, 0 changed spans vs the pre-change
      render (schema is in `<head>`, so body text must be identical after normalizing the cache-buster +
      rotating affiliate tokens). Reuse the review-pass diff method.
- [ ] Google Rich Results Test on a comparison URL (Code mode for staging, or live after deploy) = both
      Products valid, breadcrumb present, **0 errors**.

## Deploy (only after verification)
Same method as `DEPLOY-LOG-2026-06-21.md`:
1. Back up live copies of the 2 changed files into a timestamped dir OUTSIDE webroot
   (`~/rsg_backups_predeploy_<ts>/`), and snapshot-tar the live theme.
2. `cmp`-verify each deployed file is byte-identical to staging; `ea-php83 -l` on live.
3. Purge: WP Rocket file cache (`wp-content/cache/wp-rocket/*`) + `wp cache flush`; then USER clicks
   **Cloudflare "Purge Everything"** in the Rocket.net panel (edge `s-maxage` ~30d).
4. Re-validate the live comparison URL in Rich Results Test.
**Rollback:** restore the 2 backed-up files; the new functions are self-contained (no DB deps).

## After comparisons → GUIDES (phase 2, separate design)
`single-guide.php` are multi-product roundup/listicle pages. Schema design differs: emit an **`ItemList`**
of the featured products (or `Article` + `about`), NOT two Products. Scope it as its own pass once
comparisons ship. Same machinery, same verification bar.

## Key references (read these first next session)
- `theme/logs/DEPLOY-LOG-2026-06-21.md` — what's live now + backup/restore + the safe deploy pattern.
- `SCHEMA-PASS-2026-06-21.md` + `rsg-schema.php` (this folder) — the review schema pass to mirror.
- `../CONVENTIONS.md` — environment, rules, phased plan.
