# Ads baseline — Raptive, 12 Oct – 10 Nov 2026

**Status 13 Nov: paused (Ruggero). Ad placement continues as an important part of the redesign** — see "Open for the redesign" at the bottom and `../theme/THEME-REBUILD-NEXT-STAGE.md` ("Ads in the reskin").

Source: Raptive dashboard exports (Ruggero, 13 Nov). Use as the "before" for every ad change. Compare 2 weeks after each change, same metrics.

## Totals (30 days)
- Ad earnings **$1,109** (~$37/day), 163.7k page views, 130.6k sessions, page RPM 6.8, session RPM 8.5.
- Year to 10 Nov: $18.2k. Monthly earnings fell from $2.6k (Mar) to $1.1k (Oct) with traffic; page RPM ~6.6–9.2.
- Device: mobile page RPM **8.32**, desktop **5.35** (desktop = 53 % of page views).
- Country: **US = 68 % of earnings** (page RPM 16.4, CPM 2.47), UK 11 % (10.7). Japan 9.1k page views at page RPM 0.25 (likely bots).
- Monetized sessions: ~70 % (Mar–Jul) → **~55 % (Nov)**.
- Page type: comparisons 22 % of page views / 40 % of earnings (page RPM 13.4, 8.2 imps/pv); reviews page RPM 7.3 (5.9 imps/pv); homepage 19k page views at page RPM 0.54.
- Traffic source (5–11 Nov): Google page RPM 8.1, Bing 11.1, Direct 4.5 (20.5k pv, probably incl. bots); pages per session ~1.2.
- Ad density (dashboard, 13 Nov): target desktop Optimized (20–22 %), mobile Medium (22–24 %); **actual desktop 15 %, mobile 14 %**.

## By ad unit (30 days, all devices)
| Unit | Earnings | Share | Viewability | eCPM (mob / desk) |
|---|---|---|---|---|
| Content (in-content) | $344 | 31 % | **34 %** (mob 31, desk 38) | 1.16 / 1.09 |
| Footer (sticky anchor) | $336 | 30 % | 87 % | 1.48 / 1.40 |
| Video (sticky playlist) | $224 | 20 % | 82 % | 1.78 / 2.03 |
| Sticky Outstream (video ads, no content) | $169 | 15 % | 86 % | 1.08 / 1.14 |
| Sticky Sidebar (desktop) | $31 | 3 % | **57 %** | — / 1.08 |
| Below Post | $3.79 | 0.3 % | 45 % | |
| Interstitial | $2.02 | 0.2 % | 43 % | |

## Readout
- Content ads: 2 of 3 impressions are never seen, although actual density (14–15 %) is already below target → the problem is scroll depth (unseen ads lower on the page), not ad count. Raising actual density towards target would add mostly unseen impressions.
- Below Post + Interstitial: ~$6/month together; the interstitial is the most intrusive format.
- Sticky sidebar: the desktop sidebar (`.col-4`, 380 px) holds one child, the **sticky price box (~675 px tall, sticky the full column)**, checked on the Pegasus 42 review 13 Nov. The sticky sidebar ad has no room of its own → 57 %. Price box stays (affiliate); a more compact box in the reskin would leave room for an ad below it on tall screens.
- Video + Sticky Outstream: 35 % of earnings; the main "annoying vs money" decision.

## Changes log
- **11 Nov (Ruggero, dashboard):** interstitials off (desktop + mobile), mobile large format ads off. Other settings at that point: desktop sticky footer close button removed, mobile one kept; sticky outstream on both; mobile sticky video bottom right; Reduce CLS from ads on.
- **11 Nov (Ruggero, Raptive request #495303):** remove Below Post on all devices; mobile viewport limit (no two content ads on screen at once); asked why actual density is below target.
- **13 Nov ~16:00 UTC (Raptive, Keaton):** Below Post removed, mobile one-content-ad-per-viewport override on. Ruggero replied: keep the guide cap and the viewport limit as they are for now.
- Measure the 11 + 13 Nov changes from ~25–27 Nov against this baseline.

## Raptive's answers (#495303, 13 Nov — Keaton 15:58 UTC, Brianna 21:55 UTC)
- **Actual vs target density:** actual is a rolling 30-day site-wide average incl. home/category pages, so a few % below target is normal. Main brake: **Guide pages are capped at 1 content ad desktop / 2 mobile** (2–10 % actual density). The new mobile viewport limit will also lower mobile density. Raptive suggests removing both; Ruggero declined for now.
- **Page types:** today targeted **by URL**. Raptive recommends **unique body classes per page type** in the new design.
- **Selectors:** ads are inserted via HTML selectors. Raptive recommends **stable markers on the parent containers** (e.g. a `sidebar` class on the sidebar, `entry-content` on the main content); no marker per ad unit needed. → Selector dependence on Bootstrap classes can end, if Raptive re-maps on staging.
- **Widths:** main content **≥ 728 px on desktop**, ≥ 320 px on mobile; sidebar **≥ 300 px** (with content still ≥ 728). Article: https://raptive.com/resources/redesign-your-site/
- **Breakpoints:** mobile ≤ 768 px, desktop ≥ 1,024 px, tablet in between.
- **CLS:** "Reduce CLS" reserves a **250 px min-height** per ad.
- **Caps elsewhere:** How To + Review pages up to 99 content ads (limited only by page length). **Enhanced Sticky Sidebar** is on: several sticky sidebar ads, number depends on page length → sidebar must run the full article height.
- **Staging:** Raptive will set up placements on our staging site; send the link **as early as possible**.
- **Not answered:** full current placement setup (selectors/spacing per page type), which elements content ads may follow, whether staging review generates real impressions, notice period needed.

## Open for the redesign
1. **Mobile price box vs content ads:** the mobile box (`section.rsg-dfs.d-lg-none`) is a direct child of `.col-lg-8`, right after pros/cons (~2nd screen); Raptive's `.col-lg-8 > *` selector lets a content ad sit directly above or below it. Not yet seen live: Ruggero's Chrome gets Raptive's ad-blocker stub (no ads render), so check on a phone on mobile data. Fix via the new container markers / Raptive staging setup.
2. **Desktop sidebar:** compact price box + sticky ad below it (theme doc); sidebar ≥ 300 px, full article height.
3. **Speed:** `rsg-core` Raptive ads module (editors/previews/404 off, per-page switches, preconnect); check ad-caused CLS/INP.
4. **Video decision** (35 % of earnings) after the above, with before/after data.
5. **Markers + staging handover to Raptive** (body class per page type, stable content/sidebar containers, staging link) — see Raptive's answers above.
