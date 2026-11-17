# Review schema pass — 2026-06-21 (STAGING)

Invisible (frontend-unchanged) machine-readability pass. Adds valid JSON-LD
structured data to all four review types and fixes the two broken schema blocks.

## What was wrong (before)
- Rich Product/Review JSON-LD existed **only on road reviews**, and it was **invalid
  JSON** (top level was `{Product},{BreadcrumbList}` with no array wrapper — parser
  stops after Product, breadcrumb dropped).
- Trail / GPS / training reviews emitted **no JSON-LD** at all.
- The `top` (listicle) Article block was invalid (unterminated `url` string).
- `priceValidUntil` used a bad format and only +2 days.

## What changed
New self-contained include builds schema from PHP arrays + `wp_json_encode()`, so
output is **valid by construction** (no hand-written braces to break).

Files (staging theme `rsg_2022`):
- **NEW** `includes/rsg-schema.php` — schema builder + emitter.
- `functions.php` — one `require_once` line at end.
- `header.php` — road JSON-LD block (old lines 284–500) replaced by a single guarded
  call `rsg_emit_review_schema()` covering road/trail/gps/training; `top` block `url`
  string closed.

Per review the JSON-LD now contains: Product (brand, name, image, description,
sku/mpn, type-aware category), Review with expert `reviewRating` (X/10), all 10
sub-scores + weight/heel-drop as `additionalProperty` (the "/10" scale lives here via
`maxValue`, so nothing is printed on the page), pros/cons as positive/negativeNotes,
`aggregateRating` when >1 user reviews, best-effort `offers` (priceValidUntil +30d),
`BreadcrumbList`, and a `FAQPage` from the who-should/should-not-buy fields.
GPS correctly omits its N/A (zero) shoe metrics.

## Verification (staging)
- PHP 8.3 lint clean: include, functions.php, header.php.
- JSON-LD parses on all 5 page types (road/trail/gps/training = `[Product,
  BreadcrumbList, FAQPage]`; top = valid `Article`).
- **Frontend provably unchanged**: visible-text similarity 1.0000 with 0 changed spans
  on every type (after normalizing the cache-buster + rotating affiliate tokens, which
  also differ between two fetches of identical code).

## Backups (on server)
`~/rsg_backups_20260621/schema_pass_20260621/`
- `files/header.php.orig`, `files/functions.php.orig`
- `baseline/*.before.html` and `*.after.html` (the verified renders)

## Rollback
Restore `header.php.orig` + `functions.php.orig`, delete `includes/rsg-schema.php`.

## Next steps (not yet done)
1. Validate a staging URL in Google Rich Results Test / Schema.org validator.
2. Deploy theme files to production (back up prod first), purge cache.
3. Request reindex in Search Console for a few sample review URLs; watch for the
   review/star rich result to appear.
