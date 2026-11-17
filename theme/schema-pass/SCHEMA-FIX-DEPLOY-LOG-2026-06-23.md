# Schema Fix Deploy Log — 2026-06-23

Fixes two Search Console "Product snippets" / "Unparsable structured data" issues
surfaced after the June 21–22 schema passes.

**Live theme:** `~/public_html/wp-content/themes/rsg_2022`
**Changed files (2):** `includes/rsg-schema.php`, `header.php`
**Staged in:** `schema-pass/deployed-20260623/` (mirrors theme paths)

---

## Issue 1 — Comparison pages: "Either 'offers', 'review' or 'aggregateRating' should be specified" (7 items, growing)

**Root cause.** `rsg_build_product_node()` (comparison pass) attached `offers` only
when a live datafeed price > $1 existed, and `aggregateRating` only when there were
**2+** approved user ratings (`$ur['count'] > 1`). It never attached a `review`. Any
compared shoe with no live price **and** < 2 ratings emitted a `Product` with none of
the three required properties → invalid. The guide pass already avoided this by
attaching a first-party editorial `review`; the comparison pass was never given that
fallback.

**Fix (`includes/rsg-schema.php`, `rsg_build_product_node()`):**
- Lowered the aggregateRating threshold `> 1` → `>= 1` (Google accepts `reviewCount` 1).
- Added a guaranteed fallback: when neither `offers` nor `aggregateRating` is present,
  attach RSG's editorial `Review` via `rsg_build_editorial_review()` (built from the
  shoe's editorial `vote` + `verdict`). Every reviewed shoe has a `vote`, so every
  comparison Product now satisfies the requirement.

## Issue 2 — /top/ pages: "Parsing error: Missing ',' or '}'" (2 items)

**Root cause.** NOT from the new schema (first detected 22/06/2025, a year ago). The
`/top/` roundups carried a **legacy hand-built JSON-LD block in `header.php`**
(`is_singular('top')`) with a brace mismatch. The new guide builder only guarded
`is_singular('guide')`, so `/top/` was never migrated.

**Fix:**
- `includes/rsg-schema.php`, `rsg_emit_guide_schema()` guard:
  `is_singular('guide')` → `is_singular( array( 'guide', 'top' ) )`. Both post types
  share the `review_sections_2023` / `winner` / `vote` / `verdict` fields the builder
  reads, so the same clean `wp_json_encode()` output covers both.
- Breadcrumb made post-type-aware (`/top/` no longer mislabels as "Guides").
- `header.php`: removed the legacy hand-built `is_singular('top')` JSON-LD block
  entirely (superseded by the above).

---

## Verification (sandbox, no PHP runtime)
- `rsg-schema.php` brackets fully balanced ( ) 527/527, { } 145/145, [ ] 95/95.
- `header.php` after removal: PHP tags 43/43, `if:`/`endif` 4/4, `while:`/`endwhile`
  0/0, `<script>`/`</script>` 11/11, legacy block + offer URL gone.
- Simulated `rsg_build_product_node()` output for the 7-failure case (no price, no
  ratings) → valid JSON carrying `review`; 1-rating and fully-loaded cases also valid.

## Deploy
Copy the two staged files over the live theme (back up originals first), e.g.:
```
LIVE=~/public_html/wp-content/themes/rsg_2022
cp -p "$LIVE/includes/rsg-schema.php" ~/rsg_backups_predeploy_20260623/includes/rsg-schema.php
cp -p "$LIVE/header.php"             ~/rsg_backups_predeploy_20260623/header.php
cp -p schema-pass/deployed-20260623/includes/rsg-schema.php "$LIVE/includes/rsg-schema.php"
cp -p schema-pass/deployed-20260623/header.php             "$LIVE/header.php"
```
Run `php -l` on both on the server before/after. Then in Search Console hit
**Validate Fix** on both issues, and spot-check a comparison + a /top/ URL in the
Rich Results Test.

## Rollback
Restore the two backed-up originals. No DB changes.

---

## STAGING deploy — DONE 2026-06-23 (live untouched)

Per decision to validate on staging first. Staging confirmed byte-identical to live
for both files before deploy (md5 match), so the test mirrors production.

- **Target:** `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022`
- **Pre-deploy backup (outside webroot):**
  `~/rsg_backups_predeploy_staging_20260623_191415/` (header.php + includes/rsg-schema.php)
