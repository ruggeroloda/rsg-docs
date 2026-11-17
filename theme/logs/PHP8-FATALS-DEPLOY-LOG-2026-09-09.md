# PHP 8 fatals deploy log — 2026-09-09/10

Fixes three uncaught `TypeError`s introduced by the PHP 7.4 → 8.3 migration of
2026-06-21. All were warnings on 7.4 and became fatals on 8.3, which is why the
GSC "Server error (5xx)" staircase starts 2026-06-22.

Deployed from the Rocket.net control panel (File Manager + Monaco editor).
**SSH was not available** — see "Access notes" below.

---

## 1) GPS review pages — 100% of them were returning HTTP 500

**Symptom:** every one of the 18 live `/gps/...` review pages returned 500 with
truncated HTML. Road/trail/training reviews were unaffected.

**Template chain:** `/gps/...` = post type `gps` → `single-gps.php` →
`get_template_part('include','single-gps')` → **`include-single-gps.php`**.

> Note for future sessions: `single-reviews_gps.php` serves `/reviews/gps/...`
> (currently 404) and contains a **near-identical copy of the same inline JS
> block**. Output truncation looks the same in both. Confirm which template is
> actually rendering before editing — an earlier pass in this session patched
> the wrong file on that basis.

### Two separate fatals, both had to be fixed

**a) `include-single-gps.php:267`**
```php
array_walk_recursive($descriptions, function (&$value) use ($shoe_name) { ... });
```
`$descriptions` is **never defined in this template** — it exists only in
`single-reviews_gps.php`, a separate variable scope. PHP 8:
`array_walk_recursive(): Argument #1 ($array) must be of type array, null given`.

**b) `include-single-gps.php:366`**
```php
array_unshift($price_json->datafeed->items, $amazon_item_obj);
```
Throws when the DFS feed returns no items for the product. PHP 7.4 warned and
skipped the insert.

Fixing (a) alone did **not** clear the 500 — it just moved the crash to (b).
Both were pinned with temporary `echo "<!--RSGM:NNN-->"` execution markers
rather than inferred; the markers were removed before the live deploy.

### Fix
Both calls wrapped in `isset() && is_array()` guards. Deliberately a **skip**,
not a coerce-to-array: on 7.4 these calls failed and did nothing, so skipping
reproduces the pre-8.3 rendered output exactly instead of newly injecting an
Amazon row onto pages that never had one. Downstream code already guards with
`isset($price_json->datafeed->items[0])`.

**Files:** `wp-content/themes/rsg_2022/include-single-gps.php`
**Backup:** `include-single-gps.php.bak-php8fix-20260909` (same folder)
**Verified:** all 18 GPS URLs 200 on staging, then 200 on live through the edge
cache with no cache-buster.

### Same latent bug, also patched
`single-reviews_gps.php:672` — identical `array_unshift` guard. This template
currently serves only 404s, so the patch is pre-emptive.
**Rollback copy:** `~/Downloads/single-reviews_gps.php` (pre-patch, verified).

---

## 2) Paginated brand archives — `/page/N/?brands=X` returned HTTP 500

**Confirmed fatal (markers, not inference):** `archive.php:80`
```php
$query_cat->posts[0]->featured = 1;
```
when the featured-post query returns no rows. Execution markers showed line 80
reached and line 82 never reached.

Page 1 of these URLs 404s, so only page 2+ reaches the buggy block.

### Fix
Guards on the queried object, the term meta, the featured post and the merge.

**One deliberate behaviour change:** added `!empty($cat_meta['custom_term_meta'])`.
Previously an empty value ran `new WP_Query('p=')`, which returns the latest
posts and merges them into the archive — silent duplicate posts. This matches
the guard `reviews_search.php` already uses on the same block.

**Not done:** `reviews_search.php` also has `&& $paged == 1` on this block, which
stops the featured post repeating on every paginated page. That is a behaviour
change rather than a crash fix, so it was left out. One line to add if wanted.

**Files:** `wp-content/themes/rsg_2022/archive.php`
**Rollback:** original is 3677 bytes, checksum verified identical to the
January copy in `~/Downloads/rsg_2022/archive.php`.
**Verified:** `/page/{2,3}/?brands={nike,asics,saucony,brooks}` all 200.
Regression-checked category, brands, shoes_type and gps_brands archives — all 200.

---

## Cache
Rocket.net "Purge Everything" run after each of the two deploys.

---

## Access notes (supersedes SSH-ACCESS-HANDOVER-2026-09-09.md)

**SSH is not reachable from a Cowork session at all.** Port 22 fails to *every*
host — the target, github.com, 1.1.1.1 — while HTTPS works fine. The session's
egress allows HTTPS only. The `Connection timed out` in the previous handover
was this, **not** a CSF/fail2ban ban on the server. The firewall-ban theory can
be dropped. (The earlier clean `Permission denied` results are still real and
still unexplained — that keypair genuinely is not authorised.)

**Everything needed was done through the Rocket.net panel over HTTPS:**
- File Manager — browse, View/Edit (Monaco), create, download.
- WP-CLI console (Advanced tab) — works, but on an allowlist: `wp option get`
  runs, **`wp eval` is rejected**.
- phpMyAdmin, cache purge, backups.

**Gotchas:**
- Searching File Manager for the literal string `error_log` trips Cloudflare's
  WAF on `control.rocket.net` ("Sorry, you have been blocked"). Request-scoped,
  not an IP ban, recovers in seconds. Avoid that search term.
