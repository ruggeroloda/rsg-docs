# Content review (Fable), 20 Nov 2026 — work list

_Started 20 Nov 2026 with Ruggero. **Next chat: continue at "Work list" below.**_

## What happened
- Opus built a fact pack (Search Console 4 Jul 2025 – 17 Nov 2026, GA4 Jan–Nov 2025 vs 2026, Umami 17–19 Nov, 4 sample pages + 3 phone screenshots): `content-review-2026-11-20/fact-pack.md`. Raw data tables: `_work/fable-pack/data/` (Mac only, not in git).
- Ruggero ran it in a fresh Fable chat (outside the project, no tools). Fable's answer: `content-review-2026-11-20/fable-review.md`.
- Opus checked Fable's claims against the data (below), then triaged the fixes with Ruggero.

## Key findings (checked)
- Search clicks −68% (187k Aug 2025 → 60k Oct 2026); rankings mostly held, CTR fell at the same position (review #1: 3.8 % → 1.1 %; comparison #1: 15.7 % → 6.0 %). "X vs Y" queries: impressions up, CTR 9.1 % → 2.7 %.
- Publishing collapsed: reviews published 149 (2023), 110 (2024), 76 (2025), 8 (2026). New reviews decay fastest.
- Comparisons convert 2.3× reviews (GA4 key events 2.77 vs 1.20 per 100 views); new-vs-previous-version comparisons best (Ghost 17 vs 16: 9.4).
- On-site (17–19 Nov): 80–86 % of visits see one page; ~5 % of review/comparison visits click a shop; only Shoe Finder sign-ups are tracked.
- Fable's estimate: at 1 shoe a month, Google alone gives ~40k search clicks/month in a year, not 2–3×. Getting there needs ~3× shoe coverage (samples), a channel Google doesn't control (email, YouTube), or a different goal (revenue per visit). **Ruggero's call, still open.**

## Corrections to Fable
- "Email: 13 visits in 3 days" says nothing: no send happened then.
- Sticky price bar "5 of 394 clicks": live <2 days in the sample; decide at the ~25 Nov watch.
- "$77.90 = women's price": the bar shows men's by default; women's only if that browser once picked Women (stored). Optional: label it when Women is active.
- Winflo 10/10 = expert score (6.5 = user score): not a bug.
- "(August 2024)" titles: gone from live since 17 Nov.
- Forerunner 265 "broken title": GA4 artefact; live title fine.
- Comparisons already showed both dates (not only 2020).

## Done 20 Nov (live)
- **Best-of lists all in /top/**: Beginners + Heavy Runners moved from /guide/ (slug drops -2024), /guide/ archive + 2024 stability post 301 to Best Running Shoes Right Now; review "Our top pick in" box reads /top/ lists. `/guide/` is reserved for brand range guides (new template, not designed yet).
- **One visible date** on comparisons and /top/ lists: "Updated <date>" or "Published <date>"; reviews keep both. List schema `dateModified` = same date.
- Details: `theme/RUNBOOK.md`, `DECISIONS.md` 20 Nov.

## Work list (continue here, in this order)
1. **Next-step block: PARKED 20 Nov (Ruggero: "not ready, decide another time").** Not built; nothing changed on the site.
   - First idea (wireframe + render on the live Pegasus 42 review): "Compare it" chip strip after pros/cons + end block (chips · "Better for you if…" · price alert). Ruggero: not convinced by looks or position.
   - Research (live pages, 20 Nov): RTINGS ends the review with ~12 "popular comparisons", each one line of reason + side-by-side link + review link, no images/prices. RunRepeat puts a comparison block (image, score, price, short role) right after verdict/pros/cons and drops alternatives inside the test sections and "Who should NOT buy". Running Warehouse: "is for you if…", 2 vs-sections, spec table. None uses a separate related-content widget, chips or a price alert.
   - Second idea (renders `content-review-2026-11-20/next-step-v3-who.png`, `next-step-v3-list.png`): reuse what exists. (a) "Who should not buy": one line under each sentence, "Try instead: <shoe> · score · price" (tester's pick, needs a review field); (b) "How does it compare?" table → list of rows (photo, score, role tag, one-line reason, comparison + review links, price), 6 + "Show more", specs table behind a link. Ruggero: v2 too big, breaks the page balance; v3 compact still not ready.
   - Gaps found: no Pegasus 42 vs 41 comparison; no Pegasus vs Evo SL. Open questions when it comes back: who writes the reason lines (tester vs generated), phone length of the list.
   - Baseline to measure against: 2+ pages per visit (review 20.5 %, comparison 14.1 %) and shop clicks.
2. **Shoe list for the next 6 months** (draft ranked list 20 Nov: `SHOE-LIST-2026-11-20.md`, Ruggero to choose), chosen by site-search demand + how many comparisons each shoe spawns. Site search "not tested" (19 Nov): Novablast 6, Superblast 3, Kayano 33, Endorphin Azura, Cloudmonster 3, Hyperboost, 1080 v15, Triumph 24. Each new shoe → 2–3 comparisons (always vs its predecessor).
3. **Strategic call (Ruggero):** more coverage (samples; would mean rewording "No free samples" on every review), a non-search channel (email/YouTube), or revenue per visit.
4. **Track every sign-up form** in Umami (only Shoe Finder today); prerequisite for the price alert and any email decision.
5. **"Newer version" card with price at the top of old reviews** (old reviews: 1,244 visits / 3 days, 1.77 % shop click vs 5.08 % on current reviews).
6. **/guide/ brand range guide: design open** (Ruggero: "not nailed yet"). Ideas so far: a clickable matrix (use across × price tier down by list price, Neutral/Stability toggle, detail panel with specs, "replaces", our review if any, shop button); untested shoes need a light shoe record; prices for untested shoes = DFS matching or Amazon search link; photo rights; "Not tested by us · brand specs" label; a plain-text version per shoe for search/AI. Puma lineup (now `/top/puma-running-shoes/`) moves to /guide/ when the template exists.
7. **/top/ archive page** ("Tops", nearly empty): proper hub or 301 to Best Running Shoes Right Now. _Hub built on staging 20 Nov (`theme/TOP-HUB-2026-11-20.md`), waits for "deploy to live"._
8. Later / parked: Raptive first-screen floating video over the verdict on phones (Ruggero: leave for now); phone bar "Women's" label when Women is active (low); More v6 / Structure 26 / Alternatives articles convert ~0 (low volume; check offers); old list-price text in reviews stays (prices were right when written).

- **24 Nov 2026:** the card (item 5) was already live since 15 Nov; 24 Nov it also finds the newest review by name (Cumulus 16 → 28) and its review link is tracked (`theme/RUNBOOK.md`).
