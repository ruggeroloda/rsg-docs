# Live SEO + LLM check after the 17 Nov deploys (17 Nov 2026, ~09:00 UTC; guides deploy added ~10:05 UTC)

Deploys today: homepage 2026 (`live-20261117-071928`), /top/ lists rebuild + content/title fixes (`live-20261117-084127`), guide titles (`live-20261117-084915`), mega menu + homepage alt fixes (`live-20261117-085221`), **guides rebuild + list Product `@id` + Raptive TOC/FAQ slots (`live-20261117-094858`, theme `f311fe2`)**. Checked on live edge copies.

## Guides deploy (`live-20261117-094858`, 09:49 UTC)
Ruggero: "deploy to live". `deploy.sh live` uploaded exactly 3 files (`single-guide.php`, `single-top.php`, `includes/rsg-schema.php`), PHP lint 8.3 + 8.4 ok, WP Rocket minified copies + used CSS cleared, WP Rocket + CDN purged (edge MISS right after). Tag pushed to GitHub; change log on rsgmedia written (`rsg-changelog-add`, id 76).

Pages: /guide/best-running-shoes-for-beginners/, /guide/best-running-shoes-for-heavy-runners-2024/, /top/best-running-shoes-right-now/, /top/what-are-the-best-daily-trainers-right-now/, /top/puma-running-shoes/. Before = live edge copies just before the deploy, after = right after the purge.

| Check | Result |
|---|---|
| H1 / `<main>` | 1 / 1 on all 5 (guides had no `<main>` before) |
| JSON-LD | parses on all 5; only change = list Products now carry `@id` = review URL + `#product`; og/twitter/article meta unchanged |
| `@id` match | all 26 list Products match the Product `@id` on their review page (26 review pages, 200) |
| Extractors (trafilatura) | guides 172/176 (was 4/18) and 207/210 (was 2/20); top lists unchanged 208/211, 205/209, 210/214. No heading before the H1 any more on guides |
| Raptive in-content candidates (`#main .col-lg-8 >*:not(h1)…:not(#faq)`) | guides 9 / 18 (unchanged); top lists 15 / 10 / 22 (were 13 / 8 / 20: +2 each, as expected) — desktop and phone |
| JS errors | 0 (1366 + 390) |
| CLS (lab, after scrolling) | 0 desktop, 0.0001 phone, on all 5; LCP phone 0.67–0.86 s |
| Internal links (main + footer) | 42 unique, all 200 directly |
| Duplicate ids | none |
| PHP log | no fatal errors since the deploy; the guide template's old warnings (undefined `shoe_name`, `$female`, `$ID` …) still logged |

Visual before/after (full-page, 1366 + 390, ads/Typekit blocked):
- **/top/ lists: identical** (pixel diffs only in photo decoding/antialiasing; same page heights).
- **Guides: same look, not pixel-identical**: a few px of extra/less spacing per block (trust row ~+4 px, card spec row ~+8 px), so page heights moved: beginners +20 px desktop / +44 px phone, heavy runners −27 px / +4 px. Nothing missing or restyled.

Content fix after the deploy (~10:00 UTC, Ruggero): beginners guide (post 29632) field `our_top_picks_title` "The 6 best running shoes for new runners" → "The 5 best…" (the list has 5 shoes), live + staging, purged; live edge shows "The 5 best…".

## Passed (earlier deploys, ~09:00 UTC)
- 11 pages (home, 3 top lists, 2 guides, review, comparison, /reviews/, About, author): 200, self canonical, indexable, one H1, JSON-LD valid; titles ≤ 64 characters.
- No "(Month Year)" left in any top-list or guide title or H1.
- No JavaScript page errors (desktop 1366 + phone 390); no duplicate ids on new pages; no broken in-page anchors on new pages.
- 78 internal links in the body/footer of home, top lists and guides: all answer 200 directly (no redirects, no 404s).
- Raptive content-ad candidates on the top lists unchanged at that point (21 / 13 / 28 with the earlier counting method): ad positions as before.
- Header menu: all dead `ga()` onclicks gone (were 49 per page, 95 on /reviews/); "More than 1,300 reviewed so far".
- Extractors (trafilatura / readability): top lists 208/211, 205/209, 210/214 items, each shoe block "In short: <shoe>" + verdict, pros, cons, who for, specs, ratings; homepage 16/17 (only the "Popular" label); review 66/69; comparison 10/10; About 9/9.
- Core Web Vitals (lab, live): homepage LCP 0.97 s phone / 1.02 s desktop, CLS 0; best-running-shoes list LCP 0.76 s / 0.46 s, CLS 0.
- Sitemaps: homepage lastmod now 2026-11-17 (was 2020); top lists, guides and "How we review" page carry today's text change; robots.txt has the Sitemap line.

## Not passed / backlog
- ~~Guides: old structure, extractors 4/18 and 2/20, "Why you can trust us" H2 before the H1, card photos without alt~~ — fixed by the guides deploy above.
- ~~Beginners guide heading "The 6 best…" with 5 shoes~~ — fixed 17 Nov ~10:00 UTC (field `our_top_picks_title`, post 29632).
- Descriptions too long on reviews (e.g. 282 characters), comparisons (195), author pages (299); About page has no JSON-LD.
- Review pages: duplicate `#comments` id (kept on purpose for Raptive), `#more-comments` link without target, a price-box white-background thumbnail without alt.
- Author pages: extractors keep 1/25 (listing of review cards).
