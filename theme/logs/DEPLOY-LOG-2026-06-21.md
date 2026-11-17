# Deploy Log — staging → live + PHP 7.4 → 8.3

**Date:** 2026-06-21
**Operator:** Claude (Cowork session)
**Host:** `131.153.143.194` · user `jfx9wij` · port 22 · key-based
**Live theme:** `~/public_html/wp-content/themes/rsg_2022`
**Staging theme:** `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022`

---

## ⭐ WHERE THE BACKUPS ARE (for restore)

**All pre-deploy backups live OUTSIDE the webroot at:**
```
~/rsg_backups_predeploy_20260621_172408/
```
Contents:
| File / dir | What it is |
|---|---|
| `rsg_2022_theme_20260621_172408.tar.gz` (11 MB) | **Full** live theme dir, exactly as it was before deploy |
| `db_20260621_172408.sql.gz` (111 MB) | Full live DB dump before any change |
| `PRE-DEPLOY-STATE.txt` | Records live PHP = **7.4.33** at backup time |
| `deployed_files_orig/` | **Original live copy of each of the 12 modified files** (mirrors theme paths) |

> Note: prior session's backups (PHP-8 fix work + schema pass) are in a separate dir:
> `~/rsg_backups_20260621/` (`legacy/`, `edit_backups/`, `schema_pass_20260621/`). Untouched.

---

## What was deployed (Step 3) — DONE ✅

Copied **13 files** staging → live (files only; NOT DB/uploads). Verified byte-identical
(`cmp`, 0 mismatches) and PHP 7.4 lint-clean (0 failures). Permissions preserved (`cp -p`).

**12 modified** (original saved in `deployed_files_orig/<same path>`):
1. `functions.php`
2. `header.php`
3. `include-single-gps.php`
4. `include-single-review-trail.php`
5. `include-social-bar.php`
6. `include-social-bar-horizontal.php`
7. `includes/reviews/functions.php`
8. `includes/simple_html_dom.php`
9. `includes/widgets/widgets.php`
10. `reviews_search.php`
11. `single-reviews_trail.php`
12. `single-reviews_training.php`

**1 new** (no live original — to restore, DELETE it):
13. `includes/rsg-schema.php`

**Scope decision:** MINIMAL. Legacy dead files on live (`*.OLD`, `*.BAK`, `includes/plugins/rsg_aweber/`)
were intentionally LEFT IN PLACE (harmless — nothing loads them; aweber loader is commented out in the new
`functions.php`). Staging-only `*.bak-fatals2-*` files were NOT deployed.

**What these changes do:** PHP 8 fatal/deprecation fixes (so the theme is 8.3-safe) + the review schema
pass (`rsg-schema.php` + a `require_once` in `functions.php` + a `header.php` swap). Frontend unchanged.

---

## RESTORE PROCEDURE

### A. Roll back just the theme files (most likely)
```bash
BK=~/rsg_backups_predeploy_20260621_172408
LIVE=~/public_html/wp-content/themes/rsg_2022
# restore the 12 modified files from saved originals:
( cd "$BK/deployed_files_orig" && find . -type f -exec cp -p {} "$LIVE/{}" \; )
# remove the new file:
rm -f "$LIVE/includes/rsg-schema.php"
```

### B. Nuke-and-restore the whole theme dir from tarball
```bash
BK=~/rsg_backups_predeploy_20260621_172408
cd ~/public_html/wp-content/themes
rm -rf rsg_2022
tar -xzf "$BK/rsg_2022_theme_20260621_172408.tar.gz"
```

### C. Restore the DB (only if DB ever touched — this deploy did NOT touch the DB)
```bash
cd ~/public_html
gunzip -c ~/rsg_backups_predeploy_20260621_172408/db_20260621_172408.sql.gz | wp db import -
```

### D. Roll PHP back to 7.4
cPanel → Select PHP Version / MultiPHP Manager → set domain back to `ea-php74`.
(The deployed theme is forward-compatible with 7.4, so files can stay even if PHP reverts.)

**Rollback triggers (decided up front):** any fatal on a top-traffic template, pricing/DFS breaks,
affiliate links break, or wp-admin inaccessible.

---

## Step 4 — smoke-test live on PHP 7.4 — DONE ✅
Crawled 20 representative live URLs (one per template type: home, post, page, guide, review,
reviews_road/trail/gps/training, gps, top, comparison, content, + category/brands/shoes_type
archives), each cache-busted to force fresh PHP render. **Result: 0 fatals / 0 5xx.** error_log
delta during crawl = 362 bytes, **0 Fatal/Parse/Uncaught**; only 2 pre-existing non-fatal warnings
(`foreach` on non-array, `include-single-review-trail.php` lines 751/775 — known warning category).
URL list saved server-side at `/tmp/rsg_smoke_urls.txt`.

