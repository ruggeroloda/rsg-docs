# List hero + share images, and one photo rule for reviews (17 Nov 2026)

**LIVE since 17 Nov 2026 22:41 UTC** — theme repo `7df6095` + `b651a25`, tag `live-20261117-224059` (Ruggero: "deploy"). Plugin rsg-core 0.8.0. Commands and file details: the Mac's `theme/RUNBOOK.md` ("One photo rule for reviews", "List hero + share images"). Decisions: `DECISIONS.md` 17 Nov.

## What went live
1. **List images** (`rsg-core/inc/list-image.php`) for every published `/top/` and `/guide/` list (5 today), built from the picks' photos:
   - Three styles, all built for every list, chosen per list in the edit screen ("Collage style" box: three page-hero previews with a radio, then Update). **A** grid (default; 1 row up to 3 picks, 2 rows up to 8, 3 above), **B** angled slices, **C** pick #1 big + 1- or 2-column rail. Works for 2+ picks.
   - **Page hero** 1600x900: photos only (no text, no numbers). It is the list's featured image (WordPress sizes + srcset; the templates already print it at the top). Alt: "Title: model 1, model 2, …".
   - **Share image** 1200x630 (og:image / twitter:image): photos + title + the RSG logo (white/light-blue version made for navy). A: one navy bar in the middle with title + logo side by side; B: navy card top-left (title, logo under it); C: title + logo on a fade at the bottom of the big photo. No tagline, no names, no numbers, no dates.
   - Rebuilt only when something that matters changes (picks, photos, names, title, style, version), after saving a list or a review used in a list (also via REST/Make), then the list page is purged. Never at page view.
2. **One photo rule for reviews, site-wide:** a review's photo = **its featured image** (then gallery, then photos in its text). Applies to the homepage (hero + cards), comparisons (top band, "Which one to buy", share images), /reviews/ hub share image and list images. **To change a shoe's picture anywhere: change the review's featured image.** All 172 changed comparison share images were rebuilt at go-live.

## Design rounds (Ruggero, 17 Nov evening)
- Round 1 (badges + names + tagline on shares): too much text. Final: heroes photos only; shares title + real logo only.
- Logo: no separate sticker box; it sits with the title. (First raster lost the second runner: ImageMagick drew the SVG clip masks as a white bar; fixed by dropping the masks before rendering.)
- A: solid top bar covered too much → centred bar over the row seam.
- Planned "Collage photo" picker per review dropped: the featured image is better than the first gallery photo, and one rule for the whole site is simpler.

## Checks (live, 17 Nov ~22:45 UTC)
- 5/5 lists built (style A), 172/172 comparison share images rebuilt, 0 errors; full purge.
- LCP warm 0.70–0.90 s at 390 and 1366 px (lists, homepage, a comparison); CLS 0–0.0001 (unchanged). Top image downloaded: the 768 px copy, 84–103 KB (old banners were 29–96 KB).
- og:image = the new share file on all lists; comparisons = the rebuilt share image.
- PHP log: nothing from the new code, no fatals. extractcheck: identical to before (no new missing items). gitleaks: no leaks; pushed to GitHub with tags.

## Rollback
- List images only: `wp rsg-list-image restore --all` (puts back the previous featured images, deletes files + meta), then remove the require line in `rsg-core.php`.
- Photo rule: deploy the previous tag's `rsg-core/inc/share-image.php` + `includes/rsg-home.php`, then `wp rsg-share build --all` and purge.

## Notes
- Generated files live in `uploads/rsg-list/` and `uploads/rsg-share/`, which the weekly uploads backup (year folders) doesn't include: after a restore, `wp rsg-list-image build --all --force` and `wp rsg-share build --all --force`.
- Nike Journey Run's featured image has text on it (the one exception found).
- The Pixian cut-outs ("professional image") are still used in some guide boxes and the review page's mini comparison table (not part of this change).
