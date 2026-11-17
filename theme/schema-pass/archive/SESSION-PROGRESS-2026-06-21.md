# Session progress — 2026-06-21 (schema/HTML/canonical pass)

## Done & DEPLOYED to live (see GUIDES-REVIEWS-DEPLOY-LOG-2026-06-21.md)
- **Guides** (`single-guide.php`, 15 posts): added frontend-invisible JSON-LD — Article + ItemList of Product picks (each with editorial Review, brand, specs, offers, aggregateRating) + BreadcrumbList + FAQPage, publisher Organization carries independence/USP signals. Plus 9 pixel-identical heading re-levels (trust block h6→h2; per-pick sub-labels h6→h4, via `.hN` classes). 13/15 guides covered (GPS-watches + Mizuno emit nothing by graceful design).
- **Reviews hub** (`reviews_search.php` / Page 589): family pages `/reviews/{brand}/{family}/` made **self-canonical (indexable)** — 27 `shoes_family` hubs (Pegasus, Ghost, Kayano, Nimbus, etc.) now able to rank for "{brand} {family}" queries. Added canonical-gated CollectionPage→ItemList + BreadcrumbList schema (self-canonical views only).
- Files changed (5): `includes/rsg-schema.php`, `header.php`, `single-guide.php`, `functions.php`, `reviews_search.php`. Lint clean PHP 8.3 + 7.4; frontend visible-text 1.0000 vs live; byte-identical staging→live; caches purged.
- **Backups:** server `~/rsg_backups_predeploy_20260621-213334/` (+ theme tarball); project `schema-pass/predeploy-backups-20260621/` (originals) and `deployed-20260621/`.

## OUTSTANDING — manual
- ⚠️ **User must click Cloudflare "Purge Everything"** in Rocket.net panel (origin verified correct; edge cache still old).

## NOT done / next opportunities (priority order)
1. **Layer-3 content / E-E-A-T refresh** on high-demand guides — the actual HCU recovery lever. Guides still say "we tested" 0×; need visible first-hand testing, original measurements/photos, freshness cadence. Schema = legibility, not ranking recovery. (See GUIDES-SEO-ANALYSIS.md.)
2. **Monitor Search Console** for the 27 family hubs ("{brand} {family}" impressions) over coming weeks; add a sentence of unique intro copy per hub.
3. **Reviews-card heading cleanup** — dedupe responsive-twin `<h4>` shoe names + de-head Weight/Drop/Category `<h6>`. Lives in SHARED `print_review_block_2020()` (`includes/widgets/widgets.php`), used by guides/best/top too → needs cross-page visual re-verification. Method known (div.h4 / h3.h4 / div.h6 + matching margin utilities).
4. **Data nits:** doubled brand in some model names (e.g. "Hoka One One HOKA Gaviota 5"); duplicate `robots` meta + obsolete `noodp,noydir` on reviews pages.

## Env / connect (unchanged)
SSH key `../.ssh-rocket-key`; host `131.153.143.194` user `jfx9wij`; live theme `~/public_html/wp-content/themes/rsg_2022`; staging `~/public_html/jfx92wmcr3-staging.onrocket.site/.../rsg_2022`. ALL WORK ON STAGING FIRST. Lint `ea-php83 -l` + `ea-php74 -l`. WP-CLI = `wp`.
