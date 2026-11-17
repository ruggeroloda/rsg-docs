# RSG Theme Redesign — Project Conventions

---

> **START HERE → read `CLAUDE.md`** (project root). It holds the current live state, access block,
> working rules, what's shipped, and prioritised next steps. This file is the durable *reference*
> (goals, environment, theme audit, bloat inventory, pricing/caching target, phased plan, and the
> detailed 2026-06-21 work logs) — kept for depth, not as the session entry point.

---

## Project Overview
Modernise the live WordPress theme behind runningshoesguru.com (`rsg_2022`). Three primary goals, in order:
1. **Upgrade to PHP 8** (site currently runs PHP 7.4).
2. **Remove bloat / mistakes** (dead code, bundled paid plugin, hardcoded secrets, oversized templates).
3. **Move to the new pricing / caching strategy** (one cached HTML for all geos; per-country prices + MSRP delivered as data and filtered client-side).
4. **Style redesign** — done **after** the above, unless a specific change is cheaper to do during the refactor. Looks are explicitly the last phase.

**Decision (made):** Modernise the existing theme in place. Do **NOT** rewrite the theme from scratch — see rationale below.

## Environment
- **Host:** Rocket.net (cPanel / CloudLinux stack). Now operated on hosting.com's platform.
- **SSH/SFTP:** `131.153.143.194`, user `jfx9wij`, port `22`, key-based auth. WP-CLI 2.9 installed. Webroot `~/public_html`.
- **PHP:** web currently **7.4**; server has **8.0–8.4** installed (`ea-php80`…`ea-php84`). PHP 8.3 CLI at `/opt/cpanel/ea-php83/root/usr/bin/php`. Switch via cPanel "Select PHP Version" / MultiPHP.
- **Staging:** a staging site exists — `jfx92wmcr3-staging.onrocket.site` (in webroot; one-click clone re-launched 2026-06-21). **All work happens on staging. Never edit live.**
- **CDN:** Cloudflare in front (sends `CF-IPCountry`). Edge cache is aggressive (`s-maxage` ~30 days).
- ⚠️ The private SSH key was shared in plaintext chat — **rotate it** in the Rocket.net panel once work is done.

## Active Theme: `rsg_2022` — Current State (audited 2026-06-21)
- **Size:** 292 PHP files, ~21 MB. Effectively an application, not a presentation layer.
- **Big files:** `functions.php` 2,176 lines (78 KB); `header.php` 30 KB; `footer.php` 29 KB; single templates enormous — `single-guide.php` ~126 KB, `single-top.php` ~126 KB, `single-best.php` ~118 KB, `single-reviews_road/_trail/_gps/_training.php` ~95–116 KB each.
- **PHP 8 readiness is GOOD:** PHP 8.3 syntax lint = **0 hard fatals** across all 121 theme files (excl. bundled plugins). No real `each()`, no `create_function`. The two `mysql_` hits are commented-out dead code. Remaining risk is runtime *deprecations* (null-to-string args, dynamic properties in 8.2+), not parse failures — so the 7.4→8 jump is config + deprecation testing, not a rebuild.
- **Functionality is modular:** custom post types live in their own files under `includes/` — guide, review, reviews_road, reviews_gps, trail, gps, gift_guides, amazoncontent, external_product, noindex. Taxonomies: brands, review_brands, trail_brands, gps_brands, shoes_family, shoes_type, content_category, amazoncontent_category.

## Known Bloat / Mistakes to Fix
1. **Bundled ACF Pro 5.7.5** inside the theme (`includes/plugins/acf3/`), loaded via `acf/settings/path` + `include_once` (functions.php ~1421–1455). It's a paid plugin bundled the ACF-sanctioned way (runs without a per-site licence), but the version is ~2018-era and gets **zero updates** — a real PHP 8 deprecation risk. Plus custom add-ons (`amazon`, `amazon_image`, `acf-to-rest-api`) load from the same folder.
   - **Action:** install licensed ACF Pro 6.x as a normal plugin, re-home the Amazon add-ons, remove the bundle + path filters. Field *data* stays in the DB. Treat as its own task, not a freebie.
