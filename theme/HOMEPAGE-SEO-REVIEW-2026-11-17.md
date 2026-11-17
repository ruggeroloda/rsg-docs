# Homepage 2026 — SEO and machine-readability review (17 Nov 2026)

Old = live homepage (`template-home-2020.php`, edge copy as Googlebot). New = staging (`template-home-2026.php`). Search data: Search Console tables on rsgmedia (query table is partial: use shares, not totals). Scripts: `/home/claude/seo` in the session (an.py, diff.py); checks `theme/checks/seo_check.py`, `extractcheck.py`, `cwv.py`.

## Verdict
Net positive and low-to-moderate risk, if the four "must" items below are done. The new page says much more clearly what Google already ranks the homepage for: reviews and comparisons. It has one H1 instead of ten, adds structured data and gives machines real text. The real risks are the title wording, a few strong internal links the old page gave that the new one drops, and the timing (core update still rolling).

## What the homepage earns today
- #1 page by search clicks; ~2,400 clicks/month (Oct), falling with the site. Impressions jumped in Nov (491k in 14 days) from "best running shoes".
- Last 31 days (partial table): brand queries 968 clicks, generic 1,120. **Generic queries are where the risk is.**
- Main generic queries, last 90 days (clicks / impressions / position): "running shoes review" 429 / 154k / 3.3 · "running shoe reviews" 388 / 5.2k / 4.6 · "running shoes reviews" 187 / 33k / 4.0 · "best running shoes" 141 / 453k / 7.5 · "running shoe review" 105 / 1.8k / 4.0 · "compare running shoes" 90 / 26k / 4.5 · "running shoes comparison" 67 / 17k / 5.5 · "running shoe comparison" 62 / 1.5k / 5.3 · "shoes review" 49 / 11k / 4.8 · "running shoes ratings" variants ~100 clicks · "running shoes buying guide" 2 / 3.2k / 6.0.
- "best running shoes": since 26 Oct the homepage shows at ~6–7 with ~20–37k impressions/day and almost no clicks; /top/best-running-shoes-right-now/ shows at ~2.6 with ~10k/day and also ~0 clicks (looks like SERP features / AI answers, not normal blue links). The two pages compete.

## Old vs new
| | Old | New |
|---|---|---|
| Title | Running Shoes Reviews and Buying Guide \| Running Shoes Guru | Running Shoe Reviews and Comparisons \| … (see M1) |
| Description | "Need running shoes advice? Reviews, buying guides and price comparison…" | Independent reviews since 2009, 1,300+ reviews, 173 comparisons, today's prices (151 chars) |
| H1 | **10 H1s** ("Real Runners, Real Reviews", "Find your perfect shoe", "Latest road running shoes"…) | 1 H1 "Independent running shoe reviews since 2009" + 7 H2 |
| Visible text (outside menu/footer) | 580 words | 864 words |
| Main text an extractor keeps (trafilatura) | 337 chars, all menu labels + giveaway popup | 2,438 chars: intro, picks, comparisons, latest reviews with verdicts, how we test, testers |
| Word "comparison" in the text | 0 (ranks for it anyway: authority + menu) | section + 6 comparison links + "173 comparisons" |
| Structured data | none | Organization + WebSite (name variants as alternateName) |
| Unique content links | 46 (5 of them to URLs that redirect) | 51, all answer 200 directly |
| Canonical / robots / indexing | self / index | unchanged |
| LCP | 0.8–0.9 s (text) | hero photo; staging 1.1–1.7 s uncached; phones now get the 86 KB photo, desktop 282 KB; CLS 0 |

## Links the homepage stops giving (in the page body)
Still in the header menu on every page (weaker than a body link), but no longer in the homepage body: **/top/what-are-the-best-daily-trainers-right-now/ (657 clicks in 90 days)**, **/top/puma-running-shoes/ (576)**, **/guide/best-running-shoes-for-beginners/ (347)**, /guide/best-running-shoes-for-heavy-runners-2024/ (92), /reviews/trail/hoka-challenger-8-review/ (84).
Gone from the page entirely: the Fitbit Charge 6 article (200 clicks), Terra Kiger 10 (86), Cascadia 19 (78), Zara article (54), Mafate 5, Huawei GT 3, the GPS guide, news/gear/training posts and three category pages (noindex). Most are pruning candidates; the trail reviews and the Fitbit article are the ones to watch.
New body links: 9 picks (incl. Novablast 5, Winflo 11, Bondi 9…), 6 comparisons, type listings, GPS reviews, 6 author pages, About.

## Before go-live
**Must**
1. **Title: "Running Shoes Reviews and Comparisons | Running Shoes Guru".** Keeps the exact wording of the biggest generic query ("running shoes review", 154k impressions, pos 3.3) and adds comparisons. Drops "Buying Guide" (≈5k impressions, ~2 clicks in 90 days).
2. **Keep body links to the strong guides:** one quiet line under the picks, e.g. "More of our lists: Best daily trainers · Best for beginners · Best for heavy runners · Puma lineup explained". Optionally the 3 latest trail reviews as text links in the Trail tile.
3. **Fix /top/best-running-shoes-right-now/ title (still "(April 2025)")**: the new homepage hands "best running shoes" to that page on purpose (H2 "Our picks right now", anchor "best running shoes right now"). Expect the homepage to lose some of those impressions (only ~140 clicks in 90 days).
4. **Timing:** the November core update started 10 Nov and is still rolling (Google: up to 2 weeks, so ~24 Nov). Pruning is planned ~22 Nov, Black Friday 27 Nov. Changing the #1 page inside that window makes any move impossible to attribute. Recommended: **go live Tue 2 Dec** (after Cyber Monday, a week+ after the pruning, after the update settles). Alternative if Black Friday matters more: live this week, accepting a noisy read.

**Should**
- og:image is still the 2011 white logo: use the hero photo or a proper share image.
- Remove the hidden 2024 "Running Shoe GIVEAWAY" exit popup + scroll popup markup (footer.php, every page): no script opens it any more, but extractors read its text on every page.
- Description and hero line carry counts as text: refresh when they move a lot.

## Go-live and watch
1. Deploy + switch template + new title/description on live; purge; Search Console → URL Inspection → Request indexing; check the rendered HTML there.
2. Watch daily: Umami search landings on the homepage (next day); Search Console (3-day lag) for the homepage: brand clicks, generic clicks, positions for the queries above. Expect 1–2 weeks of movement.
3. **Rollback rule:** after 14 days, if generic homepage clicks are down > 30 % vs the 4 weeks before *and* the key queries lost ≥ 2 positions while the rest of the site did not, first put the old title back; if no recovery in another week, switch the template back (one command).

## Machine readability
Gate passed: extractors now keep the real page (2,438 chars vs 337 of menu text); one H1; footer column titles are labels (no footer H2s in the outline); "Most read" (h3 over a card list) is the only heading trafilatura drops. JSON-LD parses (Organization, WebSite, ContactPoint, ImageObject).
