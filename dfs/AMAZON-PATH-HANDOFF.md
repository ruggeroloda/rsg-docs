# Hand-off: theme's Amazon-direct pricing path → should move into DFS

**Created:** 2026-06-21 (during the RSG theme PHP 8.3 deploy)
**Why this is here:** this is the one real seam between the RSG-theme project and the DFS project.
Logged in both (`rsg-redesign/DEPLOY-LOG-2026-06-21.md` + here).

## What was discovered
The theme `rsg_2022` has a **second pricing path** bolted in alongside DFS:

- **Main path (correct):** `dstudio` plugin → DFS `/api/v1/products` (`newrsg-json`). This renders the
  price-comparison boxes on shoe review/guide/roundup pages. Working, untouched.
- **Legacy side-branch (in the theme):** `includes/amazon-api.php` `amazon_item_lookup()` calls Amazon's
  **Product Advertising API directly**, bypassing DFS. It fires only when:
  - the related product is a **GPS watch** (`if('gps' == get_post_type(...))` in the review-list widget), or
  - a roundup/guide item has an **`amazon_link`** ACF field set (gift guides).
  Callers (~12 templates): widgets.php (2857/3121), single-guide/top/best, single-reviews_*,
  include-single-gps/-review-trail, single-gift-guide, template-parts/guide-boxes.

## Why it broke (2026-06-21)
The PA-API call uses the **deprecated API + hardcoded AWS Associate keys** (amazon-api.php lines 5-8).
The call now fails (keys dead/unauthorized → `fopen` false), and the code `throw`s an **uncaught**
exception → the whole page 500s (homepage + GPS guide). It only "worked" before because responses sat
in the object cache; flushing cache exposed it. **Fatal on 7.4 and 8.3 alike** — not a PHP-version issue.

## Stopgap applied (theme side, today)
`amazon_item_lookup()` now **returns `array()` instead of throwing** (+ guarded final return), so a failed
Amazon call renders no box instead of crashing the page. This keeps the 8.3 deploy safe but **does not
restore Amazon pricing** — GPS/gift-guide Amazon boxes are effectively blank until properly fixed.

## The real fix (DFS project decision)
Pick one:
1. **Fold into DFS (preferred, matches target architecture):** have DFS supply GPS-watch + gift-guide
   pricing in the same `{"datafeed":{"items":[…]}}` contract the theme already consumes, and delete the
   theme's `amazon-api.php` path entirely. One pricing source, no secrets in the theme.
2. **Update PA-API in place:** migrate to current Amazon PA-API v5 auth, move keys out of source into
   server config/secrets. Keeps the architectural wart but stops the breakage. (Least preferred.)

## Security note
Hardcoded AWS Associate keys (US + UK) live in plaintext in `includes/amazon-api.php` and in old backups
— rotate/remove regardless of which option is chosen.

## Reference
Full theme-consumer contract for DFS: `DFS-Consumer-Contract-Report.md` (same folder). Note §5 already
documents the MSRP/ACF coupling; this Amazon-direct path is a separate, undocumented-until-now pricing
source that belongs on the DFS rebuild's radar.
