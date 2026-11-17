# Brief — DFS build step 5: price data + history (for a new chat)

_Written 2026-11-07 at the end of build step 3. Read `HANDOVER-STEP3.md` first (facts, gotchas, open items)._

## Start the new chat with this
> RSG project, DFS workstream: build step 5 (price data + history). Read README.md, dfs/README.md, dfs/HANDOVER-STEP3.md ("Start here"), dfs/BRIEF-STEP5.md, DECISIONS.md (DFS lines of 6–7 Nov, especially price delivery, push, links, history, freshness) and CONTEXT.md (DFS build/test container; WordPress shoe data). Build step 5 as described in the brief. One step at a time, show me the screens as they are built, wait for my input between steps. Keep the scheduler OFF while testing (use "Run now").

## Goal
Turn the offers the matcher and Amazon already produce into **the price data WordPress will receive** (one JSON per shoe) and a **daily price history**, all inside DFS on Hetzner. Nothing is sent to WordPress in this step (that is step 6); the output is visible on a new screen and testable.

## What exists (so don't rebuild it)
- View `offers` (day, post_id, gender, country, merchant, price, list_price, list_src, name, url, image, …): each feed's latest run, file feeds and Amazon (`amazon_us`, merchant "Amazon"). 317 shoes / 817 offer lines on 7 Nov.
- The Shoes screen price box (`/api/box/<post_id>` in `dfs/web.py`): US/DE switch, men/women, up to 4 shops cheapest first, list price struck through, "from" price, "Updated" = oldest price time among the shown offers. **Reuse its logic as the starting point of the JSON.**
- `feed_runs`, `amazon_pool` (price time per ASIN), `shoes` (+ `rrp_json`: rrp_us, rrp_gb, rrp_de…), feed config `subid` (param + value pattern such as `{page}-{box}-{country}`), already stored but **not yet applied to any link**.

## Build, in this order (one sub-step at a time)
1. **The JSON contract** (decide with Ruggero, show an example for 3 real shoes before coding more). Per shoe: `as_of`, then per country (US, DE) per gender (men, women): up to N offers cheapest first, each with merchant, price, list price (and where it came from), currency, deep link (affiliate link with the sub-ID applied, Amazon link untouched), image, offer time, deal flags. Must satisfy what the theme reads today: the 14 fields in `DFS-Consumer-Contract-Report.md` (MYKEY, NAME, MERCHANT_NAME, MERCHANT_LOGO, DISPLAY_PRICE, RRP_PRICE, DEEP_LINK, IMAGE_URL, COUNTRY_NAME, CURRENCY_PREFIX, CURRENCY_CODE, CATEGORY Male/Female, …), sorted price-ascending, `[0]` = best. Single quotes in values break the current theme parser, so avoid them or escape. Size per shoe should stay small (it is embedded in the page); guides and comparisons embed only the shoes they show.
2. **Link sub-IDs**: apply each feed's sub-ID (CJ `sid`, Rakuten `u1`, AWIN `clickref`, RW `from`) to the deep links. Page, box and country are only known in the page, so the JSON carries a link template/placeholder the script fills (or decide otherwise). Verify against each network's rules; Amazon links stay untouched (Creators API terms, one tag per marketplace).
3. **Freshness rules**: Amazon prices only with a price check in the last 24 h (already in the offers); "as of" on every offer; JSON rebuilt after each file import and after each Amazon re-price (so ~4 times a day for Amazon shoes). Define what happens when a feed is missing or refused (offers from the previous accepted file stay, with their older time).
4. **Daily history table**: one row per shoe × gender × country × merchant per day: price (exact), list price, currency, thumbnail and deep link of that day's cheapest offer. ~5k rows/day, keep 2 years. Written once a day (decide the hour; the last build of the day wins).
5. **Deal signals** (DECISIONS 6 Nov): (1) ≥20% below list price (feed list price, else WP `rrp_*`); (2) lowest in 90 days and ≥10% below the 30-day median. Revisit later; mind that DE has no list price today (open question, local-RRP worker) so signal 1 is US-only for now.
6. **A "Prices" screen** next to Shoes and Feeds: pick a shoe, see the JSON as WordPress will get it, the offers behind it, its history chart, the deal flags; plus a list view (shoes without offers in a country, stale prices, deals today).
7. **Scheduling**: add the "build price data" job to the scheduler (after each import / re-price, history once a day), visible on the Feeds/Prices screen. Keep the global switch semantics from step 3.5.

## Acceptance
- JSON for every shoe with offers, validated against the contract; example for 10 real shoes checked by hand against the Shoes screen price box (same cheapest prices).
- History rows written for a test day and a second test day (cheaper price → deal flag appears).
- No change on the live site, no push to WordPress, no scheduler switch-on without Ruggero.
- Docs: HANDOVER (new "Start here"), DECISIONS lines, CONTEXT facts, README status updated; code committed to GitHub `ruggeroloda/rsg-dfs`.

## Watch out
- Memory: 1 GB container shared with one running job; stream, don't load whole tables of rows when they can be aggregated in SQL.
- Don't leave the scheduler ON while testing: it starts any slot due after the switch.
- Open questions to settle with Ruggero in this step: how many offers per country/gender go into the JSON (4 like the box?), which countries are in at launch (US + DE; GB/CA/AU later), how the US-only Amazon prices show for non-US visitors.
- After this: step 6 (WordPress receiver + theme on staging), step 7 (production Linode + switch-over), step 8 (other feeds, mostly configuration).
