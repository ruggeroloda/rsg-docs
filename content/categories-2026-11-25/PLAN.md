# Shoe categories: plan (25 Nov 2026)

_Agreed with Ruggero in one chat on 25 Nov 2026. **Nothing is built.** Before anything is executed, Ruggero has the plan reviewed by Fable in a new chat (`BRIEF-FABLE-REVIEW.md` in this folder). Data and script: `category-mapping.csv` (1,359 reviews, one row each), `map.py` (makes the CSV from the live site's REST data)._

## Why
- Visitors meet the categories in two places: the **listing filter** (the only filter besides brand) and the **Reviews mega menu** (plus the homepage "Reviews by type" tiles).
- The categories date from 2009 and no longer match the market: "Cushioning" really means "neutral daily trainer"; max cushion, super trainers and tempo shoes have nowhere to go; Minimalist and Motion control are dead groups; "Road", "Trail" and "Training" sit in the same list as "Stability", so a shoe is "Road – Stability" (wrong hierarchy).
- Three overlapping systems that don't sync: Airtable "Shoe Category" (9 real values + 23 junk combination options like "Cushioning; Racer"), WordPress `shoes_type` (11 terms), and the review's "Good for" (Usage). The Airtable value only decides road / trail / training in the review flow; the real type is added by hand in WordPress (e.g. PUMA Velocity Nitro 4 is tagged "Minimalist").
- Goal served (`STRATEGY.md`): **3** (the undecided reader opens a second page: useful menu, listings, "similar shoes"). Ruggero's fear was SEO damage from a big URL change; most of it does not apply (below).

## What we found out
- **Review URLs carry the post type, not the category** (`/reviews/road/<slug>/`). Changing categories moves no review.
- **Setting terms by script does not touch a review's date** (only editing and saving does). To be proved on staging before anything else.
- **What changes is the category listings only:** type pages and brand × type pages = 506 URLs (incl. page 2, 3…), **2,970 Google clicks in 90 days = 1.6 % of the site** (Search Console to 22 Nov). Stability and Trail carry most of it, and those URLs stay.
- **Listing URLs are `/reviews/<brand>/<type>/`, `all` = "any"** (no redirect; it has been like this for years): `/reviews/all/stability/` and `/reviews/adidas/all/` are the canonical forms; `/reviews/adidas/stability/` canonicals to `/reviews/adidas/all/`; short forms `/reviews/stability/`, `/reviews/adidas/` canonical to `/reviews/` (a small existing flaw, noted, not part of this); family pages (`/reviews/nike/pegasus/`) share the second slot with types.
- **The listing code already 301s an unknown type** to the brand page or `/reviews/` (`reviews_search.php`, ~lines 70–85). Retiring old types therefore redirects by itself; we only add an "old → new" list before that fallback.
- **Outside view** (read 25 Nov): Brooks (Cushion / Energize / Speed, neutral vs support as a second axis), ASICS (cushioning, stability, trail, speed), Running Warehouse (Daily trainer: standard, premium, max cushion, energetic; Performance trainers: speed or long-run focus; Super shoes; trail: daily, max cushion, performance, race), REI (cushioning and support as separate filters; trail by terrain), RunRepeat (daily / tempo / race; "maximalist" is a stack tag). Common core: daily, max cushion, speed/performance, race, with stability as a second dimension. What dates a list is technology names (minimalist then, "super trainer" now).

## Decisions (Ruggero, 25 Nov)
1. **Three levels.** Surface: Road / Trail / Gym (the post type; unchanged). Category: one per shoe. Stability: a flag on top.
2. **Category = the kind of run the shoe is for; technology (plate, super foam, rocker) = facts, never a category.**
3. **Road categories:** Daily trainer · Max cushion · Performance trainer · Race. "Performance trainer" (Running Warehouse's term) covers tempo shoes and super trainers (Endorphin Speed, Boston, Mach X, Superblast, Megablast). "Speed trainer" rejected: super trainers don't fit it.
4. **Max cushion is narrow:** only lines sold as the brand's extreme (Bondi, More, Skyward, Glycerin Max, Ghost Max, Gaviota, Paradigm, Hurricane 22+, Invincible, MagMax, Cloudeclipse, Cloudsurfer Max, GlideRide Max, Neo Zen, GOrun Max Road, Balos, Vomero 18+ incl. Plus and Premium). "Premium" is a tier, not a category: Nimbus, Glycerin, Triumph, 1080, Clifton, Cloudmonster, Kinsei Max = Daily trainer. **Tie-breaker: when the brand has an explicit max tier, the brand wins over the shop** (Nike: "Vomero is maximum cushioning" since Nov 2024; Running Warehouse lists the Vomero 18 as a standard daily trainer). Vomero 17 and older = Daily trainer.
5. **Stability** is a flag (Kayano, Arahi, Adrenaline… = Daily trainer + Stability; Gaviota, Hurricane 22+ = Max cushion + Stability). Old Motion control → Stability.
6. **Trail:** Daily trainer (= plain "Trail"), **Trail – Max cushion** (Mafate, Stinson, Olympus, Trabuco Max, More Trail, Enduris), **Trail – Race** (S-Lab, Sense Pro, Zinal, Tecton, Ultrafly, Vectiv Pro, Pulsar). Caldera, Hierro, Ultra Glide = Daily (narrow rule). Terrain stays a review field, not a category.
7. **Gym:** no categories.
8. **"Good for" (Usage) is dropped for new reviews**; the category replaces it. Old data stays where it is.
9. **Heel stack is not a rule** (tested: it made the Endorphin Pro 3, Boston 11, Novablast 5 and Mach X 3 "max cushion").
10. **Wrong old tags are fixed by name** (35 reviews; e.g. Adios Pro, Endorphin Pro 3, Carbon X 3 → Race; Boston, Mach X, SuperComp Trainer, Deviate Nitro, Tempo Next%, Evo SL, Neo Vista, Switch FWD, Sonicblast, Aero Burst, Superblast, Megablast, Hyperion Max/Tempo → Performance trainer).
11. **Timing: early December, before the old-article prune** (below).
12. **Google sees a type or family listing only with 3+ reviews** (rule, below). Brand pages: no count rule.
13. **Airtable:** new fields, filled only for the 14 shoes to assign (not the 1,181 non-IGNORE rows).

## Mapping of the 1,359 old reviews (`category-mapping.csv`)
| | Daily trainer | Max cushion | Performance trainer | Race |
|---|---|---|---|---|
| Road | 751 | 44 | 168 | 163 |
| Trail | 192 | 14 | – | 12 |

Gym 15. Stability flag 307.
**Rules, in order** (road): old tag Racing (alone or with others) → Race; race shoe by name → Race; tempo/super trainer by name → Performance trainer; old tag Lightweight without Cushioning → Performance trainer; max-cushion line by name → Max cushion; everything else → Daily trainer (incl. Cushioning + Lightweight, Minimalist, premium daily lines). Stability or Motion control tag → Stability flag. Trail: trail racer line → Trail – Race; max-cushion trail line → Trail – Max cushion; old Racing → Trail – Race; else Daily (plain Trail). Training → Gym.
**Not mapped yet:** the reviews that the prune (week of 7 Dec) will remove are mapped anyway; it costs nothing.

## URL plan
| Type | Slug | URL |
|---|---|---|
| Daily trainer | `daily-trainer` | new |
| Max cushion | `max-cushion` | new |
| Performance trainer | `performance-trainer` | new |
| Race | `racing` (label renamed) | unchanged |
| Stability | `stability` | unchanged |
| Road | `road` (now on every road review; today only the 303 newest) | unchanged |
| Trail | `trail` | unchanged |
| Trail – Max cushion | `trail-max-cushion` | new |
| Trail – Race | `trail-race` | new |
| Gym & training | `training` (label renamed) | unchanged |

**Retired (301 via the existing fallback + an "old → new" list in `reviews_search.php`):** `cushioning` → `daily-trainer`; `lightweight` → `performance-trainer`; `minimalist` → `daily-trainer`; `motion-control` → `stability`; `spikes`, `walking` (0 reviews) → `/reviews/`. Same for `/reviews/<brand>/<old>/` (→ the brand's new type page if it has reviews, else `/reviews/<brand>/all/`), the short form `/reviews/<old>/`, and `/page/N/` (→ page 1).
**Check:** new slugs must not equal a family slug (they don't today).

**Index rule (one place, the listing template):** a type or family listing with fewer than 3 reviews gets `noindex, follow`; the page stays for readers. Brand pages: no count rule (Search Console, 90 days: Decathlon 3 reviews 127 clicks, ECCO 2 → 41, 361 Degrees 1 → 39). Brand pages with 0 reviews (Montrail, Ryka, "Sketchers") → 301 `/reviews/`. Brand × type pages unchanged (canonical to the brand page).

## What else changes in the theme (from a code search, 25 Nov)
- Mega menu: `includes/mega_menu_review.php` (lists terms; order and labels).
- Homepage "Reviews by type" tiles: `includes/rsg-home.php` (~line 247, hard-coded slugs; needs photos for the new tiles).
- Listing card label "Road · Cushioning": `includes/reviews/listing.php` (~line 39).
- Listing filter + title/description of type pages: `reviews_search.php`, SEO module; new intro texts for the 5 new type pages (`theme/SEO-SETTINGS-REVIEW-2026-11-11.md` pattern: own text or generated).
- Comparison "Shoe type" row: `rsg_cmp_main_type()` (skips "Road").
- "Similar shoes": `includes/reviews/similar.php` (already matches by type minus Road: improves by itself).
- Schema on type listings: `includes/rsg-schema.php` (~line 919).
- **Not checked yet:** site search routing (`theme/SITE-SEARCH-ROUTING-2026-11-19.md`) for type slugs; the taxonomy sitemap; internal links in articles to `/reviews/all/cushioning/` etc. (they redirect; updating them can come later).

## Data and flow
- **WordPress terms:** a WP-CLI script from `category-mapping.csv`: create the new terms, set each review's terms (category + Road/Trail/Training + Stability), remove the old relations, delete the old terms, then deploy the theme code (old → new list, labels, index rule), purge the cache. Staging first, with a check that `post_modified` is unchanged on a sample.
- **Airtable (Running Shoes):** three new single-select fields: Surface (Road / Trail / Gym), Category (the values above), Stability (yes/no). Filled by hand for the 14 shoes to assign. "Shoe Category" hidden, not deleted (revertable). The scraper's "Stability Level" / "Cushioning Level" can pre-fill as hints.
- **n8n review flow** (switch from Make on 1 Dec): reads Surface instead of parsing "Trail; Cushioning", and sends the category + Road/Trail + Stability terms to WordPress (today it sends only "Road" and the type is added by hand). After the 1 Dec switch, on staging first.
- **Airtable storage** (side note): rows are far from the Team plan's 50,000 per base; the likely cost driver is review photos (20 GB per base). Ruggero checks Settings → Usage; if tight, move old review photos out (they live on WordPress). Separate item.

## Timing
Google preannounced a December 2026 core update on 24 Nov (no date, "scheduled to be released in December 2026"). Ruggero would normally wait; agreed to go now because: the change is small to Google (listings only, 1.6 % of clicks, reviews and dates untouched); core updates judge the site as a whole, and a menu that matches today's market supports "the site improved massively"; waiting only moves the risk to the next update. Cost: December traffic moves can't be pinned on this change; acceptable at this size. The prune (~480 articles) must not land in the same week.
1. 26–30 Nov (Black Friday week): build on staging only.
2. Tue 1 Dec: n8n switch (already planned).
3. Wed 2 – Thu 3 Dec: live, only on Ruggero's "deploy to live". `rsg.change_log` row + `STRATEGY.md` change-log row (goal 3).
4. Week of 7 Dec: prune reading starts.
The 8 Dec section baseline covers reviews, comparisons and lists, not listings; the only review-page change is the type label.

## How we'll know
Baseline before go-live: listing views and listing → review clicks (Umami), menu clicks, Search Console clicks/impressions of the retired type URLs vs the new ones (old total 2,970 clicks / 90 days), "similar shoes" clicks on reviews. Read 4 weeks after go-live, keeping the December update in mind.

## Open
- Nimbus: ASICS's own positioning not checked (stays Daily trainer).
- The draft name lists (max cushion, trail max cushion, trail race, performance by name) are judgment, confirmed line by line by Ruggero; the review should test them.
- The Sheet artifact "RSG shoe categories: old reviews mapping" (claude.ai) is out of date (made before the decisions); delete it if not needed.
- Short-form listing URLs canonical to `/reviews/` (existing flaw, separate).
