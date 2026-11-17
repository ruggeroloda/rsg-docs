# Deploy log — jQuery self-hosted + Ads test page removed (2026-11-09)

## jQuery from our own domain
- **Why:** jQuery loaded render-blocking from `ajax.googleapis.com` (extra DNS/TLS connection). Throttled-mobile test (5 runs, median): first paint home 1.22 → 1.09 s, review 1.38 → 1.35 s when self-hosted; making it non-blocking would add almost nothing more and needs every inline script rewritten (theme rebuild).
- **Change:** `functions.php` → `rsg_jquery()` now registers `get_template_directory_uri() . '/js/jquery-3.5.1.min.js'`; new file `js/jquery-3.5.1.min.js` = the exact Google 3.5.1 file (sha256 `f7f6a589…6e3d`, matches the official SRI `sha256-9/aliU8d…`). Same version, same bytes, only the origin changes.
- **Rejected:** WordPress core's `wp-includes/js/jquery/jquery.min.js` (3.7.1). Tested on staging: it ends with `jQuery.noConflict()`, so `$` is undefined and every page threw "$ is not a function" (handlers dropped from 10–89 to 6–7). Reverted on staging before anything went live.
- **Checks:** staging, 16 templates × mobile/desktop, fresh renders, Google vs self-hosted swapped in the same HTML: identical jQuery version, identical bound event-handler counts per page, 0 errors. Live after deploy: 32 page/device runs, all load `jquery-3.5.1.min.js` from www.runningshoesguru.com, 0 errors, handler counts as on staging.
- **Backups:** `functions.php.bak-jquery-20261109` (live + staging). **Rollback:** copy back, `wp eval 'rocket_clean_domain();'` + `wp cdn purge`.
- Old unused `js/jquery.min.js` (v1.10.1) left in place, not loaded.

## Ads test page
- Page 39842 `/ads-test-page/` (Freestar test, Freestar-only template) **trashed** on live + staging (restorable 30 days); nothing linked to it (content, meta, menus checked). Live URL → 404. Template `page-39842.php` renamed `page-39842.php.bak-freestar-20261109` (both).
