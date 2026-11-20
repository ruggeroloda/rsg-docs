# RunningShoesGuru: fact pack for a content review

_Prepared 20 Nov 2026 from Search Console, GA4, the site's own analytics (Umami) and the live pages. Everything below is measured unless marked **(Ruggero's view)** or **(hypothesis)**._

## What I want from you

I run RunningShoesGuru.com (RSG). Please critique **my current reviews, comparisons and guides**, telling me what works and what doesn't, and use that as the basis to **structure future pieces**. Base everything on the data and pages in this pack. I don't want generic advice ("build a testing lab", "add structured data", "make it AI-friendly"). If the data can't support a conclusion, say "not enough data" and name what would settle it.

### Questions
1. **What works and what doesn't**, across reviews, comparisons and guides: which pages still earn search clicks and shop clicks, which lost them, and what the survivors have in common (query type, freshness, shoe popularity, format). Where is the **growth headroom**, i.e. demand we already rank for but don't win?
2. **Where the click is lost.** Many pages kept their position and impressions but lost CTR. How much is AI Overviews or other SERP features, and how much is the page itself (title, date, intent mismatch)? What is fixable on our side?
3. **Critique of the four sample pages** (Section 5) and the phone screenshots: compared with what a searcher (and an AI answer) already gets, what's missing, redundant or weak? What would make someone click through to RSG, and then to a shop or a second page?
4. **A structure for each future format** (review, comparison, guide) that ends a visit in either a shop click or a next page, and grows the email list, **without adding ads**. Where do the presenter video and the reusable b-roll slot in? What should we measure to know it's working?
5. **Allocation.** With about 1 new shoe a month plus refreshes of existing pages, what's the best mix of new reviews, comparisons, guide updates and refreshes? Is 2–3× today's traffic reachable this way? If not, what has to change? What should we stop doing?

### How to answer
- Start with a summary of at most 6 lines.
- For each recommendation: what to do, the data point(s) in this pack it rests on, expected impact (high/medium/low), effort, and how to measure it. Rank by impact against effort.
- Keep facts apart from your hypotheses.
- End with: what you could not conclude, and which data would settle it.
- About 2,500 words maximum. I'll ask follow-ups.
- Don't use tools or web search: everything you need is in this pack.

## 1. Context

