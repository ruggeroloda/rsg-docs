# Image ALT-text pass — 4 most recent reviews (2026-08-02)

**What & why.** All review photos site-wide carry a generic, identical alt `Picture of <model>`
(6,988 attachments). This is an SEO/accessibility experiment on the 4 most recent road reviews:
replace the generic alt with unique, descriptive per-image alt (view type + colourway + feature).
No captions, no theme change — alt only. This is a pilot for a possible site-wide rollout.

**Scope (DB-only change to `_wp_attachment_image_alt`, LIVE):**
| Review | Post ID | Attachment IDs | # imgs |
|--------|---------|----------------|--------|
| Nike Pegasus 42 | 50267 | 50268–50289 | 22 |
| ASICS Gel Cumulus 28 | 50292 | 50293–50311 | 19 |
| Brooks Glycerin 23 | 50237 | 50238–50260 | 22 |
| Saucony Ride 19 | 50194 | 50195–50217 | 22 |

Total 84 attachments (incl. the white-background product shot for each). Verified: 0 remaining
`Picture of%` alts in ID range 50194–50311 after apply.

**Method.** Alt text drafted by viewing each shoe's images (ImageMagick contact sheets pulled from
the server). Applied as a single batched SQL `UPDATE` on `{prefix}postmeta` via `wp db query`.
This touches the media library only (no theme files, no post content).

**Backup / rollback.** Pre-change alt values for all 84 IDs saved to
`theme/_work/alt-backup-4reviews-20260802.tsv` (in this folder). To roll back: restore each `post_id`'s
`meta_value` from that file (all were `Picture of <model>`). The full new mapping is in
`alt_map.tsv` (outputs).

**⚠️ USER ACTION — purge Rocket.net cache ("Purge Everything")** so the new alt serves to visitors
and to Googlebot/image crawlers. Until purged, cached HTML keeps the old alt. (Object cache + WP
Rocket page cache already cleared server-side; the Rocket.net managed edge/page cache is the only
layer left and only the panel purge clears it.)

---

## Follow-on: gallery per-image alt (theme fix, 2026-08-02)

**Problem found while auditing lazy loading.** The review gallery (`$images = get_field('gallery')`,
an ACF Gallery) rendered EVERY gallery `<img>` with `alt` taken from the *featured* image
(`get_post_thumbnail_id($post_id)`) — so all the per-image alt above would NOT surface in the top
gallery; only the in-body editor images (which read their own alt via WP core) would. The featured
"Photo 1" was already correct.

**Fix.** `single-reviews_road.php` — the 6 gallery-LOOP `<img>`/`<noscript>` tags that iterate
`$images[$i]` now use `alt="<?= esc_attr($images[$i]['alt']); ?>"` (each image's own alt). The 3
featured-image (`$attachment`) alt lines were intentionally left as-is. Covers all 4 recent reviews
(all `reviews_road`). Trail/GPS/training templates share the same pattern — NOT yet fixed (no recent
reviews there; follow-up if desired).

**NOT a broken-srcset fix.** Earlier I suspected a malformed `srcset`; on reading the template it's
valid (multiline). Only cosmetic cruft is a redundant empty `class=""` — left alone.

**Deploy.** Staging-first: edited local copy → uploaded to staging → lint ea-php83 + ea-php74 (clean)
→ smoke-test render HTTP 200, no fatals → backed up live (`single-reviews_road.php.bak-altfix-20260802`
on both staging and live) → uploaded to live → lint clean on live → verified file-on-disk + data
layer (`wp eval` returns unique per-image alt). Local pristine copy: `road.orig.php` (outputs); edited:
`road.php`.

**Rollback:** `cp single-reviews_road.php.bak-altfix-20260802 single-reviews_road.php` in the live
theme dir.

**Verification note.** Final rendered HTML can't be confirmed until the Rocket.net "Purge Everything"
runs (managed page cache). File + data layer verified correct pre-purge.

**Notes / open.**
- Several in-body photos rendered *blurred* in the source-page PDF (Intro, First Impressions, Sole
  Unit). Most likely a lazy-load placeholder caught mid-load in the screenshot — worth a quick
  visual check on the live page to confirm the full-res image swaps in.
- If this moves image impressions / rankings, the same approach scales to the other ~6,900 generic
  alts (that's where volume matters). Alt is a minor signal; expect small, hard-to-attribute effect
  on a single-page basis.
- Measurement: watch Search Console → Performance → Search type "Image" for these URLs over 4–6 wks.
