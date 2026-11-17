# Hosting — Rocket.net, bots, storage, Linode

_Last updated 2026-11-11. **Current ticket status and next step: `ROCKET-BANDWIDTH-HANDOVER.md`.** Facts in `../CONTEXT.md`. Hetzner box (n8n, Stargrid): backups, patching, security in `hetzner/HANDOVER-2026-11-05.md`._

## Goal
Pay for real traffic only. **Staying on Rocket.net:** custom Expert (~$370/mo) → **Business yearly + 15 GB extra storage = $113/mo** (agreed in principle 10 Nov, ticket 122395; confirmation mail not sent yet). **11 Nov:** Rocket.net **switched off** the runningshoesguru.com part of the geo-cache rule (~08:45 UTC). Measurement baseline and checks: `../dfs/GO-LIVE-2026-11-10.md`.

## Bandwidth: who uses the 366 GB
- Real visitors: ~160k page views × ~0.7 MB ≈ **110–150 GB/month**. The rest is bots, scrapers, hotlinking and the site requesting itself.
- Rocket.net ticket **121944**. Rocket.net replied 5 Nov (top lists by requests, asked what exactly to block). _9 Nov: a short reply was sent instead of the full draft below (see `ROCKET-BANDWIDTH-HANDOVER.md`); the draft is kept for reference._ (draft below; add: DFS server IP + /wp-json/, ads.txt exemptions so DFS pushes and Make → WordPress keep working):
  1. Block by user agent: Bytespider, SeekportBot, Baiduspider, Yahoo! Slurp, WinHttp.WinHttpRequest, HeadlessChrome, Chrome/133.0.6943.141.
  2. Block IPs 34.232.137.97, 184.72.84.161.
  3. `/go/*`: block all bots; challenge data-centre ASNs (14618, 16509, 396982, 60068, 24940, 14061, 16276).
  4. Challenge the same ASNs site-wide, except verified bots and: Googlebot, Googlebot-Image, AdsBot-Google, Mediapartners-Google, bingbot, AmazonAdBot, proximic, CriteoBot, GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, meta-webindexer, meta-externalagent, facebookexternalhit.
  5. Asked again for top paths/user agents **by bytes**.
  6. Asked whether WP Rocket caching/preload is needed on Rocket.net (own server `65.181.124.237` made 112k requests; `heartbeat.js` is the #2 path).
  7. Still open: hotlink protection for `/wp-content/uploads/`, WebP/AVIF status.
- **Never block** ad-industry crawlers (Raptive revenue) or search/AI-answer crawlers.

## robots.txt — ✅ DONE 6 Nov (applied live over SSH; backup `robots.txt.bak-20261106` on the server)
Add, without removing existing lines:
```
User-agent: *
Disallow: /go/

User-agent: SeekportBot
Disallow: /

User-agent: Bytespider
Disallow: /

User-agent: MJ12bot
Disallow: /

User-agent: DotBot
Disallow: /

User-agent: PetalBot
Disallow: /
```
Applied: all of the above plus AhrefsBot and SemrushBot. `/go/` was already disallowed under `User-agent: *`. Edit via `ssh -i ~/.rsgkey jfx9wij@65.181.124.237` (key copy of `theme/.ssh-rocket-key`), file `~/public_html/robots.txt` (physical file).

## Plan path (history; superseded 9–10 Nov: Business yearly + 15 GB, see above)
1. Now: ask Rocket.net to move to **standard Expert ($200)**: 500 GB covers today's 366 GB. Ask what the custom plan includes that standard doesn't.
2. After bots are blocked: watch bandwidth for ~2 weeks.
3. When bandwidth is well under 300 GB **and** storage is under 40 GB: **Business ($100)**.

## Storage (53 of 50 GB) — measured 5 Nov 2026
| What | Size | Notes |
|---|---|---|
| Staging (`jfx92wmcr3-staging.onrocket.site`) | 25 GB | Full clone incl. its own copy of all uploads. Nothing unique on staging (checked file by file 5 Nov: only DFS image-cache files + a plugin log). |
| Live `wp-content/uploads` | 25 GB | 12.9 GB thumbnails (237k files) + ~11.3 GB originals (37.7k images; 9.65 GB of them 1001–1920 px, avg ~600 KB). Only 151 WebP files. |
| Old themes, `runningsweb.xxx`, `tmp`, old `rsg_backups_*` | ~1.3 GB | Low-risk deletes |

Findings:
- Registered sizes are WP core only (150, 300, 768, 633 large, 1536, 2048). Other sizes (900x620, 957x470, 480x269, 920x544…) are made **on the fly** by the theme's `rsg_image_resize()` (`includes/image_resize.php`) and regenerate on demand. Sizes hard-coded in post content do not.
- If `rsg_image_resize()` can't find the original on disk it returns the original URL, so a staging **without** uploads still renders (with an `.htaccess` rule sending missing uploads to prod), but shows full-size originals (page weight not representative).
- WebP/AVIF conversion saves bandwidth, not storage (converters add files).

Options (**not needed for the downgrade any more**: Business + 15 GB = 55 GB; staging stays a full copy, Rocket.net can't exclude uploads from staging):
1. Staging without its uploads: −25 GB. Do it when staging is no longer needed as a faithful copy, or create the next staging lean (Rocket.net "create staging" copies all uploads).
2. Low-risk cleanup: −1.3 GB.
3. Re-compress 1920 px originals: est. −4–5 GB, lossy, local backup first.
4. Delete unused thumbnail sizes: a few GB, needs a post-content check first.
5. Originals > 1920 px: −1 GB.
- **Never** "re-sync staging from prod" or "push staging to live" for files without checking: it copies or overwrites the full uploads folder.

## Linode (legacy DFS) — cancel in DFS step 7 Phase F (from ~17 Nov; the new DFS went live 10 Nov)
- Remove the **Linode Managed** monitor (Cloud Manager → Managed → Monitors): ~3.4k hits/day on the site.
- ✅ Deleted the unused 58.6 GB disk (5 Nov).
- ✅ Resized to Linode 4 GB ($24 + $5 backups) 5 Nov. Measured safe (peak 1.3 GB RAM). 30–60 min downtime, IP unchanged.
