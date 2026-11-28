# Stats read 24 Nov 2026 (after the Rocket.net server move)

Session 24 Nov, data to 23 Nov (Umami, humans only unless said; Search Console to 21 Nov; Raptive Performance by Page).

## Data health after the move (07:24 UTC)
- Umami, nightly revenue run (Awin OK again, Impact OK), Search Console + metrics jobs: all fine.
- Old view log (`newguru_wp_rsg_views`, retired ~30 Nov): it always counted ~80–100 US views/hour more than Umami (automated browsers Umami drops). That surplus faded from ~22:00 UTC 23 Nov to ~0 by 08:00 UTC 24 Nov, **before the move**; outside the US the old log runs 10–35 % below Umami (varies by hour). For the ~30 Nov Umami-vs-old-log comparison: take the US surplus out, don't cut at 23 Nov.

## Watches
| Watch | Result |
|---|---|
| Reskin 7 days (GSC 8–14 vs 15–21 Nov) | Site clicks −10 % (most on 12 Nov, before the reskin). **Comparisons −28 %** from 16 Nov, Google only (Bing flat), position unchanged (~6.2); reviews +13 %. See "Comparisons" below |
| Phone price bar G + review top (phone reviews, 16–18 vs 18–23 Nov) | Shop clicks / 1,000 views 44.8 → 42.7, visits with a shop click 4.4 → 4.6 %, bar 2.2 → 3.0 / 1,000, drawer opens 9.0 → 4.9 / 1,000 (but more convert), 32 % of phone review views swipe the gallery. No drop: keep. Before sample small (60 clicks) |
| Lists picks table | ~120 human views/day on the 5 lists: too small (shop clicks / 1,000 views 55 → 71, n 11 → 59). Raptive: lists $7.38 per 1,000 views (17–22 Nov) |
| Site search (19–23 Nov, 772 searches) | Zero results 51 % → 29 % (simulation 22 %); 48 % of searching visits open a review after. "Nothing": missing models (Azura, Hyperboost, Duramo Speed), typos (ascis, edorphin, azure), off-topic. Not tested (backlog): Superblast 3, Novablast 6, Winflo 12, Kayano 33, Clifton 11, Quest 7, Mach 7. Typo tolerance: Ruggero's call (open) |
| Homepage re-check (19–23 Nov) | Desktop landings in the markets leaving without a 2nd page 49 % (n 364; was 56–59 % first days, old homepage 46 %); phone 42 %. Nothing to change |

## Comparisons drop (Google, 8–14 vs 15–21 Nov)
- Single-shoe queries ("vomero plus", "nike pegasus 40"): Google now sends them to the review instead of the comparison; RSG total for those queries flat (326 → 332 clicks).
- **"X vs Y" queries: −35 % clicks (1,037 → 677)**, not taken by another RSG page: fewer top-3 appearances (pos 1 −22 %, pos 2–3 −23 %) and lower CTR at the same position (pos 1 5.2 → 4.3 %, pos 2–3 3.9 → 3.3 %). All countries.
- Not a broken page: Bing comparison clicks flat (41 → 68 → 55 / week), Clifton vs Bondi markup clean, reviews (same reskin) up, refreshed comparisons fell as much. Not seasonal (flat in Nov 2025). Most likely the November core update; the reskin can't be ruled out (no comparison kept the old layout).
- The 5 biggest losers (Clifton vs Bondi, Nimbus vs Novablast, Nimbus vs Kayano, Megablast vs Superblast 2, Metaspeed Sky vs Edge): only Metaspeed has newer reviewed models (Tokyo/Paris); Ruggero: leave it as is. The others already carry our newest reviews; searches ask for models we haven't reviewed (Bondi 10, Novablast 6, Kayano 33, Superblast 3).
- Easing: Umami Google landings on comparisons 410–475/day (17–21 Nov) → 579 / 572 (22–23 Nov), whole site up too (Black Friday week?). Re-check with GSC ~27 Nov.

## "Head to head" test (22 Nov 09:19 → 24 Nov 00:00 UTC, 578 human visits)
Split 303 on / 275 off (fine), 1 browser in both groups, no `x`, no JS errors.

| | Off | On | Diff (95 %) | p |
|---|---|---|---|---|
| Opens a comparison after the review | 2.5 % | 6.9 % | +4.4 (+1.0 to +7.8) | 0.01 |
| Opens any 2nd page | 26.9 % | 33.3 % | +6.4 (−1.0 to +13.9) | 0.09 |
| Shop click on the review | 1.8 % | 3.3 % | +1.5 (−1.1 to +4.0) | 0.26 |
| Shop click in the visit | 3.3 % | 5.9 % | +2.7 (−0.7 to +6.1) | 0.13 |
| Pages per visit | 3.22 | 3.74 | +16 % | — |

Block clicks add journeys (2nd page without block clicks ~27 vs 28 %). Seen by 50 % of phone visits vs 77–94 % desktop. Read on 8 Dec (~2,800 visits per group: detects ~±3 points on 2nd page, ~±1 point on shop clicks).

## Raptive by page type (Performance by Page; 14 Oct–14 Nov vs 17–22 Nov)
Files: `data-drops/raptive/` (uploads 24 Nov). **From 28 Nov (Ruggero): no more exports; the same read comes from the API table `rsg.raptive_page_day` (every page, per day, no view minimum).** The 14 Oct–14 Nov export covers 100 % of that period's earnings (pages down to 10 views). Page type = `rsg.page_type_map`, else `rsg.guess_page_type`.

| Page type | $ / 1,000 views 17–22 Nov | Share of earnings | Same pages: before → after |
|---|---|---|---|
| Comparisons | 14.85 | 29 % | $/day 12.75 → 12.28; per view +10 % (≈ season) |
| Reviews | 12.35 | 49 % | **$/day 11.93 → 20.86**; imps/view 6.2 → 7.2, CPM 1.27 → 1.72 |
| /top/ lists | 7.38 | 2 % | imps/view 5.5 → 4.3 |
| Articles | 7.31 | 3 % | imps/view 7.4 → 3.4 |
| GPS reviews | 5.96 | 1 % | imps/view 8.4 → 4.5 |
| Listings | 5.55 | 9 % | per view +46 % |
| Old reviews | 4.91 | 4 % | imps/view all pages 3.00 → 2.96 (selectors updated 19 Nov) |
| Homepage | 0.51 | 1 % | 0.54 imps/view |

Caveats: 6 days; "before" Raptive page views include early-November bots (per-view gains look a bit high); season ~+11 %; Below Post removal + mobile one-ad-per-screen (13 Nov) also lower ads per view. Homepage sticky footer estimate: ~350 human views/day × 1.0–1.7 footer imps × ~$1.45 CPM ≈ $15–25/month (left out of the Raptive mail).
