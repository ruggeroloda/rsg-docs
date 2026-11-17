# Deploy planning brief — staging → live + PHP 7.4 → 8.3

> **Status: NOT a finished plan.** This captures the considerations, risks, and open
> questions so the next session can build the careful, final deploy plan fast.
> The actual go/no-go plan gets written and agreed next session.

## The core coupling (why this is delicate)
Live still runs **PHP 7.4** with the **old theme** (which has fatals under PHP 8.x).
The staging theme has those fatals fixed **plus** the schema pass. So:

- Flipping PHP to 8.3 **without** the fixed theme → live breaks (fatals).
- Therefore the **fixed theme files and the PHP 8.3 flip must both be in effect** before
  the site is "done." The question is the safest *sequence* to get there with the
  smallest risk window.

## Preferred sequence to pressure-test next session
1. **Confirm the fixed theme also runs clean on PHP 7.4** (forward-compatible). If yes,
   we can **deploy files first while still on 7.4** (no breakage), smoke-test, *then*
   flip PHP to 8.3 — which removes the scary "switch both at once" moment.
   → Open question: verify staging theme files lint/run on 7.4 too.
2. Back up **prod theme dir + DB** to outside webroot (timestamped).
3. Deploy **theme files only** (not DB/uploads — the staging clone has drifted).
   → Need an exact **changed-file manifest** (diff staging theme vs live theme) so we
     copy precisely what changed, not the whole dir (staging has `.bak-*` drift).
4. Smoke-test on 7.4 (still old PHP, new files).
5. **Flip PHP 7.4 → 8.3** for the domain (cPanel MultiPHP / Select PHP Version → `ea-php83`).
6. Purge **Cloudflare + LiteSpeed/WP-Rocket** cache (Cloudflare edge cache is ~30d s-maxage).
7. Verify (below). Watch live `error_log` throughout.

## Verification checklist (post-deploy)
- Tail live `~/public_html/error_log` for fatals/new warnings during/after.
- Re-run the **451-URL smoke crawl** sample (already used during the 8.3 fix work) → expect 0 fatals.
- Spot-check schema on a few **live** review URLs in Rich Results Test (live IS crawlable,
  unlike staging) — expect road/trail/gps/training all valid, breadcrumb present.
- Dynamic paths: comment submit, search, **DFS pricing across countries**, wp-admin.
- Confirm `aggregateRating`/offers still resolve where expected.

## Risks / things to confirm next session
- **7.4-compatibility of the fixed theme** (decides the sequence in step 1).
- **Stack compatibility on 8.3:** Object Cache Pro (Redis), WP Rocket, and all active
  plugins must be 8.3-safe. Confirm versions parity live vs staging.
- **DB drift / new expectations:** deploying files only — verify the new code doesn't
  expect any new options/ACF fields that the *live* DB lacks. (Schema pass only reads
  existing ACF fields — OK — but double-check nothing else added.)
- **Plugin/theme version parity** between staging and live.
- **Rollback:** restore prod theme backup + flip PHP back to 7.4. Define explicit
  rollback triggers (any fatal on a top-traffic template, pricing breaks, checkout/aff
  links break) before we start.
- **Timing:** pick a low-traffic window; have error_log tail + cache-purge ready.
- **Secrets at go-live:** rotate the **SSH key** and **MySQL password** (both exposed in
  old chat/backups). Remove the `ip-api.com` key in `footer.php`.

## What ships in this deploy
- The PHP-8 fatal fixes already done on staging (see main work log).
- The review **schema pass** (`includes/rsg-schema.php` + the `functions.php` require +
  the `header.php` swap). Frontend unchanged; adds valid JSON-LD across all review types.

## Out of scope for this deploy (later phases)
ACF Pro extraction → pricing/caching rework (per-country price+MSRP, client-side geo) →
style redesign. Keep this deploy strictly = "PHP 8.3 + cleaned theme + schema," nothing else.
