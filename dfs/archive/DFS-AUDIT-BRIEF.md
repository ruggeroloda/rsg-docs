# DFS Audit — Brief for a New Chat

*Paste this into a fresh Cowork chat to kick off the DFS review. Mount `/Users/ruggeroloda/Documents/claude` first. Background context lives in `projects/dfs/CONVENTIONS.md`.*

---

## What I want you to do
Help me decide one thing: **can I get away with updating my existing (legacy) Datafeed Studio price-comparison engine, instead of finishing the full Python rewrite we started?**

I want a clear, evidence-based recommendation — **update legacy** vs **finish rewrite** — with the reasoning, effort estimate, and risks for each. Don't assume; look at the actual code before recommending.

## Background (what DFS is)
**Datafeed Studio (DFS)** is RSG's price-comparison engine. It imports affiliate product feeds from merchants, matches them against a master catalogue (synced from Airtable), and feeds prices to the WordPress site (runningshoesguru.com).

There are **two** versions:
- **Legacy:** a ~14-year-old **PHP / MySQL** system. This is what currently runs in production.
- **Rewrite (half-built):** a modern **Python 3.11 / Flask / PostgreSQL / DuckDB** stack. See `projects/dfs/CONVENTIONS.md` for its full spec. Sprint tickets are in **Airtable** — base `appLoQNoAydB65Vop`, table "Dev Tickets" (`tbl9TvbH0sjZKMacR`). Check there for where the rewrite actually got to.

## Where the legacy engine lives — RESOLVED (2026-06-21)
**Legacy DFS = `45.33.39.133` (`dfs.runningshoesguru.com`), web root `/var/www/html`, MySQL db `dfs`.**
Access + box topology + verified Amazon-import state are in **`DFS-ACCESS.md`** (read that first).
The original "unknown" note is kept below for history.

During the WordPress theme audit we confirmed the **legacy DFS is NOT on the Rocket.net WordPress server** — that box only has `wp-content/uploads/dfs-images-cache` (WordPress just *consumes* DFS output) and a single WP database. So **your first job is to locate where the legacy PHP/MySQL engine actually runs.** Likely candidates:
- The **Hetzner VPS** (see `playbooks/hetzner.md`) — most probable.
- An older/separate server or a git repo.
Ask me if you can't find it; I can point you or give access.

## How WordPress calls DFS today (so you know the contract)
The theme calls DFS server-side, **one country at a time**, e.g.:
`dstudio(['query' => $pricebox, 'pricebox' => 'newrsg-json', 'countrycode' => 'GB'])`
which returns JSON with per-offer fields: `DISPLAY_PRICE`, `CURRENCY_PREFIX`, `CURRENCY_CODE`, `COUNTRY_NAME`, `DEEP_LINK`, `MYKEY`. That's the live merchant offer. MSRP/list price is handled separately on the WP side (and is currently broken for non-US — see below).

## The one requirement that may force a DFS change
I'm reworking the WordPress side to a new pricing/caching model (see `projects/rsg-redesign/CONVENTIONS.md`): **one cached page for all geographies**, with **per-country prices delivered as data** and filtered in the browser. For that to work, DFS needs to be able to:

1. **Emit all supported countries in a single payload** (not one `countrycode` call at a time), and
2. **Include the MSRP per country** (`price_msrp`) alongside the live offer in that payload.

The new DFS spec already lists `price_msrp` as a required parser field, so the data probably exists — but the current `newrsg-json` output is per-country and (on the WP side) MSRP is a manual ACF field that never gets filled in. **Please determine whether DFS (legacy and/or rewrite) can produce a per-country matrix containing both live price and MSRP.** This is the single most important question for the WordPress redesign.

Supported countries to target (current WP clamp list): **US, CA, GB, DE, FR, ES, IT, GR, SE, NO, IE, AU, NZ, MY, ID, SG, HK**.

## Deliverables I want back
1. **Where the legacy engine runs** (host, path, DB) and how it's triggered (cron? n8n? manual?).
2. **State of the Python rewrite** — what's done vs. outstanding (from code + Airtable tickets).
3. **Recommendation: update legacy vs. finish rewrite** — with effort, risk, and what each path means for PHP version, maintainability, and ongoing feed reliability.
4. **Answer the per-country price+MSRP matrix question** — can DFS emit it, in which version, and what it'd take.
5. If "update legacy" is viable, a **concrete punch-list** of what updating actually involves (PHP version, dependencies, the matrix output, anything broken).

## Working rules
- Look at real code/servers before recommending — no guessing.
- Prefer API/SSH/MCP over browser.
- Don't touch production without taking a backup and telling me first.
- Be concise; lead with the recommendation, detail underneath.
