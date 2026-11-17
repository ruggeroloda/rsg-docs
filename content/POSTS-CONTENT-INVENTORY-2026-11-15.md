# Blog posts, /content/ and old reviews — inventory + template audit (15 Nov 2026)

_Unattended deep dive while Ruggero was away. Scope: every template we had not worked on directly, i.e. the
dated `/YYYY/MM/slug/` posts (old reviews + articles), `/content/` articles, legacy `/review/` and `/gps/`.
Sources: live WordPress (WP-CLI, read-only queries), GA4 "Pages and screens" 1 Jan–15 Nov 2026 (2,384,216 views),
live/staging HTML. No Search Console export was available (needed for decision D5). Mac folder:
`content/_work/posts-inventory-20261115/` (inventory CSV, scripts, raw exports)._

## 1. What these templates are

| Template (PHP) | What uses it | Published | 2026 views | Share | Key events / 1k views |
|---|---|---|---|---|---|
| Old review `include-single-review.php` | `post` with `review_post_type=1` (2011–2022 reviews, `/YYYY/MM/…`) | 937 | 249,016 | 10.4% | 6.1 |
| Legacy trail review `include-single-review-trail.php` | legacy `review` CPT (`/review/…`, all trail, 2019–2022) | 62 | 34,850 | 1.5% | 10.3 |
| Article `include-single-post.php` | `post` without review fields (2009–2025) **and** the `content` CPT | 588 + 191 | 50,280 + 39,858 | 3.8% | 0.1–0.2 |
| Legacy GPS `single-gps.php` | `/gps/…` | 21 | 6,931 | 0.3% | 0 (GPS Amazon clicks aren't a GA key event) |

For comparison: new road reviews 15.6, comparisons 28.7 key events per 1k views. Articles earn through Raptive ads
(baseline: content article 11/11 slots), not affiliate clicks.

- **The old-review template was not in the reskin's sample pages or the 20-type check** (the "blog post" sample was
  a 2025 article). It's the 3rd-largest template by views. Checked today on staging at 390/1366 px: no overflow,
  no JS errors, the reskin fonts/colours apply; images that showed as broken were blocked by my test setup.
  → Add `/2022/05/nike-air-zoom-pegasus-39-review/` and `/review/salomon-speedcross-5-review/` to the sample set.
- `/content/` vs dated: both are still used for new posts (2025: "Alternatives to the Nike Zoom Fly 6" and
  "Nike Structure Plus first look" went to `/2025/…`). Same PHP template for both.
- **The article template shows the year**: "Updated: October 13th, 2024" under the title (WordPress `post_modified`,
  which moves on any save, not `rsg_content_modified`). So `/content/` hides the year only in the URL. → D6.
- **No JSON-LD** on articles (no Article/BlogPosting, no breadcrumb). Old reviews carry only orphan microdata
  (`itemprop` without any `itemscope`/`itemtype`): ignored by search engines → the 937 dated reviews have no valid
  structured data, while new reviews have Review + Product. → D7.
- Legacy trail template: 8–10 `<h1>` per page (old layout). Left for the redesign.

## 2. Fixed today

**F1 — "There's a new version of this shoe" notice on old reviews (staging, theme `85ca6d3`, `4fa27fa`, `0afcd73`).**
The notice followed only the old `reviews_add_next` field, which stops at the last 2022 review; reviews since 2022
link backwards via the ACF field "Previous model". So Pegasus 39 showed nothing (Pegasus 40/41/42 exist) and
Adrenaline GTS 15 pointed at GTS 22 (GTS 25 exists). New `rsg_newest_model_id()` follows both directions,
published only, cycle-safe (2 ms per page). Legacy trail reviews (`/review/`, trail) had no notice at all: added
under the hero title in a white box (the plain notice was invisible on the photo).
- Result on live data: notice on **568 old reviews (191,535 views/yr)**, was 443 (121,319). 130 get one for the
  first time (72,038 views), 248 now point at a newer review than before (81,535 views). Targets are 2023–2025
  reviews with today's prices.
- Checked on staging: Pegasus 39 → Pegasus 42, Adrenaline 15 → GTS 25, Structure 24 → Structure 26,
  Speedcross 5 → Speedcross 6 (phone + desktop screenshot). No new PHP warnings. Same wording/markup.
- **Staging only** — goes live with the reskin ("deploy to live"). Commits pushed to GitHub `main`.

**F2 — "Previous model" filled on 14 reviews (live + staging data).** Only exact n−1 matches of the same model
line: PureGrit 8, Speedgoat 4, Lone Peak 5, Caldera 5, Wave Horizon 6, Glycerin GTS 20, Kayano 29, Kayano Lite 3,
Ghost 15, Pegasus 41, SuperComp Trainer v3, Winflo 10, Speedgoat 6, Fuji Lite 3. Visible effect on live: the new
reviews' "Previous model" link. Undo: `update_field('previous_model', '', <id>)`; before-values in
`~/rsg_deploy_backups/prev-model-20261115/` (all were empty). Script `setprev-20261115.php`.

