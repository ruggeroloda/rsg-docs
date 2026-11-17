# Deploy Log — Comparison page redesign (PILOT)
**Date:** 2026-06-24 · **Env:** Rocket.net (`131.153.143.194`, user `jfx9wij`) · **Theme:** `rsg_2022`
**Status:** ✅ Deployed to live, **flag-gated to 3 pilot comparisons only**.
⚠️ Manual step outstanding: **Cloudflare "Purge Everything"**.

---

## 1. What shipped
A redesigned comparison layout that renders **only when a per-post flag is set**
(`comparison_redesign` meta = 1, or `?compare=1` for preview). Every other comparison
renders byte-identical to before. The redesign replaces the old "Similarity and differences"
table with a unified module and adds data-driven decision aids:

- **Breadcrumb** (visible, redesigned pages only) — Home › Comparisons › <title>.
- **In-page anchor nav** (Snapshot · Scores · Sole · Upper · Verdict).
- **"The verdict, in short"** — author TL;DR (`quick_verdict` meta).
- **Unified shoe module** — card header (image, name→review, score ticket, price, See deal,
  "Buy if" = `buy_if_1`/`buy_if_2`) laid out as a **shared row grid** so long/wrapping names
  never break column alignment, with the spec rows directly beneath each shoe column.
  Preserves brand/shoe-type/review internal links.
- **"What matters most to you?"** chooser — deterministic from sub-scores + weight (keyboard-accessible).
- **Score breakdown** (`<h2>`) — sub-score **radar** (6 axes; +traction on trail) + **"where each wins"**
  panel (auto from sub-score deltas + weight). Drop is treated as **neutral** (no winner note).
- Card images lazy-load and reserve height (CLS-safe). Uses theme components (score ticket,
  `btn-warning`, CSS vars) — no new visual language.

Editorial (intro, sole unit, upper, which-to-buy) and "Best price online" are **unchanged** and
still lead/anchor the page.

## 2. Files changed (live theme `rsg_2022`)
| File | Change |
|---|---|
| `includes/comparison-redesign-block.php` | **NEW** — the redesign renderer. |
| `single-comparison.php` | Early `$rsg_new_compare` flag; conditional breadcrumb; conditional `include` of the block in place of the old table (`if(!$rsg_new_compare)` wraps the old "Similarity and differences" table); anchor `id`s added to Sole/Upper/Which-to-buy headings. |

Non-pilot pages differ from the old version only by: harmless heading `id` attributes + an unused
`$rsg_new_compare=false` var (verified: old table renders, no breadcrumb/redesign).

## 3. Pilots (flag set via WP-CLI, + author copy)
Chosen from GSC (last 3 mo, page-1-not-#1, evergreen, full sub-score data):
- **28311** `asics-nimbus-vs-asics-kayano` (Nimbus 28 vs Kayano 32) — was pos ~4.5.
- **28982** `hoka-clifton-vs-hoka-bondi` (Clifton 10 vs Bondi 9) — was pos ~4.8.
- **28070** `brooks-ghost-vs-brooks-adrenaline` (Ghost 17 vs Adrenaline GTS 23) — was pos ~4.8.

Each got `comparison_redesign=1`, `quick_verdict`, `buy_if_1`, `buy_if_2` (distilled from each page's
own `which_to_buy` + each shoe's `who_should_buy_it`). These are **new meta keys** — additive, no
existing content modified.

## 4. Verification (live, cache-busted at origin)
- `ea-php83 -l` + `ea-php74 -l` clean on both files.
- Deploy bytes IDENTICAL to staging (`cmp` both files).
- All 3 pilots: 0 PHP errors, verdict present, radar (7 polygons), chooser present, breadcrumb present,
  old table absent, `$20` renders correctly.
- Non-pilot (`brooks-ghost-17-vs-brooks-ghost-16`): unchanged (old table present, no breadcrumb/redesign).

## 5. Backups
- **Server (live, pre-deploy):**
  - dir `~/rsg_backups_predeploy_compare_20260624-141615/single-comparison.php`
  - file `…/themes/rsg_2022/single-comparison.php.bak-compare-20260624-141615`
- **Project:** `comparison-redesign-20260624/` (this log). Block source also at outputs scratch.

## 6. Outstanding manual step
**Cloudflare → "Purge Everything"** (Rocket.net panel) — edge `s-maxage` ~30d; pilot URLs serve old
HTML to real visitors until purged. Verified fine at origin via `?cb=`.

## 7. Rollback (two levels)
- **Instant, per page (no file change):** `wp post meta delete <id> comparison_redesign` on any/all of
  28311 / 28982 / 28070 → that page reverts to the old layout immediately (then purge Cloudflare).
- **Full template:** restore `single-comparison.php.bak-compare-20260624-141615`; the new include then
  goes unused (can be left or deleted). Lint + purge.

## 8. Not done / next
- **Conversion pass** (deferred by user): sticky mobile buy-bar, "best price + retailer" in card,
  "price checked today" trust/freshness line.
- **ACF field registration** for `quick_verdict` / `buy_if_1/2` / `comparison_redesign` so they're
  editable in wp-admin (currently set via WP-CLI; wp-admin 403s external IPs — confirm access first).
- **Site-wide multiple-H1 fix** (backlog #4) — editorial section headings are still `<h1>` on all
  comparison pages; do as a deliberate theme-wide change, not half-isolated here.
- **Measurement:** track the 3 pilots' position/CTR/clicks vs a matched control set of similar
  untouched comparisons over 4–6 weeks before any wider rollout.
