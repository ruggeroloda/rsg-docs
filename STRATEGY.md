# RSG strategy: what success looks like, and whether we are getting there

_Agreed with Ruggero 22 Nov 2026 (chat on the page proposals). **Read after `README.md` whenever a change to pages, content, ads or tracking is proposed.** Updated at close-out when a goal, a number or a logged change moves._

## The three goals
1. **The reader gets the answer and doesn't return to Google.**
2. **Revenue per 1,000 views (shop clicks plus Raptive) goes up.** Scroll depth is not a goal.
3. **The undecided reader opens a second page, ideally a comparison.**

Not goals: scroll depth, time on page, page length.

The goals are per page. Traffic (publishing) is tracked beside them on the last scoreboard line (Ruggero 22 Nov).

## Rules
- Every proposal or change says which goal it serves and which number it should move. No goal, no build.
- Every change that reaches the site gets a row in "Change log" below (the analyst's `rsg.change_log` records *that* something changed; this records *whether it worked*).
- Close-out (`rsg-close-out`): update the scoreboard and the log when something changed.

## Scoreboard
| Goal | Number | Value (date) | Source | Gap |
|---|---|---|---|---|
| 1 | Mobile position on "[shoe] review" searches; share of impressions in the top 5 | 6.5; 25 % (Oct 2026; was 57 % Oct 2025) | Search Console, monthly (`analytics/core-updates/REPORT-2026-11-20.md`) | Proxy only: a return to Google cannot be seen from the site |
| 2 | Revenue per 1,000 views by page type, ads + affiliate | Ads only: comparisons $13.4, reviews $7.3, site 6.8 (Raptive, Oct–Nov 2026); shop click on ~5 % of review/comparison visits | `ads/BASELINE-2026-11.md`, `ads/RAPTIVE-HISTORY-2025-11_2026-11.md` (12 months + season path), Umami `shop_click` | **Affiliate income is not joined to pages: the report does not exist yet** |
| 3 | Visits with 2+ pages; clicks from a review to a comparison | Reviews 20.5 %, comparisons 14.1 % (17–19 Nov); 2–3 % of onward clicks from reviews go to a comparison | Umami; `next_click` per block live since 22 Nov, clean baseline 1–7 Dec | Baseline read due 8 Dec |
| Traffic (feeds 1–3) | Reviews published per month; Google visits a month to the pages that exist | 8 reviews in all of 2026 (46 a quarter in 2023); 66k visits a month (Oct 2026; 134k Mar 2026) | `analytics/core-updates/REPORT-2026-11-20.md`; WordPress publish dates and Search Console, monthly | No publishing plan and no tester yet (next step 1) |

## What we know (22 Nov 2026)
- **Google:** length and time on page are not what it says it rewards; the trial testimony and the 2024 leak show it records whether a searcher comes back to the results ("good", "bad", "last longest" clicks). Weights unknown; Google denies it in public. Nobody outside Google is sure.
- **Rank is not the main loss:** about three quarters of the clicks lost since Oct 2025 were lost at an unchanged rank (the results page answers first). Rank was lost on "[shoe] review" searches only; candidates: publishing nearly stopped (8 reviews in 2026) and the newest models have no review. Old ranks back ≈ 130k visits a month.
- **Ads:** comparisons earn about twice a review per view (8.2 vs 5.9 ads per view), visits average 1.2 pages. A second page, to a comparison, is the ad lever; so are ads that stay on screen (sticky sidebar/footer). Recipe sites kept income with "jump to recipe" by moving ads to the recipe card (Mediavine's own account of its own setup; to ask Raptive).
- **Peers:** RTINGS added ads, then put full test results behind a membership (Mar 2026); HouseFresh lost 95 %, partly recovered in 2025, built YouTube. No review site found reporting that a page layout fixed this. Recovery from the 2023 update is rare (~22 % of tracked sites).
- **Ruggero's objection to "answer first" (21 Nov):** the most important thing at the top means fewer people go down the page (fewer ads seen). Open; the 1–7 Dec baseline can compare the ~10 comparisons that have the verdict box with the ~130 that don't.

## Next steps (order agreed with Ruggero 22 Nov, strategy chat)
1. **Publishing plan and tester** (traffic; rank on "[shoe] review" searches): who tests, how many reviews a month (`content/SHOE-LIST-2026-11-20.md`, ROADMAP #16). The only step that brings traffic; it waits for a person, not for data.
2. **Revenue report, simple version first**: Raptive per page + shop clicks per page × earnings per click per network → revenue per 1,000 views by page type. The exact per-page affiliate join (Amazon tags, networks' sub-IDs) comes after the sub-ID checks.
3. **One mail to Raptive**: ads next to the answer block, sticky sidebar on old reviews, homepage sticky ad (ROADMAP ~24–25 Nov).
4. **8 Dec: read the section baseline** against the goals (verdict box vs no box; where onward clicks come from), then which page proposals serve which goal (`content/ENGAGEMENT-READ-2026-11-21.md`, "Ruggero's answers"). "Compare it with" on reviews runs as a test in its own chat (ROADMAP).

Why this order: ads are about $1,100 a month (163.7k views × page RPM 6.8, `ads/BASELINE-2026-11.md`); +30 % revenue per 1,000 views is about +$330 a month against a target of 5k. Page changes alone do not get there; publishing is slow too (3 reviews a month ≈ +20k Google visits a month after a year) but it is the lever that adds up.

## Change log
| Date | Change | Goal | Expected | Result (read on) | Keep / revert |
|---|---|---|---|---|---|
| 22 Nov 2026 | Section tracking live (`section_seen`, `next_click`, `jump_click`) | measures 1–3 | baseline 1–7 Dec | 8 Dec | — |
| 22 Nov 2026 | `page_left` live (where readers are when they leave; their last action) | measures 1–3 | read with the 1–7 Dec baseline | 8 Dec | — |
