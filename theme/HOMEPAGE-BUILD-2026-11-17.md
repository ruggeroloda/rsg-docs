# Homepage 2026 — build and go-live (17 Nov 2026)

**LIVE since 17 Nov 2026 07:20 UTC** (theme `1607123`, tag `live-20261117-071928`; page 4714 = `template-home-2026.php`). Spec: `HOMEPAGE-FINAL-DESIGN-2026-11-17.md`; SEO review + watch/rollback rule: `HOMEPAGE-SEO-REVIEW-2026-11-17.md`. Commands and details: the Mac's `theme/RUNBOOK.md` ("Homepage 2026").

## What went live
- New template with: dark hero (photo of a recent review, automatic — rotates daily since 17 Nov 21:23 UTC, see below), "Our picks right now" (winners of /top/best-running-shoes-right-now/, live prices, men's), comparisons ("Popular" = 6 fixed from Search Console), latest 6 reviews, brand tiles (current logos, navy SVG), type tiles + GPS watches, how we test + testers, training-plans form (MailerLite: the 5 "ebook" groups).
- SEO: title "Running Shoes Reviews and Comparisons | Running Shoes Guru" (was "Running Shoes Reviews and Buying Guide"); new description; one H1; Organization + WebSite JSON-LD; share image = hero photo.
- Counts: 1,300+ reviews (incl. the 936 reviews of 2011–2022 published as blog posts), 173 comparisons.
- Site-wide in the same deploy: "EST. 2009" under the header logo, new footer, old hidden exit/scroll popups removed, review price drawer as native dialog.
- Tracking (Umami): `home_section_seen`, `home_click`, `home_search`, `plans_signup`; picks price clicks = `shop_click` component `home_picks`. Definitions in the analyst's CONTEXT.md.
- Checks after go-live: edge copy HIT with the new page, 1 H1, JSON-LD valid, canonical self, no PHP errors from the new files, only Raptive's sticky video unit on the page (no display ads), all 6 picks with US prices.

## Hero rotation — LIVE 17 Nov 2026 21:23 UTC (theme `4c796d1`, tag `live-20261117-212319`)
- Ruggero (17 Nov): rotate the hero through the last 5 reviews, changing every morning with the price update; no false freshness; the hero stays in "Latest reviews". Went live with "go live with these two changes" together with `27cada1` (user ratings always /10 in schema, from another chat).
- How: `rsg_home_hero()` picks one of the 5 newest road/trail/training reviews that have a gallery photo, by day number mod 5. The day switches at 02:00 UTC (DFS Amazon run start); the full purge after that push (~02:45 UTC) puts the new hero on the cached page. No stored state; page, preload and share image always show the same shoe. Pool size = `RSG_HOME_HERO_POOL`.
- Label "Latest review" → "Recent review" ("Photo: <shoe>, one of our recent reviews").
- Pool and order: 17 Nov Nike Structure Plus → 18 Nov ASICS Gel Cumulus 28 → 19 Nov Nike Pegasus 42 → 20 Nov Brooks Glycerin 23 → 21 Nov Saucony Ride 19 → repeat. A new review enters the pool automatically and pushes the oldest out.
- Deployed from a clean git worktree (another chat's list-image work was uncommitted in the tree; `deploy.sh` refuses a dirty tree). Checks: live home hero + share image = Structure Plus, label "Recent review", 1 H1; comparison and review JSON-LD valid with bestRating 10; no new PHP fatal errors. Change log on rsgmedia written by hand (id 80); analyst CONTEXT.md updated (repo + rsgmedia).
- Rollback: `./deploy.sh` the previous tag (`live-20261117-105515` = `11d3061`) via staging, or set `RSG_HOME_HERO_POOL` to 1 (= newest review only).

## Rollback (whole homepage)
`wp post meta update 4714 _wp_page_template template-home-2020.php` + old title "Running Shoes Reviews and Buying Guide" + old description, then purge.

## Day-1 read by market (17 Nov 07:20–21:20 UTC) — notebook finding #6
- Human visits landing on the homepage, **RSG markets only** (Search Console country list): leave without a 2nd page 54 % (n 111) vs 45 % (n 109) on the old homepage; **desktop + tablet 59 % vs 46 %** (n 79 each), phone 41 % vs 43 %. Not significant yet (p ~0.1–0.2). Visitors outside the markets look better (54 % → 40 %) but average 4.9 pages: likely partly crawlers, so left out of the judgement. (The all-humans, same-hours figures in "Open" below point the other way: the market split is what changes the picture.)
- Desktop in the markets: 17 % click or search, 35 % reach "Our picks right now" (phone 43 % / 41 %). At 1536×730 the 720 px hero fills the whole first screen.
- 0 price clicks from the picks; 0 plans sign-ups. Hero search = top action (~15 % of visits): see `SITE-SEARCH-IDEAS-2026-11-17.md` (finding #5).
- **Decision (Ruggero, 17 Nov 22:31): give the homepage a week; re-check ~24 Nov** (note the hero rotation from 17 Nov 21:23 and the article pruning ~22 Nov fall inside the week).

## In the tank
- **Shorter desktop hero — REJECTED 19 Nov 2026 (Ruggero saw it on staging: "I hate it"). Was on staging 17:47–19:02 UTC (`548ff26`), reverted (`56c9424`); never live. Desktop stays as live.** Original note: desktop-only CSS (≥ 992 px): hero height by content (~450 px instead of 720), smaller H1, lead on one line, chips + browse links on one line; nothing removed; phone unchanged. Preview with live-page screenshots at 4 desktop sizes + phone, and the CSS: https://claude.ai/artifact/HStvL3AMcHBsAGkEPxVXRF. Use if desktop in the markets is still clearly worse after the week.

## Ruggero's notes on the journey (19 Nov 2026, for the ~24 Nov re-check; nothing to change before)
- The old desktop homepage worked as a big hero with **two big buttons**: all reviews and the Shoe Finder (he remembers the second as "best running shoes"; in `template-home-2020.php` the two orange hero buttons read "Take the quiz" → Shoe Finder and "All reviews" → `/reviews/`). In the new hero those two routes are small underlined text links under the search ("All 1,300+ reviews" / "or try the Shoe Finder").
- In his view **phone visitors scroll more** than desktop visitors. Data so far (landings in the markets, humans): scrolled past the hero desktop 42–53 % vs phone 45–58 %; clicked or searched desktop 18–32 % vs phone 40–55 % (17–19 Nov). Desktop landings left without a 2nd page 58 % / 56 % (17 / 18 Nov) vs 43 % on the old homepage (16 Nov, thin baseline).
- So the re-check looks at the routes offered on desktop (what a visitor can do from the first screen), not at making the hero shorter.

## Open
- Ruggero: Search Console → URL Inspection → Request indexing for the homepage; fix /top/best-running-shoes-right-now/ title ("(April 2025)").
- Training-plans sign-up mechanism to rethink (Ruggero doubts people subscribe; idea: monthly contest).
- Header menu unchanged ("Wizard", search box, 53 px) vs spec. **Bug:** at 1280 px wide the header's "Training" item is clipped ("Trainir"); 15 market visits came from 1280×720 screens.
- Brand names as stored ("Asics", "Hoka One One", "Adidas").
- Watch per the SEO review (generic homepage queries, 14-day rule).
- First-day stats (17 Nov, same hours vs old homepage on 16 Nov, humans): desktop pages per home-landing visit 2.7 → 3.9, median time 10 → 31 s; only ~40 % of desktop viewers scroll past the hero; hero search = most-used element; "How we test" and the plans form seen by < 10 %.