2. **Exposed secret:** paid `pro.ip-api.com` API key hardcoded in client JS (`footer.php`). Anyone can scrape and run up the bill. The pricing redesign lets us delete this call entirely.
3. **Hardcoded MySQL credentials** in a commented-out ip2nation/ip2location geo block in `functions.php` (user `runningshoesgur`, password in plaintext). Dead code, but it's in source + backups — strip and rotate.
4. Oversized single templates with lots of near-duplicate inline-echoed HTML/JS — candidates for consolidation into shared partials/template-parts.
5. `.php.old` / `.BAK` files committed in the theme (e.g. `taxonomy-shoes_family.php.old`, `template-home-2020.php.BAK`).

## New Pricing / Caching Strategy
**Today:** `RSG_get_country_code()` (functions.php:1616) reads Cloudflare `CF-IPCountry` (clamped to a supported list, default US). The theme calls DFS **once per visitor's country** (`dstudio([... 'countrycode' => RSG_get_country_code()])`) so HTML differs by geo → the CDN caches a separate variant per country. Live merchant prices come from DFS (`newrsg-json`: `DISPLAY_PRICE`, `CURRENCY_PREFIX`, `CURRENCY_CODE`, `COUNTRY_NAME`, `DEEP_LINK`, `MYKEY`). MSRP/"list price" (strikethrough) comes from ACF fields `rrp_us`/`rrp_gb`/… via `get_geo_rrp()` (functions.php:1867).

**Two problems:**
- Geo-fragmented cache (low hit rate, complex purges) and cloaking ambiguity (Googlebot on US IPs only ever sees US prices at a shared URL).
- **Per-country MSRP is never populated.** `rrp_gb/au/ca` are blank, so `get_geo_rrp()` falls back to the US value and `include-single-affiliate.php:370` hardcodes it as `… US$`. Non-US visitors see a **US-dollar** strikethrough list price. It's manual ACF work that never gets done.

