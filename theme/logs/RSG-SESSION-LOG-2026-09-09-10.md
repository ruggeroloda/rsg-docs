# RSG — session log, 2026-09-09 → 2026-09-10

Three separate production problems. All three now fixed and verified on live.
This supersedes `PHP8-FATALS-DEPLOY-LOG-2026-09-09.md` (kept for its detail).

## Status at a glance

| # | Problem | Status | Verified |
|---|---------|--------|----------|
| 1 | All 18 GPS review pages HTTP 500 | **FIXED** | 21/21 GPS URLs 200/301 through edge cache |
| 2 | `/page/N/?brands=X` HTTP 500 | **FIXED** | pages 2-4 × 15 brand slugs, no 5xx |
| 3 | Zero prices on every review, site-wide | **FIXED (stopgap)** | 152 offers across 12 sampled pages |

**Item 3 is a stopgap with a security cost. Read §3 before considering this closed.**

---

## 1. GPS review pages — HTTP 500 (PHP 8 regression)

Two uncaught `TypeError`s in `wp-content/themes/rsg_2022/**include-single-gps.php**`,
both warnings on PHP 7.4, both fatal on 8.3. Introduced by the 2026-06-21 migration;
matches the GSC 5xx staircase starting 2026-06-22.

- **line 267** — `array_walk_recursive($descriptions, …)`; `$descriptions` is never
  defined in this template (it exists only in `single-reviews_gps.php`, a separate scope).
- **line 366** — `array_unshift($price_json->datafeed->items, …)` when the feed returns
  no items.

Fixing the first alone did nothing visible — the crash simply moved to the second.
Both were pinned with temporary `echo` execution markers, not inferred.

**Fix:** both calls wrapped in `isset() && is_array()` guards. Deliberately a *skip*,
not a coerce-to-array: on 7.4 these calls failed and did nothing, so skipping reproduces
the pre-8.3 output exactly.

- Backup: `include-single-gps.php.bak-php8fix-20260909` (same folder)
- Same guard also applied pre-emptively to `single-reviews_gps.php:672`
  (serves `/reviews/gps/…`, currently 404). Pre-patch copy: `~/Downloads/single-reviews_gps.php`

> **Trap for next time:** `single-reviews_gps.php` and `include-single-gps.php` contain a
> near-identical inline JS block, so output truncation looks the same in both. `/gps/…` is
> post type `gps` → `single-gps.php` → `include-single-gps.php`. Confirm which template is
> actually rendering before editing. An earlier pass this session patched the wrong file.

## 2. Paginated brand archives — HTTP 500

**`archive.php:80`** — `$query_cat->posts[0]->featured = 1;` when the featured-post query
returns no rows. Confirmed with execution markers (line 80 reached, line 82 never).
Page 1 of these URLs 404s, so only page 2+ reaches the block.

**Fix:** guards on the queried object, term meta, featured post and the merge.
One deliberate behaviour change: added `!empty($cat_meta['custom_term_meta'])`, which also
stops a silent duplicate-posts bug. Matches the guard `reviews_search.php` already uses.

**Not done:** `reviews_search.php` also has `&& $paged == 1` on this block (stops the
featured post repeating on every page). Behaviour change, not a crash fix — left out.

Rollback: original is 3677 bytes, identical to `~/Downloads/rsg_2022/archive.php`.

## 3. No prices site-wide — DFS 403 ⚠️ STOPGAP IN PLACE

**Root cause:** Rocket.net moved this site's **outbound** IP to `65.181.124.237`.
The DFS API allow-lists only `131.153.143.194` and `35.214.170.233` (`sites.client_ip`).
Every price call returned `403 Forbidden`. WordPress swallowed it silently and rendered
its "Sorry - Product not found" fallback.

Note the in/out distinction: `131.153.143.194` is the address you SSH **in** to. Outbound
traffic leaves from a different address. DFS only ever sees the outbound one.

`65.181.124.237` belongs to **WHG Hosting Services Ltd**, range `65.181.120.0/21`
(2,048 addresses) — Rocket.net's network operator. A platform-side change, nothing on
the RSG side. **This is why it broke silently after 3+ years.**

**Proven side by side from the server**, same URL, same second:

| Request | Result |
|---|---|
| normal | 403 — "IP 65.181.124.237 is not allowed" |
| + `&ios=yes` | 200 — 8.9 MB of live products |

**Stopgap deployed to LIVE (by Ruggero, 2026-09-10):**
`wp-content/plugins/dstudio/dstudio.php` — `$url .= '&ios=yes';` added after each of the
two lines that build the `/api/v1/products` URL (originally lines 218 and 328), *above* the
`dstudio_check_cache()` call so the cache key changes too. Both sites carry a dated comment.
Rocket.net cache purged. File ~20.87 KB → ~21.9 KB.

