# Umami — what it tracks, what can be added, where to run it (research 16 Nov 2026)

_For the own-analytics workstream (Ruggero 16 Nov: own analytics as the base for two analyst agents — affiliate conversion + site usage). Not decided, not built. Sources at the end._

## Version
Latest: **v3.5.0 (12 Nov 2026)**. Line: v3.0 (Nov 2025, PostgreSQL only — MySQL dropped), 3.1 (Apr 2026: boards, session replay, Core Web Vitals; Node.js 22+), 3.2 (Jun: heatmaps with self-hosted recording/storage, property reporting, revenue reports, `data-auto-pageview`, LLM/AI channel), 3.3 (Aug: 2FA, identity stitching, property filters), 3.4 (Sep/Oct: annotations, **MCP (read-only, off by default on self-hosted: `MCP_ENABLED=1`)**, API keys, typed API client, `data-distinct-id`, session-property segments), 3.4.1 (Oct: security hotfix), 3.5.0 (Nov: JavaScript error tracking, client salt rotation, date-range comparison). Releases are frequent and include security patches (several Next.js CVE fixes) → updates are a real, recurring chore.

## What it collects by itself
- **Page view:** hostname, URL (path + query unless `data-exclude-search`, hash unless `data-exclude-hash`), page title, referrer, screen size, language. Server side adds browser, OS, device type and country/region/city from the IP (IP itself not stored).
- **Visitor / session / visit** without cookies (hash of IP + user agent + a rotating salt; rotation configurable since 3.1/3.5). Bounce rate, visit duration, entry/exit pages.
- **Sources:** referrers, UTM, channels (incl. AI/LLM referrals since 3.2), attribution report.
- Known bots filtered by user agent; bots that run JavaScript are counted (same issue as our view log on 13 Nov).

## Switch-on extras (all in the open-source version)
| Feature | Since | Notes for RSG |
|---|---|---|
| Custom events with properties | v2 | `umami.track(name, data)` or `data-umami-event-*` attributes. Name ≤ 50 chars; ≤ 50 properties; strings ≤ 500; numbers, dates, booleans kept as types via JS. **Fits our flat event model** (seen, click, drawer, switch…). |
| Session data / distinct ID | 3.3–3.4 | `umami.identify()`; useful for logged-in or email-identified visitors only (we have neither, mostly). |
| Core Web Vitals | 3.1 | `data-performance`: LCP, INP, CLS, FCP, TTFB from real visitors, per page / device. Replaces the gap left by Search Console's 28-day summary. |
| JavaScript errors | 3.5 | `data-errors`: groups errors into issues; `data-release` = theme commit. Catches price-box script failures. |
| Heatmaps (click + scroll) | 3.2 | Needs `recorder.js`; **sampled** (default 15 % of sessions). Scroll heatmap answers "how far do people get" per page, by screen width. |
| Session replay | 3.1 | Same recorder, 15 % default, inputs masked by default, kept 30 days. Heavier (storage, extra script). |
| Revenue | 3.x | Events carrying revenue + currency; reports, attribution. Our commissions arrive days later from the networks → would be pushed in server-side, not tracked in the browser. |
| Goals, funnels, journeys, retention, cohorts, segments, breakdowns | 3.0–3.3 | Funnel example: page view → price box seen → click. |
| Boards, annotations, date comparison | 3.1–3.5 | Annotations = mark deploys (reskin, prune) on charts. |
| Links + pixels | 3.0 | Short tracked links; invisible pixel = **email open tracking** (email workstream). |
| API + MCP | 3.4 | REST API with API keys, typed client; read-only MCP covering traffic, events, sessions, funnels, goals, journeys, retention, attribution, revenue, performance → **the analyst agents can query it directly**; plus plain SQL on Postgres. |

**Cloud-only (not stated for self-hosted):** Search Console reports and payment-provider revenue imports (Stripe, Paddle, WooCommerce…). For us: Search Console comes in through its own API anyway.

