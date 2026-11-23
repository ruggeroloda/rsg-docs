# Revenue data: collect, normalise, read (plan, 23 Nov 2026)

Goal (Ruggero 23 Nov): all revenue sources in the `rsg` database, comparable, at a grain fine enough to see the effect of changes made during a month. Feeds strategy step 2 (revenue per 1,000 views by page type).

## Design
- Collect **per day** where the source allows; cut any window afterwards (week, month, before/after a `rsg.change_log` date).
- One table, grain: day × source × account × tag/sub-ID × page (where known). Metrics: clicks, orders, items, sales, commission split pending / confirmed / reversed. Amounts to € at the ECB rate of the day, original currency kept. Two dates: order/click date (steering) and booking date (cash). Each load re-pulls the last 90 days and overwrites (returns and reversals land weeks later).
- Mapping table: tag / sub-ID → page type (→ spot where a network passes it).
- Loaders: plain Python on rsgmedia, daily timer; files for Amazon from a drop folder.

## Sources (researched 23 Nov; not yet tested with our accounts)
| Source | Route | Grain | Page / spot | Needs |
|---|---|---|---|---|
| Amazon US | **no reporting API**; Ruggero downloads | one summary per chosen date range (no per-day rows) | tracking ID | see routine below |
| Raptive | Creator API (beta), `publisher-api.raptive.com/creator-api/v1` | daily traffic confirmed; per-page earnings by day not stated | per page **title** (not URL) | beta access by ticket (drafted 23 Nov; Ruggero sends), token in Account Settings, expires after 365 days |
| CJ | Commission Detail API (GraphQL) | per sale | SID | personal access token |
| Awin | publisher API (transactions) | per sale | clickref | API token (20 calls/min) |
| Rakuten | reporting API (events / advanced reports) | per sale | u1 | security token |
| AvantLink | `AffiliateReport` API module | any range | ctc | affiliate id + auth key |
| Impact | Actions API | per sale | subId1 | read-only API token |
Network reporting tokens are new keys (not the DFS feed logins): Ruggero → 1Password, Claude → rsgmedia.

## Amazon routine (Ruggero 23 Nov)
- **Weekly, Wednesday:** Tracking-Id report for the previous Mon–Sun. Use: clicks per tag + total orders/earnings per week. Earnings per tag are mostly hidden in a week (threshold ~4 items).
- **Monthly, ~5th:** Tracking-Id + Category for the previous month (Category's Date column = the month end; the Tracking-Id file of the same month has the same totals: that pairs them). Linked Product / Top Sellers optional: product sales are mostly hidden.
- Files on the Mac in `data-drops/amazon/{weekly,monthly}/`, named `amazon_<report>_<start>_<end>.xlsx` (**not in git**, `.gitignore`). The files carry no date range inside (Tracking-Id) → name them when they arrive; weekly files were checked against the monthly totals (Oct: 4,563 vs 4,566 clicks).
- In hand 23 Nov: 12 weeks 31 Aug–22 Nov; months Jan–Dec 2025 + Oct 2026. Ruggero downloads Jan–Sep + Nov 2026 next.

## First reads (23 Nov)
- 2025: $15,010 from 93.3k clicks ($0.16/click); earnings halved Jan → Dec but $/click stayed $0.12–0.20 (decline = traffic). Oct 2026: $327 at $0.07/click → the value drop happened in 2026 (2026 files will show when).
- Comparisons: $0.21–0.39/click all 2025, 54 % of 2025 Amazon earnings; reviews $0.07–0.16 (2025) → $0.03–0.04 (Oct–Nov 2026).
- `pimeagi-20` (main account tag = untagged links) 13 % of 2025 clicks at $0.06. Mar 2025 (14.1k clicks, half the usual $/click) and Nov 2025 untagged spike look like junk traffic.
- Week of 16 Nov 2026: best $/click of the 12 weeks ($0.13), same week as per-spot tags, bot challenge and pre-Black-Friday: not attributable yet.
- **Page-type read (23 Nov, 14:30 CET; `analytics/amazon-reader/read_amazon.py`, all 13 monthly files name-checked OK):** Oct 2026 vs 2025 — reviews $0.03/click (2025 $0.07–0.16; orders per click 1.8 % vs 6–9 %), comparisons $0.26 (2025 $0.21–0.39), untagged $0.08 (2025 $0.02–0.11). Mix also shifted: reviews 75 % of clicks (2025 ~50 %), comparisons 12 % (2025 ~30 %). At 2025 per-type rates Oct 2026 would be ~$0.14/click: the drop is reviews converting worse plus fewer comparison clicks, not untagged links or everything. Weekly totals 31 Aug–15 Nov already $0.05–0.09/click → the drop happened between Jan and Aug 2026; the Jan–Sep 2026 monthly files (not in the folder yet) will date it.
- **Jan–Sep 2026 files in (23 Nov 14:30 CET, all name-checked OK) → the drop starts in June 2026.** $/click Jan–May 2026 $0.13–0.18 (like 2025); Jun $0.07, Jul $0.03, Aug $0.12, Sep–Oct $0.07–0.08. Jun–Jul: review-tag clicks (`rsg-single-20`) jump to 6.1k and 14.4k (usual 2–5k) while review orders fall (Apr 326 → Jun 188 → Jul 126) → most likely junk clicks; not yet checked against our click log. Aug–Oct: review clicks back to 1.5–3.4k but orders per click still 2–5 % (2025 6–9 %); comparisons keep $0.22–0.27/click but their clicks fall 2.1k (Apr) → 0.45k (Sep). Real orders halved from spring across both page types, in line with the May 2026 core update (`TRAFFIC-HISTORY-2026-11-20.md` row 5). Untagged steady at $0.03–0.09.
- Raptive AI "revenue opportunities" (22 Oct–20 Nov): homepage 23,132 views at $0.45 RPM / 0.49 impressions per view; 2012-URL reviews ~0.35 impressions per view; flat-feet article 12 % viewability. Kept for the Raptive mail (strategy step 3); individual pages not worth acting on.

## Steps
1. ~~Research sources~~ (done 23 Nov).
2. Ruggero: Raptive beta ticket, network tokens, Amazon 2026 monthly files.
3. Claude: table design + tag/sub-ID mapping (`amazon-tags-2026-11-23/PLAN.md` for Amazon).
4. Loaders: API networks (backfill ≥ 1 Sep), Amazon files, Raptive.
5. Weekly view: revenue per 1,000 views by page type, change dates marked; our click log vs Amazon clicks per week as a bot check.
