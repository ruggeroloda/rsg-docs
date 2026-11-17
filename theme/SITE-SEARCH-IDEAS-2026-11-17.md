# Site search — findings and ideas (17 Nov 2026)

Notebook: `rsg.finding` #5 (`search:zero_results|home`). Ruggero 17 Nov: no hurry on the cache fix; wants ideas to make search much better.

## What we know (day 1 of the new homepage)
- Hero search = the top homepage action: 24 searches by human visitors since 07:20 UTC (18 % of homepage visits).
- Every term is a specific model or brand: people arrive knowing the shoe.
- 16/24 reached a review or listing (3 via the brand/family redirect: "brooks", "hoka", "adrenaline"). 8 ended on the homepage or left.
- ~1/3 got **0 results**: newer models not reviewed (Triumph 24, Ghost Max 2, Mach 10, Coros Pace 4, Revolution 8, Cloud Ultra 3, Trabuco Max 5) and odd forms ("swooshfly", "ghostmax2", Japanese katakana ブルックス ゴースト).

## Current search (theme `rsg_2022`)
- Title-only WordPress search + brand/family redirect; results page at `/?s=` (Umami: url `/`, tag `archive`, query not stored).
- Log `newguru_wp_searchwp_log_copy` (`includes/search_stats.php`, `the_posts` filter): ~2,600 rows/day, nearly all bot spam, no session link, stamped in **America/New_York** (WordPress timezone, UTC−5). Only usable by matching timestamps to Umami `home_search`.
- **Edge cache:** search pages `s-maxage=2592000` (30 days); repeat searches are served by Cloudflare → never logged, and new reviews don't appear in cached results. Not urgent (Ruggero).

## Inspiration (what good WordPress / search setups do)
| Source | Idea worth taking |
|---|---|
| SearchWP (+ Live Ajax Search, Metrics) | Live results as you type; "Did you mean?" on zero results; synonyms; metrics: no-result searches, clicks per search |
| FiboSearch (WooCommerce) | Rich autocomplete: image, price, details panel on hover; fuzzy matching; mobile search mode |
| Jetpack Search | Embedded results page with filters/sort on a real URL; **AI Answers** at the top of results when useful |
| Typesense (self-hostable; WP plugins exist) | Typo tolerance, autocomplete, hybrid keyword + vector search; **natural-language search** (LLM turns a sentence into filters/sort) |
| Meilisearch (CelerSearch, Scry Search plugins) | Same family: instant, typo-tolerant, facets, optional semantic search, fallback to WP search |
| Algolia guidance | Never a dead-end zero-results page: drop words and retry, query suggestions, related content, redirects for strong intent; use zero-result terms as a content gap list |
| RunRepeat | Results and catalogue with deep spec filters (drop, weight, stack, pronation, price, score) |

## Ideas for RSG (not decided)
1. **Rich autocomplete:** RSG's own shoe photo, score, "from $X" (DFS), type; grouped Reviews / Comparisons / Brands. "X vs Y" typed → comparison.
2. **Version-aware zero results:** "Triumph 24" → "Not tested yet — our Triumph 23 review" + what changed + "tell me when it's reviewed" (email). Zero-result terms feed the review backlog.
3. **Forgiving matching:** typos, spacing/version forms ("ghostmax2", "1080v15", "cloud ultra3"), brand aliases (Hoka One One), synonyms; multilingual (katakana → brand/model).
4. **Natural-language search:** "stability shoe under 9 oz under $120" → filters on RSG's specs + DFS prices + scores. Could replace the weak Shoe Finder.
5. **AI answer on top**, only from RSG's own reviews, with links (also a GEO play). Cost and accuracy to weigh.
6. **Results page with filters** (type, brand, score, price) on a real, crawl-safe URL (noindex).
7. **Search analytics loop:** human-only searches (joined to Umami), zero-result report in the analyst's weekly.

## Engine options
- **SearchWP in WordPress** (paid, ~$99+/yr): fastest to ship; stays on Rocket.net/MySQL; limited for ideas 4–5.
- **Self-hosted Typesense or Meilisearch on rsgmedia** (Docker, like Umami), WordPress pushes posts on save (like DFS): typo tolerance, facets, hybrid/semantic, NL search (Typesense). Adds a dependency (fallback to WP search).
- **Postgres already on rsgmedia** (full text + pg_trgm + pgvector): no new service; NL/AI parts built by hand.
- **Hosted (Jetpack Search, Algolia):** quick, recurring cost, less control.
- Size: ~1,300 reviews + 173 comparisons — tiny for any engine.
