# Handover — DFS build step 5 done (7 Nov 2026)

## Start here
**Build step 5 is done** (price data JSON, link sub-IDs, freshness rules, daily history, deal signals, Prices screen, scheduling). Code on GitHub `ruggeroloda/rsg-dfs` (main). Nothing is sent to WordPress; the live site is untouched. **The scheduler is still OFF** (Ruggero turns it on on the Feeds screen; recommended: evening, check next morning).
**Next: build step 6 — WordPress receiver + theme on staging**: REST route that accepts one shoe's JSON (valid price JSON only, `update_post_meta` on `_rsg_prices` only, application password for a low-privilege user), push job (rows of `price_json` whose `hash` changed since the last push, one purge per batch, alert email on failure), theme adapter (embed JSON, render US in HTML, script swaps the visitor's country, fills `{s}`, hides Amazon prices > 24 h, click beacon, "no offers" state), sets of the 14 legacy fields derived from the JSON. Then step 7 (production Linode + switch-over), step 8 (more feeds = configuration).

## The contract (v1) — details in the docstring of `app/dfs/prices.py`
One compact JSON per active shoe (429 rows in table `price_json`, avg 0.7 KB, max 3.2 KB, 297 KB in total):
`{"v":1,"id":post_id,"as_of":"…Z"|null,"logos":{merchant:url},"c":{"US":{"cur","sym","name","rrp","men":[offer…],"women":[…]},"DE":{…}}}`
offer = `m` merchant, `p` price, `lp`/`ls` list price + source (`rrp` WordPress / `feed`), `u` link, `img` thumbnail, `t` time of that price, `d` deal flags (`list`, `low`).
- Up to 4 offers per country and gender, one per merchant, cheapest first, ties alphabetical; `[0]` = best.
- A country block exists when an enabled feed serves that country (US, DE today). `"men":[]` = **no offers: the page shows "no offers" for that country**. A country with no feed at all is absent: the page falls back to the US block.
- `{s}` in a link = where the page puts `page-box-country` (letters, digits, `_`, `-`, max 45 chars; `prices.fill()` is the reference and refuses anything else). CJ `sid`, Rakuten `u1`, AWIN `clickref` get `{s}`; Running Warehouse `from=guru` is fixed in the URL; Amazon links are untouched (tag in the URL).
- Embedding: `prices.dumps()` output has no single quotes, no `</`, no `<!--` (safe inside `<script>`).
- Not in the JSON: DESCRIPTION, MYKEY, USERFIELD1 (the theme adapter in step 6 derives the 14 legacy fields; DESCRIPTION was meant as a merchant description and was never used).

