# Brief for Fable: what should an RSG review be? (22 Nov 2026)

_Written 22 Nov 2026 by a Claude chat that talked this through with Ruggero. That chat's own opinions are **deliberately left out**: Ruggero wants an independent view, not a reaction to one. Everything below is either Ruggero's words or a fact measured on the live site on 22 Nov._

## The job
Give Ruggero a reasoned recommendation on the **format of an RSG shoe review**: how the text is organised, how the expert score and the 10 sub-scores are shown and explained, where the tester's personal impressions go. Blunt trade-offs, no reassurance. He decides; nothing is built from this chat.

Two questions are tangled together; keep them apart and say what each costs:
- **A. Text format** (sections, order, voice) for **new** reviews.
- **B. Scoring scheme** (the 10 sub-scores, the expert score, what is scored and what is only described), which touches the history (367 scored reviews, comparison tables, schema).

Deadline that matters: the format should be settled **before the brief for the new tester** is written (STRATEGY.md next step 1: publishing plan + tester). No layout change on reviews goes live before the section baseline is read on 8 Dec.

## Read first
1. `README.md`, `ROADMAP.md`, `STRATEGY.md` (the three goals; every proposal names the goal and the number it should move).
2. `content/ENGAGEMENT-READ-2026-11-21.md`: where readers stop on reviews, section by section, and the 7 proposals (none decided).
3. Data for this brief: `content/review-format-2026-11-22/reviews-2026-11-22.csv` (one row per scored review: expert score, 10 sub-scores, best for, previous model, fit, ride descriptors, words per section). Method at the bottom.
4. Two live reviews, read as a reader: road `/reviews/road/nike-pegasus-42-review/`, trail `/reviews/trail/brooks-cascadia-19-review/` (trail uses different sections, see facts).

## Ruggero's ideas, in his words (22 Nov)
1. **Score and breakdown together:** "these are the ratings of the various parts, this is how each one compares with the average shoe in that category, the rating that would come out of the breakdowns is X, while the rating we gave is Y (a rating can differ for personal reasons)."
2. **Impressions vs stats:** "I would like to separate the runner's impressions from the stats... they all look like stats. I think it should be more personal."
3. **The review as the explanation of the ratings:** "what if the review was actually explanations to the rating breakdowns? So a review will talk about 'ride and cushioning' and there we will be talking about the sole unit, the toe-off, the transition... then the 'comfort, fit and sizing' where we will evaluate these things, then 'quality, durability and value for money'... so the rating breakdown will have a summary of these, then the review text will expand on those." He means **the whole review becomes that**, not extra sections: "I wasn't thinking of adding text sections about rating breakdowns, I was thinking of making the review into that."
4. **History:** "it would be a massive change to existing reviews and not really possible to compare them."
5. **Categories:** "we don't have these categories. I have been wanting to change the way our categories work for a while, but am afraid of messing up all the history." On "Best for": "not sure if it's good enough, and what to do when a shoe is best for more than one thing."

What he has already reacted to (draft pictures in that chat):
- A combined block placed where "Rating breakdown" is today (screen 4), with a jump from the score badge at the top: "not sure about placing it there so low and having people click the badge. Do they really? What do they expect to happen?" (Fact: taps on the badge are not tracked.)
- Each bar with a small mark for the category average and a ±number: "the 'vs average' is not clear in the chart. I don't get it, even after reading the description."

## Facts (live site, 22 Nov 2026)
**The page today (road template):** hero (title, photos, expert score badge "7/10", summary, trust line) → verdict → pros/cons → price → rating breakdown (10 bars) → facts → "Our tester's take" card (fit drawing, "Best for", ride descriptors) → who should / should not buy → text: Introduction, First impressions, Upper, Sole unit, Conclusions → compare table → price list → similar shoes. Reach per section: `ENGAGEMENT-READ-2026-11-21.md` table (ratings 73 % of phone readers, who should buy 41 %, sole unit 22 %, conclusions 19 %; the introduction loses a quarter of its readers).

**Reviews:** 367 scored reviews (303 road, 58 trail, 6 training) plus 62 old reviews of post type `review` with no scores. Published per year: 32 (2022), 145 (2023), 106 (2024), 76 (2025), 8 (2026). Testers: 20+ people; Brandon Law wrote 131, then Olivia Yoder 34, Kristin Lassen 27, Jeremy Yoder 23, TJ Jones 22, Larry Lam 21.

**Text sections are separate fields** (the template can reorder them without touching text). Median words, road: introduction 240, first impressions 139, upper 157, sole unit 295, conclusions 399, total 1,359. **Trail reviews already use aspect sections:** impressions 242, protection 116, durability 101, responsiveness/speed 122, comfort and fit 121, conclusions 383 (58 reviews). Training: one section per use (running, HIIT, weights, dance, gym).

**Expert score:** whole numbers in 366 of 367 (8: 125, 7: 107, 9: 84, 10: 23, 6: 20, 5: 7, one 7.5). Road mean 7.88.