### ⚠️ This must be reverted
`ios=yes` is an **unauthenticated bypass of the DFS IP check** — anyone who knows the
parameter can query the API from anywhere. It is a pre-existing hole in DFS, not something
created here, but using it means the allowlist is currently decorative.

**To close properly:**
1. Get `65.181.124.237` actually registered in `sites.client_ip` (an attempt on 2026-09-10
   did not take effect — verify the row: `SELECT id, name, client_ip FROM sites;`).
   `api_attempts` logs every rejection with the caller IP and timestamp.
2. **Ask Rocket.net whether that IP is pinned or floats** within `65.181.120.0/21`.
   If it floats, allow-listing one address will fail again, silently.
3. Then remove the two `ios=yes` lines.
4. Better still: replace the IP allowlist with API-key auth, already proposed in
   `dfs/ARCHITECTURE.md`.

---

## Still open (not actioned)

1. **`ios=yes` bypass is live** — see §3. Highest priority.
2. **No production error logging.** `public_html/error_log` last written 2026-08-26 despite
   dozens of 500s; the theme-level `error_log` holds only CLI lint output from 21 June.
   All three problems above were completely silent. Fix this and the next one is caught in
   hours, not weeks.
3. **Amazon PA-API is retired** (deprecated 2026-04-30, endpoint gone 2026-05-15 — see
   `dfs/DFS-ACCESS.md`). The theme's `amazon_item_lookup()` calls PA-API v5 `getitems`,
   which no longer exists. The hardcoded AWS keys in `includes/amazon-api.php` are **dead,
   not merely exposed — delete them, don't rotate**. The theme already fails safe
   (`return array()`), so this too is silent.
4. **Plaintext secrets in the `dfs/` project folder** — root SSH password, MySQL root
   password, private key. Worth addressing.
5. **GSC:** Indexing → Pages → "Server error (5xx)" → **VALIDATE FIX**. Export the URL list
   first: if it contains shapes other than `/gps/…` and `/page/N/?brands=…`, there is a
   fourth fatal still out there. Do **not** validate the `/go/…` robots-blocked group.
6. **`includes/widgets/widgets.php:2256`** — same unguarded `array_unshift`. Not patched.
7. **`functions.php:856`** — `Undefined array key "HTTP_HOST"` warning, surfaced by WP-CLI.
8. **`include-single-gps.php:852`** reads `$descriptions[...]` which is never populated —
   that copy has rendered empty on 7.4 too. Content gap, not a crash.
9. **GPS pages show zero offers** — those products genuinely have no feed data. Commercial
   question, not a bug.
10. **Leftover:** empty `wp-content/rsg-logs/` folder on **staging**. Harmless; delete when
    convenient (the panel's delete dialog would not accept an automated click).

---

## Access notes (supersedes SSH-ACCESS-HANDOVER-2026-09-09.md)

**SSH is unusable from a Cowork session.** Port 22 fails to *every* host — the target,
github.com, 1.1.1.1 — while HTTPS works. The session's egress is HTTPS-only. The
`Connection timed out` in the old handover was that, **not** a server-side firewall ban.
Ports 2200 (DFS) and 2222 (Hetzner) are blocked too. Both RSG keys show **Authorized** in
the Rocket.net panel, so key auth was never the problem.

**Everything was done through the Rocket.net panel over HTTPS:**
File Manager (browse / View-Edit via Monaco / create / download), WP-CLI console
(allow-listed: `wp option get` works, **`wp eval` is rejected**), phpMyAdmin, cache purge,
backups.

**Panel gotchas:**
- Searching File Manager for the literal string `error_log` trips Cloudflare's WAF on
  `control.rocket.net`. Request-scoped, recovers in seconds. Avoid that search term.
- Match rows by **exact filename** before clicking the row menu. Grabbing "the last menu
  button" silently targets a `.bak-*` file when one matches the search — that happened once
  and was reverted.
- The Create menu and row menus are flaky; they often need a second click, and sometimes
  stop responding entirely until a full page reload.

**Linting:** every patch was `php -l` clean (PHP 8.4 in the sandbox). `ea-php83 -l` /
`ea-php74 -l` per CONVENTIONS.md could **not** be run — no server shell. No
version-specific syntax was used, but this is a gap in the usual process.

**Verification method:** every editor save was confirmed by comparing an in-page checksum
of the Monaco model against a locally linted copy of the same patch before saving.

---

## Lesson worth keeping

Three wrong turns this session all came from trusting the **January 2026 theme copy** in
`~/Downloads/rsg_2022/` instead of reading the live file:

1. Patched `single-reviews_gps.php` when the live template was `include-single-gps.php`.
2. Flagged an uncaught `throw` in `amazon-api.php` that had already been removed in June.
3. Assumed road/trail templates contained `array_unshift($price_json…)`; they do not.

**Read the live file before diagnosing or editing. Always.** A second pattern: the first
`array_unshift` "fix" looked like a failure because a *second* fatal sat behind it —
when a fix appears to do nothing, instrument before re-theorising.
