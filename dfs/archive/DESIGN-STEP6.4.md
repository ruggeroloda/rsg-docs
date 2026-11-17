# DFS 6.4 — click beacon + `/go/` stub: design (8 Nov 2026)

**Decided 8 Nov ~22:05 (Ruggero: "choose on simplicity, elegance, understandability, maintainability, upgradeability"):** (1) B, no id map; (2) gender recorded; (3) clicks read with a query on request (command in the mu-plugin header); a Prices-screen view later if the data is worth it.
**Changed while building A:** validation no longer checks the shop against the shoe's *current* data (a shop dropped by the latest push would reject a real click from a page cached earlier); it checks that the shoe is a DFS shoe and that the shop name is a plain string of ≤ 64 chars. Box name = 2–8 lowercase letters (no fixed list to keep in sync). Server HTML now carries `data-cc="US"` so server HTML = JS repaint still holds.

## Measured first: live `/go/` traffic, 31 Oct 12:14 – 8 Nov 20:58 UTC (8.4 days, Rocket.net access log)
- 70,443 `/go/` hits. 53,340 = 301 to home (no RSG referer): Googlebot 19.7k; "t.co" 8.0k = 4 IPs in 185.25.35.64/30 (scraper with a fake referer); "google.com" 2.7k = 2,607 IPs, one identical Chrome/152 UA (proxy scraper). **No human traffic from outside the site found.**
- 13,584 = 302 forwarded to the old DFS (RSG referer), but 6,474 of those claim the **home page** as referer, which shows no prices: fakes. So the old click count is mostly bots.
- 86% of `/go/` URLs carry the page they came from in their own `referer=` parameter.

## A. Click beacon (staging, behind the existing `?dfs=1` switch)
- **JS** (`rsg-dfs.js`, ~15 lines): on `click` / middle-click of any offer link (`a[data-m]` inside `[data-dfs-shoe]`), `navigator.sendBeacon('/wp-json/rsg/v1/click', …)` with page, box, shoe, merchant, country actually shown, gender. `paint()` stamps `data-cc` on each box so the country is the painted one (US before/without the swap). The link itself is untouched (direct affiliate link, new tab).
- **Endpoint + table**: new mu-plugin `rsg-dfs-clicks.php` (theme-independent, survives the theme rewrite). Table `{prefix}rsg_clicks`: id, ts (UTC), page_id, box, shoe_id, merchant, country, gender. **No cookie, IP, user agent.**
- **Validation (rejects junk without IPs):** Origin/Referer must be the site; box from the fixed list; country + merchant must exist in that shoe's `_rsg_prices` data; otherwise 400, nothing written. Returns 204.
- Bots that only fetch links never fire a beacon, so this counts people, unlike `/go/`.
- Box off: no change (script only loads with the box; the mu-plugin only adds a route).

## B. `/go/` stub (built + tested on staging now; live only when the old DFS is switched off)
- Replace the id→shoe map (Linode export) with: **301 to the page named in the link's own `referer=`** (host must be runningshoesguru.com, path only), **else home**. Same noindex/no-store headers.
- Why: nobody outside the site uses `/go/`; an in-site click from a stale page lands back on that page with live prices; no Linode export; and bots are never sent to merchants (a map to affiliate links would hand the networks thousands of bot clicks a day).
- Live timing: live `/go/` must keep forwarding to the old DFS while any live page (and GPS) uses it, so the stub goes live with the switch-off, not before.

## Open questions
1. Approve B (no id map) instead of the 6 Nov plan?
2. Gender in the click row (not in the 6 Nov list): ok?
3. Reading the clicks: WP-CLI query on request for now, or a daily pull into DFS + counts on the Prices screen (extra small step)?

## Checks (both parts)
lint 8.3 + 7.4, `node --check`; curl tests (valid → 204 + row; each invalid field → 400, no row); Playwright clicks (row, best button, middle-click, Amazon, late DE swap → country DE) with merchant navigation blocked; `chk.js` + `val.js` regression, `tagdiff.py` box off, `sweep.py` subset; `/go/` stub with 20 real log URLs (+ `referer=https://evil.com`). Backups + DEPLOY-LOG entries; code to GitHub.