**Target architecture:**
- DFS emits a **per-country JSON matrix** carrying, per country: the live merchant offer **and** the MSRP. Ideally stored in **post meta** (written by DFS on a schedule) so page render no longer depends on a live DFS call → maximally cache-friendly.
- WordPress serves **one identical HTML** to everyone (all countries embedded or fetched once); **JavaScript shows only the visitor's country** and hides the rest.
- **Country detection client-side** via a **Cloudflare edge-set cookie** (we're already on Cloudflare) — NOT the current exposed `ip-api.com` call and NOT server-side render.
- **FOUC/CLS:** default every country block to `display:none` in CSS; JS reveals the match.
- **Roundup pages** (`single-guide`/`single-best`, dozens of shoes, already 100 KB+ HTML): don't inline every country for every shoe — fetch the matrix from **one cached JSON endpoint** keyed by product IDs and filter in JS.
- **Schema:** emit a clean canonical Product/Offer set, not the hidden duplicates.

**Wins:** one cache entry instead of N (faster TTFB, simpler purges), no cloaking ambiguity, and the MSRP gap is **fixed by automation** (DFS supplies it) — deleting `get_geo_rrp()` and the `rrp_xx` fields.

**Hard dependency on DFS:** this only works if DFS can output all countries at once **and** include `price_msrp` per country. The new DFS spec already requires a `price_msrp` field, so the data likely exists — but the current `newrsg-json` is fetched one country at a time. **This is the key question for the DFS task** (see `projects/dfs/`).

## Phased Plan
1. **Staging baseline** — confirm staging clone is ready; snapshot/backup; point work there.
2. **PHP 8** — flip staging to PHP 8.x; fix runtime deprecations; smoke-test every template type (review road/trail/gps/training, guide, best, comparison, gift-guide, home, wizard).
3. **Debloat & secure** — remove `.old`/`.BAK`, strip hardcoded DB creds, remove exposed ip-api key (folded into step 4), consolidate duplicate template HTML.
4. **ACF extraction** — licensed ACF Pro plugin; migrate Amazon add-ons; drop the bundle.
5. **Pricing/caching rework** — depends on DFS emitting the per-country price+MSRP matrix; move country selection to a Cloudflare cookie + client-side JS; store matrix in post meta; default-hidden CSS; cached JSON endpoint for roundups; clean schema.
6. **Style redesign** — last; restyle the now-clean PHP 8 theme.
7. **Go live** — verify on staging, then deploy; rotate the SSH key.

## Open Questions
- Is the staging clone (`jfx92wmcr3-staging.onrocket.site`) finished and usable?
- Does DFS `newrsg-json` already expose MSRP per country, and can it emit all countries in one payload? (→ DFS task)
- Which countries must be supported in the matrix? (current clamp list: US, CA, GB, DE, FR, ES, IT, GR, SE, NO, IE, AU, NZ, MY, ID, SG, HK)
- Do we want the companion-plugin extraction of CPTs now, or defer until after PHP 8 + pricing are stable?

## Working Rules
- **Never edit live.** Staging only, with a backup taken first.
- API/MCP/SSH over browser; Chrome last resort.
- Concise updates; flag risks early.

## Work Log — 2026-06-21 (PHP 8.3 on staging)
All work on staging (`jfx92wmcr3-staging.onrocket.site`), web PHP confirmed **8.3.22** (LiteSpeed). Every edited file lint-checked with `ea-php83 -l`. Backups of all edited/removed files live **outside the web root** at `~/rsg_backups_20260621/` (`legacy/` = dead files, `edit_backups/` = pre-edit copies). SSH key still pending rotation at go-live.

**PHP 8.3 smoke test.** Crawled one cache-busted (fresh-render) URL per template type with `WP_DEBUG_LOG` on; results in `logs/php83-smoke-test-2026-06-21.md`. Earlier "clean 200s" were LiteSpeed cache; fresh renders exposed 3 fatals.

**3 fatals fixed (were HTTP 500 on fresh render):**
- `includes/widgets/widgets.php` (×2 sites) — "modify property items on null"; added object/array guards before `$price_json->datafeed->items[...]`.
- `single-reviews_trail.php` (×3: lines ~2175/2184/2193) — legacy `implode($array,$glue)` order (removed in PHP 8.0); swapped + `(array)` guard.
- `single-reviews_training.php` (×3) — `implode()` fed a string from `get_field('usage',…)`; wrapped in `(array)`.
All three templates re-tested → 200, 0 fatals.

**AWeber fully removed** (unused ~10yrs; site uses MailerLite now):
- Commented loader `require_once('includes/plugins/rsg_aweber/rsg_aweber.php')` in root `functions.php` (this killed the live comment-form hooks + the ~29/page return-type deprecations).
- Deleted bundled library dir `includes/plugins/rsg_aweber/` (108 KB, 12 files).
- Removed the 2 dead AWeber newsletter functions in `widgets.php` (`rsg_newsletter_widtget`, `rsg_newsletter_main`; only caller in `sidebar.php` was commented).
- Note: footer AWeber form was already inside a `<!-- -->` block (dead). Remaining "aweber" text matches are all dead/commented.

**Google+ removed** (service dead since 2019): stripped gplus buttons from `include-social-bar.php`, `include-social-bar-horizontal.php`, and the social widget in `widgets.php`. (Confirmed: share *widget* and *horizontal* bar aren't rendered — callers commented; only the vertical `include-social-bar.php` is live via `include-single.php`.)

**Security:** removed the dead ip2nation/ip2location block in root `functions.php` (`RSG_get_country_code`) that held plaintext **MySQL user+password**. ⚠️ Still **rotate that DB password** (it remains in old backups/source history). The exposed `pro.ip-api.com` key in `footer.php` is still TODO (folds into pricing rework).

**Deprecation/warning cleanup (phase 1):**
- `utf8_encode()` (deprecated 8.2) eliminated: `includes/reviews/functions.php:991` → `mb_convert_encoding(...)` (was ×181/run); `includes/simple_html_dom.php:849` idiom replaced.
- `rsg_datafeed_json()` hardened with `(string)` cast → kills null `strpos`/`str_replace` deprecations there.
- `includes/reviews/functions.php:960` — guarded `$current_post_brand[0]->term_id` (WP_Error/empty) → kills term_id null warnings.
- `reviews_search.php` — `if/elseif($args['shoes_type'])` → `!empty(...)` (was "Undefined array key shoes_type" ×149).
- `widgets.php` — `basename((string)$template)` guard.

**Legacy files archived** (moved to `~/rsg_backups_20260621/legacy/`, not deleted): guide-boxes.php.BAK, header.css.OLD, taxonomy-shoes_family.php.old, mega_menu_review.php.OLD, template-home-2020.php.BAK, reviews_wizard_2020.js, Rokkitt-Regular.ttf.BAK, star-rating1.png.BAK, finder_icon_2020.jpg, 2 wizard header .BAK images. **Kept (still referenced):** `template-home-2020.php` (assigned to a live page), `include-reviewslist-fullwidth-2020.php`, `images/logo2020.svg`, `images/plans/plans_banner_2020.jpg`, `template-wizard-2020.php`.

**Status after today:** all template types render 200 with 0 fatals on PHP 8.3. Remaining non-fatal warnings are now localized — mainly `single-reviews_trail.php` / `single-reviews_training.php` (`$override_tag`, `$price_event` undefined; a `str_replace` null), `widgets.php` (array-offset-on-null ×49), `includes/amazon-api.php` ("Amount" on null ×32). These need per-context fixes (next focused pass).

**Broad smoke crawl (451 URLs, random across all types + taxonomy archives).** 423 clean 200s; **27 fatals** from 6 root causes — caught what the representative test missed (the gps single template was a *false pass* earlier: the sampled URL 301-redirected to a reviews_gps page, so the real `include-single-gps.php` was never tested). All data/template-dependent PHP 8 type errors. **All fixed** (backups `.bak-fatals2-20260621`), re-crawl of the 27 = 27/27 200:
- `include-single-gps.php:266` (×18, ALL gps pages) — `array_walk_recursive()` on null → guard `is_array($descriptions)`.
- `reviews_search.php:529` (×3 shoes_type archives) — `count(null)` → `is_array() && count()`.
- `single-reviews_trail.php:1891` (×2) — `in_array($x, $usages['value'])` with string → `is_array() && in_array()`.
- `single-reviews_training.php:1634` + `includes/widgets/widgets.php:604` — `$weight*28.35` string×float → `(float)$weight`.
- `include-single-review-trail.php:450` — `null + string` rating sum → `(float)` casts.
- `include-single-review-trail.php:1387/1423` + `include-single-gps.php:1111/1147` — star-rating `get_comment_meta('meta_rate')*100/5` string×int → `(float)` cast (×4).

**Deploy verdict (2026-06-21):** with all fatals fixed, fresh renders are clean across a 451-URL random sample + the 27 former failures. Production theme still contains these fatals, so **deploy theme files + flip PHP 8.3 together** (flipping PHP alone would break live). Deploy *files only*, not DB/uploads (staging clone has drifted). Pre-deploy: back up prod files+DB, then purge Cloudflare + LiteSpeed cache, watch prod `error_log`.

**Still TODO:** rotate MySQL password + SSH key; remove `ip-api.com` key (`footer.php`); phase-2 *warning* cleanup (non-fatal: trail/training `$override_tag`/`$price_event`, widgets array-offset, amazon-api "Amount" on null); confirm dynamic paths (comment submit, search, DFS pricing across countries, wp-admin); then ACF extraction → pricing/caching rework → style → go live.

## Work Log — 2026-06-21 (Review schema pass, on staging)
**Goal:** invisible (frontend-unchanged) machine-readability pass. Full detail in
`schema-pass/SCHEMA-PASS-2026-06-21.md`; the include is `schema-pass/rsg-schema.php`.
- **Was broken:** rich JSON-LD only on road reviews **and invalid** (top level `{Product},{Breadcrumb}`
  with no array wrapper → breadcrumb dropped); trail/gps/training had **no** JSON-LD; `top` Article had an
  unterminated `url` string; `priceValidUntil` bad format/+2d.
- **Fix:** new self-contained include `includes/rsg-schema.php` builds schema from PHP arrays +
  `wp_json_encode()` (valid by construction). Wired via one `require_once` in `functions.php`; `header.php`
  road block (old lines 284–500) replaced by one guarded `rsg_emit_review_schema()` call covering all four
  review types; `top` url string closed. Emits Product + Review (expert `reviewRating` X/10) + all 10
  sub-scores & weight/heel-drop as `additionalProperty` (so "/10" stays in markup, **not** on the page) +
  pros/cons + `aggregateRating` (when >1 user reviews) + best-effort `offers` (priceValidUntil +30d) +
  Breadcrumb + FAQPage. GPS drops its N/A zero metrics; `category` is type-aware (GPS Running Watch).
- **Verified on staging:** PHP 8.3 lint clean (all 3 files); JSON-LD parses on all 5 page types; **frontend
  provably unchanged** (visible-text similarity 1.0000, 0 changed spans, all types); **Google Rich Results
  Test** — staging road = 5 valid items incl. Breadcrumbs, live = 4 (breadcrumb dropped by the old bug).
- **Backups:** `~/rsg_backups_20260621/schema_pass_20260621/` (`files/header.php.orig`, `files/functions.php.orig`,
  `baseline/*.before|after.html`). **Rollback:** restore the two `.orig`, delete `includes/rsg-schema.php`.
- **NOT yet on production** — ships with the staging→live deploy (next session).
