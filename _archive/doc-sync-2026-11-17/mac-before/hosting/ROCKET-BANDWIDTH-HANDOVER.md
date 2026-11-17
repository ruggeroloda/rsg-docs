# Handover — Rocket.net bandwidth, plan and geo rule (tickets 121944, 122395) — updated 11 Nov 2026

**Now (11 Nov):** waiting for Rocket.net's answer to Ruggero's 11 Nov mail (ticket 122395) asking to disable the runningshoesguru.com part of the geo-caching rule. Then: multi-country cache check (`../dfs/GO-LIVE-2026-11-10.md` item 5), then the separate mail confirming **Business yearly + 15 GB ($113/mo)**. Sections below are the history, oldest first.

## Goal (9 Nov; plan path superseded 10 Nov, see "Now" at the top)
Pay for real traffic only: custom Expert (~$370/mo) → standard Expert ($200) → Business ($100) once bandwidth is well under 300 GB/mo **and** storage under 40 GB (storage today 53/50 GB, half of it the staging copy — separate topic, same plan decision).

## Facts
- Last 30 days (Rocket.net, 5 Nov): **366 GB, 14.1M requests**. Real visitors ≈ 160k page views → ~110–150 GB. The rest: bots, scrapers, hotlinking, the site calling itself.
- Rocket.net's top lists (5 Nov, **by requests, not bytes**): IPs 34.232.137.97 + 184.72.84.161 (AWS, ~418k requests), 65.181.124.237 (our own Rocket.net server — WP Rocket), Googlebot; paths `/`, `wp-rocket/.../heartbeat.js` (#2), `/go/index.php`; ASNs Amazon 14618/16509, Google, Facebook, Microsoft, Datacamp 60068; UAs incl. Bytespider, Baiduspider, Yahoo! Slurp, Linode Managed (old DFS monitor, goes away when the old DFS is retired ~end Nov).
- Done: robots.txt bot blocks (6 Nov). Site changes 9 Nov that lower human bandwidth: lazy loading back on (review pages 55–85 % fewer bytes on load; estimate −25–60 GB/mo), dead Amazon widget + Freestar leftovers removed. Week-on-week effect visible from ~16 Nov.
- **9 Nov: Ruggero replied (short, on purpose):** block IPs 34.232.137.97 + 184.72.84.161 and UAs containing Bytespider / SeekportBot / Baiduspider; send back the rules as applied; future requests possible — Rocket.net may propose rules but **must propose before applying**; noted the email was AI-assisted. **Rocket.net's answer to this arrived 9 Nov evening** (Gmail thread "[Rocket.net] Re: excessive use of bandwidth").