## What Umami doesn't do (we add it)
- Whether a specific box was **seen** (we send it as an event; heatmaps are only a sample).
- **Orders/earnings** from Amazon/AWIN/CJ/Rakuten and **ad revenue** from Raptive: pulled from their reports/APIs into the same Postgres (or sent in as revenue events).
- **Link a commission to a visit/source:** only possible where the network sub-ID can carry an ID (AWIN clickref ≤ 50 chars, CJ, Rakuten u1); not Amazon (tag only). Option to evaluate: add a short visit ID to the sub-ID at click time.
- RSG data (shoe, brand, review age): joined from WordPress at report time via page ID.
- Ad blockers: anything blocked isn't counted (see below).

## Where to run it: Rocket.net vs Hetzner
**Umami itself cannot run on Rocket.net** (managed WordPress: PHP + MySQL; Umami needs Node.js 22 + PostgreSQL). So the app runs on Hetzner either way; the real choice is **how visitors' browsers reach it**:
- **A. Own subdomain straight to Hetzner** (e.g. `t.runningshoesguru.com`: Route 53 A record → rsgmedia, SSL via Nginx Proxy Manager).
- **B. Through Rocket.net on the main domain** (`www.runningshoesguru.com/<path>` forwarded to Hetzner): either a PHP forwarder in WordPress, or an edge rule at Rocket.net's Cloudflare (Rocket.net support would have to set it up).
- In both, the small tracker file can be a copy in the theme served from Rocket.net's CDN (cached, no origin load), pointed at the collector with `data-host-url`.

| Aspect | A. Subdomain → Hetzner | B1. PHP forwarder on Rocket.net | B2. Rocket.net edge rule |
|---|---|---|---|
| Page speed for visitors | none (script deferred, data sent in the background) | none | none |
| Load on Rocket.net origin | 0 | +1 uncached PHP request per page view and per event batch | ~0 (edge only) |
| Ad-blocker losses | low–medium (own domain, renamed script/endpoint via `TRACKER_SCRIPT_NAME`, `COLLECT_API_ENDPOINT`) | lowest (same host as the site) | lowest |
| Data quality | direct: real IP → country, sessions | must pass the visitor IP/country in headers (`CLIENT_IP_HEADER`) or every visit looks like Rocket.net's server | same header passing at the edge |
| If Hetzner is down/slow | analytics gap; site unaffected | **risk to the site**: slow forwards hold PHP workers on Rocket.net | analytics gap; site unaffected |
| Control / changes | ours | ours (code in theme) | Rocket.net ticket for every change |
| Cost | €0 on rsgmedia | €0 + origin load | €0 if Rocket.net allows it |
| Privacy | EU server, no cookies, IP not stored | same + data passes Rocket.net (US) | same |

Distance (Nuremberg ↔ US visitors) doesn't matter: the data goes in the background after the page is shown.

**Server:** rsgmedia (CPX32, 4 vCPU / 8 GB, 17 % disk, Postgres 16 and Nginx Proxy Manager already there, nightly encrypted backups to Drive). ~160k page views/30 days + a few events per view is light for Postgres; replays are the only heavy part (keep off or low sample at first). The dashboard login is public unless hidden behind the tunnel/allow-list like the other admin tools; MCP off unless the analyst agents need it (API keys, read-only).

**Recommendation (to decide):** A on rsgmedia, tracker copy served from the theme, renamed script + endpoint; measure blocked share for ~2 weeks against our own same-domain view log (trace failures 7–9 %, ad-blocker driven). Only if the loss is material, ask Rocket.net for B2. Not B1 (puts the site at risk for analytics).

## Sources
- heise: Umami v3 (Nov 2025); Umami 3.1 (boards, replay, Web Vitals).
- newreleases.io: v3.2.0, v3.3.0, v3.3.1, v3.4.0 release notes; releasebot.io release feed (v3.5.0, 12 Nov 2026).
- docs.umami.is: tracker configuration, tracker functions, track events, heatmaps, replays, environment variables, bypass ad blockers.
- alternativeto.net: Umami 3.4 (MCP); Umami Cloud error tracking / Search Console / revenue imports (29 Oct 2026).
