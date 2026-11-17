# Live 500 fix on the `[dfscat]` post — 8 Nov 2026 18:46 UTC (staging + LIVE)

- Page: `/2013/12/choosing-a-lightweight-running-shoe/` (post 12094, the only published post with `[dfscat]`) returned **HTTP 500 on live and staging**: the old DFS category API answers nothing and `rsg_dfs_category()` (`includes/widgets/widgets.php` line 2491) called `count()` on null (PHP 8 fatal).
- Fix (one line, first line of `rsg_dfs_category()`): `if ( ! is_object( $price_json ) || empty( $price_json->datafeed->items ) || ! is_array( $price_json->datafeed->items ) ) { return ''; }`. With data the function is unchanged. Script `dfs/_work/step6.3/fix500.py`.
- **Live** `wp-content/themes/rsg_2022/includes/widgets/widgets.php`: patched in place (the live file = the pre-DFS original, md5 f6944395…; DFS edits are NOT on live). Backup `widgets.php.bak-fix500-20261108-1846`. New md5 179319ad….
- **Staging** same line added to the DFS-edited file; backup `widgets.php.bak-fix500-20261108-1846`. Note: rolling back the DFS widgets edit (`.bak-dfs63-20261108-1633`) would also drop this line on staging.
- Checks: lint 8.3 + 7.4 (both files); staging 200 with `?dfs=0`, `?dfs=1` and plain; live 200 after purge, no PHP warnings; live home, reviews list, road review, guide, legacy review, comparison all 200.
- **Purge done by Claude**: `wp cdn purge` (host mu-plugin `cdn-cache-management`, = Rocket.net "Purge Everything"); confirmed with `CDN_Clear_Cache_Api::cache_api_call([], 'purge_everything')` → `{"success":true}`.
- Rollback (live): `cp widgets.php.bak-fix500-20261108-1846 widgets.php` in `includes/widgets/`, then `wp cdn purge`.