## Rules for this conversation (Ruggero)
- **Few, simple, high-impact asks at a time** (2–3), not long lists.
- Never block or challenge: Googlebot/-Image, AdsBot, Mediapartners, bingbot, AI search crawlers (GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot), ad crawlers (AmazonAdBot, proximic, CriteoBot, meta) — Raptive ad revenue.
- Must keep working: **`/wp-json/*`** (the new DFS pushes prices from **135.181.110.157**, Hetzner ASN 24940, from Phase C; Make → WordPress via the integromat connector runs on AWS), `/ads.txt`, `/robots.txt`, sitemaps. Our servers: 135.181.110.157 (DFS), 116.203.146.177 (rsgmedia/n8n).
- Trust but verify: get the exact rule expressions back and read them; test from outside (blocked UA → refused, normal browser + Ruggero's home connection + Googlebot → page); compare week before/after: Rocket.net bandwidth, GA4 users, Raptive page views/revenue, Search Console crawl stats.
- Broad rules (whole data-centre networks) only as **managed challenge**, first in **log-only** mode (Cloudflare Enterprise), never as hard blocks.

## Parked ideas (ask one at a time, only if still needed)
1. Same top lists **ranked by bytes**.
2. Hotlink protection for `/wp-content/uploads/` (block other sites' referrers; allow empty + search engines).
3. Our own server as #4 requester + heartbeat.js #2: is WP Rocket preload needed on Rocket.net?
4. Data-centre ASN challenge (log-only first).
5. Plan change to standard Expert once bandwidth is clearly under 500 GB.

Background: `hosting/README.md`, `CONTEXT.md` (Rocket.net section).

## Update 9 Nov evening — Rocket.net reply (Andre Correa, follow-up ticket **122395**)
- Says rules added "as proposed". **Did not send the rule expressions back** (asked for).
- Advises waiting a few days to measure before more actions.
- **Bandwidth overage = $0.08/GB** (100 GB = $8); says overages caused by crawlers/malicious traffic are not charged.
- Verified 9 Nov from Ruggero's connection: Bytespider / SeekportBot / Baiduspider UA → 403; Chrome + Googlebot UA → 200; `/wp-json/` → 200; `/ads.txt` → 301 to Raptive (adthrive) → 200. IP blocks not testable from outside.
- Implication: bandwidth is cheap at overage rates, so the money is in the plan (custom Expert ~$370), not in bot-hunting. Storage (53/50 GB, ~half staging) is the real constraint for downgrading. Parked bot ideas drop in priority.

## 9 Nov 22:10 — Ruggero replied on ticket 122395 (plan change)
- Context given: traffic ~10% of what it was; staying on Rocket.net, need a smaller plan.
- Asked to move to **Business ($100/mo: 40 GB storage, 300 GB bandwidth)** and to a normal setup without the custom cache configuration.
- Asked about storage: site ≈26 GB, want regular staging for development, so a full copy (≈52 GB) is over 40 GB (and over Expert's 50 GB). Asked whether extra storage can be added to Business and at what price, and whether staging can skip `wp-content/uploads`.
- Asked whether the switch is in place or needs a migration, and for a copy of the bot-block rules.
- Next: read their answer; Business vs Expert decision depends on storage options.

## 10 Nov 2026 — plan change settled (Mehedi Hassan, ticket 122395)
- Extra storage $2/GB/mo. Staging can't exclude `wp-content/uploads` (always a full copy).
- **Business yearly $83/mo + 15 GB $30/mo = $113/mo ($1,356/yr)**, in place, no migration, no downtime.
- **Geo-caching rule** (Ben Gabler, Jan 2024, ticket 36910) is tied to the hostname: the downgrade does not touch it. Removable in place on request, no plan restriction, **can be re-added later**. Expression: `(http.host contains "runningshoesguru.com" and not http.request.uri contains ".php" and not http.request.uri.path contains "wp-admin") or (http.host contains "lebeddie.cz")`, setting "Eligible for cache". The country part of the cache key was not shown. The rule also covers another customer's host (lebeddie.cz): when asking for removal, ask to remove only the runningshoesguru.com part.
- Earlier note "can't be removed / can't be re-enabled" was wrong (also ticket 95578, Jan 2026: Marcela offered to disable it).
- Next: Ruggero confirms the downgrade; ask to disable the geo rule only after DFS step E + GPS links live (old price boxes and GPS depend on it).

## 11 Nov 2026 — geo rule removal requested
- New DFS live since 10 Nov (`../dfs/GO-LIVE-2026-11-10.md`): the price box no longer depends on the per-locale cache, and the GPS boxes are an Amazon button. So the condition above ("after DFS step E + GPS links live") is met.
- 06:56 UTC Ruggero to Mehedi (ticket 122395): new setup live, please disable the geo-caching rule, **only the runningshoesguru.com part** (the `lebeddie.cz` part is another customer's). Business plan move kept for a **separate mail**. No answer yet (checked 08:00 CET).
- Next: when they confirm, run the multi-country check (same HTML and `cf-cache-status` from several countries, right box after the swap); if anything breaks, ask them to re-add the rule (they confirmed they can). Then send the Business plan confirmation. WP Rocket page cache can be switched on afterwards (decision 10 Nov).

