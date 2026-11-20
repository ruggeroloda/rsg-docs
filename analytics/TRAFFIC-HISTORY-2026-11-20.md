# RSG traffic history, Feb 2023 – Oct 2026: when it went wrong

_20 Nov 2026, from GA4 (`rsg.ga4_*`, "GA4 history" in `README.md`). Organic search was 85–90 % of all visits until 2025 and the only channel that moved, so "traffic" here = Google search visits (2026 Direct includes bot waves). Google update dates: Google Search Status Dashboard via Search Engine Roundtable / SEJ / Rank Math lists (searched 20 Nov). Page types from the landing URL (`rsg.guess_page_type`, trailing slash added: GA4 landing pages have none)._

## The shape
- Organic visits per day: **32.6k (Aug 2023, the peak)** → 24k (Jan 2024) → 14k (Aug 2024) → 12k (Apr 2025) → 8k (Jun 2025) → 5k (Jan 2026) → **2.7k (Oct 2026)**: −92 %.
- Since mid-2024 every month is **−45 to −59 % on the same month a year earlier**: the site halves every year. Most of it is a steady slide, but five periods lost more than the season explains.
- Season to keep in mind (2024 and 2025): Jan high, Aug→Oct −13 to −15 %, Black Friday week up, Dec −17 %.

## The five breaks
| # | When | Lost (beyond season) | Who / what | Matches |
|---|---|---|---|---|
| 1 | **Late Aug – Oct 2023** | Sep −14 %, Oct −15 % a month (normal −3 to −10 %); ~−15 % extra | **US** (−42 %); UK only −27 %, which is close to season. Old reviews (`/2022/..`, `/review/`) −46 %, articles −42 %, guides −60 %; new reviews −22 %, comparisons −20 % | Aug 2023 core (22 Aug–7 Sep), **Helpful Content Update (14–28 Sep)**, Oct 2023 core (5–19 Oct) |
| 2 | **May – Aug 2024** (slow, 4 months) | −34 % Mar→Aug; year-on-year went from −16 % (Mar) to −56 % (Aug) | **UK joins**: UK year-on-year +5 % (Mar) → −55 % (Aug); US −42 % → −67 %. Reviews −40 %, old reviews −51 %, guides −68 %; **comparisons held (+1 %)** | March 2024 core (5 Mar–19 Apr, merged the Helpful Content system into core); **AI Overviews in US from 14 May 2024**, UK from Aug 2024 |
| 3 | **May – Jun 2025** | Jun −27 % in one month (Jun 2024: −9 %); −38 % Apr→Jul | **Everything, everywhere**: all countries (US −37, UK −38, AU −42, CA −47 %), mobile and desktop; **comparisons fell too (−39 %)** for the first time | **AI Overviews to 200+ countries + AI Mode in the US, 20 May 2025** (Google I/O). The June 2025 core (30 Jun–17 Jul) gave a little back (+13 % vs −13 % a year before) |
| 4 | **Dec 2025** | Dec −29 % (Dec 2024: −17 %), Jan–Feb didn't recover | **US only**: US −21 % around the update, UK +14 %. Reviews −32 %, comparisons −13 % | **December 2025 core (11–29 Dec)** |
| 5 | **Late May – Jun 2026** | May −12 %, Jun −17 % | **UK hardest** (year-on-year −61 % → −64/−67 %); comparisons −34 %, reviews −31 % | **May 2026 core (from 21 May)** |

No visible effect: Nov 2024, Dec 2024, Mar 2025, Mar 2026 core updates (moves within season).

## What it says
- **Two different killers.** 2023–24 was Google's quality systems hitting the old content first (2022-era reviews, articles, guides), then the newer reviews. 2025 was AI answers: the 20 May 2025 step hit every page type and every country at once, including comparisons that had survived everything before.
- **Comparisons are the most resilient pages**: 15 % of search visits in early 2023, 31–35 % in 2026. Reviews went from 47 % (Q1 2024) to 29 %.
- **The US and the UK broke at different times** (US in autumn 2023 and Dec 2025, UK in summer 2024 and May 2026). Recent months: US year-on-year is improving (−59 % Apr → −48 % Oct 2026), UK is not (−65 %).
- Non-search traffic never mattered (≤ 10 %); the Direct jumps (Feb, Sep, Nov 2026) are bot waves (`README.md` caveat 5).

## Limits
- GA4 starts Feb 2023, so the 2023 breaks have no year-before season check; the "beyond season" figures for 2023 use the 2024–2025 season.
- GA4 sessions depend on cookie consent; a consent change would move all numbers at once (none visible as a single-day step).
- Landing page "(not set)" (sessions without a page view) grew from 3 % to 12 % of organic visits; page-type shares are of the visits with a known landing page.
