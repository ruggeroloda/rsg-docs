# /reviews/ hub — Search Console + tech audit (17 Nov 2026)

## Status (17 Nov 10:55 UTC): LIVE
Fixes for problems 1–7 (except the mobile filter duplication) are **live since 17 Nov 2026 10:55 UTC** (Ruggero: "deploy to live"), commit `11d3061` = tag `live-20261117-105515`; rollback = deploy tag `live-20261117-094858`. Logged in `rsg.change_log` by deploy.sh. Details: `theme/RUNBOOK.md` → "/reviews/ listings". Correction to problem 1: the count does not clash (1,365 = shoe reviews only; the homepage's 1,300+ = 1,394 incl. GPS).
- **Hub only:** title `1,365 Running Shoe Reviews, Bought and Tested`; description "Every shoe bought with our own money and run for at least 50 miles before we review it. Browse 1,365 reviews by brand, type or name, newest first." (+ " Page N of 137." on page 2+); new intro + "How we review" link (the old intro's link to /top/best-running-shoes-right-now/ is gone); share image = newest review's photo. Brand/type/family listings keep their titles ("(Month Year)") and texts.
- **All listings (same template):** list in `<main>`; one `h2` per card + hidden "Expert score: N/10"; specs as `<dl>`; count/Search/Filter no longer headings; dead `ga()` + orphan itemprops removed; **reflected XSS in `?s_rev` fixed** (search term was printed raw) + search results noindex; pages past the last → 404; type filter links with trailing slash (were 301s); pagination HTML fixed + links every 25 pages (wraps on phones).
- **Checks (staging, then live after the deploy):** Raptive slots unchanged (hub 7 phone / 9 desktop; Nike 6/9; stability 6/9; Pegasus family 7/9 on live); card element positions identical to the old layout at 390 and 1366 px; all listing links 200; trafilatura keeps 10/10 shoe names, scores and prices; no PHP fatals after the deploy.
- **Left for later (Ruggero, 17 Nov):** brand/type/franchise intro texts (10/35 brands and 1/9 types have their own text, all 27 franchises do; the rest show "The best … on the internet!"; existing texts have typos and dated facts); mobile filter `<select>`s vs desktop sidebar links (reskin).
- **To measure:** Search Console for /reviews/ and the listings from ~24 Nov (title/description change); keep apart from the ~22 Nov article pruning when reading it.

_Read-only audit below. Raw files: `theme/_work/reviews-hub-audit-20261117/` on the Mac (live HTML of /reviews/, page 2, Googlebot fetch, screenshots). Data: `rsg.gsc_page_day` / `rsg.gsc_query_day` on rsgmedia (to 14 Nov), Umami 16 Nov (human sessions)._

## 1. Search Console — it does not rank well
| | Clicks, last 90 days | Same 90 days 2025 |
|---|---|---|
| /reviews/ (hub) | 259 | 886 |
| /reviews/page/N (123 URLs) | 24 | 9 |
| Brand/type listings (/reviews/nike/all/, /reviews/all/stability/… 1,006 URLs) | 5,139 | 12,463 |
| Whole site | 199,150 | — |

- Hub = 0.13 % of site clicks. ~85 % of its clicks are **brand** queries ("running shoes guru", "runningshoesguru"…): it is the brand sitelink, not a ranking page. Brand clicks site-wide fell 2,860 → 987 / month (Jul 2025 → Oct 2026).
- **Generic head terms** ("running shoes review(s)", "running shoe reviews"): position ~6 and 40–60 clicks/month until Nov 2025 → position 12–15 from Dec 2025 (core update) → generic clicks ~0 since Jun 2026.
- Since Aug 2026 the hub shows again at "position 3–5" for "running shoes review(s)" and "running shoes comparison" with thousands of impressions and **0 clicks** (e.g. "running shoes comparison": ~500 impressions/day 27 Oct–10 Nov, 1 click). Spiky, zero-click: most likely an AI Overview / AI Mode citation or a non-blue-link placement, not a real top-5 organic result. Long synthetic queries ("reviews running shoes pros and cons", "…buying guide", "recommended by pros running shoes…") at positions 26–76 point the same way (AI fan-out queries).
- What ranks is the **individual reviews** (/reviews/road/…, avg position 7–9), on their own content.

## 2. Usage (Umami, 16 Nov, humans)
- ~170 views/day on /reviews/, almost all internal navigation (google.com referrer: 6). It is a browse page.
- Web Vitals (review_list p75): LCP 1.0 s phone / 1.3 s desktop, CLS 0.03–0.04, INP 124–208 ms, TTFB 0.62–0.73 s. Good; TTFB the weakest (uncached MISS 0.82 s; template runs 8 count queries with `posts_per_page -1`).
- Price buttons on the cards: 482 seen, 4 clicks (CTR 0.8 %; 0 on phones).

## 3. Tech audit (template `reviews_search.php`, shared with all brand/type listings)
Not reskinned: still the 2022 Bootstrap template.

**OK:** 200, self canonical (page N self), robots index, `lang="en"`, CollectionPage + ItemList (10) + BreadcrumbList JSON-LD, one H1, shoe images with real alt + width/height + `fetchpriority` on the first, Amazon links `sponsored nofollow`, out-of-range pages noindexed, `?s_rev` searches canonical to /reviews/.

**Problems (by impact):**
1. **Title** `1365 Running Shoes Reviews (November 2026)`: month implies freshness (newest card is a May 2026 shoe); no thousands comma; "Running Shoes Reviews" is clumsy.
2. **Meta description / intro**: "The best Running Shoes Reviews on the internet! … hundreds of feedback from our readers!" — hype, unsupported, grammar; same on every page N. No word on how RSG tests (bought, 50+ miles), no E-E-A-T on the hub. og:image from 2020.
3. **Heading outline**: no H2/H3. "1365 matching reviews", "Search", "Filter" are H4; every shoe name is an H4 **twice** (mobile + desktop copies both in the DOM), spec labels H6. Machines see a flat list of duplicates.
4. **Duplicate DOM**: mobile and desktop versions of filters, stars and titles both printed (hidden with CSS).
5. **Score not machine-readable**: stars are a CSS width (`width:70%`), no text or aria value.
6. **Dead code**: 8 inline `ga('send'…)` handlers (Universal Analytics, stubbed to a no-op); orphan microdata (`itemprop` with no `itemscope`); ~54 KB inline CSS + ~51 KB inline JS in a 219 KB page.
7. **Pagination**: 137 pages, links only to 2, 3 and 137; deep pages reachable only through brand/type listings and sitemaps. Page 200 returns **200 + noindex** (soft 404) instead of 404.
8. **Naming**: "Hoka One One" (now HOKA), "ON", "Puma PUMA MagMax Nitro 2".
9. Filters on phones are `<select>` + JS (desktop sidebar has real links, so crawling is fine).

## 4. Proposed next steps
1. **Decide the hub's job**: browse page + brand sitelink, not an SEO target for "running shoes reviews" (lost since Dec 2025, zero-click now). SEO effort goes to the brand/type listings (5k clicks / 90 days, 1,006 URLs) — same template, so every fix below covers both.
2. **Quick head fixes** (staging → live): title without month, honest description + intro (how we test), og:image, distinct descriptions per page N, out-of-range page → 404, `?s_rev` → noindex. _Done 17 Nov._
3. **Markup clean-up** (same look): H2 per card once, specs as `<dl>`, one DOM for mobile/desktop where possible, score as text + aria, drop `ga()` and orphan `itemprop`. _Done 17 Nov (ItemList image + rating not done)._
4. **Reskin the listing template** in the new design (hub + brand/type listings), with a real browse structure (brand × type links, newest, top rated) and a decision on price buttons on cards (0.8 % CTR).
5. **Measure**: Umami review_list funnel + GSC listing clicks before/after; avoid overlapping with the ~22 Nov pruning window.