## What was built (all in `app/dfs/`)
| Sub-step | What | Where |
|---|---|---|
| 5.1 | JSON contract, builder, validator, tables `price_json`, `price_runs`, `merchant_logos` | `prices.py` |
| 5.2 | Sub-IDs from the feed config: `with_subid()`, `fill()` | `prices.py` |
| 5.3 | Freshness: Amazon only if priced < 24 h ago (from `amazon_pool.last_priced`); file feeds until their last accepted import is > 72 h old; a missing/refused file keeps the old offers with their old time; JSON rebuilt after **every** feed job (import, search, re-price, also after a refused or failed download) | `prices.collect()`, `jobs.py` |
| 5.4 | Table `price_history` (day × shoe × gender × country × merchant: exact price, list price, currency, thumbnail, link), 2 years kept, rewritten per day (last build wins) | `prices.snapshot()` |
| 5.5 | Deal flags: `list` = ≥ 20 % below the shown list price (RRP first, else the shop's); `low` = best price of shoe/gender/country is the lowest of 90 days and ≥ 10 % under the 30-day median, evaluated on the best offer only and only with ≥ 14 days of history | `prices.flags()`, `history_stats()` |
| 5.6 | Prices screen `/prices` (overview, filters, run buttons, merchant logos) and `/prices/<post_id>` (JSON, offers, history charts) | `prices_web.py`, `templates/prices.html`, `price_shoe.html` |
| 5.7 | Scheduler: build is chained after each feed job; **history daily 22:00 UTC** as a system slot; "Run now" for both on the Prices screen; failures email like feed failures | `scheduler.py` (`SYSTEM`), `jobs.py` |

## Checks (all passed 7 Nov)
- `docker exec -i dfs python - < app/tools/check_prices.py` → 884 checks, 0 failed (sub-ID rules, JSON validity of every shoe, history written twice = no duplicates, "low" appears when history is higher and not when it was cheaper; test days are removed again).
- Against the Shoes screen price box for all 429 shoes × 2 countries × 2 genders (1,716 lists): the same offers and the same prices in every list; 12 differ only in the order of equal prices (the JSON puts ties alphabetically, the box did not).
- Run now → build, history and (once) an Amazon re-price all went through the scheduler thread with the switch OFF. The one allowed Amazon re-price (390 calls, 594 s) ended with the chained build: 313 shoes' JSON changed, `price_runs` shows it; no Amazon search was run.

## Facts and gotchas
- Code is baked into the image: after a change `rsync -a --exclude .git --exclude __pycache__ app/ ruggero@116.203.146.177:/home/ruggero/dfs/app/` (device shell, key copied to `$HOME/k/`, `chmod 600`) then `cd /home/ruggero/dfs/app/docker && docker compose up -d --build`. Scripts can be piped in with `docker exec -i dfs python -`.
- Build/history runs are logged in `price_runs` (not `feed_runs`: that table is tied to feeds). Chained builds log there too.
- `as_of` = oldest price time among the shown offers; the JSON therefore changes only when data changes (hash stable between builds). Step 6 pushes rows whose `hash` differs from the last pushed hash.
- Real history started on 7 Nov (816 rows). The `low` flag only appears after 14 days; the "Deals today" filter shows `list` flags until then.
- DE has no list price today (no `rrp_de`), so `list` flags are US-only. Nike DE has no struck-through price.
- Merchant logos (8 Nov update): old files pulled from the old Linode (`/var/www/html/logos/`, read-only) and normalised to trimmed transparent PNGs ≤ 240×80 in `dfs/logos/normalized/` (amazon, zappos, nike, running-warehouse; Nike DE reuses nike). Rule: one logo per merchant, shown in a 110×32 slot, centred, scaled to fit. Still to do: host them publicly (proposal: theme assets, on staging first, in step 6) and enter the URLs on the Prices screen. `dfs/logos/_` has an empty old Zappos file and sheet previews (cleanup).
- (older note) Merchant logos: none configured yet (table `merchant_logos`, one URL per merchant on the Prices screen). The old DFS keeps them in its `datafeeds.logo` column (read-only SELECT on the old Linode needs Ruggero's OK); the theme resizes them through `rsg_image_resize_ext`.

## Open items
- **Ruggero:** logos (see above); still open from step 3: rotate the Linode root password and the MailerSend token.
- Per-merchant search link for "country has a feed but no offer on this shoe" was deferred (needs a search-URL template per feed and per-network deep-link rules); the page shows a plain "no offers" for now.
- The deal-signal list price: the brief said "feed list price, else RRP"; the offer filter and the JSON use RRP first. Step 5 uses RRP first (what the visitor sees struck through). Easy to flip in `prices.collect()`.
- The 7 Nov 04:58 UTC failure email (`invalid.example.invalid`) was a test from the step 3 session, not a real failure.
- Also rotate the old DFS MySQL root password (printed into session output 7 Nov).
- Cleanup left for Ruggero or a session with deletion allowed: folders `dfs/_shots/` and `dfs/_work/step5-shots/`, `.git/stale-*` lock files, rollback copies listed in HANDOVER-STEP2 after ~20 Nov.
