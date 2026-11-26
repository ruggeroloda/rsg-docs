# Next session: automatic hero + share images for /top/ and /guide/ (17 Nov 2026)

**DONE — live since 17 Nov 2026 22:41 UTC. What was built (and what changed from this brief: no names/numbers/tagline on any image, real logo, no per-review "collage photo" field because a review's photo is now its featured image site-wide): `theme/LIST-IMAGES-2026-11-17.md`.** The prompt below is kept for history.

Prompt to paste into a new chat in the RSG project:

```
Task: automatic hero + share images for /top/ and /guide/ pages, in three styles I can choose from per page (RSG theme repo, rsg-core plugin). Staging only; live only after I say "deploy to live".

Read first: theme/RUNBOOK.md (access, deploy.sh, git), theme/archive/COMPARISON-RESKIN-2026-11-15.md (C6 share image), rsg-core/inc/share-image.php (the comparison generator to reuse: fonts, cover crop, hash/rebuild, save_post hook, WP-CLI, restore), theme/SEO-AUDIT-2026-11-17.md. Mockups + reference renderer: theme/_work/list-images-2026-11-17/ (directions-board.jpg, render.py, A-/B-/C-*.jpg). Fonts and colours as share-image.php (Big Shoulders Black, Public Sans Bold; ink #13263B, blue #1570C9, yellow #FFC83D).

Decisions already made (Ruggero, 17 Nov):
- Three styles, all built for every list: A = grid (justified rows, every pick equal), B = angled vertical slices, C = hero pick + rail. Default A.
- Choosing: a "Collage style" setting in the list's edit screen showing the three previews side by side (radio with thumbnails); save = that style becomes the page hero + share image. Also WP-CLI `use <id> A|B|C`.
- Text: the PAGE HERO carries no title, no names and no categories: photos + small number badges only (the H1 and the list sit right under it; text would be unreadable at ~390 px on phones). The SHARE image (og:image/twitter:image, 1200x630) carries the title band, "N picks · bought and tested", the RSG brand label, number badges and the shoe model names; no category labels. No dates anywhere.
- Must work for any number of picks (2-10): A = 1 row up to 3, 2 rows up to 8, 3 rows above; B = slice width adapts, names wrap to 2 lines; C = pick #1 big + 1- or 2-column rail.

What to build:
1. Generator in rsg-core (e.g. inc/list-image.php) for post types `top` and `guide`. Picks = ACF repeater review_sections_2023 (count in meta review_sections_2023; per row: _winner = review post id, _title = category).
2. Photo per pick: new optional review field "collage photo" (attachment); empty = rsg_review_photo_id($review, 0). The first gallery photo is often a close-up or an outsole (Propel v5, Winflo 11, Bondi 9 in the mockups), so: WP-CLI `show <id>` lists the photo each pick uses, and the edit-screen previews make bad ones obvious.
3. Model name = review title without " Review" and anything after it ("Nike ZoomX Vaporfly 4 Review – Leaner…" → "Nike ZoomX Vaporfly 4"); never a doubled brand ("Puma PUMA …" → "PUMA …"). Title = in_page_title, fallback post title.
4. Outputs per list: 3 heroes 1600x900 JPEG (16:9; the chosen one becomes the featured image = the LCP image at the top of the page; WP sizes + srcset; aim ≤ 250 KB) and 3 shares 1200x630. Keep the previous featured image id in meta for `restore`. Alt text: "<Title>: <model 1>, <model 2>, …".
5. Rebuild only when picks, photos, names, title, chosen style or a version constant change (hash in meta): on save_post of the list (after ACF saves), when a review used in any list is saved (incl. its collage photo), and by WP-CLI build [<id>…] [--all] [--force] [--dry-run], use, show, restore.

Before building the code: re-render the three styles with the text rules above (hero clean, share with names) for a 5-pick and a 6-pick list and show me hero + share, desktop and phone size, so I can confirm the look.
Checks before asking me for "deploy to live": all /top/ and /guide/ posts built on staging, previews work in the edit screen, LCP and CLS unchanged or better, og:image correct, no PHP warnings, extractcheck no new missing items.
```