**F3 — 21 redirect rules (live + staging), 156 → 177.**
- 18 × `/family/<x>/` → the family listing (e.g. `/family/nike-vomero/` → `/reviews/nike/vomero/`). 81 links in
  old posts point there; the URL never existed and WordPress guessed a random old review (Vomero → Vomero 17).
- 3 × `/guide/best-running-shoes/`, `/guide/best-running-shoes-2024/`, `/guide/best-running-shoes-women/` → hub
  (29 links in old posts; same rule as the guides cleanup: generic/women → hub).
- All checked: one 301 → 200. Backups `~/rsg_deploy_backups/family-redirects-20261115/301_redirects-*.json`.

## 3. Decisions for Ruggero

**D1 — Make the new-version notice sell (old reviews, 191k views/yr).** Today it's one text line. Option: a small
card "Newer version: Pegasus 42 — from $X at Nike · Read the review", using the price data the target review
already has (`rsg_prices_best_html`). Old reviews convert at 6.1/1k vs 15.6 on new reviews; this is the
cheapest lever on 10% of the site's views. _Recommended._

**D2 — Same notice on the new review templates.** 149 of 367 road/trail/training reviews have a newer review of
the line and say nothing (173,158 views/yr; top: Nimbus 27 5.1k, Pegasus 41 4.5k, Ride 18 4.2k, Vaporfly 3 3.6k,
Pegasus 40 3.2k). Their own prices still show (often discounted), so: notice only, or notice + newer model's price?
Needs a place in the reskinned hero. _Recommended (notice only)._

**D3 — "Previous model" gaps.** 143 new reviews still have no Previous model (no unambiguous n−1 match: renamed
lines, skipped numbers, first of a line). Each gap breaks a chain. Option: I prepare a match list for you to tick.

**D4 — Comparison-type articles have no prices.** 7 shoe/watch comparison pages, 15,228 views/yr, 0 key events: Alternatives to the
Nimbus 26 (6,015), Pegasus 41 vs Plus vs Premium (4,621), Alternatives to the Zoom Fly 6 (2,791), Forerunner 265 vs
Pace 3 (1,609)… They link to our reviews in text only. Options: (a) add review rows with the price button
(`[rsg_review_row_in_content id=…]`, exists) to these pages; (b) an automatic "Shoes in this article" price block in
the article template, built from the reviews the text links to; (c) leave. Also: Nimbus 26 → 28 is now the current
model (a refresh of the top page). _Recommended: (b), it also covers future "3-shoe" posts._

**D5 — Pruning old articles (needs Search Console first).** Rough buckets from the inventory (GA views only):
- 64 best-lists/brand guides/gear lists by title (34 `/content/`, 30 dated; 2016–2019, e.g.
  `/content/best-asics-running-shoes/`, `…definitive-guide…`) — 56 under 100 views/yr. Same kind as the retired `/guide/` pages → redirect them the same way?
- 108 old news/launch posts under 100 views/yr (100 dated, 8 `/content/`); 254 dated articles and 71 `/content/` articles under 50 views/yr.
- 92 old reviews in the article template, no review fields (73 from 2009–2011); 53 under 100 views/yr.
Options per bucket: keep / noindex / 301 to the closest live page / unpublish. I'd decide with GSC clicks + backlinks
per URL, not views. _Suggest: send me a GSC "Pages" export (16 months) and I'll mark each URL._

**D6 — Dates on articles.** Keep "Updated: <date>" (honest; Google shows dates anyway), switch to the review rule
(Published · updated only when the text changes, `rsg_content_modified`), or hide? Today an automation saving a
post would bump the shown date. _Recommended: the review rule._

**D7 — Structured data for old reviews and articles.** Add Review JSON-LD to old reviews (the fields exist: stars,
pros, cons, verdict, shoe name) and BlogPosting to articles. Fits the machine-readability work. _Recommended,
staging first with `schema_snap.py`._

**D8 — 35 broken internal link targets (404)**, 57 links in 44 posts: removed `/overview/`, `/product/`,
`/web-stories/` pages, deleted off-topic guides (crossfit, HIIT…), two missing 2012 reviews (list:
`broken-404-targets.json`). Option: a script that unlinks them (keeps the text). 196 more links go through a 301
(fine; could be repointed later).

**D9 — One home for new blog posts.** Both `/content/` and dated URLs are in use. Decide `/content/` only (then
the 11 posts from 2024–25 under `/2024/`, `/2025/` could move with a 301) or leave as is.

