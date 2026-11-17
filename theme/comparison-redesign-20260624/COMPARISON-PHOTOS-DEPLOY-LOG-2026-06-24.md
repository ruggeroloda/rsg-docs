# Deploy Log — Comparison page PHOTOS (sole / upper / verdict)
**Date:** 2026-06-24 · **Env:** Rocket.net (`131.153.143.194`, user `jfx9wij`) · **Theme:** `rsg_2022`
**Status:** ✅ Deployed to live, **flag-gated to the same 3 pilot comparisons only**.
⚠️ Manual step outstanding: **Cloudflare "Purge Everything"**.

---

## 1. What shipped
Real human photos pulled from each shoe's **review** and shown side-by-side ("Treatment A"
compare band) on the redesigned comparison pages. Renders **only when `$rsg_new_compare` is set**
(`comparison_redesign` meta = 1, or `?compare=1` preview) — every other comparison is unchanged.

Three bands added, each two photos side-by-side with a small "VS" and shoe-name captions:
- **Sole unit** section — first image from each shoe-review's `sole_unit` ACF field.
- **Upper unit** section — first image from each shoe-review's `upper` ACF field.
- **Which one to buy** (verdict) section — each shoe-review's **WordPress featured image**
  (the hero photo at the top of the review) as a visual capstone at the decision/buy point.

Image handling: resolves the WP attachment (from the `wp-image-{ID}` class in raw meta, or the
post thumbnail for the featured band) and renders via `wp_get_attachment_image(…, 'large')` →
responsive `srcset`, `loading="lazy"`, `decoding="async"`, served at 633px (not the 1920px
original). Each image gets a **contextual alt** ("HOKA Clifton 10 sole unit", "Hoka Bondi 9 review",
etc.) — a small SEO bonus, since the source images carry generic `alt="Picture of …"`.

Robustness: a section renders nothing if neither shoe has an image; degrades to a single centred
cell if only one shoe has one; collapses to one column under 480px. No new attachments/media —
existing review images are reused.

### Root-cause fix worth remembering
First pass parsed `get_field('sole_unit')` (the rendered ACF WYSIWYG). On the **frontend** that
output runs the `the_content` filters, which **strip the `wp-image-{ID}` class and rewrite the
src** → attachment lookup failed → fallback emitted a mangled 23×17px URL (WP Rocket region).
Fix: parse the **raw** `get_post_meta()` value (class intact), then `wp_get_attachment_image`.
Confirmed responsive output on staging + live afterward.

## 2. Files changed (live theme `rsg_2022`)
| File | Change |
|---|---|
| `includes/comparison-redesign-block.php` | **+5 helper fns** inserted before the early-return guard: `rsg_cr_first_img` (sole/upper extractor), `rsg_cr_featured_img` (review thumbnail), `rsg_cr_render_band` (shared Treatment-A renderer + one-time CSS), `rsg_cr_photo_band`, `rsg_cr_featured_band`. Rest of file unchanged. |
| `single-comparison.php` | **3 one-line inserts** (each guarded `if ( ! empty($rsg_new_compare) && function_exists(...) )`): sole band before the `sole_units` editorial div, upper band before the `upper` editorial div, featured band before the `which_to_buy` editorial div. |

Diff was confirmed surgical (live → new) before upload. Non-pilot pages: byte-identical render
(helpers only defined when the redesign block is included; template calls are flag-guarded).

## 3. Verification
- **Lint:** `ea-php83 -l` and `ea-php74 -l` clean on both files (staging + live).
- **Render (staging `?compare=1`, then live `?cb=`):** all 3 pilots = **6 band imgs** each
  (sole 2 + upper 2 + verdict 2); responsive `srcset` + contextual alt present.
- **Control:** non-pilot `asics-nimbus-28-vs-hoka-bondi-9` = **0 band imgs** (correctly gated).

Pilots (live):
- `/comparison/hoka-clifton-vs-hoka-bondi/` (28982 · reviews 48843 / 48375)
- `/comparison/asics-nimbus-vs-asics-kayano/` (28311 · reviews 50068 / 49463)
- `/comparison/brooks-ghost-vs-brooks-adrenaline/` (28070 · reviews 49582 / 50052)

## 4. Backups & rollback
- **Live backup dir:** `~/rsg_backups_20260624_comparison-photos/` (both pre-change files).
- **Per-file:** `single-comparison.php.bak-20260624-160907`,
  `includes/comparison-redesign-block.php.bak-20260624-160907` (next to each file).
- **Rollback:** restore the two files from either backup, re-lint, re-upload, purge Cloudflare.
  (Bands can also be turned off per page by removing the `comparison_redesign` meta — that reverts
  the whole redesign for that page, not just photos.)
- **Staging backups (same change):** `*.bak-20260624-155456`.

## 5. ⚠️ Manual step
**Cloudflare "Purge Everything"** (Rocket.net panel) — edge cache `s-maxage` ~30d. Until purged,
the 3 pilot URLs may keep serving pre-photo HTML. (This can be the same purge already pending for
the 2026-06-23 schema fix and the earlier 2026-06-24 comparison redesign.)

## 6. Notes / possible follow-ups
- The source review images carry generic alt (`Picture of <model>`); the bands now emit better
  contextual alt at render time, but the **stored** attachment alt is still generic — ties into
  the deferred site-wide **alt-text backfill** (highest-value images = exactly these).
- Featured/sole/upper photos are pulled live from the linked reviews, so they **auto-update** if a
  review's images change. No per-comparison image management needed.
- If rolled wider: extractor already handles missing sections/single-image gracefully.
