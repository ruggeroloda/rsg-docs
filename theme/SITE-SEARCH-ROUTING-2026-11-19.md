# Site search routing — 19 Nov 2026

**DONE — live 19 Nov 2026 15:25 UTC** (theme `04479d8`, tag `live-20261119-152531`, with the listings reskin; Ruggero: "deploy to live"). Code: `rsg_2022/includes/rsg-search.php`. Earlier ideas: `SITE-SEARCH-IDEAS-2026-11-17.md`.

## What the data said (16–19 Nov, humans, Umami page views matched to the search log by timestamp)
- ~110 search visits/day (~5 % of visits); 723 searches in 343 visits; half of those visits searched more than once. Only 21 % from the homepage hero, the rest from the header.
- ~90 % of searches are a shoe name: 49 % a line without a number ("pegasus"), 41 % with one ("novablast 6"); brand only 4 %, questions 5 %.
- **51 % got zero results** (61 % with a version number). Causes: ~40 % newer models not reviewed (Novablast 6, Endorphin Azura, Cloudmonster 3, Hyperboost, 1080 v15, Superblast 3, Triumph 24, Kayano 33, Clifton 11…), ~25 % typos/spacing ("1080v15", "adios pro4", "ghostmax2" — the old search matched titles word by word), ~10 % questions, rest off-topic/spam/non-Latin script.
- After results: 62 % opened a review, 9 % left. After zero results: 47 % searched again (half of those failed again), 19 % left, 8 % reached a review.
- Bare line searches that opened a review picked the latest version 55× vs an older one 16×.
- 17 % of results-page views had no log row: repeats served from the 30-day edge cache. Ruggero 19 Nov: keep the cache (he purges when he publishes); measurement moved to Umami.

## Rules (rsg_search_route)
Index = one row per published shoe review (GPS excluded) from the titles: brand (taxonomy) + model words + version (trailing number, `v 14`, `3.0`, roman), cached 1 day, cleared on post/term changes. Search words: lower case, letters split from numbers, brand detected (aliases: hoka, on running, nb, sketchers…), stop words out, decoration words ignored (gel, fresh foam (x), zoom, air, wave, fuelcell, adizero, terrex, gts unless typed).
- brand only → brand page; a family name without number → family page (302).
- name + number we reviewed → that review; name without number, one review → that review.
- an exact version wins across lines ("adidas pro 4" → Adios Pro 4); same name + number at 2+ brands and no brand typed → "closest" list.
- name without number, several reviews → "line" page (newest first, max 12, heading "HOKA Speedgoat reviews").
- name + number not reviewed → "We haven't tested the X yet" + card of the nearest older version (or the newest if they typed a newer one; gap ≤ 2) + link to the family/line.
- unsure (letters-only match, bigger gap) → "No exact match" + closest 3.
- unknown shoe: family name inside → family page; known brand → that brand's 3 newest (unless the title search found posts); nothing → the old title search, else "No results" + newest 3.
- Simulation on 605 real searches: zero results 50 % → 0 %; 65 % land on the shoe (review 24 %, line 17 %, family 10 %, not tested 11 %, closest 3 %), 14 % brand, 22 % nothing recognised (typos ~1/5 of those).

## Measurement
- Search log `newguru_wp_searchwp_log_copy`: column `engine` = `rsg:<outcome>` (brand_page, family_page, review, line, not_tested, closest, brand, nothing, results).
- Umami: `search_submit` (q, form = hero | results | header) and `search_result` (outcome, n, q; fires on cached pages too).
- 7-day watch ~26 Nov: share of `search_result` outcome none/nothing, searches per visit, review opens after a search; the top `not_tested` lines = review backlog.

## Parked / dropped (Ruggero 19 Nov)
- Typo tolerance (step 3): parked until the 7-day data (~3 % of searches; needs a guard: "mills" → Salming Miles, "anta" → Salomon Mantra in the test).
- Results page as shoe cards (step 4): dropped — only ~3 % of searches still reach the old list.
- Edge cache on search pages: kept at 30 days.
- No new families: the 27 families stay; 41 untagged reviews of those families were tagged (live 19 Nov).
