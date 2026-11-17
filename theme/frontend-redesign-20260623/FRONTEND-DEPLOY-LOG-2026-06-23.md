# Deploy Log — Road review specs/facts front-end redesign
**Date:** 2026-06-23 · **Env:** Rocket.net (`131.153.143.194`, user `jfx9wij`) · **Theme:** `rsg_2022`
**Status:** ✅ Deployed to live (road reviews only). ⚠️ Manual step outstanding: **Cloudflare "Purge Everything"**.

---

## 1. What shipped
Replaced the old full-width **dark specs panel** (`panel-inverted`, `#1C2226`) on **road**
reviews with a new **light, native** "specs & tester assessment" block. Visible-text only;
the JSON-LD schema (shipped 2026-06-22) is unchanged and still matches the visible values.

The new block:
- **The facts** — objective spec-sheet data (weight, drop, carbon plate, price, category) as
  neutral tiles, plus a **side-profile stack graphic** (heel vs toe height + drop).
- **Our tester's take** (blue eyebrow = clearly opinion, not spec):
  - **Size & fit** — full-width "runs small/large/true to size" banner + a **shoe diagram**
    with heel/midfoot/toebox zones (deviations auto-highlighted) and a fit list.
  - **Cushioning & ride** — name + bold value on one line; a right-aligned ~500px spectrum
    bar with **both poles named** (Responsive↔Plush, Little↔High, Neutral↔Stable, Rigid↔Flexible).
  - **Best for** — usage chips, selected one in blue.
- Uses the theme's native typography (source-sans-pro `<h3>`s, proxima body, `.text-muted`)
  so it reads as part of the article rather than an embedded card.

## 2. Files changed (live theme `rsg_2022`)
| File | Change |
|---|---|
| `includes/review-assessment-block.php` | **NEW** — the block renderer (ACF → HTML/SVG). |
| `single-reviews_road.php` | Dark specs panel (≈324 lines) replaced by one line: `include get_template_directory() . '/includes/review-assessment-block.php';` |

Diff confirmed surgical: live→deployed differs **only** in that one hunk (panel → include).

## 3. Verification
- **PHP lint:** `ea-php83 -l` + `ea-php74 -l` clean on both files (live).
- **Live render (cache-busted)** on `asics-gel-cumulus-28-review` and `nike-pegasus-41-review`:
  new block present, old `#1C2226` panel absent, stack graphic present, 0 PHP errors,
  schema `additionalProperty` (9 props) still intact.
- Built/approved iteratively on staging behind a `?redesign=1` flag, then the flag + old
  panel were removed for the permanent version (verified the normal staging URL before live).

## 4. Backups
- **Server (live, pre-deploy):** `~/public_html/wp-content/themes/rsg_2022/single-reviews_road.php.bak-redesign-20260623-053701`
- **Project folder:**
  - `frontend-redesign-20260623/predeploy-backups/single-reviews_road.php` — live original.
  - `frontend-redesign-20260623/deployed/` — the two deployed files.

## 5. Outstanding manual step
**Cloudflare → "Purge Everything"** in the Rocket.net panel — verified at origin with a
`?cb=` cache-buster; edge-cached road URLs serve old HTML until purged.

## 6. Rollback
Restore the one template file and purge caches (the new include is then simply unused; it can
be left in place or deleted):
```bash
T=~/public_html/wp-content/themes/rsg_2022/single-reviews_road.php
cp -p "$T.bak-redesign-20260623-053701" "$T"
ea-php83 -l "$T"
```

## 7. Fixed after initial deploy
- ✅ **Internal links restored (brand / family / previous model).** The initial block dropped the
  old panel's internal links; re-added 2026-06-23 as a compact linked metadata line at the top of
  the block (above "The facts"): Brand → `/reviews/{brand}/all/`, Family → `get_term_link(shoes_family)`
  (gated on the `family` ACF flag), Previous model → `get_permalink(previous_model)`. Model left as
  plain text (already in `<h1>`/`<title>`/JSON-LD). This preserves crawl/link-equity to the 27
  indexable family hubs. Live block backup: `includes/review-assessment-block.php.bak-20260623-054247`.

## 8. Trail + training ported (2026-06-23)
- ✅ Same block now live on **trail** and **training** too. The block is per-type aware:
  - **Facts:** carbon plate gated to road only (`'reviews_road' === $pt`); trail/training match their
    original panels (weight, drop, stack, price, category — no carbon plate).
  - **Cushioning & ride / usage** adapt via `rsg_review_assessment_map()`: trail adds the `Grip (lugs)`
    spectrum + a second "Good for running" chip row; training uses the road field set (empty fields skip).
  - Brand/family/previous-model nav links work for all types.
- Templates changed: `single-reviews_trail.php`, `single-reviews_training.php` — dark panel replaced
  by the include (regex swap, only that hunk). Backups:
  `single-reviews_trail.php.bak-redesign-20260623-194346`, `single-reviews_training.php.bak-redesign-20260623-194346`.
- Live verified (cache-busted): trail = new block + lugs + good-for-running + 20 schema props + brand link;
  training = new block, no lugs/good-for-running, 7 schema props, brand link. 0 PHP errors, lint clean 8.3/7.4.

## 9. Not done / next
- ⚠️ **Cloudflare "Purge Everything"** still required for the trail/training URLs (verified at origin).
- **GPS** has no fit/cushioning data — left on its existing panel by design.
- Staging still has the earlier scratch files (`preview-assessment.php`, `preview-assessment-block.php`);
  harmless, can be deleted.