- The row action menu needs the row matched by exact filename. Grabbing "the
  last menu button" silently targets a `.bak-*` file when one matches the
  search — that happened once here and was reverted.
- Editor saves were verified by comparing an in-page checksum of the Monaco
  model against a locally linted copy of the same patch before every save.

**Linting:** `php -l` was run against every patch in the sandbox (PHP 8.4).
`ea-php83 -l` / `ea-php74 -l` per CONVENTIONS.md could **not** be run — no shell
on the server. The changes use no version-specific syntax, but this is a gap in
the usual process.

---

## Open items (not actioned)

1. **PHP fatals are not being logged anywhere findable.** `public_html/error_log`
   was last written 2026-08-26 despite many fresh 500s; the theme-level
   `error_log` only holds CLI lint output from 21 June. You are effectively blind
   to production errors — worth fixing before the next incident.

2. **SECURITY — `includes/amazon-api.php` has an AWS access key and secret key
   hardcoded in plaintext** at the top of the file. Rotate and move to
   `wp-config.php` constants. (Also still outstanding: the MailerSend token in
   `.credentials-local.md`, flagged "rotate when convenient" on 2026-07-05.)

3. **Uncaught `throw` in the Amazon path.** `amazon_item_lookup()` does
   `if (!$fp) { throw new Exception(...); }` and **no call site in the theme has
   a try/catch** — including `single-reviews_road.php:673` and
   `single-reviews_trail.php:675`. If Amazon's PA-API has a bad day, road and
   trail reviews 500 exactly as GPS did. Needs staging verification first: it is
   a shared function with 15+ call sites.

4. **`includes/widgets/widgets.php:2256`** has the same unguarded
   `array_unshift($price_json->datafeed->items, ...)`. Not patched.

5. **`functions.php:856`** — `Undefined array key "HTTP_HOST"` warning, surfaced
   unprompted by WP-CLI. Another PHP 8 strictness miss in the same migration.

6. **`include-single-gps.php:852`** reads `$descriptions[$range][$selected_key]`,
   but `$descriptions` is never populated in that template — so that descriptive
   copy has rendered empty on 7.4 too, for as long as the file has existed. A
   content gap, not a crash. Decide whether it should show something.

7. **`single-gps.php:9`** — `if($_REQUEST['alt'] == 1)` with no `isset()`.
   Warning only, but same family.

---

# ADDENDUM — 2026-09-10: site-wide missing prices (separate incident)

**Not related to the PHP 8 fixes above.** Reported during the same session: every review
page on live shows "Sorry - Product not found" instead of prices.

## Root cause — CONFIRMED

The WordPress server's **outbound IP is now `65.181.124.237`**. The DFS API
(`http://45.33.39.133/api/v1/products`) allow-lists only **`131.153.143.194`** and
**`35.214.170.233`** (`sites.client_ip`, see dfs/ARCHITECTURE.md §8). Every price
request is therefore **403 Forbidden**:

```
HTTP/1.1 403 Forbidden
IP 65.181.124.237 is not allowed to access this service.
```

WordPress swallows this silently: `rsg_datafeed_json()` returns empty, the template
falls through to its "Product not found" branch. No error, no log, no alert.

**Method:** temporary `rsg-dfs-probe.php` on the **staging** site (same box, same egress
IP), secret-gated, `ignore_errors=true` so 4xx bodies are visible. Deleted immediately
after; verified 404.

## Fix (must be done on the DFS box — not reachable from a Cowork session)
Add `65.181.124.237` to `sites.client_ip` for the RSG row. Prices return within the
6-hour `dstudio` cache TTL, or sooner after a DFS cache clear + Rocket.net purge.

**Confirm first if wanted:** the DFS `api_attempts` table logs every rejected call with
the caller IP — it should be full of `65.181.124.237`, and the earliest entry dates the
outage.

## Follow-ups
- **Ask Rocket.net whether `65.181.124.237` is pinned or pooled.** If it floats, this
  recurs silently. Get the range if pooled.
- **Replace the IP allowlist with API-key auth** (already proposed in ARCHITECTURE.md
  §"Client-site activation"). An IP allowlist against a managed host is fragile.
- **Amazon PA-API is retired** (deprecated 2026-04-30, endpoint retired 2026-05-15 — see
  dfs/DFS-ACCESS.md). The theme's `amazon_item_lookup()` calls PA-API v5 `getitems`,
  which no longer exists. The hardcoded AWS keys in `includes/amazon-api.php` are
  **dead, not merely exposed** — delete them rather than rotating. Note the theme-side
  code already fails safe (`return array()`), so this is silent too.

## Ruled out during diagnosis
- `dstudio` plugin — **active**.
- `ds_url` option — correct (`http://45.33.39.133`).
- Merchant exclusions — no `*merchant*` or `*exclude*` options in `wp_options`;
  exclusions are per-brand via `rsg_taxonomy_<term_id>['custom_term_merchants']`, which
  could not produce a site-wide outage.
- Templates — rendering correctly; this is the designed empty-feed fallback.
- The 2026-09-09/10 PHP 8 fixes — different files, and road/trail/training reviews
  (untouched) are equally affected.

## Session hygiene note
Three wrong turns in this session all came from trusting the **January 2026 theme copy**
in `~/Downloads/rsg_2022/` instead of reading live files: the wrong template
(`single-reviews_gps.php` vs `include-single-gps.php`), a "fix" for a `throw` that had
already been removed in June, and an assumption about which files call `array_unshift`.
**Read the live file before diagnosing or editing. Always.**