- **Uploaded md5:** header `d3e0eb5e3c4ed1d922538b47f0fb1b61`,
  rsg-schema `e4f522f2b88ab45f1908153b35a71ae5` (match local edited files).
- **`ea-php83 -l` (live handler):** no syntax errors in either file.
- **Functional check (WP-CLI eval, real data):**
  - Comparison #50021 (Hoka Mach X 3 vs X 2): Mach X 3 now VALID via `review`
    fallback; X 2 VALID via offers+aggregateRating; JSON valid.
  - Top #49387 (/top/puma-running-shoes/): new Article + ItemList (6 valid picks)
    + BreadcrumbList; JSON valid; legacy brace-error block gone.

**Staging test URLs (Rich Results Test / view-source):**
- https://jfx92wmcr3-staging.onrocket.site/comparison/hoka-mach-x-3-vs-hoka-mach-x-2/
- https://jfx92wmcr3-staging.onrocket.site/top/puma-running-shoes/

**Next:** after sign-off on staging, deploy the same two files to the live theme
(back up live originals first), then GSC → Validate Fix on both issues.

## Staging rollback
Restore from `~/rsg_backups_predeploy_staging_20260623_191415/` over the staging theme.

---

## LIVE deploy — DONE 2026-06-23 (signed off on staging)

- **Target:** `~/public_html/wp-content/themes/rsg_2022`
- **Pre-deploy backup (outside webroot):**
  `~/rsg_backups_predeploy_live_20260623_193834/` (header.php `5f8f48f0…`,
  includes/rsg-schema.php `8e9169ff…` — the originals).
- **Uploaded md5:** header `d3e0eb5e3c4ed1d922538b47f0fb1b61`,
  rsg-schema `e4f522f2b88ab45f1908153b35a71ae5` (match local edited files).
- **`ea-php83 -l`:** no syntax errors in either file.
- **Functional check (WP-CLI, LIVE data):**
  - Comparison #50021: Hoka Mach X 3 VALID via `review` fallback; Mach X 2 VALID
    via offers+aggregateRating; JSON valid.
  - Top #49387: Article + ItemList (6 valid picks) + BreadcrumbList; JSON valid.

**Next (USER):** GSC → Validate Fix on both issues; optionally Cloudflare "Purge
Everything" so recrawls see the new markup sooner.

## Live rollback
Restore from `~/rsg_backups_predeploy_live_20260623_193834/` over the live theme.

---

## Rev 2 — legacy `post`-type reviews (2026-06-23, after first GSC Validate)

GSC re-validation surfaced 2 comparison items still invalid: **ASICS GEL-Nimbus
Lite 3** (#36652) and **Nike Metcon 6** (#31183). Both are legacy `post`-type
reviews (not `reviews_*`) that store the editorial score in a **`stars` meta
(0-10)**, not the ACF `vote` field — so the rev1 `review` fallback found no score
and attached nothing. (The other 5 flagged items were already valid post-rev1; GSC
had simply listed the pre-deploy 22 Jun crawl.)

**Fix (`includes/rsg-schema.php`, rev2):**
- `rsg_build_editorial_review()`: when ACF `vote` is empty, fall back to the legacy
  `stars` meta (0-10) and to the `verdict` meta. Gives legacy shoes a valid
  first-party Review.
- `rsg_build_product_node()`: safety net — if a node still has none of
  offers/review/aggregateRating, return empty so callers skip it rather than emit an
  invalid Product. Guarantees no future invalid item regardless of data gaps.

**Deploy:** staging then live, both `ea-php83 -l` clean.
- rev2 md5 (header unchanged): rsg-schema `166b9ca1e2554f941c66c59df5acce3e`.
- Live backups: `~/rsg_backups_predeploy_live_<ts>_rev2_rsg-schema.php`.
- **Verification (WP-CLI, LIVE):** all 7 flagged comparisons → 14 Product nodes,
  **0 invalid**, every page's JSON valid. Legacy #36652 and #31183 now satisfy via
  `review` (stars 7 and 9).

## Follow-up scheduled
- SSH key rotation reminder set for 2026-06-30 (key was shared in plaintext;
  rotate in Rocket.net panel, replace `.ssh-rocket-key`, revoke old key).