- **History:** RSG has run since 2009. **(Ruggero's figures)** It peaked at about **1.5M visits a month in 2016–2020**, then declined, with a sharp drop after Google's Helpful Content Update (Sept 2023). Today GA4 shows about **160k page views per 30 days** (Nov 2026). Organic search clicks were 187k in Aug 2025 and 60k in Oct 2026 (Section 2.1).
- **Revenue today:** display ads through Raptive are the largest source, then affiliate (mostly Amazon US, plus retailer programmes), then deals emails to a list of about 50k (about 20k active). Affiliate used to be the main business. **(Ruggero's view)** I still want affiliate to grow, but today ads pay more.
- **Goal:** **2–3× today's traffic** (not pre-AI levels). Each visit should either **click to a shop** or **go deeper** (more pages, so more ad views at today's ad density). **No more ads per page.** Grow the email list.
- **Resources:** one tester/writer (Brandon Law, marathon runner, author of the sample reviews and 222 articles on the site; terms for the restart are being discussed), about **1 shoe bought per month** (about $100 with a retailer discount), and AI for low-value tasks. Samples are acceptable for secondary coverage. I run the site part-time alongside a full-time job.
- **Video plan:** a **human presenter on camera** (a-roll), plus a **library of b-roll shot once per shoe** and reused across reviews, comparisons, lineups, Shorts and Reels. RSG has a YouTube channel that isn't active now.
- **Ruled out (don't suggest these):** building a lab or measurement data moat; automated videos without a human presenter; selling the price engine as RSG's differentiator (others do it better, and Google has merchant feeds); more ads per page.
- **(Ruggero's bets; challenge them if the data disagrees)** Google will send more traffic to links again for many queries; being cited in AI answers will matter commercially (brands will pay to be mentioned); fewer ads are better for conversion and rankings.
- **What changed on the site in the last 10 days** (so the Umami numbers reflect the new site): a new price engine since 10 Nov (25 retailer feeds, 11 countries; price boxes on reviews and comparisons, Amazon fallback). Theme reskin since 15 Nov. Homepage, best-of lists and listings rebuilt 17–19 Nov. Review top on phones changed 18 Nov. A "verdict / Buy if" box added to 5 of 39 outdated comparisons that are being refreshed. A prune of old articles is planned.
- **Top site-search terms with no RSG review** (19 Nov): Novablast 6, Endorphin Azura, Cloudmonster 3, Hyperboost, 1080 v15, Superblast 3, Triumph 24, Kayano 33.

## How to read the data
- **Search Console:** 4 Jul 2025 – 17 Nov 2026. "90 days" = 18 Aug – 15 Nov of each year. The page table is complete. **Query-level tables are partial** (Google drops about 40% of clicks when queries are included), so use them for shares and rates, not totals. Position is impression-weighted.
- **Page types (from the site's templates):** `review` = current review template (mostly 2022+); `old_review` = old template (2009–2021); `comparison` = "X vs Y" pages; `review_list` = brand/category listings ("1366 Running Shoes Reviews"); `top_list` = best-of guides; `guide` = /guide/ pages; `article` = news/blog; `home` = homepage.
- **GA4:** by page title (no URLs), 1 Jan – 20 Nov 2026 vs 2 Jan – 21 Nov 2025. **"Key events" is probably the affiliate-click event, but this is not verified** (33k key events vs 42k `affiliate_click` events in 2026). Engagement time is not comparable year on year.
- **Umami (the site's own analytics):** human sessions only (bot filter), **17–19 Nov 2026 only**, on the newly launched design: a short sample. Newsletter: **only Shoe Finder sign-ups are tracked as events**; other sign-up forms aren't measured.

## 2. Data

### 2.1 Search clicks per month by page type (Search Console, complete)
| month | total | review | comparison | old_review | review_list | top_list | home | article | gps_review | guide | other |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 2025-08 | 186,816 | 75,063 | 72,292 | 19,005 | 6,392 | 571 | 6,039 | 5,365 | 1,244 | 292 | 553 |
| 2025-09 | 162,539 | 68,410 | 59,667 | 16,858 | 4,807 | 499 | 5,829 | 4,472 | 1,143 | 400 | 454 |
| 2025-10 | 161,172 | 72,564 | 53,299 | 18,065 | 4,248 | 569 | 5,244 | 4,703 | 1,759 | 341 | 380 |
| 2025-11 | 152,714 | 66,107 | 53,548 | 16,498 | 3,324 | 595 | 5,051 | 5,064 | 1,867 | 275 | 385 |
| 2025-12 | 107,498 | 46,959 | 36,869 | 11,728 | 2,141 | 480 | 3,977 | 3,418 | 1,440 | 191 | 295 |
| 2026-01 | 119,752 | 48,689 | 43,470 | 13,701 | 3,081 | 583 | 4,262 | 3,887 | 1,533 | 204 | 342 |
| 2026-02 | 114,175 | 43,038 | 44,696 | 13,099 | 3,266 | 709 | 3,641 | 3,703 | 1,389 | 191 | 443 |
| 2026-03 | 128,590 | 47,972 | 51,332 | 14,727 | 3,606 | 1,026 | 3,913 | 3,913 | 1,367 | 157 | 577 |
| 2026-04 | 120,275 | 39,600 | 51,017 | 15,306 | 3,501 | 676 | 4,311 | 4,033 | 1,118 | 117 | 596 |
| 2026-05 | 108,848 | 34,477 | 45,942 | 14,977 | 3,424 | 595 | 3,898 | 3,771 | 1,150 | 118 | 496 |
| 2026-06 | 87,942 | 28,972 | 35,086 | 12,648 | 2,566 | 504 | 3,514 | 2,984 | 1,076 | 122 | 470 |
| 2026-07 | 83,367 | 26,653 | 34,410 | 11,782 | 2,038 | 457 | 3,117 | 2,810 | 1,582 | 81 | 437 |
| 2026-08 | 81,727 | 25,217 | 34,245 | 11,851 | 2,107 | 501 | 2,825 | 2,847 | 1,629 | 93 | 412 |
| 2026-09 | 69,749 | 22,442 | 28,241 | 10,505 | 1,615 | 449 | 2,490 | 2,545 | 1,065 | 88 | 309 |
| 2026-10 | 59,855 | 18,635 | 23,467 | 9,574 | 1,559 | 339 | 2,407 | 2,412 | 1,030 | 133 | 299 |

CTR % per month:
| month | review | comparison | old_review | review_list | top_list | home |
|---|---|---|---|---|---|---|
| 2025-08 | 1.18 | 2.75 | 0.87 | 0.60 | 1.37 | 0.93 |
| 2025-09 | 1.07 | 2.56 | 0.91 | 0.49 | 1.08 | 1.29 |
| 2025-10 | 1.19 | 2.60 | 1.04 | 0.57 | 1.28 | 1.84 |
| 2025-11 | 1.12 | 2.33 | 0.98 | 0.54 | 1.05 | 2.30 |
| 2025-12 | 1.08 | 1.74 | 0.84 | 0.43 | 1.15 | 2.15 |
| 2026-01 | 0.89 | 1.58 | 0.83 | 0.49 | 0.73 | 2.08 |
| 2026-02 | 0.83 | 1.48 | 0.71 | 0.36 | 0.71 | 3.08 |
| 2026-03 | 0.81 | 1.30 | 0.84 | 0.22 | 0.63 | 3.38 |
| 2026-04 | 0.65 | 1.11 | 0.75 | 0.38 | 0.51 | 2.22 |
| 2026-05 | 0.64 | 1.34 | 0.73 | 0.39 | 0.58 | 1.54 |
| 2026-06 | 0.75 | 1.48 | 0.75 | 0.48 | 0.60 | 1.15 |
| 2026-07 | 0.79 | 1.70 | 0.77 | 0.47 | 0.70 | 1.47 |
| 2026-08 | 0.79 | 2.00 | 0.83 | 0.45 | 0.60 | 0.89 |
| 2026-09 | 0.82 | 1.99 | 0.86 | 0.51 | 0.69 | 1.33 |
| 2026-10 | 0.80 | 1.85 | 0.92 | 0.54 | 0.17 | 0.73 |

### 2.2 CTR at the same ranking position, 18 Aug – 15 Nov 2025 vs 2026 (query-level, partial)
| page type | position | CTR 2025 % | CTR 2026 % | impr. 2025 | impr. 2026 |
|---|---|---|---|---|---|
| review | 1 | 3.84 | 1.09 | 244,943 | 129,067 |
| review | 2 | 4.77 | 1.07 | 306,982 | 169,846 |
| review | 3 | 3.70 | 1.72 | 465,613 | 205,038 |
| review | 4-5 | 2.97 | 2.09 | 1,206,913 | 440,781 |
| review | 6-10 | 1.22 | 0.71 | 4,551,998 | 2,639,402 |
| review | 11+ | 0.40 | 0.32 | 2,268,040 | 966,200 |
| comparison | 1 | 15.70 | 5.97 | 277,586 | 167,852 |
| comparison | 2 | 8.67 | 5.39 | 314,410 | 255,740 |
| comparison | 3 | 5.34 | 3.16 | 147,909 | 139,946 |
| comparison | 4-5 | 3.83 | 1.59 | 182,648 | 240,890 |
| comparison | 6-10 | 1.01 | 0.52 | 822,194 | 860,340 |
| comparison | 11+ | 0.50 | 0.50 | 418,856 | 96,190 |
| old_review | 1 | 3.99 | 2.54 | 132,907 | 91,000 |
| old_review | 2 | 3.26 | 1.64 | 155,811 | 138,188 |
| old_review | 3 | 2.22 | 1.44 | 174,220 | 155,960 |
| old_review | 4-5 | 1.71 | 1.07 | 338,157 | 370,236 |
| old_review | 6-10 | 1.00 | 0.60 | 749,008 | 854,241 |
| old_review | 11+ | 0.38 | 0.25 | 624,901 | 434,821 |
| review_list | 1 | 2.12 | 1.33 | 27,756 | 20,195 |
| review_list | 2 | 4.84 | 0.89 | 20,639 | 5,727 |
| review_list | 3 | 2.43 | 0.42 | 33,194 | 25,652 |
| review_list | 4-5 | 0.79 | 0.71 | 144,085 | 43,449 |
| review_list | 6-10 | 0.63 | 0.78 | 443,091 | 158,522 |
| review_list | 11+ | 0.29 | 0.37 | 438,633 | 253,403 |

### 2.3 Search intent of queries (query-level, partial; same windows)
Classified by keywords in the query, first match wins: versus (vs/or/compare/difference) → sizing/fit → specs → best/top → review → price/buy → alternatives → release/new → durability → everything else (mostly bare model names).
| query intent | impr. 2025 | impr. 2026 | clicks 2025 | clicks 2026 | CTR 25 % | CTR 26 % | pos 25 | pos 26 |
|---|---|---|---|---|---|---|---|---|
| model name only/other | 7,902,403 | 7,030,987 | 90,627 | 38,424 | 1.15 | 0.55 | 12.8 | 8.9 |
| review | 5,813,516 | 1,434,358 | 105,496 | 30,247 | 1.81 | 2.11 | 7.1 | 6.6 |
| versus | 971,388 | 1,181,533 | 87,917 | 32,194 | 9.05 | 2.72 | 4.8 | 5.0 |
| best/top | 754,233 | 1,063,444 | 2,953 | 1,848 | 0.39 | 0.17 | 25.3 | 8.9 |
| release/new | 521,749 | 532,635 | 5,074 | 2,547 | 0.97 | 0.48 | 11.4 | 8.5 |
| alternatives | 17,301 | 122,551 | 632 | 213 | 3.65 | 0.17 | 7.1 | 4.1 |
| specs | 410,732 | 106,559 | 2,577 | 439 | 0.63 | 0.41 | 7.2 | 6.8 |
| sizing/fit | 135,969 | 39,407 | 1,999 | 401 | 1.47 | 1.02 | 10.3 | 8.0 |
| price/buy | 105,720 | 25,076 | 466 | 134 | 0.44 | 0.53 | 11.9 | 7.7 |
| durability | 29,289 | 16,017 | 513 | 156 | 1.75 | 0.97 | 12.3 | 14.5 |

### 2.4 Clicks by publication year (same 90-day windows; pages with any click)
| type | published | pages | clicks 90d 2026 | clicks 90d 2025 | change % | pages ≥30 clicks (2026) |
|---|---|---|---|---|---|---|
| comparison | 2020 | 55 | 24,856 | 56,997 | -56 | 40 |
| comparison | 2021 | 18 | 10,267 | 25,779 | -60 | 17 |
| comparison | 2022 | 5 | 307 | 1,019 | -70 | 2 |
| comparison | 2023 | 25 | 6,303 | 19,311 | -67 | 22 |
| comparison | 2024 | 32 | 5,854 | 27,196 | -78 | 30 |
| comparison | 2025 | 30 | 19,852 | 38,660 | -49 | 28 |
| comparison | 2026 | 12 | 9,383 | 0 |  | 12 |
| gps_review | 2018 | 6 | 191 | 376 | -49 | 3 |
| gps_review | 2019 | 5 | 196 | 585 | -66 | 2 |
| gps_review | 2020 | 3 | 153 | 300 | -49 | 2 |
| gps_review | 2021 | 4 | 210 | 768 | -73 | 3 |
| gps_review | 2023 | 4 | 2,401 | 1,788 | 34 | 3 |
| gps_review | 2024 | 4 | 287 | 517 | -44 | 3 |
| old_review | 2009 | 8 | 49 | 38 | 29 | 0 |
| old_review | 2010 | 1 | 25 | 18 | 39 | 0 |
| old_review | 2011 | 29 | 398 | 556 | -28 | 2 |
| old_review | 2012 | 82 | 1,114 | 1,419 | -21 | 7 |
| old_review | 2013 | 67 | 679 | 935 | -27 | 5 |
| old_review | 2014 | 76 | 1,388 | 1,483 | -6 | 6 |
| old_review | 2015 | 75 | 1,291 | 1,939 | -33 | 8 |
| old_review | 2016 | 101 | 1,547 | 2,680 | -42 | 13 |
| old_review | 2017 | 114 | 1,792 | 2,734 | -34 | 14 |
| old_review | 2018 | 100 | 2,056 | 3,382 | -39 | 20 |
| old_review | 2019 | 104 | 4,722 | 7,929 | -40 | 51 |
| old_review | 2020 | 97 | 4,766 | 9,287 | -49 | 53 |
| old_review | 2021 | 90 | 5,947 | 11,068 | -46 | 65 |
| old_review | 2022 | 55 | 4,093 | 8,795 | -53 | 42 |
| review | 2012 | 1 | 194 | 271 | -28 | 1 |
| review | 2022 | 32 | 2,361 | 7,218 | -67 | 24 |
| review | 2023 | 145 | 13,015 | 47,742 | -73 | 121 |
| review | 2024 | 107 | 16,906 | 80,958 | -79 | 95 |
| review | 2025 | 75 | 22,291 | 72,448 | -69 | 71 |
| review | 2026 | 8 | 6,695 | 0 |  | 8 |

### 2.5 Top 40 pages by search clicks, 18 Aug – 15 Nov 2026 vs same window 2025 ("–" = page didn't exist or had no clicks)
| page | type | clicks 26 | clicks 25 | chg % | CTR 26 | CTR 25 | pos 26 | pos 25 | impr 26 |
|---|---|---|---|---|---|---|---|---|---|
| / | home | 7,724 | 16,333 | -53 | 0.63 | 1.44 | 8.4 | 29.6 | 1,226,131 |
| /comparison/asics-megablast-vs-asics-superblast-2/ | comparison | 3,412 | – |  | 2.75 |  | 5.2 |  | 124,021 |
| /comparison/hoka-clifton-vs-hoka-bondi/ | comparison | 3,234 | 988 | 227 | 1.66 | 0.77 | 4.7 | 7.2 | 194,472 |
| /reviews/road/nike-pegasus-42-review/ | review | 2,879 | – |  | 0.90 |  | 7.1 |  | 318,770 |
| /comparison/asics-superblast-vs-asics-novablast-3/ | comparison | 2,634 | 6,730 | -61 | 2.69 | 5.62 | 5.3 | 4.9 | 97,908 |
| /comparison/nike-vomero-premium-vs-nike-vomero-plus/ | comparison | 2,625 | – |  | 1.38 |  | 6.9 |  | 190,648 |
| /comparison/asics-nimbus-vs-asics-novablast/ | comparison | 2,284 | 4,387 | -48 | 3.82 | 5.10 | 4.5 | 5.0 | 59,746 |
| /comparison/asics-nimbus-28-vs-asics-nimbus-27/ | comparison | 2,239 | – |  | 3.43 |  | 4.9 |  | 65,224 |
| /comparison/asics-nimbus-vs-asics-cumulus/ | comparison | 2,173 | 6,057 | -64 | 3.18 | 5.60 | 4.7 | 5.3 | 68,321 |
| /comparison/asics-nimbus-vs-asics-kayano/ | comparison | 2,117 | 7,424 | -71 | 2.59 | 4.67 | 4.1 | 4.7 | 81,853 |
| /comparison/nike-vaporfly-next-vs-nike-alphafly-next/ | comparison | 2,109 | 4,518 | -53 | 2.72 | 3.72 | 4.6 | 4.6 | 77,675 |
| /comparison/brooks-ghost-vs-brooks-adrenaline/ | comparison | 1,952 | 3,658 | -47 | 1.68 | 2.59 | 5.1 | 6.0 | 116,145 |
| /reviews/gps/garmin-forerunner-265/ | gps_review | 1,676 | 626 | 168 | 1.61 | 0.53 | 8.4 | 7.1 | 103,876 |
| /comparison/asics-megablast-vs-asics-novablast/ | comparison | 1,601 | – |  | 4.57 |  | 4.7 |  | 35,050 |
| /comparison/asics-nimbus-28-vs-hoka-bondi-9/ | comparison | 1,539 | – |  | 3.15 |  | 4.9 |  | 48,927 |
| /comparison/new-balance-1080-vs-new-balance-880/ | comparison | 1,482 | 3,503 | -58 | 2.78 | 2.97 | 5.4 | 6.7 | 53,288 |
| /comparison/asics-novablast-vs-asics-kayano/ | comparison | 1,362 | 4,504 | -70 | 4.11 | 5.04 | 4.7 | 4.5 | 33,114 |
| /reviews/road/nike-vomero-plus-review/ | review | 1,314 | 1,618 | -19 | 0.48 | 1.84 | 5.2 | 13.6 | 274,460 |
| /comparison/brooks-ghost-vs-brooks-glycerin/ | comparison | 1,284 | 2,359 | -46 | 1.53 | 2.35 | 5.3 | 5.5 | 83,928 |
| /reviews/road/nike-pegasus-premium-review/ | review | 1,223 | 1,763 | -31 | 0.82 | 1.46 | 9.2 | 7.4 | 149,728 |
| /comparison/nike-alphafly-next-vs-adidas-adizero-adios-pro/ | comparison | 1,218 | 2,092 | -42 | 2.98 | 3.97 | 5.8 | 7.4 | 40,927 |
| /comparison/asics-kayano-vs-asics-gt-2000/ | comparison | 1,216 | 3,102 | -61 | 2.76 | 4.07 | 5.5 | 5.8 | 44,015 |
| /reviews/road/puma-velocity-nitro-4-review/ | review | 1,181 | 1,069 | 10 | 0.60 | 1.28 | 8.8 | 10.7 | 196,206 |
| /comparison/hoka-bondi-9-vs-hoka-bondi-8/ | comparison | 1,150 | 662 | 74 | 1.71 | 0.83 | 5.5 | 6.6 | 67,252 |
| /reviews/road/new-balance-fuelcell-rebel-v5-review/ | review | 1,146 | 3,285 | -65 | 0.76 | 0.97 | 8.7 | 8.1 | 150,140 |
| /comparison/asics-gt-1000-vs-asics-gt-2000/ | comparison | 1,135 | 2,546 | -55 | 2.80 | 4.00 | 5.3 | 6.4 | 40,570 |
| /comparison/asics-metaspeed-edge-tokyo-vs-asics-metaspeed-sky-tokyo/ | comparison | 1,011 | – |  | 2.21 |  | 6.0 |  | 45,777 |
| /comparison/nike-pegasus-vs-nike-vomero/ | comparison | 1,008 | 3,694 | -73 | 1.29 | 3.00 | 5.5 | 5.4 | 78,212 |
| /comparison/asics-cumulus-vs-asics-novablast/ | comparison | 996 | 2,496 | -60 | 4.67 | 9.11 | 4.9 | 4.6 | 21,338 |
| /comparison/saucony-endorphin-speed-5-vs-saucony-endorphin-speed-4/ | comparison | 990 | 5,014 | -80 | 2.05 | 4.31 | 7.0 | 7.7 | 48,180 |
| /comparison/asics-metaspeed-sky-vs-asics-metaspeed-edge/ | comparison | 970 | 3,002 | -68 | 2.97 | 4.45 | 5.5 | 5.7 | 32,670 |
| /comparison/saucony-ride-19-vs-saucony-ride-18/ | comparison | 966 | – |  | 4.23 |  | 5.5 |  | 22,853 |
| /reviews/road/asics-gel-cumulus-28-review/ | review | 964 | – |  | 2.06 |  | 7.2 |  | 46,711 |
| /reviews/road/new-balance-fresh-foam-x-more-v6-review/ | review | 944 | – |  | 0.68 |  | 8.3 |  | 139,411 |
| /comparison/nike-zoom-fly-vs-nike-vaporfly-next/ | comparison | 941 | 2,413 | -61 | 2.85 | 3.81 | 5.2 | 7.4 | 33,005 |
| /comparison/nike-pegasus-premium-vs-nike-pegasus/ | comparison | 921 | 1,626 | -43 | 1.60 | 2.15 | 7.0 | 6.4 | 57,398 |
| /content/alternatives-to-the-asics-nimbus-26/ | article | 918 | 1,513 | -39 | 0.99 | 3.68 | 3.8 | 6.5 | 93,159 |
| /comparison/brooks-glycerin-23-vs-asics-nimbus-28/ | comparison | 893 | – |  | 4.27 |  | 4.3 |  | 20,911 |
| /reviews/road/asics-gel-nimbus-28-review/ | review | 853 | – |  | 2.50 |  | 7.5 |  | 34,115 |
| /comparison/nike-vomero-plus-vs-nike-pegasus-plus/ | comparison | 801 | – |  | 1.78 |  | 6.3 |  | 45,098 |

### 2.6 The 25 pages that lost the most clicks (same windows)
| page | type | clicks 26 | clicks 25 | CTR 26 | CTR 25 | pos 26 | pos 25 | impr 26 | impr 25 |
|---|---|---|---|---|---|---|---|---|---|
| / | home | 7,724 | 16,333 | 0.63 | 1.44 | 8.4 | 29.6 | 1,226,131 | 1,136,837 |
| /comparison/asics-nimbus-vs-asics-kayano/ | comparison | 2,117 | 7,424 | 2.59 | 4.67 | 4.1 | 4.7 | 81,853 | 159,038 |
| /comparison/asics-superblast-vs-asics-novablast-3/ | comparison | 2,634 | 6,730 | 2.69 | 5.62 | 5.3 | 4.9 | 97,908 | 119,757 |
| /comparison/brooks-ghost-17-vs-brooks-ghost-16/ | comparison | 746 | 4,802 | 1.23 | 3.80 | 7.5 | 6.3 | 60,627 | 126,524 |
| /comparison/saucony-endorphin-speed-5-vs-saucony-endorphin-speed-4/ | comparison | 990 | 5,014 | 2.05 | 4.31 | 7.0 | 7.7 | 48,180 | 116,265 |
| /reviews/road/adidas-adizero-evo-sl-review/ | review | 742 | 4,642 | 0.72 | 0.54 | 8.4 | 8.6 | 102,993 | 860,974 |
| /comparison/asics-nimbus-vs-asics-cumulus/ | comparison | 2,173 | 6,057 | 3.18 | 5.60 | 4.7 | 5.3 | 68,321 | 108,066 |
| /comparison/hoka-clifton-10-vs-hoka-clifton-9/ | comparison | 655 | 3,941 | 1.40 | 3.05 | 5.5 | 4.4 | 46,726 | 129,015 |
| /reviews/road/hoka-rocket-x-3-review/ | review | 409 | 3,575 | 1.37 | 1.93 | 10.1 | 21.4 | 29,887 | 184,848 |
| /comparison/asics-novablast-vs-asics-kayano/ | comparison | 1,362 | 4,504 | 4.11 | 5.04 | 4.7 | 4.5 | 33,114 | 89,411 |
| /reviews/road/adidas-adistar-4-review/ | review | 461 | 3,535 | 1.58 | 4.23 | 8.0 | 21.9 | 29,189 | 83,518 |
| /comparison/asics-gel-nimbus-27-vs-asics-gel-nimbus-26/ | comparison | 482 | 3,498 | 0.77 | 2.73 | 7.6 | 7.4 | 62,252 | 128,249 |
| /reviews/road/asics-novablast-5-review/ | review | 443 | 3,420 | 0.70 | 0.89 | 8.9 | 8.8 | 63,035 | 384,675 |
| /reviews/road/asics-s4-yogiri-review/ | review | 300 | 3,095 | 1.22 | 7.01 | 7.1 | 4.2 | 24,523 | 44,149 |
| /comparison/asics-metaspeed-sky-tokyo-vs-asics-metaspeed-sky-paris/ | comparison | 664 | 3,421 | 1.75 | 6.50 | 7.0 | 6.1 | 37,951 | 52,601 |
| /comparison/nike-pegasus-vs-nike-vomero/ | comparison | 1,008 | 3,694 | 1.29 | 3.00 | 5.5 | 5.4 | 78,212 | 123,312 |
| /comparison/nike-pegasus-plus-vs-nike-pegasus-41/ | comparison | 760 | 3,410 | 1.89 | 3.45 | 6.7 | 6.4 | 40,254 | 98,820 |
| /comparison/asics-novablast-5-vs-asics-novablast-4/ | comparison | 401 | 2,937 | 0.72 | 1.60 | 7.6 | 7.1 | 55,989 | 183,522 |
| /reviews/road/asics-metaspeed-sky-tokyo-review/ | review | 250 | 2,691 | 0.22 | 1.22 | 10.4 | 6.8 | 115,050 | 219,892 |
| /comparison/nike-vaporfly-next-vs-nike-alphafly-next/ | comparison | 2,109 | 4,518 | 2.72 | 3.72 | 4.6 | 4.6 | 77,675 | 121,436 |
| /reviews/nike/stability/ | review_list | 284 | 2,574 | 0.97 | 3.10 | 9.3 | 11.9 | 29,136 | 83,005 |
| /reviews/road/brooks-ghost-17-review/ | review | 72 | 2,245 | 1.05 | 4.03 | 13.3 | 8.3 | 6,872 | 55,686 |
| /comparison/new-balance-rebel-v5-vs-new-balance-rebel-v4/ | comparison | 658 | 2,806 | 1.50 | 4.81 | 7.5 | 5.7 | 43,896 | 58,333 |
| /reviews/road/new-balance-fuelcell-rebel-v5-review/ | review | 1,146 | 3,285 | 0.76 | 0.97 | 8.7 | 8.1 | 150,140 | 337,004 |
| /comparison/nike-vaporfly-4-vs-nike-vaporfly-3/ | comparison | 517 | 2,641 | 1.36 | 4.45 | 7.3 | 5.9 | 37,967 | 59,357 |

### 2.7 Top 30 queries by impressions, 18 Aug – 15 Nov 2026
Note: impressions for the query "best running shoes" (shown mostly with the homepage) were 4–32k a month from Jul 2025 to Sep 2026, then 256k in Oct 2026 and 444k in 1–17 Nov 2026, with CTR near 0. **(hypothesis)** This may be RSG appearing in AI Overview / AI Mode results; Search Console doesn't say.
| query | impr. | clicks | CTR % | avg pos | main RSG page |
|---|---|---|---|---|---|
| best running shoes | 715,158 | 170 | 0.02 | 6.2 | / |
| running shoes review | 244,331 | 463 | 0.19 | 3.3 | / |
| vomero plus | 125,066 | 86 | 0.07 | 4.2 | /reviews/road/nike-vomero-plus-review/ |
| running shoes reviews | 123,865 | 189 | 0.15 | 4.1 | / |
| nike pegasus 42 | 118,451 | 321 | 0.27 | 8.4 | /reviews/road/nike-pegasus-42-review/ |
| best running shoes 2025 | 86,219 | 0 | 0.00 | 8.9 | / |
| asics metaspeed sky tokyo | 84,149 | 42 | 0.05 | 10.6 | /reviews/road/asics-metaspeed-sky-tokyo-review/ |
| puma velocity nitro 4 | 83,286 | 254 | 0.30 | 9.0 | /reviews/road/puma-velocity-nitro-4-review/ |
| nike pegasus premium | 68,216 | 162 | 0.24 | 10.7 | /reviews/road/nike-pegasus-premium-review/ |
| under armour running shoes | 55,140 | 27 | 0.05 | 11.8 | /reviews/under-armour/all/ |
| coros pace 3 | 53,926 | 175 | 0.32 | 8.3 | /reviews/gps/coros-pace-3/ |
| alternatives to asics | 52,260 | 0 | 0.00 | 2.2 | /content/alternatives-to-the-asics-nimbus-26/ |
| nike vs adidas running shoes | 50,828 | 0 | 0.00 | 7.4 | /comparison/nike-pegasus-vs-adidas-ultra-boost/ |
| nike vomero plus | 48,989 | 77 | 0.16 | 7.6 | /comparison/nike-vomero-premium-vs-nike-vomero-plus/ |
| running shoes comparison | 47,293 | 66 | 0.14 | 4.8 | / |
| under armour hovr | 46,732 | 0 | 0.00 | 3.2 | /reviews/road/under-armour-hovr-turbulence-2-review/ |
| pegasus 42 | 46,140 | 219 | 0.47 | 6.7 | /reviews/road/nike-pegasus-42-review/ |
| runrepeat alternatives | 46,075 | 0 | 0.00 | 4.6 | / |
| compare running shoes | 41,525 | 85 | 0.20 | 4.6 | / |
| saucony | 35,986 | 4 | 0.01 | 3.7 | /reviews/road/saucony-endorphin-speed-4-review/ |
| nike metcon 6 | 31,174 | 9 | 0.03 | 8.1 | /2020/07/nike-launches-the-metcon-6/ |
| nike structure 26 | 30,869 | 97 | 0.31 | 10.7 | /reviews/road/nike-structure-26-review/ |
| forerunner 265 | 29,813 | 43 | 0.14 | 10.7 | /reviews/gps/garmin-forerunner-265/ |
| nike pegasus 40 | 26,714 | 66 | 0.25 | 10.5 | /reviews/road/nike-air-zoom-pegasus-40-review/ |
| nike winflo 11 | 25,973 | 71 | 0.27 | 9.6 | /reviews/road/nike-winflo-11-review/ |
| cloudrunner 2 | 25,002 | 45 | 0.18 | 7.0 | /reviews/road/on-cloudrunner-2-review/ |
| new balance fresh foam x more v6 | 24,974 | 114 | 0.46 | 8.1 | /reviews/road/new-balance-fresh-foam-x-more-v6-review/ |
| skechers aero burst | 23,709 | 36 | 0.15 | 11.1 | /reviews/road/skechers-aero-burst-review/ |
| metcon 6 | 23,672 | 8 | 0.03 | 6.4 | /2020/07/nike-launches-the-metcon-6/ |
| on cloudrunner 2 | 23,477 | 56 | 0.24 | 7.2 | /reviews/road/on-cloudrunner-2-review/ |

Queries at position 4–15 with ≥1,500 impressions in those 90 days, grouped by the type of page that ranks:
| landing page type | queries | impressions | clicks |
|---|---|---|---|
| review | 253 | 1,076,924 | 6,399 |
| old_review | 122 | 451,510 | 1,916 |
| article | 26 | 191,683 | 30 |
| home | 16 | 137,655 | 112 |
| comparison | 38 | 129,164 | 787 |
| review_list | 21 | 67,819 | 176 |
| page | 2 | 26,105 | 2 |
| gps_review | 10 | 24,646 | 84 |
| top_list | 5 | 15,529 | 129 |
| archive | 1 | 6,127 | 0 |
| shoe_finder | 1 | 1,991 | 27 |

### 2.8 GA4: page views and key events by page type
| period | page type | views | share % | key events | key ev. /100 views | avg engaged s/user |
|---|---|---|---|---|---|---|
| 2026 (1 Jan-20 Nov) | review | 1,050,402 | 43.5 | 12,656 | 1.20 | 22 |
| 2026 (1 Jan-20 Nov) | comparison | 551,645 | 22.9 | 15,293 | 2.77 | 32 |
| 2026 (1 Jan-20 Nov) | listing/hub | 325,704 | 13.5 | 2,633 | 0.81 | 16 |
| 2026 (1 Jan-20 Nov) | homepage | 208,921 | 8.7 | 0 | 0.00 | 8 |
| 2026 (1 Jan-20 Nov) | article/other | 192,802 | 8.0 | 530 | 0.27 | 19 |
| 2026 (1 Jan-20 Nov) | best/top list | 58,950 | 2.4 | 1,645 | 2.79 | 23 |
| 2026 (1 Jan-20 Nov) | shoe_finder | 25,237 | 1.0 | 685 | 2.71 | 50 |
| 2025 (2 Jan-21 Nov) | review | 2,535,342 | 48.0 | 34,320 | 1.35 | 11 |
| 2025 (2 Jan-21 Nov) | comparison | 1,096,563 | 20.8 | 27,896 | 2.54 | 12 |
| 2025 (2 Jan-21 Nov) | listing/hub | 765,529 | 14.5 | 8,937 | 1.17 | 13 |
| 2025 (2 Jan-21 Nov) | article/other | 387,577 | 7.3 | 1,103 | 0.28 | 10 |
| 2025 (2 Jan-21 Nov) | homepage | 288,569 | 5.5 | 0 | 0.00 | 16 |
| 2025 (2 Jan-21 Nov) | best/top list | 148,698 | 2.8 | 4,378 | 2.94 | 18 |
| 2025 (2 Jan-21 Nov) | shoe_finder | 55,291 | 1.0 | 1,684 | 3.05 | 40 |

GA4 events, site-wide:
| GA4 event | 2 Jan–21 Nov 2025 | 1 Jan–20 Nov 2026 | change % |
|---|---|---|---|
| page_view | 5,304,609 | 2,413,661 | -54 |
| session_start | 3,613,673 | 1,921,054 | -47 |
| affiliate_click | 98,323 | 41,789 | -57 |
| top_pick_click | 43,782 | 12,961 | -70 |
| comparison_test | 297,670 | 133,550 | -55 |
| view_search_results | 222,105 | 72,633 | -67 |
| Wizard-Heatmap-2024 | 45,515 | 27,945 | -39 |
| click | 21,711 | 10,571 | -51 |
| reviews_best_click | 3,467 | 953 | -73 |

### 2.9 GA4: top pages by views in 2026, with key events per 100 views
| type | page | views 2026 | views 2025 | key ev. 2026 | /100 views 2026 | /100 views 2025 |
|---|---|---|---|---|---|---|
| comparison | ASICS Superblast vs ASICS Novablast | 20,968 | 50,140 | 969 | 4.62 | 1.43 |
| comparison | Hoka Clifton vs Hoka Bondi | 19,243 | 7,283 | 410 | 2.13 | 1.98 |
| comparison | ASICS Megablast vs ASICS Superblast 2 | 18,500 | 0 | 548 | 2.96 | 0.00 |
| comparison | Asics Nimbus vs Asics Kayano - Detailed Comparison | 16,878 | 45,225 | 237 | 1.40 | 1.42 |
| comparison | Asics Nimbus vs Asics Cumulus - Detailed Comparison | 15,069 | 50,950 | 257 | 1.71 | 2.88 |
| comparison | ASICS Nimbus vs ASICS Novablast | 14,976 | 24,346 | 488 | 3.26 | 1.42 |
| comparison | Nike Vaporfly vs Nike Alphafly | 13,771 | 14,766 | 91 | 0.66 | 0.63 |
| comparison | Nike Vomero Premium vs Nike Vomero Plus | 13,447 | 1 | 133 | 0.99 | 0.00 |
| comparison | Brooks Ghost vs Brooks Adrenaline | 13,124 | 34,241 | 653 | 4.98 | 5.07 |
| comparison | ASICS Nimbus 28 vs ASICS Nimbus 27 | 11,028 | 0 | 310 | 2.81 | 0.00 |
| comparison | ASICS Novablast vs ASICS Kayano | 10,143 | 28,411 | 324 | 3.19 | 1.24 |
| comparison | New Balance 1080 vs New Balance 880 | 9,760 | 22,118 | 319 | 3.27 | 3.49 |
| comparison | Nike Pegasus Vs Nike Vomero | 9,049 | 15,890 | 92 | 1.02 | 0.87 |
| comparison | Brooks Ghost vs Brooks Glycerin | 8,901 | 15,152 | 264 | 2.97 | 3.08 |
| comparison | Saucony Endorphin Speed 5 vs Saucony Endorphin Speed 4 | 8,555 | 14,249 | 416 | 4.86 | 2.44 |
| comparison | HOKA Clifton 10 vs HOKA Clifton 9 | 8,554 | 14,812 | 272 | 3.18 | 2.82 |
| comparison | Brooks Ghost 17 vs Brooks Ghost 16 | 8,284 | 17,453 | 775 | 9.36 | 3.87 |
| comparison | ASICS Nimbus 28 vs HOKA Bondi 9 | 8,111 | 0 | 209 | 2.58 | 0.00 |
| comparison | Asics Kayano vs Asics GT 2000 | 7,805 | 15,459 | 139 | 1.78 | 2.11 |
| comparison | Asics GT 1000 vs Asics GT 2000 | 7,383 | 18,953 | 219 | 2.97 | 3.17 |
| comparison | Brooks Glycerin vs Brooks Glycerin GTS | 7,249 | 18,390 | 121 | 1.67 | 3.80 |
| comparison | Hoka Bondi 9 vs Hoka Bondi 8 | 7,238 | 7,626 | 379 | 5.24 | 5.36 |
| comparison | ASICS Megablast vs ASICS Novablast | 7,212 | 0 | 343 | 4.76 | 0.00 |
| comparison | ASICS Novablast 5 vs ASICS Novablast 4 | 7,060 | 35,864 | 518 | 7.34 | 2.80 |
| comparison | Asics Cumulus vs Asics Novablast | 6,988 | 14,000 | 299 | 4.28 | 1.89 |
| comparison | ASICS Metaspeed Sky vs ASICS Metaspeed Edge | 6,547 | 28,573 | 116 | 1.77 | 2.87 |
| comparison | Nike Alphafly vs Adidas Adizero Adios Pro | 6,539 | 12,082 | 86 | 1.32 | 0.76 |
| comparison | Nike Pegasus Premium vs Nike Pegasus | 6,397 | 5,791 | 59 | 0.92 | 0.98 |
| comparison | Nike Zoom Fly vs Nike Vaporfly | 6,248 | 18,663 | 32 | 0.51 | 1.06 |
| comparison | Nike Winflo vs Nike Pegasus | 6,087 | 15,841 | 86 | 1.41 | 1.08 |
| review | Nike Vomero Plus Review | 15,081 | 8,374 | 180 | 1.19 | 1.42 |
| review | adidas adizero Evo SL Review | 14,340 | 50,159 | 241 | 1.68 | 0.90 |
| review | Nike Pegasus 42 Review | 12,012 | 0 | 73 | 0.61 | 0.00 |
| review | ASICS Novablast 5 Review | 11,558 | 45,274 | 403 | 3.49 | 1.40 |
| review | PUMA Velocity Nitro 4 Review | 11,141 | 5,636 | 58 | 0.52 | 0.80 |
| review | New Balance FuelCell Rebel v5 Review | 10,073 | 13,651 | 91 | 0.90 | 0.59 |
| review | ASICS Gel Nimbus 28 Review | 9,870 | 0 | 93 | 0.94 | 0.00 |
| review | ASICS Superblast 2 Review | 9,823 | 20,011 | 430 | 4.38 | 0.90 |
| review | New Balance Fresh Foam X More v6 Review | 9,652 | 0 | 9 | 0.09 | 0.00 |
| review | adidas adiSTAR 4 Review | 9,297 | 8,103 | 140 | 1.51 | 0.88 |
| review | ASICS MEGABLAST Review | 8,233 | 2,732 | 41 | 0.50 | 0.51 |
| review | Nike Pegasus Premium Review | 7,864 | 13,403 | 82 | 1.04 | 0.93 |
| review | adidas adizero Boston 13 Review | 7,360 | 3,975 | 78 | 1.06 | 0.73 |
| review | HOKA Clifton 10 Review | 7,327 | 17,382 | 101 | 1.38 | 3.15 |
| review | ASICS Gel Kayano 32 Review | 6,819 | 19,253 | 117 | 1.72 | 3.40 |
| review | Nike Winflo 11 Review | 6,708 | 17,977 | 45 | 0.67 | 0.79 |
| review | Nike Vomero 18 Review | 6,504 | 26,707 | 52 | 0.80 | 0.59 |
| review | ASICS Gel Cumulus 28 Review | 6,490 | 0 | 28 | 0.43 | 0.00 |
| review | Nike Zoom Fly 6 Review | 6,436 | 24,357 | 56 | 0.87 | 0.54 |
| review | New Balance FuelCell SuperComp Trainer v3 Review | 6,302 | 10,007 | 148 | 2.35 | 1.81 |
| review | Adidas Adizero Adios Pro 4 Review | 6,226 | 22,585 | 66 | 1.06 | 0.66 |
| review | Brooks Glycerin 23 Review | 6,163 | 0 | 152 | 2.47 | 0.00 |
| review | Nike Structure 26 Review | 5,766 | 1,110 | 4 | 0.07 | 0.00 |
| review | Brooks Adrenaline GTS 25 Review | 5,713 | 0 | 82 | 1.44 | 0.00 |
| review | PUMA MagMax Nitro 2 Review | 5,616 | 0 | 20 | 0.36 | 0.00 |
| review | Nike Vomero Premium Review | 5,614 | 3,426 | 41 | 0.73 | 2.31 |
| review | New Balance Fresh Foam X 1080 v14 Review | 5,464 | 14,554 | 148 | 2.71 | 1.39 |
| review | ASICS SONICBLAST Review | 5,432 | 2,662 | 33 | 0.61 | 0.49 |
| review | Saucony Ride 19 Review | 5,349 | 0 | 26 | 0.49 | 0.00 |
| review | ASICS Gel-Contend 9 Review | 5,262 | 9,499 | 142 | 2.70 | 1.76 |
| best/top list | Best Running Shoes Right Now | 20,101 | 33,456 | 888 | 4.42 | 3.62 |
| best/top list | Best ASICS Running Shoes (August 2024) | 4,687 | 11,677 | 117 | 2.50 | 2.00 |
| best/top list | What Are the Best Daily Trainers Right Now ? | 4,199 | 9,155 | 182 | 4.33 | 3.92 |
| best/top list | Best Running Shoes for Heavy Runners (August 2024) | 3,123 | 8,299 | 82 | 2.63 | 2.51 |
| best/top list | Best Nike Running Shoes (August 2024) | 3,085 | 7,499 | 29 | 0.94 | 1.20 |
| best/top list | The 6 Best Running Shoes for Women (August 2024) | 2,877 | 7,848 | 86 | 2.99 | 4.27 |
| best/top list | Best New Balance Running Shoes (October 2024) | 2,582 | 6,334 | 69 | 2.67 | 2.49 |
| best/top list | Best Running Shoes for Beginners (August 2024) | 2,157 | 5,383 | 29 | 1.34 | 3.40 |
| best/top list | Best GPS Running Watches | 1,846 | 3,298 | 0 | 0.00 | 0.00 |
| best/top list | Best Adidas Running Shoes (August 2024) | 1,649 | 4,791 | 19 | 1.15 | 2.13 |
| best/top list | Best Hoka Running Shoes (May 2025) | 1,516 | 2,912 | 29 | 1.91 | 5.53 |
| best/top list | Best Brooks Running Shoes (August 2024) | 1,450 | 4,002 | 17 | 1.17 | 2.50 |
| article/other | Garmin Forerunner 265 ReviewThe Garmin Forerunner 265 is great for runners who a | 6,262 | 2,472 | 0 | 0.00 | 0.00 |
| article/other | Alternatives to the Asics Nimbus 26 | 6,062 | 14,706 | 0 | 0.00 | 0.02 |
| article/other | Effects of Running on the Female Body (by a Medical Doctor) | 3,312 | 6,259 | 0 | 0.00 | 0.00 |
| article/other | Decoding the Puma Running Lineup: How to Choose the Right Shoe for You | 3,235 | 192 | 44 | 1.36 | 2.60 |
| article/other | Alternatives to the Nike Zoom Fly 6 | 2,830 | 6,685 | 0 | 0.00 | 0.04 |
| article/other | Running Shoes Reviews and Comparisons | 2,519 | 0 | 0 | 0.00 | 0.00 |
| article/other | About Running Shoes Guru | 2,478 | 1,545 | 0 | 0.00 | 0.00 |
| article/other | A Formula to Calculate your Ideal Running Weight | 2,439 | 5,481 | 1 | 0.04 | 0.02 |
| article/other | Free Running Training Plans | 2,407 | 3,938 | 0 | 0.00 | 0.00 |
| article/other | adidas Terrex Agravic Flow 2 | 1,841 | 3,577 | 21 | 1.14 | 1.96 |
| article/other | The Global Economy of Sneakers: Where They're Made and Where They're Sold | 1,762 | 1,859 | 0 | 0.00 | 0.05 |
| article/other | Running Shoes Guru Giveaway | 1,666 | 1,122 | 0 | 0.00 | 0.00 |

### 2.10 On the site, human visits 17–19 Nov 2026 (Umami, new design; short sample)
Visits by source: search 5,811, direct 2,616, referral 34, social 21, email 13, internal 2, ai 2.

| landing page type | human visits | pages/visit | % 2+ pages | % visits with shop click | shop clicks | newsletter sign-ups |
|---|---|---|---|---|---|---|
| review | 2580 | 1.68 | 20.5 | 5.08 | 174 | 1 |
| comparison | 1670 | 1.29 | 14.1 | 5.51 | 106 | 1 |
| old_review | 1244 | 1.40 | 17.5 | 1.77 | 23 | 0 |
| review_list | 807 | 1.98 | 27.4 | 1.73 | 24 | 1 |
| home | 737 | 4.88 | 63.5 | 4.21 | 49 | 4 |
| article | 433 | 1.47 | 19.4 | 1.62 | 10 | 0 |
| gps_review | 104 | 1.35 | 14.4 | 1.92 | 2 | 0 |
| top_list | 75 | 2.28 | 22.7 | 6.67 | 6 | 0 |
| page | 43 | 1.47 | 23.3 | 0.00 | 0 | 0 |
| archive | 43 | 2.23 | 37.2 | 0.00 | 0 | 0 |
| shoe_finder | 8 | 4.75 | 50.0 | 0.00 | 0 | 1 |
| guide | 5 | 1.40 | 40.0 | 0.00 | 0 | 0 |

Shop clicks in the same days, by price spot, country and shop:
- **component:** price_card 71, price_block 68, deal_row 66, review_card 50, best_price_boxes 25, newer_card 21, mini_table 21, full_list 15, drawer 15, quiz_result 6, header_card 6, sticky_bar 5, text_link 5, item_button 4, bought_link 4, home_picks 3, picks_box 3, price_box 3
- **country:** US 310, GB 30, AU 14, DE 7, CA 5, GR 4, SE 3, NL 3
- **merchant:** Amazon 255, Amazon search UK 22, Amazon search US 18, Amazon search DE 17, Running Warehouse 14, Hoka 13, Zappos 13, Running Warehouse AU 8, Nike UK 5, Amazon search CA 4, amzn.to 4, runningwarehouse.com 4
- **store:** amazon 322, shop 72

## 3. Sample pages
Four live pages as plain text (fetched 20 Nov 2026; menus, ads and comments removed; layout lost; prices are what our server location saw, not necessarily what a US visitor sees). Search and GA4 numbers for each are in Sections 2.5 and 2.9:
- Nike Pegasus 42 review: our top review by search impressions, low conversion (0.61 key events/100 views).
- ASICS Novablast 5 review: high conversion for a review (3.49/100), but search clicks down 87% year on year.
- Brooks Ghost vs Brooks Adrenaline: a strong comparison (4.98/100).
- Best Running Shoes Right Now: the main best-of guide (4.42/100; 317k search impressions at position 4.5 with 0.07% CTR).

**Phone screenshots** of the Pegasus 42 review, taken by Ruggero on 20 Nov, are attached as 3 images. They show: the first screen (title, photo gallery, verdict line, a video ad covering part of the verdict, a sticky ad at the bottom); then, after scrolling, a sticky price bar ("from $77.90 at Zappos · All 4 prices · See deal"), author line, trust box, verdict, then pros and cons, and a "Best price today" box.

### 3.1 Nike Pegasus 42 review
```text
# Nike Pegasus 42 review
The Nike Pegasus 42 is a durable and stable daily trainer for relaxed runs and distances up to 21 kilometres.
Tested by Brandon Law Marathon Runner and Shoe Expert · 222 articles
Published 10 May 2026 Updated 21 Jun 2026
Bought with our own money at Running Warehouse No free samples How we review
7/10 expert score
1/16
See all 16 photos
## Nike Pegasus 42 Verdict
The Nike Pegasus 42 is a durable and stable daily trainer for relaxed runs and distances up to 21 kilometres. It has a new full-length Zoom Air unit which eliminates the lumpy forefoot that was present in its predecessor, however it doesn't make it feel more responsive. I was disappointed with the performance of the Pegasus 42 because it doesn't feel as engaging or as versatile as other daily trainers in its price range.
### The pros
- Stylish design- one of the better looking daily trainers
- Big reflective strips on the heel
- Stable and supportive ride
- Soft and padded upper
- Tough, durable outsole
### The cons
- ReactX midsole feels flat and outdated
- High 10 mm drop favours only heel strikers
- Very narrow toe box
### Rating breakdown
Comfort 8.0
Build quality 9.0
Upper 9.0
Sole unit 6.0
Landing 6.0
Transition 6.0
Toe-off 6.0
Traction 8.0
Durability 9.0
Value / Price 6.0
### The facts
Weight 10.3 oz
Drop 10 mm
Carbon plate No
Category Cushioning
Stack height 38 mm heel · 28 mm toe 10 mm drop
Brand Nike
Family Pegasus
Previous model Pegasus 41
Our tester's take Hands-on judgments, not specs
### Size & fit
Runs small: buy half size bigger
- Daily training
### Cushioning & ride
Type of cushioning Balanced on a scale from Responsive to Plush
Amount of cushioning Medium cushioning on a scale from Little to High
Stability Some stability on a scale from Neutral to Stable
Flexibility Flexible on a scale from Rigid to Flexible
## Who should buy the Nike Pegasus 42?
If you buy the Pegasus every year, the Pegasus 42 is a slight upgrade with a smoother ride. If you want a really durable, well built daily trainer with an outsole that will last a long time, you should get the Pegasus 42.
## Who should not buy the Nike Pegasus 42?
If you want a fun daily trainer with good energy return and bounce, the Pegasus 42 will feel too dull. If you want a versatile daily trainer for all paces and distances, the Pegasus 42 won’t feel fast enough or cushioned enough.
## Nike Pegasus 42 Introduction
When the original Pegasus dropped in 1983, it moved 300,000 pairs in the first six months. It was the first shoe to put air only in the heel, the Air Wedge Nike called it- positioned exactly where your foot strikes the ground.
Then the nineties happened and Nike eventually pulled it from the line entirely. It came back in 2000 as the Air Pegasus 2000 with full-length air. The Pegasus 35 shifted to a full-length Zoom Air unit again and sold 12 million pairs in a year.
I’ve owned 10 different versions of the Pegasus over the years . My favourite was version 32 and my least favourite was version 36. The thing that I respect the most about all the Pegasus versions over the years is how durable and how well-built they feel.
Somewhere in the last five or six years, the Pegasus became the shoe that you buy when you want a gym or walking shoe that you can also occasionally run in. It’s fine. It does its miles without complaining and asks nothing of you in return.
The Pegasus 41 that came out 2 years ago was one of the most visually different versions ; however, it still rode like a typical Pegasus due to the air bag and outdated foam setup: firm and bland. After testing it, I only used it for gym and casual wear.
The Pegasus used to always be the biggest running shoe release of the year but there were no carbon plated racing shoes or super foams back then. The Pegasus was the shoe that runners got excited about. Now, it just exists. The running equivalent of a grey rental car.
So here’s the 42 with a new full-length, curved Air Zoom unit (which isn’t exactly new) and a slightly roomier toe box. Nike also says that it has an increased toe spring thanks to an updated geometry.
The real question is whether anyone at Nike can revive the Pegasus and make runners fall in love with it again.
## Testing Details
- 10.5 km easy run at 6:22 per km
- 12 km recovery run at 7:12 per km
- 11 km easy run at 6:22 per km
- 8.5 km recovery run at 7:13 per km
- 10.6 km recovery run at 7:10 per km
- 23 km long run at 6:26 per km
- 6 km recovery run at 6:59 per km
## Nike Pegasus 42 First Impressions
When I tried the Pegasus 42 on in the store, the toe-box felt too tight for me so I decided to size up. A half size bigger was way more comfortable . This is the first Pegasus that I’ve gone a half size up in.
My first run was a 6 kilometre recovery run. The ride felt very similar to the previous version. The cushioning was medium-firm and there wasn’t much energy return from the midsole.
The only difference was that transitions felt smoother thanks to the full-length Zoom Air unit. The previous version had separate air units and the forefoot air unit felt lumpy.
## Nike Pegasus 42 Upper
The Pegasus 42’s upper is far better than its midsole and it’s the best Pegasus upper to date. It’s padded in all the right places and foot lockdown is superb.
The tongue is semi-gusseted and it doesn’t slide around. There are also big reflector strips on the heel for safety. The upper is on the warm side because of the padding but breathability is decent- there are plenty of ventilation holes on the toe box and side panels.
It’s a very narrow toe box so you need to go up a half size (or get the wide version) if you prefer a more spacious interior for your feet to swell. If you’re only using it for casual wear, going true to size is fine.
## Nike Pegasus 42 Sole Unit
The ride of the Pegasus 42 feels familiar rather than exciting and if you’ve run in previous versions of the Pegasus, you’ll be able to run in v42 blindfolded and be able to identify it. It has that distinct dense, rubbery, Pegasus midsole feel.
The ReactX in the midsole is the same as in the Pegasus 41 and it doesn’t feel very lively. The closest midsole foam to ReactX is FF Blast Max that’s in the ASICS Cumulus 28. Both these midsoles feel like they are from 5 years ago- not much compression and decompression during transitions.
I only use the Pegasus for easy/recovery runs because it doesn’t enjoy picking up the pace . I don’t feel a lot of responsiveness from the ReactX and the Zoom Air unit also doesn’t provide much bounce so it’s difficult to pick up the pace in it.
A large percentage of Pegasus buyers use it for only walking or casual wear so the design doesn’t have a forefoot rocker (even though Nike advertises a higher toe spring). This contributes to flat feeling, slow transitions; that’s why I don’t use the Pegasus for any type of uptempo or long run.
The longest run I did in the Pegasus 42 was 23 kilometres and I wouldn’t do anything longer in it. It has decent stack height but it doesn’t feel like a very cushioned trainer. The softer Nike Vomero and Vomero Plus have forefoot rockers and are much better long run trainers.
With a 10 mm drop, the Pegasus is considered a high drop trainer and the rearfoot feels much more cushioned than the forefoot. This alienates all runners who are forefoot strikers because the forefoot doesn’t feel cushioned enough.
The durable outsole is a strength of the Pegasus 42. It’s not called Waffle traction anymore but there are still protruding rectangles on the forefoot to bite into the ground. The hard rubber on the high wear areas is supremely abrasion resistant and lasts long.
## Nike Pegasus 42 Conclusions
The Pegasus 42 is not the upgrade I was hoping for . It’s still one of the heaviest daily trainers on the market- the Saucony Ride 19, ASICS Cumulus 28, HOKA Clifton 10 and Brooks Ghost 17 are all lighter than the Pegasus 42 and its midsole doesn’t feel more cushioned.
The biggest crime is that the Pegasus feels more like a casual walking shoe than a modern day, mid-range daily trainer . The ride feels flat, there’s no rocker and it’s too firm. It’s not a running shoe that I look forward to running in and it doesn’t feel very inspiring.
I don’t think that the Zoom Air in the Pegasus contributes anything in terms of performance and it adds weight. ReactX also needs to be upgraded to a supercritical foam or it needs to be reformulated to be much softer.
Compared to the Pegasus 41, the Pegasus 42 is slightly better because the ride is smoother- the forefoot isn’t lumpy like the 41 as the Zoom Air unit is full-length. Overall, the rides are very similar though and if you just bought the 41, you don’t need to buy the 42.
## How does the Pegasus 42 compare?
Asics Gel Nimbus 28
Nike Pegasus 42
Nike Vomero 18
Expert score
7 /10
User score
No ratings yet
7.7
Best price, men
Asics Gel Nimbus 28, men: from $129.95 list price $169.95 24% off as of 20 Nov, 08:17 UTC at Amazon See deal
Nike Pegasus 42, men: from $87.00 list price $144.95 40% off at Zappos See deal
Nike Vomero 18, men: from $97.97 list price $155.00 37% off at Nike See deal
Best price, women
Asics Gel Nimbus 28, women: from $122.59 list price $169.95 28% off as of 20 Nov, 08:17 UTC at Amazon See deal
Nike Pegasus 42, women: from $77.90 list price $144.95 46% off at Zappos See deal
Nike Vomero 18, women: from $90.47 list price $155.00 42% off at Zappos See deal
Retail price
US$169.95
US$144.95
US$155
Brand
Asics
Nike
Review
Gel Nimbus 28 review
Pegasus 42 review
Vomero 18 review
Shoe type
Cushioning
Weight
9.9 oz
10.3 oz
11.5 oz
Heel Drop
8 mm
10 mm
Recommended for
Daily training, long distance racing
Daily training
Cushioning type
balanced/plush
balanced
plush
Cushioning amount
Highly cushioned
Medium cushioning
Flexibility
medium
flexible
Stability
some stability
very stable
Sizing
true to size
buy half size bigger
## Why you can trust us
As editorial policy, we do not accept free samples from companies. We purchased this pair of Nike at Running Warehouse with our own money. This page contains affiliate links. As an Amazon Associate we earn from qualifying purchases. Read more about our policy.
Best price today Nike Pegasus 42, men: from $87.00 list price $144.95 40% off at Zappos See deal Nike Pegasus 42, women: from $77.90 list price $144.95 46% off at Zappos See deal
## Nike Pegasus 42 price comparison
Prices for ( USD ) Prices checked 19 – 20 Nov
Nike Pegasus 42, men: Zappos from $87.00 list price $144.95 40% off See deal Nike Pegasus 42, men: Nike from $91.97 list price $144.95 37% off See deal Nike Pegasus 42, men: Amazon from $95.99 list price $144.95 34% off as of 20 Nov, 08:19 UTC See deal Nike Pegasus 42, men: Running Warehouse from $99.88 list price $144.95 31% off See deal
Nike Pegasus 42, women: Zappos from $77.90 list price $144.95 46% off See deal Nike Pegasus 42, women: Nike from $89.97 list price $144.95 38% off See deal Nike Pegasus 42, women: Amazon from $93.17 list price $144.95 36% off as of 20 Nov, 08:18 UTC See deal Nike Pegasus 42, women: Running Warehouse from $99.88 list price $144.95 31% off See deal
We may earn a commission when you buy through these links; it doesn't change your price. How we work
## Reviewed by Brandon
This review was written by Brandon Law. Published 10 May 2026 , updated 21 Jun 2026 . Brandon is a South African marathoner based in Malaysia, where the humidity is high and his shoe rotation is even higher. He’s a runner with a full-blown addiction to running shoes. Most people buy shoes to run—Brandon runs to justify buying more shoes. If there’s a new drop, he’s probably already logged 100km in them.
### Leave a Reply
This site uses Akismet to reduce spam. Learn how your comment data is processed.
```

### 3.2 ASICS Novablast 5 review
```text
# Asics Novablast 5 review
The ASICS Novablast 5 is an excellent, versatile daily trainer and the best Novablast version to date.
Tested by Brandon Law Marathon Runner and Shoe Expert · 222 articles
Published 10 Dec 2024 Updated 3 Jan 2025
Bought with our own money at Running Warehouse No free samples How we review
9/10 expert score
User score 8.1/10 · 7 reviews
1/16
See all 16 photos
## Asics Novablast 5 Verdict
The ASICS Novablast 5 is an excellent, versatile daily trainer and the best Novablast version to date. The new FF Blast Max midsole made from POE is a significant improvement over the EVA midsoles of previous iterations, providing a softer ride with better energy return. Version 5 is a big upgrade over version 4, becoming lighter, more comfortable and more lively. It performs better over long distances and on uptempo runs than its predecessor.
### The pros
- Softer ride than previous 2 versions
- Lighter than the Novablast 4
- No more slappy transitions
- Increase in energy return
- Wider toe-box
### The cons
- Long label on the tongue feels scratchy and cheap
- Outsole picks up small stones
- Less stable than v4
### Rating breakdown
Comfort 9.0
Build quality 9.0
Upper 9.0
Sole unit 9.0
Landing 9.0
Transition 9.0
Toe-off 9.0
Traction 7.0
Durability 9.0
Value / Price 8.0
### The facts
Weight 8.7 oz
Drop 8 mm
Carbon plate No
Category Cushioning
Stack height 41 mm heel · 33 mm toe 8 mm drop
Brand Asics
Family Novablast
Previous model Novablast 4
Our tester's take Hands-on judgments, not specs
### Size & fit
True to size
- Daily training
### Cushioning & ride
Type of cushioning Balanced on a scale from Responsive to Plush
Amount of cushioning Highly cushioned on a scale from Little to High
Stability Some stability on a scale from Neutral to Stable
Flexibility Flexible on a scale from Rigid to Flexible
## Who should buy the Asics Novablast 5?
If you find the Novablast 4 too firm and flat, the Novablast 5 is a worthy upgrade. If you’re looking for a versatile, neutral daily trainer that won’t break the bank, the Novablast 5 is one of the best in its class.
## Who should not buy the Asics Novablast 5?
If you like the firm ride of the Novablast 4, you might find version 5 too soft/mushy. If you like your daily trainers to have a stiff forefoot with a prominent rocker, the Novablast 5 is not the shoe for you.
## Asics Novablast 5 Introduction
There used to be a time when if a beginner runner asked you to recommend a daily trainer for them, the default answer would be the Nike Pegasus . But over the years, the Pegasus has become boring: firm, flat and heavy. It’s also increased in price by $20 over the past 3 years.
The new default, mid-range, neutral daily trainer is the ASICS Novablast and it has become one of the biggest running shoe launches of the year .
Both the Novablast and the Pegasus are the same price but the main differences between the Novablast and the Pegasus are that the Novablast weighs much less and it has a more energetic midsole. These differences result in a much more versatile trainer which can handle uptempo runs better.
The Novablast 4 of 2023 was a good trainer, not a great one . I found its midsole to be a bit too firm and I found it lacking a bit of energy return but it was still miles better than the Pegasus 40. I still use the Novablast 4 for gym and the occasional easy run.
The Novablast 5 looks similar to the Novablast 4 but under the hood, it gets some significant upgrades. It gets a new upper, outsole and a brand new midsole foam which completely changes its ride.
On the ASICS website, they’ve listed the Novablast 5’s weight at 9 oz (255 g) but my pair weighs 8.8 oz (250 g). This is 0.4 oz (10 g) lighter than the Novablast 4. The Novablast 5 maintains its $140 price this year and its stack height.
## Asics Novablast 5 First Impressions
During my first run, I was amazed at how different the Novablast 5 felt compared to the previous 2 versions. The midsole felt significantly squishier and more lively.
The ride transitions also felt less slappy than the Novablast 4 due to the softer midsole foam. The upper fit me really well and was more accommodating than the 4, especially in the toe-box.
The shoe that it reminded me of most was the Novablast 2 . They felt similar in terms of softness by the main difference is that the Novablast 5 has more cushioning depth.
## Asics Novablast 5 Upper
Although the Novablast 4 was fine for me, there were some Running Shoes Guru readers who complained that the Novablast 4 toe-box was too narrow and that it caused them blisters. The good news is that the Novablast 5 has an improved fit with a roomier toe-box .
The new upper is made from a rough, technical mesh which feels thinner and more breathable than the furry, knit-like upper of the Novablast 4. The thin, gusseted tongue has been improved with a new loop on it for the laces to go through- this prevents tongue slide. I’m not a fan of the long, scratchy label on the tongue because it makes it feel cheap.
The upper is more spacious than your average running shoe, especially in the forefoot and toe-box but I still recommend buying true to size. If you like a snug fit, or you have very narrow feet, you can go down a half size. Foot lockdown is superb.
Unfortunately, there are no reflectors on the Novablast 5, so you’ll have to buy the Light-Show version next year if you need low-light visibility.
## Asics Novablast 5 Sole Unit
After I reviewed the Novablast 4, a lot of readers disagreed with me . I felt that it had a relatively firm and flat ride compared to other daily trainers. It had changed too much from the wild, fun, squishy rides of the first 2 versions. The Novablast 4 felt more like a speed trainer than a do-it-all, daily trainer.
The Novablast 5 midsole is now FF Blast Max. It’s made from POE (polyolefin elastomer) and it has no EVA in it. The ride is a very big improvement as it’s significantly softer and more lively than the FF Blast+ Eco in the Novablast 4. Overall, the ride feels more modern.
Easy runs are a lot more enjoyable now because of the softer ride . Runs above 21 kilometres are also more comfortable thanks to the increase in long-distance comfort. The longest run I did in it was 25 kilometres and my legs felt great the next day.
Stability is not as good as in version 4. Not only is the midsole softer but the heel and the forefoot are also 5 mm narrower. If you’re a slight overpronator, you should still be OK in the Novablast 5 as it doesn’t feel unstable (unlike the original Novablast).
The Novablast 5 has a wide pace range, from slow runs which are 7 minutes per km/11:14 per mile (or slower) down to up tempo runs in the 4:30 per kilometre/7:15 per mile region. Even though the ride is softer, there’s more energy return which makes it much easier to pick up the pace or hold a fast pace .
The only runs it doesn’t excel in are threshold and V02 Max workouts. Its midsole is not stiff because it doesn’t have a shank/plate in its midsole so it doesn’t feel comfortable at those all-out efforts. It feels most comfortable at easy and steady paces.
Coming in at 41.5 mm in the heel, the Novablast 5 is technically illegal to use in a World Athletics race but if you’re not competing for prize money, nobody will have an issue with you racing in it. It has plenty of cushioning depth for a marathon and it’s suitable if you’re aiming to complete it in 3 hours 30 minutes or slower .
The outsole is made of AHAR LO rubber and has a similar design to the Novablast 4. There is a little bit more rubber coverage on the edges of the outsole and there are now holes in the rubber to shed some weight. I noticed that the holes pick up small stones now.
Outsole durability is excellent; the rubber on my pair hasn’t worn down much after 83 kilometres. Traction is good on dry surfaces but average on wet surfaces. The rubber is flat so it doesn’t bite into surfaces that well.
## Asics Novablast 5 Conclusions
The previous 2 versions of the Novablast were only lukewarm. They lacked excitement and innovation. The Novablast 5 takes the best features from each previous version and combines them : it has the squish and energy return of the first 2 versions, the stability of versions 4 & 5, and it has the comfortable upper of version 4. The Novablast 5 is certainly the best Novablast version to date.
If you have the Novablast 4 or an earlier version, it’s worth upgrading to the 5. Version 5 has the smoothest ride, the most long-distance comfort and it feels the most polished. It will be staying in my rotation as the core workhorse as I enter the 150 km-a-week depths of my marathon training block.
The Novablast 5’s weight is what sets it apart from the other mid-range daily trainers . It’s 0.7 oz less than the Brooks Ghost 16 , 1 oz lighter than the Adidas Supernova Rise , and 1.1 oz lighter than the Saucony Ride 17. Only the Hoka Clifton 9 comes in lighter than the Novablast 5 at 8.7 oz but the Novablast has superior energy return as the Clifton is still using EVA.
Version 5 cements the Novablast as the default trainer for all kinds of runners . It’s one of the most balanced trainers on the market, it’s a decent price point for such a polished and well-built trainer, and it’s never completely sold out.
## How does the Novablast 5 compare?
Asics Novablast 4
Asics Novablast 5
Asics Gel Nimbus 26
Expert score
7 /10
9 /10
10 /10
User score
8.8
8.1
8.3
Best price, men
Asics Novablast 4, men: from $109.90 list price $140.00 22% off as of 20 Nov, 08:05 UTC at Amazon See deal
Asics Novablast 5, men: from $119.90 list price $150.00 20% off as of 20 Nov, 08:07 UTC at Amazon See deal
Asics Gel Nimbus 26, men: from $96.24 list price $159.95 40% off as of 20 Nov, 08:05 UTC at Amazon See deal
Best price, women
Asics Novablast 4, women: from $68.35 list price $140.00 51% off as of 20 Nov, 08:03 UTC at Amazon See deal
Asics Novablast 5, women: from $129.94 list price $150.00 13% off as of 20 Nov, 08:07 UTC at Amazon See deal
Asics Gel Nimbus 26, women: from $72.95 list price $159.95 54% off as of 20 Nov, 08:04 UTC at Amazon See deal
Retail price
US$140
US$150
US$159.95
Brand
Asics
Review
Novablast 4 review
Novablast 5 review
Gel Nimbus 26 review
Shoe type
Cushioning
Weight
9 oz
8.7 oz
10.7 oz
Heel Drop
8 mm
Recommended for
Speedwork
Daily training
Cushioning type
responsive
balanced
responsive/balanced
Cushioning amount
Highly cushioned
Flexibility
medium
flexible
Stability
very stable
some stability
Sizing
true to size
## Why you can trust us
As editorial policy, we do not accept free samples from companies. We purchased this pair of Asics at Running Warehouse with our own money. This page contains affiliate links. As an Amazon Associate we earn from qualifying purchases. Read more about our policy.
Best price today Asics Novablast 5, men: from $119.90 list price $150.00 20% off as of 20 Nov, 08:07 UTC at Amazon See deal Asics Novablast 5, women: from $129.94 list price $150.00 13% off as of 20 Nov, 08:07 UTC at Amazon See deal
## Asics Novablast 5 price comparison
Prices for ( USD ) Prices checked 20 Nov
Asics Novablast 5, men: Amazon from $119.90 list price $150.00 20% off as of 20 Nov, 08:07 UTC See deal Asics Novablast 5, men: Zappos from $129.95 list price $150.00 13% off See deal Asics Novablast 5, men: Running Warehouse from $129.96 list price $150.00 13% off See deal
Asics Novablast 5, women: Amazon from $129.94 list price $150.00 13% off as of 20 Nov, 08:07 UTC See deal Asics Novablast 5, women: Zappos from $129.95 list price $150.00 13% off See deal Asics Novablast 5, women: Running Warehouse from $129.96 list price $150.00 13% off See deal
We may earn a commission when you buy through these links; it doesn't change your price. How we work
## Reviewed by Brandon
This review was written by Brandon Law. Published 10 Dec 2024 , updated 3 Jan 2025 . Brandon is a South African marathoner based in Malaysia, where the humidity is high and his shoe rotation is even higher. He’s a runner with a full-blown addiction to running shoes. Most people buy shoes to run—Brandon runs to justify buying more shoes. If there’s a new drop, he’s probably already logged 100km in them.
### Leave a Reply
This site uses Akismet to reduce spam. Learn how your comment data is processed.
```

### 3.3 Brooks Ghost vs Brooks Adrenaline
```text
# Comparison: Brooks Ghost vs Brooks Adrenaline
The Ghost and the Adrenaline are the most popular running shoes in the Brooks lineup. Here we compare the latest version of each shoe we have bought and tested: the Brooks Ghost 17 vs the Brooks Adrenaline GTS 25.
Ghost 17
Adrenaline GTS 25
Written by Ruggero Loda Founder and Publisher, Running Shoes Guru · 319 articles
Published 19 Feb 2020 Updated 18 Nov 2026
We buy every shoe we test with our own money No free samples How we review
The Ghost and the Adrenaline GTS are Brooks' two best-selling running shoes, and the biggest difference between them has always been the same: the Ghost is a neutral trainer, while the Adrenaline is a stability shoe.
With these versions, the two have never been closer. The Adrenaline GTS 25 now uses the same nitrogen-infused DNA Loft v3 foam as the Ghost 17, and both have gone from a 12 mm to a 10 mm drop. So what's left to choose between them? Let's find out!
## Similarity and differences: Ghost 17 vs Adrenaline GTS 25
Our verdict: Two Brooks daily workhorses that now share the same DNA Loft v3 foam and 10 mm drop: the difference is support. The Ghost 17 is the neutral pick, stable enough for most runners; the Adrenaline GTS 25 adds GuideRails for runners who need help keeping their stride in line.
Brooks Ghost 17
Brooks Adrenaline GTS 25
Expert score
8 /10
User score
No ratings yet
Best price, men
Brooks Ghost 17, men: from $98.95 list price $149.95 34% off as of 20 Nov, 08:12 UTC at Amazon See deal
Brooks Adrenaline GTS 25, men: from $109.95 list price $154.95 29% off as of 20 Nov, 08:11 UTC at Amazon See deal
Best price, women
Brooks Ghost 17, women: from $98.94 list price $149.95 34% off as of 20 Nov, 08:09 UTC at Amazon See deal
Brooks Adrenaline GTS 25, women: from $109.95 list price $154.95 29% off as of 20 Nov, 08:18 UTC at Amazon See deal
Buy if
you don't need extra support and want a comfortable, well-cushioned neutral trainer with a premium upper (go wide or half a size up if your feet are wide).
you need some stability and want a soft, smooth daily trainer with GuideRails (try it on first: the new heel collar isn't for everyone).
Review
Ghost 17 review
Adrenaline GTS 25 review
Brand
Brooks
Shoe type
Cushioning
Stability
Weight
10.1 oz lighter
10.6 oz
Heel drop
10 mm
Cushioning
Medium cushioning
Cushioning type
Balanced
Balanced/plush
Stability
Very stable
Flexibility
Flexible
Medium
Sizing
buy half size bigger
true to size
Best for
Daily training
Retail price
US$149.95
US$154.95
## Score breakdown: Ghost 17 vs Adrenaline GTS 25
Ghost 17
Adrenaline GTS 25
10
Comfort
8
7
Sole unit
9
Upper
7
Transition
8
Toe-off
9
Durability
8
Ghost 17 wins: Comfort, Upper, lighter
Adrenaline GTS 25 wins: Sole unit, Transition, Toe-off
Even: Durability
## Sole unit: Ghost 17 vs Adrenaline GTS 25
Ghost 17
Adrenaline GTS 25
The Ghost 17 and the Adrenaline GTS 25 now share the same midsole foam: DNA Loft v3, an EVA-based foam injected with nitrogen. It makes the ride softer and gives a noticeable pop at toe-off, although the energy return is still far below new-generation trainers like the ASICS Novablast 5 or the Adidas EVO SL.
In the Ghost 17 , Brooks added 2 mm of foam in the forefoot, lowering the drop from 12 to 10 mm for the first time in the Ghost’s history. The forefoot feels softer and toe-offs more pleasant, which makes it a better fit for midfoot and forefoot strikers than previous Ghosts. The ride is supportive and very stable for a neutral shoe, and it shines on easy and recovery runs. At 10.1 oz (286 g), it’s too heavy for uptempo runs.
The Adrenaline GTS 25 also went from a 12 to a 10 mm drop. Our tester found it noticeably softer than previous Adrenalines, with less break-in time and a smooth, bouncy ride from landing to toe-off.
The main difference is still the GuideRails stability system in the Adrenaline. Think of them as the bumpers in a bowling alley: they let your foot follow its natural path and only engage when it starts to move too far to the side. In the GTS 25 the GuideRails are slightly more pronounced than in recent versions, but they stay unobtrusive. The Ghost has no GuideRails: it’s stable for a neutral shoe, but not as stable as the Adrenaline.
Both shoes use Brooks’ RoadTack rubber on the outsole. On the Ghost 17 it’s thick and hard, so durability is good but grip on damp surfaces is poor. On the Adrenaline GTS 25 it’s thinner and placed only in the high-wear areas, and our tester found the traction reliable in a variety of conditions.
## Upper unit: Ghost 17 vs Adrenaline GTS 25
Ghost 17
Adrenaline GTS 25
The uppers are close too: both use a double jacquard mesh that feels premium, with generous padding and very good foot lockdown.
The difference is at the heel. The Adrenaline GTS 25 has a new flat-knit collar that extends above the usual heel collar, like a turtleneck, and the heel is heavily padded. Our tester found it strange and slightly uncomfortable, so try it on before you buy. The Ghost 17 has a more traditional heel with a flared heel tab.
The Ghost’s weak point is its very narrow toe box: our tester went up half a size, and we recommend the wide version or a half size up if you have wide or high-volume feet.
## Which one to buy: Ghost 17 vs Adrenaline GTS 25
Ghost 17
Adrenaline GTS 25
In this case, the choice is still pretty easy: it comes down to support.
Both shoes are reliable daily trainers for beginners and experienced runners who want to log most of their miles in comfort, and their prices are close: $150 for the Ghost , $155 for the Adrenaline .
If you need extra support, for example because your ankles roll in or you feel less stable as you get tired, go for the Adrenaline GTS 25 . It’s softer and smoother than previous Adrenalines, with the same proven GuideRails. If you’re sensitive around the ankle, try it on first because of the new heel collar.
If you don’t need extra stability, go for the Ghost 17 . It’s the best Ghost to date, softer and more modern than its predecessors, and already very stable for a neutral shoe. Just check the fit, as the toe box is narrow.
## Best price online: Ghost 17 vs Adrenaline GTS 25
Best price today Brooks Ghost 17, men: from $98.95 list price $149.95 34% off as of 20 Nov, 08:12 UTC at Amazon See deal Brooks Ghost 17, women: from $98.94 list price $149.95 34% off as of 20 Nov, 08:09 UTC at Amazon See deal
## Brooks Ghost 17 price comparison
Prices for ( USD ) Prices checked 20 Nov
Brooks Ghost 17, men: Amazon from $98.95 list price $149.95 34% off as of 20 Nov, 08:12 UTC See deal Brooks Ghost 17, men: Running Warehouse from $109.96 list price $149.95 27% off See deal
Brooks Ghost 17, women: Amazon from $98.94 list price $149.95 34% off as of 20 Nov, 08:09 UTC See deal Brooks Ghost 17, women: Running Warehouse from $109.96 list price $149.95 27% off See deal
We may earn a commission when you buy through these links; it doesn't change your price. How we work
Best price today Brooks Adrenaline GTS 25, men: from $109.95 list price $154.95 29% off as of 20 Nov, 08:11 UTC at Amazon See deal Brooks Adrenaline GTS 25, women: from $109.95 list price $154.95 29% off as of 20 Nov, 08:18 UTC at Amazon See deal
## Brooks Adrenaline GTS 25 price comparison
Prices for ( USD ) Prices checked 20 Nov
Brooks Adrenaline GTS 25, men: Amazon from $109.95 list price $154.95 29% off as of 20 Nov, 08:11 UTC See deal Brooks Adrenaline GTS 25, men: Brooks from $124.95 list price $154.95 19% off See deal Brooks Adrenaline GTS 25, men: Zappos from $124.95 list price $154.95 19% off See deal Brooks Adrenaline GTS 25, men: Running Warehouse from $124.96 list price $154.95 19% off See deal
Brooks Adrenaline GTS 25, women: Amazon from $109.95 list price $154.95 29% off as of 20 Nov, 08:18 UTC See deal Brooks Adrenaline GTS 25, women: Brooks from $124.95 list price $154.95 19% off See deal Brooks Adrenaline GTS 25, women: Zappos from $124.95 list price $154.95 19% off See deal Brooks Adrenaline GTS 25, women: Running Warehouse from $124.96 list price $154.95 19% off See deal
We may earn a commission when you buy through these links; it doesn't change your price. How we work
## Written by Ruggero
This comparison was written by Ruggero Loda. Published 19 Feb 2020 , updated 18 Nov 2026 . Ruggero is the founder and publisher of Running Shoes Guru. After years of experience working for brands such as Nike, Reebok/Adidas, Ruggero launched Running Shoes Guru in 2009 because he could not find a reliable, independent and trusted source of running shoe reviews elsewhere on the internet.A runner and triathlete, Ruggero has tested dozens of shoes over more than 15 years. He is an advocate for transparency and that's why he purchases all shoes for testing with his own money instead of
```

### 3.4 Best Running Shoes Right Now
```text
# Best Running Shoes Right Now
By Ruggero Loda , Founder and Publisher, Running Shoes Guru Every shoe bought with our own money and run 50+ miles before we score it. How we test
Our pick for each kind of running, from the 1,300+ shoes we have bought and tested. Each one links to why we like it and what else we considered.
## Our top picks for each category
Best for Beginners
Brooks Ghost 17
The Ghost is the classic first running shoe, and version 17 is the best one to date: deeper, softer cushioning, excellent stability and a luxurious upper.
10.1 oz · 10 mm drop
8/10 expert score
Brooks Ghost 17, men: from $98.95 list price $149.95 34% off as of 20 Nov, 08:12 UTC at Amazon See deal Brooks Ghost 17, women: from $98.94 list price $149.95 34% off as of 20 Nov, 08:09 UTC at Amazon See deal Why we like it ↓
Best for Daily Training
Asics Novablast 5
Soft, bouncy and versatile: the best Novablast to date, lighter and livelier than version 4.
8.7 oz · 8 mm drop
9/10 expert score
Asics Novablast 5, men: from $119.90 list price $150.00 20% off as of 20 Nov, 08:07 UTC at Amazon See deal Asics Novablast 5, women: from $129.94 list price $150.00 13% off as of 20 Nov, 08:07 UTC at Amazon See deal Why we like it ↓
Best Budget Running Shoes
Nike Winflo 11
If you're looking for a shoe that will allow you to train and run a parkrun (5k), and ideally train for up to 10k, the Winflo is a top choice.
11.4 oz · 9.1 mm drop
10/10 expert score
Nike Winflo 11, men: from $77.00 list price $110.00 30% off at Zappos See deal Nike Winflo 11, women: from $78.47 list price $110.00 29% off as of 20 Nov, 08:06 UTC at Amazon See deal Why we like it ↓
Best for Long Runs
Hoka One One Bondi 9
The most cushioned Bondi yet, with a very stable ride for slow, easy long runs.
9.3 oz · 5 mm drop
8/10 expert score
Hoka One One Bondi 9, men: from $128.80 list price $170.00 24% off as of 20 Nov, 08:08 UTC at Amazon See deal Hoka One One Bondi 9, women: from $139.96 list price $170.00 18% off at Running Warehouse See deal Why we like it ↓
Best for Fast Training
Adidas adizero Evo SL
The EVO SL is a standout running shoe, at the price of a daily trainer.
8.1 oz · 7 mm drop · half-length carbon plate
9/10 expert score
Adidas adizero Evo SL, men: from $87.63 list price $149.95 42% off as of 20 Nov, 08:13 UTC at Amazon See deal Adidas adizero Evo SL, women: from $93.00 list price $149.95 38% off as of 20 Nov, 08:13 UTC at Amazon See deal Why we like it ↓
Best for Marathon Racing
Asics METASPEED Edge Tokyo
One of the best carbon racers of 2025 and our favourite Edge to date.
5.6 oz · 5 mm drop · carbon plate
9/10 expert score
Asics METASPEED Edge Tokyo, men: from $269.95 as of 20 Nov, 08:17 UTC at Amazon See deal Asics METASPEED Edge Tokyo, women: from $269.95 as of 20 Nov, 08:17 UTC at Amazon See deal Why we like it ↓
We earn a commission if you buy through these links. It never changes a score. How we work
## Best for Beginners
When you just start running, a quality, running-specific shoe is a must. You want a shoe that is versatile, comfortable and a good combination between cushioning and lightweight.
Our pick
### Brooks Ghost 17
Brooks Ghost 17, men: from $98.95 list price $149.95 34% off as of 20 Nov, 08:12 UTC at Amazon See deal Brooks Ghost 17, women: from $98.94 list price $149.95 34% off as of 20 Nov, 08:09 UTC at Amazon See deal
8/10 expert score
#### Why it won : Brooks Ghost 17
The Ghost is the classic first running shoe, and version 17 is the best one to date: deeper, softer cushioning, excellent stability and a luxurious upper. It picked up some weight and the toe box is narrow, so try it on first, but for comfort mile after mile it's hard to beat.
#### The pros
- Plusher ride than any previous Ghost
- More forefoot cushioning
- Excellent stability
#### The cons
- Very narrow toe box
- Big weight increase
- Price increase
Weight 10.1 oz
Drop 10 mm
Heel / toe (mm) 36 / 26
Carbon plate No plate
Read the full Ghost 17 review → Rating breakdown, size & fit, who should buy it
## Best for Daily Training
Daily trainer and shoe for beginner could overlap. However, in this category we generally assume the runner is slightly faster than a beginner and will appreciate the ability to go a little faster in training.
Our pick
### Asics Novablast 5
Asics Novablast 5, men: from $119.90 list price $150.00 20% off as of 20 Nov, 08:07 UTC at Amazon See deal Asics Novablast 5, women: from $129.94 list price $150.00 13% off as of 20 Nov, 08:07 UTC at Amazon See deal
9/10 expert score
#### Why it won : Asics Novablast 5
Soft, bouncy and versatile: the best Novablast to date, lighter and livelier than version 4. One of the most successful franchises of the past 5 years, the Novablast 5 returns to the characteristics that made the 1 and the 2 so popular among runners. Amazing energy return places the Novablast at ease both for uptempo or long runs.
#### The pros
- Softer ride than previous 2 versions
- Lighter than the Novablast 4
- No more slappy transitions
#### The cons
- Long label on the tongue feels scratchy and cheap
- Outsole picks up small stones
- Less stable than v4
Weight 8.7 oz
Drop 8 mm
Heel / toe (mm) 41.5 / 33.5
Carbon plate No plate
Read the full Novablast 5 review → Rating breakdown, size & fit, who should buy it
## Best Budget Running Shoes
It's easy to pick up "cheap" running shoes. Budget running shoes are a different breed: shoes that perform almost as good as a $140 daily trainer, but with an eye to the list price too.
Our pick
### Nike Winflo 11
Nike Winflo 11, men: from $77.00 list price $110.00 30% off at Zappos See deal Nike Winflo 11, women: from $78.47 list price $110.00 29% off as of 20 Nov, 08:06 UTC at Amazon See deal
10/10 expert score
#### Why it won : Nike Winflo 11
If you're looking for a shoe that will allow you to train and run a parkrun (5k), and ideally train for up to 10k, the Winflo is a top choice. Further, it doubles up as a general training or walking shoe.
#### The pros
- Budget-friendly
- Can be worn for many activities
- Durable
#### The cons
- Not for longer distances
- Laces come undone easily if not double-knotted
Weight 11.4 oz
Drop 9.1 mm
Carbon plate No plate
Read the full Winflo 11 review → Rating breakdown, size & fit, who should buy it
## Best for Long Runs
Long Runs? Typically your Saturday or Sunday run, where speed is not the objective, but being able to sustain a prolonged slow effort is. Long run shoes are comfortable in the uppers, but especially in the sole - with soft, plush cushioning that lasts for miles and miles.
Our pick
### Hoka One One Bondi 9
Hoka One One Bondi 9, men: from $128.80 list price $170.00 24% off as of 20 Nov, 08:08 UTC at Amazon See deal Hoka One One Bondi 9, women: from $139.96 list price $170.00 18% off at Running Warehouse See deal
8/10 expert score
#### Why it won : Hoka One One Bondi 9
The most cushioned Bondi yet, with a very stable ride for slow, easy long runs. Hoka, the king of max cushioning, waited a couple of years to update the Bondi.
This version has a good inherent stability, which is always good in a long run shoe.
#### The pros
- Gained 2 mm in stack height
- Reflectivity on the upper
- Extremely stable ride
#### The cons
- Overpriced for an EVA midsole trainer
- Standard width is too narrow
- Not much energy return
Weight 9.3 oz
Drop 5 mm
Heel / toe (mm) 44 / 39
Carbon plate No plate
Read the full Bondi 9 review → Rating breakdown, size & fit, who should buy it
## Best for Fast Training
What is fast training for some can be racing for others.
Our pick
### Adidas adizero Evo SL
Adidas adizero Evo SL, men: from $87.63 list price $149.95 42% off as of 20 Nov, 08:13 UTC at Amazon See deal Adidas adizero Evo SL, women: from $93.00 list price $149.95 38% off as of 20 Nov, 08:13 UTC at Amazon See deal
9/10 expert score
#### Why it won : Adidas adizero Evo SL
The EVO SL is a standout running shoe, at the price of a daily trainer.
If there's one shoe this year you shouldn't miss, is this.
#### The pros
- Super lightweight for such a cushioned ride
- Spacious and accommodating upper
- Very high energy return
#### The cons
- Midsole foam scuffs and punctures easily
- Tongue edges fold when putting it on
- Not enough supply to meet demand
Weight 8.1 oz
Drop 7 mm
Heel / toe (mm) 39 / 33
Carbon plate Half length carbon plate
Read the full adizero Evo SL review → Rating breakdown, size & fit, who should buy it
## Best for Marathon Racing
Nike's Breaking2, and the introduction of the Carbon plate running shoe changed everything in Marathon racing.
Our pick
### Asics METASPEED Edge Tokyo
Asics METASPEED Edge Tokyo, men: from $269.95 as of 20 Nov, 08:17 UTC at Amazon See deal Asics METASPEED Edge Tokyo, women: from $269.95 as of 20 Nov, 08:17 UTC at Amazon See deal
9/10 expert score
#### Why it won : Asics METASPEED Edge Tokyo
One of the best carbon racers of 2025 and our favourite Edge to date. FF Leap foam makes it softer and noticeably bouncier than before, and it stays efficient from 5K all the way up to the marathon.
#### The pros
- Lightest Edge version to date
- Most energetic Edge midsole
- Softer ride than predecessor
#### The cons
- Flat, ungusseted tongue folds
- Steep price increase
Weight 5.6 oz
Drop 5 mm
Heel / toe (mm) 40 / 35
Carbon plate Full length carbon plate
Read the full METASPEED Edge Tokyo review → Rating breakdown, size & fit, who should buy it
## How to pick the right running shoes for you
The first thing to consider when choosing a pair of running shoe is what will you be using them for .
Are you just starting out and looking for your first running shoe? You are looking for a daily trainer, or a supportive daily trainer. You’ll want Max Cushion if you are a heavier-set runner or if simply you are looking for the max comfort.
Here are other things you should know when picking a running shoe:
Comfort and Fit Before you look into different kind of foams or technologies, make sure you pick a shoe that fits you well and it’s comfortable.
Cushioning The sole of your running shoes will absorb some of the impact of your foot landing on the ground – this is what we call cushioning .
Responsive vs Plush Cushioning
- Plush is the feeling you have when running on dry sand on a beach. The step is soft and cushioned, but a lot of your energy is absorbed by the sand – requiring you to put more effort into running.
- Responsive is the feeling you have when running on wet sand. The step is less cushioned, but you go faster, easier.
The choice is personal and depends on what you use the shoe for (racing? long slow training miles?).
### Why you should trust our recommendations
- We purchase all the shoes ourselves. We never accept free shoes from shoe companies.
- We have reviewed more than 1,300 shoes so far. The only way to know running shoes is to test as many as possible.
- We run at least 50 miles in each shoe. A quick run is not enough to properly evaluate a running shoe.
- We have been doing this since 2009. We have been the first website to properly and thoroughly review running shoes, starting in 2009.
## Quick Questions you might have
After 17 years and more than 1,300 running shoe reviews, we can comfortably say there is not a best running shoe brand. The competition is fierce and information is readily available: any brand not producing great running shoes would simply not have survived.
While technically you could run in anything (or even barefoot), running shoes are designed for the simple but unique running gait: from heel to toe-off. Shoes for other sports might be focusing on lateral movements or change of direction, while not focusing in shock absorption and forward motion.
A $70 running-specific shoe will be better than any non-running shoe. This said, more expensive running shoes will generally be better than cheaper options. $100 is a great place to start, with $130-$140 being the median price for great daily trainers. Racers usually cost closer to $250.
A very simple test you can do to check your ankle stability is this. Stand on one foot for 45-60 seconds. Does it come easy, or do you wobble and struggle? In this case, you might benefit from a supportive running shoe.
### Leave a Reply
This site uses Akismet to reduce spam. Learn how your comment data is processed.
```