## ⚠️ PHP VERSION — RESOLVED: live is genuinely 7.4, flip is 7.4 → 8.3
Conflicting signals reconciled:
- WordPress **Site Health** (production backend): critical issue = "running PHP **7.4.33**".
- Definitive **web-handler probe** (dropped a 1-line PHP file in webroot, curl'd it, deleted it):
  `WEBPHPMARKER=7.4.33`. ← ground truth for what executes the site.
- CloudLinux **alt-php selector** (`selectorctl --user-current`): **7.4** → `/opt/alt/php74/usr/bin/php-cgi`.
- cPanel `uapi LangPHP` showed `ea-php81` for the vhost — **RED HERRING**, not the effective handler.

**Effective control = the alt-php selector, surfaced in the Rocket.net panel → Advanced Settings →
"PHP Version" dropdown** (currently shows 7.4 checked; offers 5.6…8.4). This is the correct, host-
supported knob. **Do NOT flip via SSH/uapi.**

So: my earlier "live is on 8.1" note was WRONG. Live = **7.4**; this is a real 7.4 → 8.3 jump (the big,
coupled one the project notes warned about). The Step-4 curl smoke test therefore validated the fixed
theme on the actual **7.4** web handler → 0 fatals (forward-compat confirmed). Live error_log (since
07:20 today) = 0 fatals. Stack 8.3-ready: Object Cache Pro 1.25.4, WP Rocket 3.21.2, Akismet 5.7 current.

**Flip method (USER does this in browser):** Rocket.net panel → PHP Version dropdown → select **8.3**
(NOT 8.4 — staging validated on 8.3 only) → save. **Rollback:** same dropdown back to **7.4** (instant;
deployed theme is 7.4-compatible so files can stay).

## 🔴 INCIDENT — 8.3 flip surfaced fatals NOT caught on staging (2026-06-21 ~17:37)
After USER flipped to 8.3 (web probe confirmed 8.3.22), verification crawl returned HTTP 200 on all
20 URLs BUT the live error_log showed **6 fatals** (fatal fires mid-render, after 200 header → page
truncates for visitors). Root causes — a duplicated-code pattern only PARTIALLY fixed last session:
- `round($rrp)` on a non-numeric MSRP **string** → `single-guide.php:879`, `single-reviews_road.php:1448`
  (+ same unguarded `round($rrp)` copies in single-top, single-reviews_trail/training/gps).
- `implode(', ', get_field('best_for'/'usage', ...))` unguarded fallback → `single-comparison.php:471`
  (+ `single-reviews_road.php:2167-69`, `single-reviews_gps.php:1885-87`).
- Also at risk: `round($weight*28.35)` where `$weight` is a string → `include-single-affiliate.php:350`,
  `include-single-review-trail.php:927`.
Static scan found ~8 templates sharing this copy-pasted MSRP/usage block; staging's sample never hit the
triggering data, so they slipped through.

**Mitigation:** roll PHP back to **7.4** via Rocket.net dropdown (instant; these are warnings on 7.4).
**Fix plan:** systematic hardening pass on staging — cast `round((float)$rrp)`, `(float)$weight`,
and `implode(', ', (array) get_field(...))` across ALL copies; lint 8.3+7.4; redeploy changed files;
re-flip; then a BROAD live crawl (100+ URLs) watching error_log = 0 fatals before sign-off.

## Step 5a/5b DONE ✅ — rolled back + hardened + redeployed (still on 7.4)
- **5a:** USER flipped PHP back to **7.4** via Rocket.net dropdown (web probe confirmed `WEBPHPMARKER=7.4.33`).
  Live visitors safe; incident exposure window ended.
- **5b:** Systematic hardening pass on staging (perl, across 141 php files):
  `round($rrp)`→`round((float)$rrp)`; `round($weight*`→`round((float)$weight*`;
  `implode('…', get_field(…))`→`implode('…', (array) get_field(…))`. **10 files changed:**
  include-single-affiliate.php, include-single-review-trail.php, includes/widgets/widgets.php,
  single-comparison.php, single-guide.php, single-reviews_gps.php, single-reviews_road.php,
  single-reviews_trail.php, single-reviews_training.php, single-top.php.
  Lint clean on **8.3 AND 7.4** (0 failures). 0 remaining uncast round($rrp)/round($weight*)/
  implode(...,get_field) patterns theme-wide. Casts make those calls total (cannot throw on any data).
- **Staging snapshot before edits:** `…/staging_theme_prehardening_174341.tar.gz`.
- **Deployed all 10 to live** (byte-identical, 0 lint fail). Pre-hardening live copies saved at
  `…/deployed_files_orig2_hardening/<paths>`. Staging vs live now = only 2 inert legacy assets
  (finder_icon_2020.jpg, reviews_wizard_2020.js), intentionally out of scope.
- **Expanded verification set** built: `/tmp/rsg_verify_urls.txt` = **108 URLs** weighted to risk
  templates (guide 15, road 15, comparison 15, trail 15, gps 8, training 6, top 3, +review/gps singles).

### Restore note — round 2
To revert hardening only: copy files from `deployed_files_orig2_hardening/` back over the live theme.
Full theme restore tarball + DB + round-1 originals all still in `~/rsg_backups_predeploy_20260621_172408/`.

## Step 5c — re-flip to 8.3 + 108-URL crawl — round/implode FIXED, Amazon fatal found
USER re-flipped to **8.3** (probe `WEBPHPMARKER=8.3.22`). Ran 108-URL crawl (weighted to risk templates)
+ error_log tail.
- **round()/implode() class: 0 fatals** across all 108 — hardening confirmed working. ✅
- **NEW blocker (2 fatals → 2× HTTP 500):** `includes/amazon-api.php:63` `Uncaught Exception: "Exception
  Occured"`. Hit homepage (`/`) + `/guide/best-gps-running-watches/`. (1 URL returned `000` = 15s timeout
  on a heavy page, not a fatal.)
- **Diagnosis:** `amazon_item_lookup()` does `@fopen()` to Amazon PA-API v5 with HARDCODED AWS keys
  (amazon-api.php lines 5-8). The call fails (keys appear dead/unauthorized → fopen=false), and lines
  63/67 `throw new Exception` with NO caller-side try/catch → whole page 500s. **Version-independent**
  (fatal on 7.4 too); was masked because Amazon responses were cached until I ran `wp cache flush`.
- "Amazon box" = per-product merchant price widget (Amazon price + strikethrough MSRP + thumbnail +
  "Best Price" affiliate button) rendered for products with an `amazon_link` ACF field; appears in the
  review-list widget (homepage) and guide/roundup/review pages. Callers (~12 templates) are all unguarded.

**Status: PAUSED at user request** to discuss the Amazon integration before changing behavior.
**Full-page cache deliberately NOT purged** → real visitors still served good cached pages; 500 risk only
on fresh/uncached renders. Live remains on 8.3 (round/implode safe; Amazon landmine equal on 7.4/8.3).

**Proposed fix (on hold):** make `amazon_item_lookup()` return `array()` instead of throwing (+ guard final
return) so a failed Amazon call renders no box instead of 500ing. Single chokepoint, safe on 7.4 & 8.3.

## 🔐 SECURITY TODO (noted for later, per user)
- Hardcoded **AWS Associate keys** in `includes/amazon-api.php` (lines 5-8, both US + UK functions) — plaintext.
- Plus existing: rotate **SSH key** + **MySQL password**; remove **ip-api.com** key in `footer.php`.

## Amazon stopgap DONE ✅ + FULL VERIFY PASSED — site live & clean on PHP 8.3
**Decision:** graceful stopgap (per user). Edited `includes/amazon-api.php` (staging→deployed live;
pre-fix original saved in `deployed_files_orig2_hardening/includes/amazon-api.php`):
- both `throw new Exception("Exception Occured")` (lines 63/67) → `return array();`
- final return guarded: `return (isset($response->ItemsResult->Items) ? … : array());`
Failed Amazon calls now render no box instead of 500ing. Lint clean 8.3 + 7.4.

**Final verification on 8.3 (fresh, cache-busted renders):**
- 108-URL crawl (weighted to risk templates) = **0 fatals**, 107×200 + 1 slow page (trail roundup,
  200 in 10.2s — exceeded the 15s parallel-crawl window, fine standalone). Homepage + best-gps + 8 GPS
  reviews all 200.
- **Schema valid** on road/trail/gps/training: each = Product + BreadcrumbList + FAQPage (json-parsed OK).
- DFS price boxes render (57 `/go/` links on a road review). Search 200. wp-admin/ 302→login.
- ⚠️ `wp-login.php` = 403 from external IP — likely Rocket.net/Cloudflare login protection (edge, not
  our change). USER to confirm normal admin login still works.

**Caches:** server-side purged (WP Rocket file cache + Object Cache Pro/Redis flushed). EDGE = **Cloudflare**
(`s-maxage=2592000` ~30d). Cloudflare purge needs panel/API → **USER must click "Purge Everything"** in
Rocket.net/Cloudflare so the schema + fresh 8.3 pages roll out immediately (else they update over ~30d TTL).

## ✅ DEPLOY COMPLETE (pending Cloudflare edge purge by user)
Live = PHP **8.3.22** + hardened `rsg_2022` theme + schema pass. All fatals resolved. Theme files only
(no DB/uploads touched). Rollback paths intact (see backups section).

## Open / follow-up
- [ ] USER: Cloudflare "Purge Everything" (edge) · confirm wp-admin login OK
- [ ] Fold theme's Amazon-direct pricing (GPS + `amazon_link`) into DFS, or update PA-API + move keys out
      of source → tracked in **dfs/AMAZON-PATH-HANDOFF.md**
- [ ] Security rotations: AWS Associate keys (amazon-api.php), SSH key, MySQL password; remove ip-api.com
      key in footer.php
- [ ] Phase-2 non-fatal warning cleanup (trail/training `$override_tag`/`$price_event`, etc.)
- [ ] Step 6: purge Cloudflare + LiteSpeed cache
- [ ] Step 7: verify (error_log, smoke crawl, schema on live, dynamic paths)
- [ ] Post-go-live: rotate SSH key + MySQL password; remove `ip-api.com` key in `footer.php`

_(This file is updated as each step completes.)_
