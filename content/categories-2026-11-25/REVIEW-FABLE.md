# Shoe categories plan: independent review (Fable, 25 Nov 2026)

_Review of `PLAN.md` as asked in `BRIEF-FABLE-REVIEW.md`. Nothing was built or changed; the live site, WordPress, Airtable and n8n were only read. Goal served: 3 (the plan's own claim; I agree)._

## Verdict
**The scheme is right. The mapping and the redirect plan are not ready, and 2–3 Dec is the wrong week.** Build on staging now; fix four things first; go live 8–10 Dec.

1. **The mapping splits 74 of 420 road lines across categories** (371 reviews): Superblast 1 is Race and Superblast 2 Performance trainer; Clifton 5 is Race, Clifton 7 Performance trainer, the other Cliftons Daily. Cause: the rule order. Fix: decide per line, not per review.
2. **Retiring Lightweight, Minimalist and Motion control throws away about 810 Google clicks per 90 days** that arrive on searches using exactly those words. Keep those three as hidden old tags instead of redirecting them.
3. **The rollback is not real** as written (terms deleted, relations replaced, 301s served). It becomes real with a database copy of three tables and no term deletion.
4. ~~The timing premise could not be confirmed.~~ **Corrected 25 Nov, 23:00: the premise is right, my first search missed it.** Google preannounced the December 2026 core update on 24 Nov, the day the November update finished (see "Follow-up" at the end). The other timing argument (the baseline week) stands.

## What I checked, and what I could not
| Checked (25 Nov, evening) | How |
|---|---|
| All 1,359 CSV rows by rule; read one by one: all 35 "by name" rows, all 58 max-cushion rows, all 12 trail-race rows, 15 gym rows, 136 "old tag Lightweight" rows, 160 "old tag Racing" rows, ~150 Daily trainer rows from 2019 on | `category-mapping.csv`, `map.py` |
| Live listing pages: status, title, canonical, robots for 20 URLs; sitemap; term counts | read-only fetches, WordPress REST |
| Search Console, 25 Aug – 22 Nov (90 days), listing URLs by type; queries on the retired types | `rsg.gsc_page_day`, `rsg.gsc_query_day` (query table is partial: shares, not totals) |
| Listing code and where templates read the shoe type | `theme/rsg_2022/` at `38dde6a` |
| Outside: Running Warehouse category pages, Nike's lineup statement, ASICS Nimbus 28 page, Brooks Glycerin pages, On Cloudmonster page, Google's Search Status Dashboard | web, today |

**Not checked:** Hoka (Clifton, Bondi), New Balance (1080), Saucony's own site (Triumph), ASICS's own pages for Superblast and Megablast, REI and RunRepeat category pages; `rsg-schema.php`, the mega menu and homepage tile code; internal links to old type URLs inside article text (needs a database search on staging); site search routing for type words; the plan's brand-page click figures (Decathlon, ECCO, 361 Degrees). Shoe-type judgments on old models (e.g. "Wave Rider 17 is a daily trainer") are from my knowledge of the shoes, not looked up one by one.

## 1. Scheme
**Sound, and it matches the market.** Running Warehouse today: Daily trainers (standard, premium, max cushion, energetic), Performance trainers (speed focus, long-run focus), Super shoes (high and low stack); trail: daily, and a "Super Trail | Race Ultra" group. On labels shoes "Daily trainer" and "Super trainer". Nike sorts by cushioning (Pegasus responsive, Vomero maximum, Structure supportive). Brands differ; daily / max cushion / performance / race with stability as a second axis is the common ground, as the plan says.

**What would break it in 2–3 years**
- **Max cushion.** Stacks keep rising, and brands and shops already call premium daily shoes "max": ASICS's own Nimbus 28 page says "Cushion: Maximum"; REI and Road Runner Sports list the Triumph 23 as max cushion; Backcountry calls the Glycerin 23 a "max-cushion trainer". A name list needs a decision for every new model, forever. It holds only with one written definition (section 2).
- **Performance trainer holds two groups** that Running Warehouse already separates (fast, low shoes vs tall super trainers). Fine now at 168 reviews; splitting later is one new term. No action.
- **Road-to-trail shoes** (Running Warehouse sells an "Evo SL ATR"). Surface is the post type, so a shoe can only be one. Rare; note it.
- **One taxonomy still holds three things** (surface, category, stability). The plan fixes the labels, not the data. That is acceptable, but the code must know which slug is which. Today at least four templates take "the first term" of a review (`single-reviews_road.php` ~593 and ~1500, `include-single-review.php` ~680–727, `single-reviews_gps.php`), which works only by alphabetical luck. **The plan's file list misses these.** One small list in the theme (slug → kind, label, order, page title) used by menu, filter, card label, comparison row, "similar shoes" and schema makes this safe and future-proof.

## 2. Max cushion rule
The narrow rule is the right call. **The tie-breaker as worded is not consistent**: "the brand's explicit max tier wins" would move the Nimbus to Max cushion, because ASICS's own page says "Cushion: Maximum".

| Shoe | Plan | What I found | Consistent? |
|---|---|---|---|
| Nimbus | Daily | ASICS product page: "Cushion: Maximum" | **No**, under the plan's wording |
| Glycerin | Daily | Brooks sells Glycerin Max as the separate max model; Glycerin 23 text says "premium"; shops say max cushion | Yes |
| Triumph | Daily | Running Warehouse: premium daily (Triumph 24); REI, Road Runner Sports: max cushion | Yes against RW; a reader coming from REI will ask |
| 1080 | Daily | Not checked | – |
| Clifton | Daily | Not checked at Hoka | – |
| Vomero 17 / 18 | Daily / Max | Nike (Nov 2024): Vomero is the maximum-cushioning line, Vomero 18 its base model; Running Warehouse lists the 18 as a standard daily trainer | Yes, and the hardest one to explain |
| Superblast, Megablast | Performance | Fits Running Warehouse's "long-run focus" group; **CSV has Superblast 1 as Race** | Rule yes, data no |
| Cloudmonster | Daily | On: "Daily trainer", cushioning "High". Cloudmonster Hyper: "Super trainer", cushioning "Max"; **CSV has the Hyper as Daily** | Rule yes, data no |

**Wording that holds:** "Max cushion = the line a brand sells as its most cushioned: 'Max' is in the name, or the brand names the whole line its maximum-cushioning line. A spec-sheet value or a shop's label does not count." Put that sentence at the top of the Max cushion listing.

**Likely error in the list:** "Hurricane 22+". Saucony made the Hurricane a max-stack shoe with version 24 (2024); 22 (2020) was a normal premium stability shoe. From my knowledge, not looked up; the CSV has no stack for the 22. Should be "Hurricane 24+".

## 3. Mapping
**Precedence is the wrong way round.** "Old tag Racing" comes before "by name", so the old tag overrules Ruggero's own name decisions. The Racing tag was also put on tempo shoes and plated trainers from 2017 to 2023.

Clear errors (all visible in the CSV):
| Problem | Examples |
|---|---|
| Same line, different category because of an old tag | Superblast (1 Race, 2 Performance); Endorphin Speed (1, 3 Race; 4, 5 Performance); Boston 7, 8 Race, the other eight Performance; Mach 5 Race, 2/4/6 Performance; Deviate Nitro 1 Race, 2/3 Performance; Zoom Fly, 3, 4, 5 Race, Flyknit and 6 Performance; Noosa Tri 13, 14 Race, 15, 16 Performance; Adios 7, 8 Performance, the other seven Race; Kinvara 7 Daily / 7 Performance; Rebel v3 Daily, the other four Performance; Rincon 3 Performance, 1/2/4 Daily; Launch 6, 7 Performance, 8–11 Daily |
| Super trainers filed as Race | adidas Prime X (all four), FuelCell TC |
| Daily and stability shoes filed as Performance trainer ("Lightweight" used to mean weight, not speed) | Wave Rider 17, Ride 6, Wave Inspire 13 and 16, Clifton 7, Arahi 2, Ravenna 9, Vongo v5, Lunarglide 7, 870v3, FuelCell Propel v2 |
| Race shoes filed as Performance trainer | Adios 7, 8; Wave Ronin 5; Hyper Speed 4 (1 and 3 are Race) |
| Other | Clifton 5 → Race; Magic Speed 1 → Daily + Stability (2–4 Race); Hyperion GTS 1, 2 → Daily + Stability (Hyperion 2, 3 Performance); Cloudmonster Hyper → Daily; Brooks PureGrit 5 (a trail shoe) → Road, Performance; Mizuno Wave Ascend 7 → Trail – Race + Stability |
| Trail name patterns too narrow or too wide | Sense 4 Pro → Daily but Sense Pro 2 → Race; Sense Pro Max → Race; Flight Vectiv (only "Vectiv Pro" matches) and Catamount → Daily |
| **Missing from the CSV** | The 6 newest gym reviews (Nano X3, Nano X4, Metcon 8, Metcon 9, Cloud X 3, Freedom Crossport): the site has 1,365 reviews and 21 Training, the CSV 1,359 and 15 |
| In the CSV but not reviews | At least 4 old posts that carry a type tag (e.g. "Best cushioning running shoes for 2013", "Nike unveils new Joyride cushioning") |

Answers:
- **Order:** by name first, then Racing, then the rest.
- **"Lightweight without Cushioning → Performance trainer":** not sound before about 2019. Use it as a hint only.
- **"Cushioning + Lightweight → Daily trainer":** right as a default.
- **"Minimalist → Daily trainer":** acceptable, but the CSV does not do it: 13 minimalist shoes tagged Lightweight (Nike Free, Skora, Minimus) land in Performance trainer.
- **The fix that removes most of the above:** one category per line, with named version breaks (Vomero 18+, Hurricane 24+). Only the 74 split lines need a decision; a script applies it to every version. About an hour of Ruggero's time with a prepared list.

## 4. URLs and SEO
**Verified on the live site:** `/reviews/all/stability/` and `/reviews/adidas/all/` are self-canonical; `/reviews/adidas/stability/` canonicals to `/reviews/adidas/all/`; `/reviews/stability/` canonicals to `/reviews/`; an unknown type 301s (`/reviews/all/daily-trainer/` → `/reviews/`); the type sitemap lists `/reviews/all/<type>/` and will follow the terms by itself; the plan's 2,970 clicks match my count; no family slug equals a new slug (27 families).

**What the plan misses**
1. **Brand × type pages earn most of the type clicks, although their canonical points away.** 2,460 of the 2,970 clicks land on `/reviews/<brand>/<type>/` (adidas stability 431, Nike stability 262, Nike trail 119). Google ignores that canonical. Leave it alone now, but these are the pages to protect and to measure.
2. **The retired types rank for their own words.** 90 days: Lightweight 439 clicks, Minimalist 266, Motion control 108, Cushioning 152 (965 in all, a third of the type clicks). Searches: "nike minimalist running shoes", "brooks minimalist shoes", "motion control running shoes", "lightest asics running shoes". A 301 from `/reviews/nike/minimalist/` to a Nike daily-trainer list does not answer that search; expect those clicks to go. That also works against goal 1.
   **Recommendation:** keep Lightweight, Minimalist and Motion control as old tags on the reviews that have them: out of the menu, the filter and the card label, pages and URLs alive, no new reviews added. Only `cushioning` → `daily-trainer` needs a redirect (and `/reviews/<brand>/cushioning/` → the brand's daily-trainer page). This needs the "which slug is what" list from section 1, which is needed anyway.
3. **Reusing `racing`, `stability`, `trail`, `training`: right.** But the page title is built as "N + label + Running Shoes Reviews": "Race Running Shoes Reviews" and "Gym & training Running Shoes Reviews" read worse than today's. Give each type its own title text ("Racing shoes", "Max cushion running shoes").
4. **`road` on every road review:** fine (2 clicks in 90 days on that listing today).
5. **Cache:** the edge keeps pages up to 30 days (`README.md`). All 1,365 reviews change their type label and "similar shoes": purge everything at go-live.
6. **`/page/N/` of retired types → page 1:** fine (150 URLs, 9 clicks).

## 5. Index rule
**As written it changes nothing on day one**: all 27 families have 4+ reviews, every new type has 12+, and empty listings already carry `noindex` (checked: `/reviews/all/walking/`, `/reviews/montrail/all/`). It is a sound guard for future families; 3 is a reasonable number. Empty brand pages → 301 `/reviews/`: fine. No rule for brand pages: fine (8 brands have 1–2 reviews). The pages where a count rule would matter are brand × type, and the plan leaves those as they are; I agree for now. Revisit after the 4-week read, with the clicks from point 1 in hand.

## 6. Timing
**For 2–3 Dec:** small for Google (listings only, 1.6 % of clicks); review URLs and dates untouched; a wrong menu costs readers every day it stays.

**Against:**
- ~~The premise.~~ **Corrected:** the preannouncement is real ("Follow-up" at the end). The November update ran 10–24 Nov; the December one has no date.
- **It lands inside the 1–7 Dec baseline week.** The plan says only the type label changes on review pages. Not so: the mega menu changes on every page, and "similar shoes" on every review changes which shoes it shows. Both move `next_click` and "visits with 2+ pages", the numbers the 8 Dec read is for.
- **The mapping needs the line pass** (section 3), in Black Friday week, next to the 1 Dec n8n switch.

**My view: go live Tue 8 – Thu 10 Dec**, after the baseline read. The prune that week starts with reading, not removing, so the two do not collide. A week's delay costs nothing measurable at 33 type clicks a day; a muddied baseline and a wrong mapping cost more.

## 7. What's missing
- **Rollback.** After the plan's script the old terms are gone and Google has seen 301s. Make it real: (1) before the script, copy `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` (staging and live) and write down the restore command; (2) do not delete old terms in the same step: Cushioning can go after the 4-week read, the three old tags stay (section 4); (3) the theme change is one revertable commit, as always. The CSV alone is not a rollback: 6 reviews are missing from it.
- **Dependency:** the n8n flow must send the new terms from the first review published after go-live; otherwise new reviews arrive with "Road" only and no category.
- **Measurement:** add to the baseline the clicks per group from this review (brand × type 2,460; retired types 965) and the three query groups (minimalist, lightweight / lightest, motion control).
- **Staging checks to add:** every template that reads the type shows the category, not "Road" or "Stability" (trail shoes with the Stability flag are the risky case); title and description of each type page; one brand × old-type URL per retired type redirects where expected.
- **Smaller:** `sketchers` is a duplicate brand with 0 reviews; the review template links to `/reviews/all/<type>` without the closing slash (one extra redirect per click).

## Questions for Ruggero
1. **Old tags:** keep Lightweight, Minimalist and Motion control as hidden old tags with live pages (my recommendation), or redirect them as planned and accept losing about 810 clicks per 90 days?
2. **Lines:** OK to decide the 74 split lines one by one from a prepared list (one category per line, named version breaks)?
3. **Kinvara, Rebel, Rincon, Launch, Noosa Tri, Magic Speed, Zoom Fly:** Daily, Performance trainer or Race? These are judgment calls the old tags answered both ways.
4. **Max cushion wording:** OK with "the line a brand sells as its most cushioned; a spec-sheet value or shop label does not count", shown on the listing? And Hurricane 24+ instead of 22+?
5. **Go-live:** 8–10 Dec instead of 2–3 Dec?
6. **Core update:** where did the 24 Nov "December 2026 core update" note come from?
7. **The non-review posts with a type tag:** drop the tag, or leave them in the listings?

Sources (web, 25 Nov 2026): [Running Warehouse, men's shoe types](https://www.runningwarehouse.com/mens-running-shoe-types.html) · [Running Warehouse, daily trainers](https://www.runningwarehouse.com/catpage-MSHEDM.html) · [Nike, road running lineup](https://about.nike.com/en/newsroom/releases/nike-road-running-footwear-lineup) · [ASICS Gel-Nimbus 28](https://www.asics.com/us/en-us/gel-nimbus-28/p/ANA_1012B899-100.html) · [Brooks Glycerin](https://www.brooksrunning.com/en_us/featured/glycerin/) · [On Cloudmonster](https://www.on.com/en-us/collection/cloudmonster) · [REI, Triumph 23](https://www.rei.com/product/249639) · [Google Search Status Dashboard, ranking](https://status.search.google.com/products/rGHU1u87FJnkP6W2GwMi/history) · [Search Engine Journal, November 2026 core update](https://www.searchenginejournal.com/google-november-2026-core-update/596614)

## Follow-up, 25 Nov 23:00 (Ruggero's answers)
1. **Old tags: keep.** Lightweight, Minimalist and Motion control stay as hidden old tags with live pages. Only Cushioning redirects.
2. **Lines: done by Fable** (Ruggero: "only if you can do it"). `map2.py` applies one category per line with named version breaks and writes **`category-mapping-v2.csv`** (the first CSV is unchanged). 133 reviews changed, each marked `CHANGED:` with the reason in the last column; the 6 missing gym reviews are added (1,365 rows). New totals: road Daily trainer 771, Performance trainer 176, Race 135, Max cushion 43; trail 189 / 14 / 16; gym 21; Stability flag 306.
   - The lines I decided on judgment, for Ruggero to overrule: **Performance trainer:** Kinvara, Rebel, Zante, Cloudflow, Magic Speed, Zoom Fly, Noosa, Mach, Zoom Elite, Razor, Wave Shadow. **Daily trainer:** Rincon, Launch, adidas SL. **Race:** Adios, Hyper Speed. **Trail – Race:** Catamount, Flight Vectiv, Sense 4 Pro (Sense Pro Max → Daily).
   - Also applied: Hurricane is Max cushion from 24, not 22; Cloudmonster Hyper → Performance trainer; old Minimalist shoes that had landed in Performance trainer → Daily trainer; PureGrit 5 → Trail; Magic Speed 1 loses the Stability flag.
   - 17 lines still hold two categories on purpose: different models under one name (Deviate Nitro vs Elite, SuperComp Trainer vs Elite, Rebellion Flash vs Pro), named breaks (Vomero 18+, Hurricane 24+, the 2016 Hyperion) and a few 2012–2018 one-offs.
3. **Go-live 8–10 Dec: agreed**, unless point 4 changed it. It does not.
4. **Core update: Ruggero's source is right.** Google preannounced the December 2026 core update on its Search Status Dashboard on 24 Nov, about an hour after it marked the November update complete: "This update is scheduled to be released in December 2026", no date. It is the first core update Google has announced in advance. One report says a Google spokesperson called the launch "not imminent" (seen second-hand, not at the source). The Reddit link itself could not be opened from here.
   **What it means for the date:** with no date, neither 2–3 Dec nor 8–10 Dec is safely "before" the update. The baseline-week argument decides, so 8–10 Dec stays. If the update starts before go-live, my advice is to go anyway: the change touches listings only, and waiting out a rollout pushes it into January.

Still open: the Max cushion wording (question 4) and the non-review posts with a type tag (question 7).

Sources for point 4: [Search Engine Roundtable](https://www.seroundtable.com/google-december-2026-core-update-42615.html) · [Search Engine Land](https://searchengineland.com/google-preannounced-the-december-2026-core-update-but-it-is-not-live-or-active-yet-502947) · [Search Engine Journal](https://www.searchenginejournal.com/google-december-2026-core-update/598760/) · ["not imminent"](https://maccus.fr/en/google-core-update-december-2026/)
