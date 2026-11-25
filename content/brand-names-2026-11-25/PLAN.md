# Brand names: ASICS and HOKA (plan, 25 Nov 2026)

_Agreed with Ruggero 25 Nov 2026. **Nothing is built.** Separate from the shoe-categories plan (`content/categories-2026-11-25/PLAN.md`), except that the `/reviews/hoka/` address change rides on the same "old → new" redirect list on the same day._

## Why
The site spells two brands every which way, in page titles, headings, menus and brand pages. The brands write themselves "ASICS" and "HOKA" (HOKA dropped "One One"); Airtable's Brands table already says "ASICS" and "HOKA", so new reviews come in right. Goal: consistency and accuracy (trust); no traffic goal of its own.

## What we found (live site, 25 Nov; titles of 2,321 posts, reviews, comparisons, lists, guides, articles)
- **ASICS:** 134 titles "ASICS", **185 "Asics"** (150 old articles, 24 comparisons, 11 reviews). The brand term is named "Asics", so every brand page, menu, breadcrumb and review heading says "Asics".
- **HOKA:** "Hoka One One" 56 titles, "HOKA ONE ONE" 2, "Hoka ONE ONE" 2, "Hoka OneOne" 5, plain "Hoka" 38, "HOKA" 27. The brand term is named "Hoka One One" (slug `hoka-one-one`), so review headings read e.g. "Hoka One One Mach X review" while the page title says "Hoka Mach X Review".
- **Addresses:** 57 old review addresses contain `hoka-one-one` (e.g. `/2017/06/hoka-one-one-clifton-4-review/`). The brand listing is `/reviews/hoka-one-one/all/` plus its type and family pages: 43 addresses, **68 Google clicks in 90 days** (Search Console to 22 Nov). New reviews already get `hoka-…` addresses (title from Airtable).
- **Searches:** queries containing "hoka one one": 171 clicks / 28.9k impressions in 90 days, almost all old models (Challenger ATR 6, Stinson, Clifton 8, Carbon X, Speedgoat 2/4); queries with "hoka" only: 6,809 clicks / 464k impressions. So "One One" is ~2.5 % of HOKA search clicks.
- **Where the names live:** brand term name (headings, brand pages, menus, breadcrumbs, schema), `post_title`, the SEO title meta (`title`, read by the theme's SEO module for `<title>`), comparison titles. Theme code also uses the slug `hoka-one-one`: `includes/mega_menu_review.php` (brand list, logos), `includes/rsg-home.php` (brand tiles), `includes/comparison-snapshot.php` (brand-once logic, comment).
- **Price engine (DFS) reads brand names:** `dfs/app/dfs/rulebuild.py` (aliases "hoka one one" → "hoka", brand words) and `dfs/app/dfs/shoes.py` (brand list has both "Hoka" and "Hoka One One"). Matching looks case-insensitive, **not verified**.

## Decisions (Ruggero, 25 Nov)
1. Every "Asics" → **ASICS**; every Hoka variant ("Hoka One One", "HOKA ONE ONE", "Hoka ONE ONE", "Hoka OneOne", "Hoka") → **HOKA**. In: brand term names, post titles, SEO titles, comparison titles.
2. **Body text untouched** (old articles keep "Hoka One One" in their text; many are prune candidates).
3. **Old review addresses never change** (the 57 `hoka-one-one-…` ones stay).
4. **Brand listing address → `/reviews/hoka/`** (cleaner, shorter, accurate), with 301s from `/reviews/hoka-one-one/…`, done with the categories' "old → new" list on the same day.
5. Treated separately from the categories plan (Fable had already reviewed that one).

## Plan
1. **Staging:**
   - Rename the brand terms: "Asics" → "ASICS", "Hoka One One" → "HOKA" (name only; slug stays for now).
   - Script (WP-CLI) on post titles + SEO title meta + comparison titles, whole-word replace of the variants above. Direct field update so `post_modified` does not move (a spelling fix is not new content); check a sample. Purge after.
   - List every changed title (before / after) in this folder for Ruggero to scan.
2. **DFS test bench:** run the rule build with the renamed brands; offers per HOKA and ASICS shoe must be identical before and after. If not, fix the aliases first.
3. **Live** on Ruggero's "deploy to live", **after Cyber Monday** (1 Dec or later). No address changes in this step.
4. **With the categories go-live (2–3 Dec):** brand slug `hoka-one-one` → `hoka`; `/reviews/hoka-one-one/<anything>/` → `/reviews/hoka/<same>/` in the "old → new" list (incl. page N → page 1); theme slug references updated (mega menu, homepage tiles, logo file name if it follows the slug); site search alias check.
5. `rsg.change_log` rows for both steps; `DECISIONS.md` lines.

## Risks
- **Letter case:** none for Google.
- **Dropping "One One" from titles:** at most part of ~171 clicks / 90 days on old models; the brand is understood as the same, and the old reviews keep the words in address and text.
- **Brand listing move:** 68 clicks / 90 days, covered by 301s.
- **DFS matching:** the one real risk; step 2 checks it before anything goes live.

## Not checked yet
DFS matching after the rename; other theme places that print the brand name from a hard-coded string; Airtable "Running Shoes" names of old rows ("Brand + Shoe Name") — not shown on the site, left as they are.
