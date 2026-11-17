# Brief — DFS build step 6: WordPress receiver + theme on staging (for a new chat)

_Written 2026-11-07 at the end of build step 5. Read `HANDOVER-STEP5.md` first (contract, facts, open items)._

## Start the new chat with this
> RSG project, DFS workstream: build step 6 (WordPress receiver + theme on staging). Read README.md, dfs/README.md, dfs/HANDOVER-STEP5.md ("Start here"), dfs/BRIEF-STEP6.md, DECISIONS.md (DFS lines of 6-7 Nov: price delivery, push, links, freshness, history, country fallback) , CONTEXT.md (WordPress/Rocket.net, theme, DFS build/test container) and theme/CLAUDE.md. Build step 6 as described in the brief, on STAGING only. One step at a time, show me the result of each, wait for my input between steps. Keep the DFS scheduler OFF while testing (use "Run now").

> **Status 8 Nov 22:40: items 1–5 DONE on staging (5 = logos, done 7–8 Nov during 6.3). Item 6: clicks done 8 Nov (URL level); report checks moved to after go-live. **Step 6 complete on staging.** Facts: `HANDOVER-STEP6.md`.**

## Goal
Get the JSON from `price_json` into WordPress staging and render the price box from it in the theme, without touching the live site. Order (one sub-step at a time):
1. **Receiver**: a small mu-plugin/REST route on staging: accepts one shoe's JSON, validates it against the contract (same rules as `prices.validate`), `update_post_meta` on `_rsg_prices` only (never `wp_update_post`), application password for a dedicated low-privilege user. Test with a real shoe.
2. **Push job in DFS**: push rows of `price_json` whose `hash` changed since the last push (table to track it), one purge per batch (decision 6 Nov: Rocket.net `CDN_Clear_Cache_Api`), failure email, "Run now" + Prices screen status. Staging URL/credentials only in the server `.env`.
3. **Theme adapter**: embed the JSON in the page, render the US block in HTML (links with `{s}` filled, `rel="sponsored nofollow"`), derive the 14 legacy fields the templates read (see `DFS-Consumer-Contract-Report.md`), "no offers" state, Amazon price hidden if older than 24 h with an "as of" stamp, CLS-safe country swap script (Cloudflare `/cdn-cgi/trace`, fallback US).
4. **Click beacon** into a WordPress table (page, box, shoe, merchant, country; no cookie, no IP) and the legacy `/go/` stub (needs the id→shoe map exported from the old Linode first; read-only, ask before connecting).
5. **Logos**: host the normalised logos in `dfs/logos/normalized/` (theme assets on staging) and set their URLs on the Prices screen.
6. Verify one real click per network (CJ `sid`, Rakuten `u1`, AWIN `clickref`) shows the sub-ID in the network's report/URL.

## Rules
- Staging only; no change on the live site, no scheduler switch-on without Ruggero. Theme rules: `theme/CLAUDE.md` (staging first, backup, deploy log).
- No secrets in docs/git. Docs at the end: HANDOVER (new "Start here"), DECISIONS lines, CONTEXT facts, README status; code committed to GitHub `ruggeroloda/rsg-dfs`.
- After this: step 7 (production Linode + switch-over), step 8 (more feeds = configuration).