**Sub-scores** (same 10 on all templates): comfort, build quality, upper, sole unit, landing, transition, toe-off, traction, durability, value/price. Road means 7.7–8.4, spread (sd) 1.1–1.6. 10/10 is common (e.g. value 48, comfort 46, durability 46 of 303).
- Landing = transition = toe-off in **158 of 303** road reviews; the four ride scores (sole unit, landing, transition, toe-off) are within 1 point in **250 of 303**; correlations between them 0.70–0.83.
- Correlation with the expert score: sole unit 0.74, landing 0.71, transition 0.70, toe-off 0.70, comfort 0.65, value 0.47, build 0.46, upper 0.41, durability 0.26, traction 0.19. The plain average of the 10 correlates 0.80.
- Expert score vs plain average of the 10 (road): less than 0.5 apart in 189, 0.5 or more apart in **114** (expert lower in 76, higher in 38), 1 point or more in 41. Trail: 25 of 58 are 0.5+ apart.
- Known contradiction: Triumph 23 gives comfort, transition and toe-off 10/10 (expert 8) while its text says it is not soft enough and has no energy return (ROADMAP "Small / waiting").

**Things that are described, not scored** (in "Our tester's take"): fit verdict (true to size 247 of 303 road, runs small 47, runs large 9), cushioning type (balanced 121, responsive/balanced 76, balanced/plush 52, responsive 30, plush 24), cushioning amount (highly 159, medium 130, little 14), stability, flexibility.

**Grouping fields that exist:**
- "Best for" (road): daily training 231 of 303, speedwork 108, racing 78, long distance racing 41, ultra 3; 116 road reviews have 2 or more tags, 4 have none. Trail "Best for" is a terrain list (13 options, typically 5–9 ticked per shoe). Training: empty.
- Shoe type taxonomy (`/reviews/all/<type>/`: cushioning, lightweight, racing, stability, motion control, minimalist, road, trail, training). As shown in the compare table's "Shoe type" row (280 road reviews have it): cushioning 151, racing 49, road 48, lightweight 31, minimalist 1, **stability 0** (to check in WordPress whether stability shoes carry that term).
- "Previous model" links to a review of the predecessor in 207 of 303 road reviews.

**Where scores are used besides the review:** comparison tables ("Expert score" row, sub-score wins), the `/reviews/` listings, schema (`reviewRating` /10, sub-scores as `additionalProperty`).

## Questions for you
1. **Text format (A):** should a new review be organised around what is rated (Ruggero's idea 3), around the reader's questions, around the test itself, or something else? Give one recommended outline with section names, order, the job of each section and rough length, and say where the tester's personal voice and testing context (distance, runs, paces) live.
2. **Scoring (B):** keep the 10 sub-scores, merge them, or change what is scored vs described? For each option: what it fixes, what it costs for the 367 existing reviews, comparisons and schema, and whether old and new can sit side by side.
3. **Score vs breakdown (idea 1):** is showing "the parts say X, we say Y" a good idea at all? If yes, how to show it so a reader understands it in seconds (Ruggero did not understand an average mark on a bar), and what to compare with ("average shoe in that category" needs categories RSG doesn't have cleanly; see "Best for" and shoe type above).
4. **Where it goes on the page** (top vs where "Rating breakdown" is now), given the reach numbers and that badge taps aren't tracked.
5. **Categories:** is a new single "type" per shoe needed for any of this? If yes, how to add it without touching URLs, listings and history.
6. **Outside view:** how do the strongest running-shoe review sites structure a review and its scores today (for example RunRepeat, Doctors of Running, Believe in the Run, Road Trail Run, Solereview, Running Warehouse's own reviews)? What is common, what is distinctive, what would RSG copy or avoid? Check them live, don't rely on memory.
7. For each recommendation: which of the three goals it serves, which number should move, how it can be measured (section tracking is live; baseline 1–7 Dec).

## Out of scope
Building anything, the theme repo, staging, live. Rewriting old reviews (say what should happen to them, don't do it).

## At the end of that chat
Write the answer to `content/REVIEW-FORMAT-<date>.md`, Ruggero's decisions one line each in `DECISIONS.md`, `ROADMAP.md`, then the `rsg-close-out` skill.

## Method (data)
Fetched the 429 review URLs in the WordPress sitemaps (`reviews_road`, `reviews_trail`, `reviews_training`, `review`) from the live site on 22 Nov 2026, one page at a time with pauses (the edge rate-limits faster crawling: Cloudflare error 1015). Parsed from the page HTML: expert score badge, the 10 `.rsg-rate` values, "Best for" items shown as ticked, "Previous model" (linked = reviewed), fit verdict, ride descriptors, `div.review-section` word counts, publish date and author from the trust line. Shoe type from the compare table's "Shoe type" row, middle column (this shoe). The 62 old `review` pages have none of these fields and are not in the CSV. Scripts were throwaway (not kept).
