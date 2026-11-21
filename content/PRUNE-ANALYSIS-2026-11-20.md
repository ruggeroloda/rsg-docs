# Prune analysis: should RSG prune, what, and how far?

_Claude Fable, 20 Nov 2026, from `BRIEF-PRUNE-ANALYSIS-2026-11-20.md`. **Updated 21 Nov with Bing Webmaster link data (section 6): tier 3 is not link-free; the conclusions stand, proposed: the batch-1 list shrinks by 39 pages.** An analysis and a staged plan; **nothing changed on live or staging**. Working files (Mac only, not in git): `_work/prune-analysis-20261120/` (`build.py` → `pages.csv`, one row per published indexable page; `tab.py`; `unknown-urls-oct.csv`). "Clicks" = Search Console web search; "12 months" = Nov 2025 – Oct 2026; "views" = GA4 page views, all sources._

## Answer in six lines
1. **A prune will not bring traffic back, and should not be sold as that.** RSG's own data, Google's statements and the outside cases all point the same way (section 3).
2. **A cleanup of the dead articles is still worth doing, as housekeeping.** It costs almost nothing: the 480 weakest articles earned 508 clicks in 12 months (0.04 % of the site's).
3. **Do not extend it to old reviews.** The 936 dated reviews bring 13 % of clicks, 16 % of views, 5 % of affiliate clicks and hold more outside links than any other group. Only 87 of them are dead, and removing those gains nothing.
4. **The planned date (~22 Nov) is wrong.** It would be the third change inside the core-update window, five days before Black Friday. Nothing is lost by waiting: first batch in the week of 7 Dec.
5. **One rule in the plan needs changing:** 301 only to a page about the same shoe or topic. A launch post redirected to a brand listing is treated by Google as a soft 404; a plain 404/410 is cleaner.
6. **The effect cannot be read precisely**, with or without a holdout (section 5). Two batches five weeks apart, a fixed set of numbers, and a stop rule: if nothing moves by the next core update, pruning is closed as a topic.

## 1. The candidate groups
2,391 published indexable pages (`wp_pages`, 20 Nov). Site totals in this join: 1.20M clicks and 2.49M views in 12 months; 58.1k clicks in Oct 2026 (97 % of Search Console's 59.8k; the rest is the URLs in "Not in WordPress" below).

| Group | Pages | Oct clicks (share) | 12-mo views (share of Oct views) | Affiliate clicks, 12 mo (share) | Under 10 clicks in 12 mo | Pages with 2+ linking sites* | Not from Google** |
|---|---|---|---|---|---|---|---|
| Reviews, current template (`/reviews/<type>/`) | 375 | 19,429 (33.4 %) | 942k (35.1 %) | 14,513 (38 %) | 0 | 258 | 19 % |
| Comparisons | 173 | 23,467 (40.4 %) | 641k (24.2 %) | 18,677 (49 %) | 0 | 31 | 2 % |
| **Old reviews, dated URLs** | 936 | 7,730 (13.3 %) | 271k (15.6 %) | 1,896 (5 %) | 87 | 461 | 31 % |
| Old reviews, `/review/` | 62 | 1,806 (3.1 %) | 40k (2.1 %) | 400 (1 %) | 0 | 19 | 5 % |
| **Articles, dated URLs** | 587 | 1,329 (2.3 %) | 52k (3.2 %) | 8 (0 %) | 414 | 49 | 32 % |
| **Articles, `/content/`** | 191 | 1,105 (1.9 %) | 46k (2.3 %) | 6 (0 %) | 119 | 26 | 16 % |
| Pages (incl. homepage, `/reviews/`, Shoe Finder) | 41 | 2,652 (4.6 %) | 453k (16.2 %) | 1,178 (3 %) | 29 | 11 | 32 % |
| GPS reviews | 21 | 235 (0.4 %) | 8k (0.3 %) | 0 | 4 | 4 | 28 % |
| `/top/` lists (URLs changed 20 Nov: undercounted) | 5 | 338 (0.6 %) | 34k (0.9 %) | 1,521 (4 %) | 2 | 1 | 61 % |

\* Search Console "top linked pages" export of 15 Nov, capped at 1,000 rows, a sample. \** Share of views not inside a Google-search visit; for old pages part of it is bots (the brief's "Direct" warning).

What the table says:
- **Articles are a third of the site's pages (778) and 4.2 % of its clicks, with no affiliate income at all** (14 affiliate clicks in 12 months). At the site's page RPM ($6.8) all 778 earn about $660 a year in ads; the part the plan retires earns about $115.
- **Old dated reviews are not dead weight.** Their median page had 72 clicks in 12 months. The "1,008 old pages under 5 clicks in October" figure overstates the tail: the weaker half of the old reviews (468 pages) still brought 14.3k clicks in the year. They also held up better than anything else: Oct 2025 → Oct 2026 clicks ×0.53, against ×0.26 for current-template reviews and ×0.44 for comparisons.
- **Links sit on old reviews.** Of the 1,000 most-linked URLs, 484 are old dated reviews (1,670 linking sites in total; current reviews 266 pages / 1,258). Deleting or redirecting them in bulk puts the site's largest link holding at risk.
- **Truly dead pages** (under 10 clicks in 12 months): 655 across the site, 1,300 clicks together (0.11 %). 533 are articles, 87 old reviews, 29 pages.
- **What old reviews are found for:** model-name searches (15.1k clicks, May–Oct 2026) and "[model] review" (6.3k), e.g. Structure 24, Zoom Fly 4, Cumulus 16, Bondi 7: people buying an older or discounted model. That is real demand, and the reason the "newer version" card with a price is the right treatment (old reviews: 1.77 % shop click vs 5.08 % on current ones, `CONTENT-REVIEW-2026-11-20.md`).

### Not in WordPress: the "378 URLs"
My normalisation gives 363 Search Console rows (317 distinct paths), 1,722 clicks and 344k impressions in October:
| What | URLs | Clicks | Note |
|---|---|---|---|
| Review listings (`/reviews/<brand>/…`, `/reviews/all/…`) | 228 | 1,458 | Taxonomy pages, not posts: live and useful, not a prune topic |
| Old `/guide/` URLs (redirected 15 + 20 Nov) | 16 | 171 | Google has not swapped them yet; will fade |
| Two PDFs in `/book/` (training e-books) | 2 | 45 | Indexed files; harmless |
| Author archives | 17 | 22 | Live (200) |
| Same pages with `?…` or `#…` | 65 | 6 | Not separate pages |
| Paginated listings | 18 | 3 | — |
| `/gps_brands/…`, `/content_category/…` archives | 8 | 2 | Live (200), 6.5k impressions, thin; candidates for noindex in batch 2 |
| Root-level review URLs on the non-www host, `/comparison/`, `/top/`, other | 9 | 15 | Old redirects |
Nothing here changes the picture.

## 2. Options per group
| Group | Call | Why |
|---|---|---|
| Articles with no value (plan's tier 3: 480 URLs, under 10 clicks in 16 months and at most 1 linking site) | **Remove** (draft = 404). 301 only the 76 with a same-shoe or same-topic target. **21 Nov, proposed: the 39 with 2+ linking sites in Bing move to the hand list (section 6)** | 508 clicks and 7.7k views in 12 months, about $50 a year in ads, no page with 2+ linking sites in the Search Console sample (Bing: 39 have). Giveaways from 2013, launch news of retired shoes, "Around the Web": nothing a reader in 2026 wants |
| Articles with some value that the plan retires (tier 2: 85 × 301, 22 × remove) | **By hand, second batch** | 2,993 clicks in 12 months and 18 pages with links. Each needs a real target or stays |
| Articles marked "KEEP?" (106, mostly training/health advice) | **Keep for now** | 2,277 clicks a year, 24 pages with links. Deciding them needs reading them, not a threshold. This is the one group a quality rater might call unhelpful (generic health and training advice, not shoe testing), so it is the right place for a later "improve or remove" read |
| Articles that earn (85) | Keep | 35.8k clicks |
| Old dated reviews + `/review/` (998) | **Keep all. Improve with the "newer version" card** (already work-list item 5) | See section 1. Merging into the newest model's review would throw away the model-name demand; noindex would throw away 9.5k clicks a month for no known gain |
| The 87 dead old reviews | **Leave** | 416 clicks a year, 14 with links. Removing 87 of 2,391 pages changes nothing Google could notice, and "1,300+ reviews" is a trust line on the homepage |
| Pages (41) | 22 are junk with 0 clicks and under 20 views in 12 months (2013 contests, 2023 Prime Day pages, `/test-cache/`, hiring post from 2015, thank-you pages). **Hand list in batch 2**: thank-you pages that forms land on get noindex, the rest go | Were outside the plan's scope |
| Comparisons, current reviews, lists, GPS | Out of scope | Every one earns |

Plan corrections (`ARTICLES-PRUNE-PLAN-2026-11-15.md`):
- Bucket C rule "301 → same-shoe review, else line listing, else brand listing": stop at the same-shoe review (or the same line's listing when the post is about that line). Google's John Mueller: a redirect to a category page ends up treated as a soft 404 and puzzles the visitor, while a 404/410 is "not a negative quality signal" (source in section 3). Same for bucket B's "brand guide → brand listing" where the guide had no clicks.
- The plan's keep rule for old reviews in the article template (bucket A, keep at 10+ clicks) is right and matches the above.
- Counts move: 778 articles today, not 779. Re-run the joins on a fresh export before applying, as the plan says.

## 3. What a prune can and cannot do
**RSG's own evidence**
- Every loss since 2023 hit all page types and ages alike (`REPORT-2026-11-20.md`); since Oct 2025 about three quarters of lost clicks were lost at an unchanged rank. A prune does not touch click-through.
- Old pages are the group that fell least (×0.53 in a year). If old stock were dragging the site, the pattern would be the other way round or at least not this.
- Jul 2024: 157 `/overview/` pages removed (2.9k Google visits a month, 0.6 %). Google visits went on falling: 471k (Jul) → 441k → 412k → 383k (Oct 2024), no lift at the Aug 2024 core update.
- Crawl budget is not a constraint at 2,400 pages; the crawler-eye check found nothing technical.

**What Google says**
- "Deleting content is a last resort"; the same page adds that wanting to delete whole sections suggests they were written for search engines in the first place (core updates page): https://developers.google.com/search/docs/appearance/core-updates
- Old is not unhelpful: "That's not a thing!" (Search Liaison, Aug 2023, on CNET's mass deletion): https://www.seroundtable.com/google-dont-delete-older-helpful-content-35846.html . Mueller on the same case: reasonable, effect not noticeable; and "removing content doesn't make the rest rank higher": https://seroundtable.com/google-removing-content-36097.html
- Against that: the helpful-content guidance said that removing unhelpful content may help the rest of a site rank, the reviews system can judge a whole site when reviews are much of it (https://developers.google.com/search/docs/appearance/reviews-system ; RSG: 1,373 of 2,391 pages are reviews), and site quality is judged on indexed pages only (Mueller, 2018: https://www.seroundtable.com/google-site-quality-indexed-pages-25593.html ). So removing *unhelpful* pages can count; removing *old or unpopular* ones does not. Mueller draws the same line: low traffic is "less popular", not low quality: https://www.searchenginejournal.com/google-explains-content-pruning/371252
- 404 and 410 are normal and not a quality signal: https://www.seroundtable.com/google-why-404s-seo-40692.html
- Timing: changes can take months and may need the next core update.

**Outside cases**
- Where pruning worked, the sites were very large (HubSpot, a 4.86M-page site) with crawl problems, or the removed pages were mass-produced or AI text (Ahrefs' collection: https://ahrefs.com/blog/content-pruning/ ). RSG is neither.
- Sites hit in Sep 2023 that reworked and removed content: none of 200+ tracked had recovered by Mar 2024 (https://www.seroundtable.com/google-helpful-content-recovery-core-update-37095.html ); 22 % of ~400 were up 20 %+ after Aug 2024 (report's source), with no evidence that pruning was what separated them.

**So:** expect no visible change in rank or clicks. The case for the cleanup is different and modest: a third of the site is articles nobody reads; readers and reviewers who land on a 2013 giveaway or a "hello world" post see a neglected site; fewer pages mean less to maintain through the theme rebuild. The reviews-system point cuts the other way from the usual prune logic: what Google would judge site-wide is the reviews, and those are fixed by publishing and by the newer-version card, not by deleting.

## 4. Risk and order
- **Wait.** The November core update started 10 Nov and can run to ~24 Nov; Google says to read results a week after completion (~1 Dec). The reskin went live 15 Nov with watches ending 22 Nov – 3 Dec. Black Friday is 27–30 Nov. A prune on ~22 Nov would make all three unreadable and risks a mistake (the 15 Nov listing 500s came from drafting one post) in the best revenue week.
- **Waiting costs nothing:** the pages in question earn about $4 a month.
- **Order**
  | Step | When | What |
  |---|---|---|
  | 0 | now – 6 Dec | Nothing on the site. Fresh Search Console exports (performance + links) and a fresh Bing links pull (`_work/prune-analysis-20261120/bing_join.py`), re-run the joins, Ruggero reads the tier 3 list bucket by bucket (D, C, B, A, E), baseline numbers below noted |
  | 1 | week of 7 Dec | Tier 3 without the pages with 2+ linking sites (≈441 articles) on staging, then live on "deploy to live", with the plan's apply checklist. One change-log row |
  | read | ~11 Jan (5 weeks) | Numbers in section 5 |
  | 2 | mid Jan | Tier 2 by hand (≈107), junk pages (22), thin archives (noindex). Only if step 1 was clean |
  | read | 5 weeks later, and after the next core update | Stop rule |
  | — | not planned | Old reviews, "KEEP?" advice articles |
- **Undo:** drafted posts are republished; redirect rules are removed from the option backup. Keep it that way (no hard deletes) until the stop rule is read.

## 5. How we would know
**What a holdout can and cannot show.** The only effect that would matter is site-wide, and a holdout inside the pruned set shares the same site, so it cannot show it. Inside the set there is nothing to compare either: both halves have about zero clicks. For the 301s a holdout could show whether targets gain, but tier 2 is ~250 clicks a month in total: too small to read. So: no holdout; two batches five weeks apart, read against the trend before.

| Number | Baseline | Read |
|---|---|---|
| Cost: clicks on the removed URLs + their redirect targets | Tier 3 ≈ 42 clicks a month, tier 2 ≈ 250 | +5 weeks: the loss should be at most that |
| Errors: retired URL → one 301 → 200, or 404; no kept page broken; PHP log quiet | — | same day and +1 week |
| Search Console Pages report: indexed pages, "crawled, currently not indexed" | Export in step 0 (Ruggero) | +5 weeks: removed URLs gone from the index |
| Mobile position on "review" searches / share of impressions in the top 5 | 6.5 / 25 % (Oct 2026) | +5 weeks and after the next core update |
| Impressions of kept pages (current reviews, comparisons), weekly | 8 weeks before step 1 | Against that trend |

**Stop rule:** the last two rows are the only place a benefit could appear, and new reviews, the trust details and the next core update move the same numbers. If they are flat after step 2 and the next core update (2026's came in March, May and November), pruning is done and no further groups are added. If they rise, the prune cannot be credited alone.

## Reading before the first batch (Ruggero 20 Nov: "we'll have to do reading too")
The numbers say which pages nobody visits; they do not say which pages are unhelpful. Three reads, none done yet:
1. **Tier 3 list (~480 articles), all of it, by Ruggero**, bucket by bucket (D, C, B, A, E) from titles + first lines (`excerpts-live.jsonl`): the automatic first pass has wrong targets and may hide a page worth keeping.
2. **~20 old reviews**, spread over 2012–2022 and over high and low clicks: would a reader today call them honest first-hand reviews (outdated is fine) or thin? This tests the "keep all old reviews" call.
3. **~20 of the 106 "KEEP?" advice articles** (training, health): the group most likely to be judged unhelpful. Outcome per article: keep, improve, or move into batch 2.
Reads 2 and 3 can be done by Claude with a short verdict per page for Ruggero to check; read 1 needs his OK per bucket.

## Limits
- Search Console links are a 1,000-row sample from 15 Nov; a page outside it has few linking sites, not provably none. Bing's list (section 6) fills this in for dated URLs but is a different crawler's view, and it shows almost nothing for `/reviews/` and `/comparison/` URLs (links to their old addresses are counted on the old URL).
- Ad income per group is page views × the site's page RPM, not Raptive's per-page figure; affiliate clicks are GA4 key events and end on 11 Nov.
- GA4 non-Google views include bots, mostly on old pages.
- I did not read the old reviews or the advice articles. Whether they would be judged unhelpful is a reading job (20 sampled per group would do), not a data one.
- Tiers come from the plan's automatic first pass (16-month clicks to 13 Nov); I checked the totals against 12-month data, not each URL.

## 6. Update 21 Nov: Bing Webmaster links
_Bing Webmaster API (`GetLinkCounts` + `GetUrlLinks`, pulled 21 Nov): 1,044 RSG URLs with outside links, every linking URL listed, joined to `pages.csv` → `_work/prune-analysis-20261120/bing/pages-bing.csv` (script `bing_join.py`, raw `bing/links-20261121.json`; copy on rsgmedia `~/bing/`). "Sites" = distinct linking domains; "in-text" = a site with at most 3 links to the page (more = a blog list, sidebar or footer)._

| Group | Pages | 2+ linking sites: Search Console sample | Bing | Either |
|---|---|---|---|---|
| Old reviews, dated URLs | 936 | 461 | 491 | **659** |
| Old reviews, `/review/` | 62 | 19 | 22 | 32 |
| Articles, dated URLs | 587 | 49 | 87 | 106 |
| Articles, `/content/` | 191 | 26 | 16 | 36 |
| **Tier 3 (plan: remove)** | 480 | **0** | **39** (34 in-text) | 39 |
| Tier 2 (by hand) | 213 | 42 | 43 | 62 |
| of which "KEEP?" advice articles | 106 | 24 | 21 | 32 |
| The 87 dead old reviews | 87 | 14 | 25 | 33 |

What changes:
- **Tier 3 is not link-free.** 39 of the 480 have 2+ linking sites (30 set to 404, 9 to 301). Most are small running blogs, Reddit threads and coupon sites; the big counts are blog-list widgets (one blog, `chrisultra.blogspot.com`, gives 229–384 links to each June 2020 "new shoe releases" post). Weak links, but the plan's rule ("at most 1 linking site") does not hold for them. **Proposed (Ruggero to OK): these 39 leave batch 1 and join the tier 2 hand list** (301 to a same-topic page where one exists, else keep). Batch 1 becomes ≈441 pages with no known links: cleaner, and nothing is lost by the delay.
- **Old reviews hold even more links than the sample showed:** 659 of 936 dated reviews have 2+ linking sites in one source or the other (461 in the sample). This strengthens "keep all old reviews", and the 87 dead ones too (33 have links, not 14).
- **The other conclusions stand:** no traffic effect expected, wait until the week of 7 Dec, no holdout, the stop rule. Links of this kind (blog lists, forums, small blogs) do not change the size of what is at stake; they change which pages should be redirected rather than removed.
- List of the 39: run `bing_join.py` (prints them with action, bucket and top linking sites), or filter `pages-bing.csv` on `tier = 3` and `bing_sites >= 2`.
