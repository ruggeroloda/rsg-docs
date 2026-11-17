# Deploy Log — Comparison redesign ACF fields (wp-admin editable)
**Date:** 2026-06-28 · **Env:** Rocket.net (`131.153.143.194`, user `jfx9wij`) · **Theme:** `rsg_2022`
**Status:** ✅ Deployed to live. Admin-only change (ACF editor UI) — **no front-end output, no Cloudflare purge required.**
Closes TECHNICAL backlog item **2(c)** + the "ACF field registration" item from the 2026-06-24 redesign log.

---

## 1. Problem
The comparison-redesign controls (`comparison_redesign` flag + `quick_verdict`, `buy_if_1`, `buy_if_2`)
were only ever written as **raw post meta via WP-CLI** for the 3 pilots. They were never part of any
ACF field group, so wp-admin showed **no editing UI** and there was **no checkbox to force the new
design** when creating a comparison.

## 2. What shipped
A **code-registered ACF local field group** (no DB change), so the fields are now editable in the
comparison editor. ACF is bundled in the theme (ACF **Pro 5.7.5**, `includes/plugins/acf3/`); every
other field group on this site lives in the DB (ACF UI). This one is intentionally in code so it
deploys through the normal staging→lint→live flow, is version-controlled, and can't be lost.

Field group **"Comparison — New Design"** (`group_rsg_comparison_redesign`), location `post_type == comparison`,
position `acf_after_title`:

| Field name (= meta key) | Type | Label | Notes |
|---|---|---|---|
| `comparison_redesign` | true_false | Use new comparison design | default OFF; toggles the redesigned layout |
| `quick_verdict` | textarea | The verdict, in short | `new_lines=wpautop`; conditional on toggle ON |
| `buy_if_1` | text | Buy the FIRST shoe if… | maxlength 160; conditional on toggle ON |
| `buy_if_2` | text | Buy the SECOND shoe if… | maxlength 160; conditional on toggle ON |

Field **names are identical to the meta keys the redesign already reads** (`single-comparison.php`
reads `comparison_redesign` via `get_post_meta` truthy; `comparison-redesign-block.php` reads
verdict/buy-if via `rsg_cr_meta()` = `get_field` with `get_post_meta` fallback) → fully backward
compatible with the existing WP-CLI pilots.

## 3. Files changed (live theme `rsg_2022`)
| File | Change |
|---|---|
| `includes/rsg-comparison-acf-fields.php` | **NEW** — `acf_add_local_field_group()` on `acf/init`. |
| `functions.php` | +1 line: `require_once('includes/rsg-comparison-acf-fields.php');` after the `rsg-wizard-capture.php` require (line 822). |

Source copy also kept at `comparison-redesign-20260624/rsg-comparison-acf-fields.php`.

## 4. Verification
- **Staging first:** uploaded, `ea-php83 -l` + `ea-php74 -l` clean on both files; group registers,
  all 4 fields resolve by name/key, group attaches to the comparison editor, `update_field`/`get_field`
  round-trip OK (verdict returns wpautop-wrapped, as the block renders). Test meta cleaned up.
- **Live:** group registered (`bool(true)`); all 4 fields resolve; group present on post 28982 editor;
  **all 3 pilots read correctly, no value loss** — 28982/28311/28070 each `redesign='1'`, verdict
  present (191/237/266 chars), buy_if_1/2 set. Live file byte-identical to staging (`cmp`).

## 5. Backups / rollback
- **Live backup:** `…/themes/rsg_2022/functions.php.bak-acf-20260628-122927`.
- **Rollback:** restore that `functions.php` backup (drops the require) — or just delete the require line;
  the new file then loads nothing. Optionally remove `includes/rsg-comparison-acf-fields.php`. Lint after.
  No DB to revert (zero DB writes). Existing pilot meta is untouched either way.

## 6. How to publish a new comparison in the new format (for the user)
1. wp-admin → Comparisons → Add New (or edit an existing comparison).
2. Under the title, toggle **"Use new comparison design"** ON.
3. Fill **The verdict, in short** + **Buy the FIRST/SECOND shoe if…**.
4. Publish. (Photo bands auto-pull from each linked review's `sole_unit`/`upper`/featured image.)
5. New URL serves the redesign at origin immediately; **purge Cloudflare** so edge serves it to visitors.

## 7. Notes / not done
- The `MYSQL_OPT_RECONNECT` line in WP-CLI output is a harmless PHP 8 mysqli deprecation notice.
- Fields are **code-defined** → not editable in the ACF UI (Custom Fields screen) by design; values
  still save to post meta normally. To change a field definition, edit the PHP file and redeploy.
