# RSG week 46: search clicks down ~16 % since 12 Nov

_Sample weekly report (written by hand by Claude with the analyst's method, 16 Nov 2026). Search Console: 6–12 Nov (settled days only; 13 Nov shown as preliminary). Umami: day one only (16 Nov from 09:25 UTC), so no week-on-week site numbers yet._

## In short
- **Search clicks fell from 12 Nov**: ~1,480 a day on 12–13 Nov vs ~1,760 a normal day in late October (−16 %). The loss sits on the money pages: comparisons (−19 %) and current reviews (−18 %), mostly on phones. Positions barely moved. Most likely Google's November core update (rolling out since 10 Nov), but it lands the day after our SEO batch — not separable yet. **Watch, decide on 21 Nov.**
- **The week as a whole** (6–12 Nov): 12,495 search clicks vs 13,659 a week in the 4 weeks before (−9 %, within normal swings). Same week last year: 34,680 (−64 %).
- **A second bot group is inflating "human" visits**: about half of today's "human" visits are direct visits from 40+ countries outside RSG's markets, mostly at screen 1920×1080 with en-US browsers. Until the bot rules are tightened, **visits and page views in Umami are overstated (~2×)**; click rates per price spot are less affected.
- Search impressions jumped +50 % on desktop/US from 6 Nov (the home page alone went from ~30k to ~45k a day) with no extra clicks. Not a loss — it's why click-through rate "dropped" (flags fired on it). Low priority.

## Scorecard
| Metric | This week | vs 4-week avg | Note |
|---|---|---|---|
| Search Console clicks (6–12 Nov) | 12,495 | 13,659 (−9 %) | within noise; the drop starts 12 Nov |
| Search impressions | 1.60M | 1.35M (+19 %) | desktop/US + home page; not traffic |
| Search click-through | 0.78 % | 1.01 % | follows the impressions, not lost clicks |
| Average position | 7.1 | 8.3 | better, driven by the same new impressions |
| Human visits / page views / spots seen / shop clicks | — | — | Umami from 16 Nov: first comparison ~23 Nov |
| Bot share (Umami) | 28 % today | — | + a second group not yet caught (see Data notes) |

## Going well
- **Phone price block** (the one in the content): 3 shop clicks from 53 times seen today (5.7 %) — the best of the big spots on day one. Too early to call (one morning), but it's the spot to watch against the phone sticky bar.
- Two reviews gaining from Google this week: **Hoka Cielo X1 2.0** (25 clicks vs ~8 a week) and **New Balance 880 v15** (22 vs ~7). Both recent shoes: a sign that fresh reviews still get picked up.

## Needs attention
- **Search clicks from 12 Nov (priority 2, finding #1).** See the deep dive. Do: nothing on the site yet; we check the settled data for 13–17 Nov on 21 Nov.
- **Second bot group (priority 2, finding #2).** Do: approve a rule v2 for the bot score (proposal below) so the Umami numbers can be trusted before the first weekly comparison.

## Deep dive: is the 12 Nov search drop us or Google?
**Answer:** most likely Google (medium-low confidence), not yet provable.

| Search clicks a day | Late Oct (20–30 Oct) | 6–11 Nov | 12–13 Nov |
|---|---|---|---|
| Whole site | 1,760 | 1,839 | 1,484 (−16 %) |
| Comparisons | 678 | 674 | 553 (−19 %) |
| Current reviews | 568 | 606 | 467 (−18 %) |
| Old reviews | 297 | 307 | 253 (−15 %) |
| Phones / desktop | 1,164 / 568 | 1,221 / 586 | 951 / 506 |

- **Where:** spread across comparisons, current and old reviews alike — not one template. Positions are stable (comparisons 6.0 → 6.3, reviews 8.4 → 8.2); reviews lost impressions (−15 %), comparisons lost click-through (1.8 % → 1.5 %).
- **When:** a step on 12 Nov (Google's day). Candidates in the change log: Google's November core update (from 10 Nov), the review-page markup batch (11 Nov evening), the SEO batch (11 Nov 23:30 UTC = the afternoon of 11 Nov in Google's time zone).
- **For Google:** comparisons fell as much as reviews, but the 11 Nov markup batch only touched review pages; the SEO batch changed archives, sitemaps, canonicals of paginated pages and a few titles — not comparisons. A core update hits all page types.
- **For us:** the timing is the day after both of our batches.
- **What settles it:** (1) pages whose titles changed on 11 Nov vs similar pages that didn't; (2) whether the drop deepens or recovers over 13–17 Nov; (3) Umami search visits per landing page from 17 Nov as the next-day signal. **Next check: 21 Nov** (17 Nov is settled then).
- Context, not news: search clicks are down 64 % on the same week last year; the biggest step came between late November and mid-December 2025 (~33,000 → ~21,000 clicks a week: post-Black-Friday season plus the December core update from 11 Dec).

## Experiments
- None running. **Proposal (waiting for your OK):** *Is the phone sticky price bar hurt by the ads over it?* First two weeks: just measure clicks per time seen for the sticky bar vs the phone price block (both phones, same pages). Rule of thumb for a later test: at ~2 % click rate, telling a 50 % difference apart needs ~3,100 views seen per variant — we'll know after a week whether that's days or months.
- Alt-text test (since 11 Nov): first read ~9 Dec.

## Open findings
- #1 Search clicks down from 12 Nov on comparisons/reviews — monitoring, next check 21 Nov.
- #2 Second bot group in Umami (direct, 1920×1080, 40+ countries) — proposed rule v2, waiting.
- #3 Home page impressions +50 % from 6 Nov without clicks — watch, low priority.

## Data notes
- Umami day one: 528 "human" visits by 12:40 UTC, of which 261 direct from outside RSG's markets (median 8 s, almost no price spots seen, 2 shop clicks). Rule v2 proposal: *direct visit + country outside RSG's markets + en-US browser + screen 1920×1080 → suspect* (excluded from the numbers, not deleted).
- Search Console flags this week included three "−99 %" guide pages (beginners, ASICS, Hoka): Google showed them for six days (31 Oct–5 Nov) and then stopped — back to their October level, not a loss. Lesson built into the method: always look at the longer series before calling a page drop.
- 13 Nov Search Console data is preliminary (not yet re-confirmed by Google).
