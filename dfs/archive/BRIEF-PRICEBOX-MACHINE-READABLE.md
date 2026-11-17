# Brief — make the price box exemplary for machines (search engines, AI engines, assistive tech)

_Written 8 Nov 2026. Own chat. Comes **before** the box goes onto more templates (DFS 6.3 rest), so every template gets the final version._

> **DONE 8 Nov 2026** (staging, approved by Ruggero). Result and as-built spec: `PRICEBOX-MACHINE-READABLE-RECOMMENDATION.md`; log: `DEPLOY-LOG-STEP6.md`; next: DFS 6.3 rest (`HANDOVER-STEP6.md`).

## Start the new chat with this
> RSG project: price box machine-readability (dedicated chat). Read README.md, dfs/BRIEF-PRICEBOX-MACHINE-READABLE.md (this brief), dfs/app/wordpress/theme/README.md and the box code (dfs/app/wordpress/theme/includes/rsg-dfs-prices.php, js/rsg-dfs.js, css/rsg-dfs.css), dfs/HANDOVER-STEP5.md (data contract), DECISIONS.md (7–8 Nov price box lines) and theme/CLAUDE.md. Phase 1 is discovery only: research, audit and a written recommendation with trade-offs. No code until I approve. Then build on STAGING (review 48721, `?dfs=1`), validate, show me. One step at a time, wait for my input.

## Goal
The box (and its schema) should be as good as it gets for machines, without changing how it looks unless agreed:
- **Search engines:** unambiguous offers, valid structured data that matches the visible page, eligible for whatever price/review features Google shows a review site.
- **AI engines** (Google AI Overviews / AI Mode, ChatGPT search, Perplexity, Claude…): can extract "who sells it, for how much, as of when, how much below list" correctly and cite RSG.
- **Assistive tech / accessibility:** clear link purpose, prices and savings read out sensibly (WCAG 2.2 AA).

## Where it stands (8 Nov, staging)
- Server renders US offers (men + women) in the HTML; script swaps country; rows are `<a class="rsg-dfs-row">` with loose spans (`from`, `<b>` price, `<s>` list price, `-41%` tag, Amazon "as of" text); logos are `<img alt="Nike">`; links `rel="sponsored nofollow noopener"`; every link reads "See deal".
- Schema: one `AggregateOffer` (USD low/high/offerCount, url) on the review's `Product` (`rsg_prices_schema_offer()`), US only, Amazon > 24 h excluded.
- Dates/prices are plain text (no `<time>` / `<data>`); embedded data is a custom JSON (not a standard).
- First assessment (8 Nov): good for people, average for machines. Gaps: (1) no semantic structure (list/table) for comparison data; (2) numbers and dates not machine-readable; (3) schema stops at the range, no per-seller offers; (4) generic link text; (5) "-41%" as a symbol, not words.

## Phase 1 — discovery (deliverable: a short written recommendation)
1. **Rules and guidance (primary sources, current versions):** Google Search Central on Product snippets vs merchant listings (what a non-seller review site may mark up), `AggregateOffer` with nested `Offer`s, offer `url` for affiliate links, `priceValidUntil`/availability, review + product together; Google "AI features and your website"; schema.org `Offer`/`AggregateOffer`/`seller`; WCAG link purpose, `<data>`/`<time>` semantics, tables vs lists for comparison data.
2. **What the best do:** inspect the HTML + JSON-LD of strong review/comparison sites (e.g. RunRepeat, Wirecutter, RTINGS, Believe in the Run, Running Warehouse product pages): what they mark up, how, and what Google shows for them.
3. **What machines actually extract today:** ask a few AI engines and run Google's tools on the staging page (Rich Results Test in code mode, Schema Markup Validator, W3C validator, axe/Lighthouse accessibility); note what is misread or missing.
4. **Options with trade-offs:** e.g. list vs table; per-offer schema (which fields, US only vs per country, women's offers?); visible wording ("41% off list price", "Prices checked 8 Nov, 08:05 UTC"); link labels; risks (schema/visible mismatch, spam policies, CLS, payload size, keeping PHP/JS mirror simple).
5. **Recommendation** + test plan → Ruggero approves.

## Phase 2 — build (after approval)
In the one box component (PHP + JS mirror), staging only, review 48721. Must stay: zero layout shift, no text change on repaint, small HTML, same look unless agreed. Validate with the tools above, show before/after, deploy log, DECISIONS line, commit to `ruggeroloda/rsg-dfs`.

## After this
DFS 6.3 rest (box on the other templates): prompt in `HANDOVER-STEP6.md`.
