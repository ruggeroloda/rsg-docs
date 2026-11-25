# Brief: build the shoe categories on staging (new chat)

**Status 26 Nov 00:25: steps 1–6 done on staging (theme branch `categories` `2a15a83`); next step 7 after the 1 Dec n8n switch, step 8 before go-live, step 9 8–10 Dec. Details: `ROADMAP.md`, `theme/RUNBOOK.md`.**

_Written 25 Nov 2026, 23:15, after the Fable review and Ruggero's answers. **Live date: Tue 8 – Thu 10 Dec, only on Ruggero's "deploy to live".** Until then everything is staging._

## Read first
1. `README.md`, `ROADMAP.md`, `STRATEGY.md` (goal 3), `theme/RUNBOOK.md`, `theme/CLAUDE.md`.
2. `content/categories-2026-11-25/PLAN.md`: the last section, "Changes after the Fable review", overrules the text above it.
3. `REVIEW-FABLE.md` (same folder): sections 1, 4 and 7 and "Follow-up" are what the build must respect.
4. `category-mapping-v2.csv` is the data (1,365 reviews). `category-mapping.csv` is superseded; `map2.py` holds the line rules.

## Decided (do not reopen)
- Scheme: Surface (Road / Trail / Gym) + one category + Stability flag. Road: Daily trainer, Max cushion, Performance trainer, Race. Trail: plain Trail, Trail – Max cushion, Trail – Race.
- Lightweight, Minimalist and Motion control **stay** as hidden old tags: out of the menu, the filter and the card label; their listing pages and URLs stay alive; no new review gets them. Only `cushioning` → `daily-trainer` redirects (also `/reviews/<brand>/cushioning/` and `/page/N/`).
- No old term is deleted in the go-live step. Cushioning's term goes after the 4-week read.
- Max cushion listing text (Ruggero's words, use as written): "Shoes with thick, soft soles that are meant to maximise comfort while staying overall lightweight". Which lines are in: the name list in `PLAN.md` decision 4, with Hurricane from 24.
- Non-review posts lose their type tag: 11782 (Best Cushioning Running Shoes for 2013), 11853 (Best Running Shoes for Flat Feet – 2013), 26389 (Nike Unveils New Joyride Cushioning), and the "Energy Boost 2 preview" post. Check on staging for others (posts with a `shoes_type` term that are not reviews) and show Ruggero the list before removing.

## Steps (one at a time; show Ruggero the result of each before the next)
1. **Staging data.** Copy `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` and write the restore command into `theme/RUNBOOK.md`. Record `post_modified` of 20 sample reviews.
2. **Terms script (WP-CLI, staging).** From the v2 CSV: create `daily-trainer`, `max-cushion`, `performance-trainer`, `trail-max-cushion`, `trail-race`; rename the labels of `racing` (Race) and `training` (Gym & training); per review set category + `road` / `trail` / `training` + `stability` (Motion control reviews also get `stability`); remove the `cushioning` relation; leave `lightweight`, `minimalist`, `motion-control` relations as they are. Then: `post_modified` unchanged on the sample; term counts equal the CSV totals (road 771 / 43 / 176 / 135, trail 189 / 14 / 16, gym 21, stability 306).
3. **One type list in the theme** (slug → kind: surface, category, stability, old tag; label; menu order; page title; intro text) and a helper that returns a review's category from it. Replace every "first type of the review" read: `single-reviews_road.php` (~593, ~1500), `include-single-review.php` (~680–727), `single-reviews_gps.php` (~591), `single-review.php`, listing card label (`includes/reviews/listing.php`), comparison "Shoe type" row (`rsg_cmp_main_type()`), "similar shoes" (`includes/reviews/similar.php`), schema (`includes/rsg-schema.php`), mega menu (`includes/mega_menu_review.php`), homepage "Reviews by type" tiles (`includes/rsg-home.php`), listing filter and titles (`reviews_search.php`, `functions.php` ~684–725).
4. **Listing pages.** Own page title per type (not "Race Running Shoes Reviews"); intro text for the 5 new type pages; `cushioning` → `daily-trainer` in the old → new list before the existing fallback; the index rule (type or family listing under 3 reviews → `noindex, follow`); 0-review brand pages (`montrail`, `ryka`, `sketchers`) → 301 `/reviews/`.
5. **Show Ruggero on staging, phone next to desktop:** menu, filter, homepage tiles, a listing of each new type, a road review, a trail review with the Stability flag, a comparison, an old Minimalist review and its listing.
6. **Airtable (Running Shoes):** fields Surface, Category, Stability; fill the 14 shoes to assign; hide "Shoe Category" (do not delete).
7. **n8n review flow** (after the 1 Dec switch, staging first): read Surface / Category / Stability and send the terms to WordPress.
8. **Baseline, before go-live:** Search Console clicks per group from the review (brand × type 2,460; old tags 813; cushioning 152, 90 days to 22 Nov), the three query groups (minimalist, lightweight / lightest, motion control), listing → review clicks and menu clicks in Umami.
9. **Go-live 8–10 Dec, on "deploy to live" only:** table copy on live, terms script, theme deploy, purge the whole cache (every review's label and "similar shoes" change), `rsg.change_log` row, `STRATEGY.md` change-log row (goal 3), read after 4 weeks.

## Not checked by the review (check during the build)
`rsg-schema.php`, mega menu and homepage tile code, site search routing for type words, links to `/reviews/all/cushioning/` inside article text (database search on staging).

## Rules
`CLAUDE.md`. One chat on the theme repo at a time (another chat worked on it on 25 Nov: check it is closed, or use a `git worktree`). End with the `rsg-close-out` skill.
