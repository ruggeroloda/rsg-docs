# Brief for a new chat: show Ruggero the page proposals (21 Nov 2026)

_Written 21 Nov 2026 by the chat that did the reading (`ENGAGEMENT-READ-2026-11-21.md`). That chat is building the section tracking in the theme repo at the same time, so **this chat does not touch the theme repo, staging or live**: it explains and shows, on mock-ups only._

## The job
Ruggero wants the proposals for reviews, comparisons and `/top/` list posts **explained and shown**, so he can say yes, no or "different" to each. Output = pictures of real RSG pages before and after, phone next to desktop, plus a short explanation with the numbers. Nothing is built for real here.

## Read first
1. `README.md`, `ROADMAP.md` (as every session).
2. `content/ENGAGEMENT-READ-2026-11-21.md`: the findings, the numbers and the 7 proposals. This is the source; don't re-derive it.
3. `content/CONTENT-REVIEW-2026-11-20.md`, work list item 1 (the parked "next-step block") and the two renders `content/content-review-2026-11-20/next-step-v3-who.png`, `next-step-v3-list.png`: what Ruggero already saw and turned down.
4. `theme/CLAUDE.md` and `theme/archive/RESKIN-DESIGN-DOC.md` for the look (fonts, colours, components), so mock-ups look like the live site.

## What Ruggero already said (don't repeat these mistakes)
- Next-step block, first idea (chip strip + end block): "not convinced by looks or position". Second idea, v2: "too big, breaks the page balance"; v3 compact: "still not ready".
- So: small, in the page's own style, reusing blocks that exist. No new widget look, no chips, no price alert.
- He decides one thing at a time. Long lists: a short summary first, then step by step, waiting for his answer.
- Blunt trade-offs, no reassurance. If a proposal has a cost (ads per view, page length, work per page, who writes the text), say it.
- Page designs: **always phone next to desktop.** Price spots by the name he sees ("sidebar price card", "phone price block"), never by code.

## What to show, in this order (one at a time)
For each: the finding in two lines with its numbers, a before/after picture of a real page (phone + desktop), what it costs, how it will be measured (the section tracking: reach per section, seconds to reach it, onward clicks per block; baseline 1–7 Dec).

1. **Comparisons: the answer at the top.** "Which one to buy" directly under the table; verdict / "Buy if" box on a page that has none. Show on `/comparison/asics-nimbus-28-vs-hoka-bondi-9/` (no box today) next to `/comparison/brooks-ghost-vs-brooks-adrenaline/` (has the box). Numbers: "Which one to buy" reached by 33 % phone / 27 % desktop; the table by ~9 in 10.
2. **Reviews: the order.** "Who should / should not buy" up under pros/cons; in the text, sole unit first, then upper, then conclusions; the introduction cut to the 2–3 sentences about this model. Show on `/reviews/road/nike-pegasus-42-review/`. Numbers: ratings → who should buy 73 % → 41 % (phone); introduction loses a quarter of its readers; sole unit reached by 22 %, conclusions 19 %.
3. **Reviews: "Compare it with".** The shoe's existing comparisons, predecessor first, right after "Who should / should not buy", plus links in the text where a rival with a comparison is named. Show on `/reviews/road/asics-gel-nimbus-28-review/` (it has at least five comparisons: vs Nimbus 27, Bondi 9, Glycerin 23, Cumulus, Kayano). Must be visibly smaller and quieter than the rejected v2/v3. Numbers: no review links to a comparison in its body; compare table seen on 11.5–14 % of views; comparisons convert 2.3× reviews.
4. **A finding under every heading + jumps.** Heading line such as "Upper: the best Pegasus upper yet; very narrow, go half a size up"; jumps from a rating line, a fit result, a con to the paragraph that explains it; 3–4 reader questions after "Who should buy". Show on the Pegasus 42 review. Numbers: readers reach the conclusions in a median 87 s (scanning); the existing jump line on comparisons got 0 of 81 recorded taps.
5. **Comparisons: related, not latest.** "More comparisons with the Nimbus 28 / Bondi 9" under "Which one to buy" instead of the six newest at the bottom (reached by 12–14 %).
6. **`/top/` list posts:** no proposal yet (no reading done). If Ruggero wants it, read 3–4 list posts the same way first; the tracking will give the baseline.

Not in scope here: old-template reviews (Ruggero: leave alone for now), ads, the homepage, the tracking build.

## How to make the pictures
- Take the live page, change it in a headless browser (move, add or rewrite blocks in the page itself), screenshot at 390 px and 1366 px, put before and after side by side. The live page is only read, never changed. Block the tracker (`t.runningshoesguru.com`) and ads, and answer the price beacons locally, as `theme/checks/shots.py` does.
- Scripts from the reading (Mac only, not in git): `content/_work/engagement-2026-11-21/survey.py` (full-page shots with Raptive's ad slots in place, section positions), `analyze.py`, `urls.txt`. Edge rate limit: about one page at a time, short pauses.
- Texts in mock-ups (verdict box, heading findings, reader questions) come from the page's own review text, marked as examples. Who writes them for real (tester, Ruggero, generated then read by a person) is a question to put to Ruggero, not to decide.
- Save the pictures in `content/proposals-2026-11-21/` (in git, like the 20 Nov renders) and show them in the chat.

## At the end of that chat
Ruggero's answers per proposal → a "Ruggero's answers" section at the bottom of `content/ENGAGEMENT-READ-2026-11-21.md`, one line each in `DECISIONS.md`, `ROADMAP.md`, then the `rsg-close-out` skill. Nothing accepted is built before the baseline is read on 8 Dec.