**D10 — Small ones.** Speedgoat 2 is reviewed twice (`/2017/09/…speedgoat-2-review/` 0 views vs `/review/…speedgoat-2/`
2,144) → 301 the first to the second? 5 old reviews' "next model" points at a draft (skipped by F1). GA: GPS Amazon
button isn't a key event; `/go/index.php` counts as 23k page views.

## 4. Files
- `posts-content-inventory-2026-11-15.csv` (1,799 rows: id, template, kind, path, title, dates, words, 2026
  views/users/key events, newest review, links, suggestion). "kind" is a title heuristic; check before acting.
- Raw: `inv-20261115.jsonl` (WP fields), `links-20261115.jsonl` (4,340 internal links, resolved), `redirects-live.json`,
  `chain.tsv`, `prevmodel.tsv`, `titles.tsv`, `newest-live*.json`.
- Scripts (server copies in `~/rsg_deploy_tmp/`): `inv.php`, `links.php` (read-only), `setprev-20261115.php`,
  `family-redirects-20261115.php`, `hub-redirects-20261115.php` (dry run unless `RSG_APPLY=1`).

## 5. Git note
All theme commits of this work were made in a separate clone and pushed to GitHub `main` (`8fee4dd..7f6ed99`, staging tags
`staging-20261115-104255` … `-121034`). Staging = `7f6ed99`. **The Mac repo's `main` is behind GitHub**: next session with delete
permission runs `git fetch origin && git checkout main && git merge --ff-only origin/main` before committing.
(The Mac working tree was on branch `live-actual-20261115` from the parallel guides chat; untouched.)

## 6. Follow-up, 15 Nov afternoon (Ruggero's answers)
| Item | Decision | Done |
|---|---|---|
| D1 | Card on old reviews (newer shoe + its best price, box code `newer`) | staging `e5b7e23` |
| D2 | Notice only on current reviews ("Newer version: X, read our review") | staging `e5b7e23` |
| D3 | Ignore the remaining Previous-model gaps | — |
| D4 | Articles get the review layout + sidebar; "Shoes in this article" box = reviews the text links to (only what the article names), 2 shown + "Show N more"; fallback = the hub's category winners. Box codes `art`/`hub` (sidebar), `artm`/`hubm` (in the text, below 1,140 px). Note: the price "view" log now also counts article pages (lower site-wide clicks per 1,000 views) | staging `67b5534`, `557e1fb` |
| D5 | Separate chat (`content/ARTICLES-PRUNE-PLAN-2026-11-15.md`, on hold until ~22 Nov) | — |
| D6 | Review rule for article dates: "By <author> · Published <date> · updated <date>" | staging `97d4d91` |
| D7 | JSON-LD: old reviews (Product + Review /10 + Breadcrumb), legacy `/review/` (current review schema), posts BlogPosting, /content/ Article; user-rating scale fix (bestRating 5 except road); `rsg_schema_dates` (published = post_date, modified never earlier). All 1,716 posts valid | staging `97d4d91`, `a03866f` |
| D8 | 57 links in 44 posts: 15 repointed, 42 unlinked (text kept), direct DB update (dates unchanged) + 5 redirect rules (182). Backup `~/rsg_deploy_backups/broken-links-20261115/` | **live + staging** |
| D9 | No URL moves (new posts go to /content/; kept 2024–25 posts move with the prune or never). Article HTML: byline `<div>`, in-text box `<aside>`, boilerplate headings → `p.hN` (form plans, subtitle, author box, comment count, comment-form post title). trafilatura on 4 articles ≥ live | staging `2c830ce`, `b06655d`, `6cf0d6f` |
| D10 | Speedgoat 2 duplicate post 20022 → draft (URL already 301s), Speedgoat 1 next / Speedgoat 3 previous → `/review/` Speedgoat 2; 3 "next model" pointers to drafts → published successors, 2 to missing posts cleared. Backup `~/rsg_deploy_backups/small-fixes-20261115/`. GA: no page links to `/go/` since 10 Nov and `/go/index.php` has no GA tag → new `/go/` page views should stop (check GA in a week); old footer `/go/` click handler removed | **live** data; footer on staging `7f6ed99` |

GSC "Why pages aren't indexed" (15 Nov screenshot): **Blocked by robots.txt 49.2k** = old `/go/index.php?…` affiliate URLs (robots.txt `Disallow: /go/` on purpose): correct state, not an error; it shrinks slowly as Google stops seeing the links (none since 10 Nov). Don't unblock (Google would crawl ~49k redirects) and don't "Validate fix". **404 51k**: examples are fake media URLs (`/upload/*.m3u8`, `…mp4?t={seek_to_start_number}`), template strings Google reads out of a video player script, not links on our pages (check one example's "Referring page" in URL inspection to confirm); 404 is the right answer and doesn't hurt. Also seen: 55 published posts whose URLs already 301 (shadowed by a redirect rule); not in the sitemaps; can go to draft with the prune.
