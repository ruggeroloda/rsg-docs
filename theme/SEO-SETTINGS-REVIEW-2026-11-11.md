# SEO settings review (11 Nov 2026)

_**Status 11 Nov ~23:45 CET — Ruggero's calls:** P1 do, P2 do + keep it right going forward, P3 leave (fine once publishing restarts), P4 do, P5 propose, P6 do, P7 do, P8 do the easy ones, P9 ok (affiliate_product cleanup), P10 check properly, P11 ok (in the rewrite). **On staging** (`8601f20` + 2 follow-ups, last deploy `staging-20261111-223626`): P1, P2, P4, P6, P7, P8, P9 and P10 (overrides removed: the six post IDs no longer exist). Parity check live vs staging on 83 URLs: only intended differences. Staging backfill of text-change dates run (1,574 from "modified", 395 bulk-day → publish date). **Live 12 Nov ~00:05 CET** (Ruggero: "go live, check and re-check"; theme `3e7f0fe`): backfill run on live (1,574 from "modified", 395 bulk-day → publish date), transients cleared, purged. **Re-check:** all 2,625 baseline URLs re-crawled — only intended changes (canonicals on authors/categories/GPS brands/content categories/blog, author descriptions, noindex on 4 empty listings, fixed redirects, Garmin title, og:title on 424 pages); live sitemaps 2,478 URLs, none redirected/noindex/404 (3 crawl timeouts checked by hand: fine, one was the Pearl Izumi 500, fixed). `/test-cache/` (2022 cache test page) now noindex and out of the sitemap. **Still for Ruggero:** resubmit the sitemap in Search Console. 12 Nov: P5 live; 2015 gift-guide redirect removed; flat-feet articles both kept._

_Ruggero (11 Nov): examine all SEO settings, check current Google guidance, propose what to change. Read-only: nothing changed. Evidence: every SEO option on live (WP-CLI), the code that applies them, and the head tags of **all 2,625 URLs in the sitemaps** (crawled 11 Nov from the edge). Raw crawl: `heads.json` in the session; summary below._

## What is set today
Settings were made in 2010 in *Platinum SEO Pack* (an All in One SEO fork; its option names start `aiosp_`/`psp_`). Since June 2026 the robots tag comes from the theme (`rsg_robots_filter`), which still reads the same options.

| Setting | Value | What it does today | Verdict |
|---|---|---|---|
| Title rewrite (`aiosp_rewrite_titles`) + formats | on; `%post_title% \| %blog_title%`, same for pages/categories/tags/taxonomies, search `%search% \|…`, 404 "Nothing found for…", date `%date% \|…`, paged ` - Part %page%` | Every title = custom title (post meta `title`, 2,668 posts) or post title, + ` \| Running Shoes Guru`. Listing pages add counts + month in the theme. | **Keep** the format (Google: site name at start or end with a delimiter is fine). Fix the broken cases below. |
| Home title | page meta: "Running Shoes Reviews and Buying Guide"; `aiosp_home_title` "Real Runners, Real Reviews" only on `/blog/` | | Keep. |
| Descriptions | custom (post meta `description`, 2,689) → else full excerpt → else first 160 chars of content (`aiosp_generate_descriptions` on) | Reviews median 312 chars (max 1,108) = the review's own summary | **Keep** (Google: no length limit, truncates on display; page-specific text is what it wants). |
| Meta keywords (`aiosp_home_keywords`, post meta `keywords` 2,408) | set | Not printed since 11 Nov | Dead (ignored by Google). Drop data in the rewrite. |
| Canonical (`psp_canonical`) | off | Theme prints canonicals for single pages and the /reviews/ listings only | See P1, P4. |
| Robots: noindex search results / tags / date archives / categories | on | | **Keep**. |
| Robots: noindex author archives | off, but theme noindexes authors with < 6 posts | | Keep; sitemap bug P2. |
| `noodp`, `noydir` | on | Not printed since June | Dead, drop. |
| `nofollow` on login/register and tag links | on | | Obsolete (internal nofollow does nothing useful). Drop. |
| Comment pages noindex | on | Comment pages redirect anyway | Drop. |
| Feeds noindex | on | | Keep (harmless). |
| 404 → "permalink changed" redirect (`psp_permalink_redirect`) | on | Exact-slug match → 301 | **Keep** (made safe in step 0); exclude unregistered types (P9). |
| `max-snippet:-1, max-image-preview:large, max-video-preview:-1` | theme | | **Keep**: allows full snippets, large images (Discover) and AI features to quote. |

## What the crawl found (2,625 sitemap URLs)
- **Paginated listings point their canonical at page 1**: `/reviews/page/2/` → `/reviews/`, `/reviews/nike/all/page/2/` → `/reviews/nike/all/`.
- **Sitemap lists URLs that should not be there:** all 68 categories (48 noindex, 20 redirect to `/reviews/…`), 12 thin-author pages that are noindex (the exclusion misses authors whose posts are all `content`), the 4 `noindex` posts, ~45 published posts that 301 elsewhere (old guides/apps articles, `content` copies of guides, `gps` → `reviews/gps`), 8 published posts that answer 404.
- **Sitemap `lastmod` = WordPress "modified"**, which moves on bulk edits (26 road reviews share 2024-03-28), not on text changes.
- **Month in listing titles moves every month on its own**: "164 Nike Running Shoes Reviews (November 2026)" — all 37 brand, 27 family, 11 type pages and /reviews/. Guides show "(August 2024)" (last edit).
- **Archives without canonical or description:** 57 author pages, 8 GPS brand pages, 7 content categories, `/blog/`, comparison/guide archives.
- **Duplicate descriptions:** 82 URLs in 13 groups — 39 share the /reviews/ text (shoe-type and old category pages), family pages reuse the brand text (`/reviews/nike/pegasus/` = Nike's description).
- **Broken titles:** date archives "| Running Shoes Guru" (empty); paged "…(November 2026)  Page 2 |…" (double space) and author page 2 "Page 2 | … - Part 2" (two numbering systems); 2 duplicate titles (Adidas Energy Boost, Polar M600 — old duplicate posts).
- **og:title = plain post title**, not the SEO title, and unescaped.
- **Affiliate links:** 377 of 397 sampled carry `rel="sponsored nofollow noopener"`; 20 direct Running Warehouse links (`?from=guru`) have no rel.
- **Multiple h1:** every comparison (173), legacy `review` (62), `gps` (18) — template work, already in `TECHNICAL-SEO-BACKLOG.md`.
- Removed `affiliate_product` URLs 301 to `/?p=<id>` → 404 (cleanup step).

## Current guidance used (Google Search Central)
- Titles: unique, descriptive, site name at start/end with a delimiter is fine, no length limit (display truncates), **keep dates in titles current**; Google may build the title link from `<title>`, the h1, **og:title**, headings, anchors, `WebSite` data.
- Pagination: **"Don't use the first page of a paginated sequence as the canonical page"**; each page self-canonical; rel next/prev no longer used; paginated pages may share titles.
- Sitemaps: list the URLs you want in results (canonical, indexable); `lastmod` used only if "consistently and verifiably accurate" (main content, structured data, links); priority/changefreq ignored.
- Helpful content self-check: "Are you changing the date of pages to make them seem fresh when the content has not substantially changed?"; reviews: show how many products were tested / evidence of testing.
- Snippets: no length limit for meta descriptions; page-specific, programmatic generation is fine if human-readable and diverse; avoid identical descriptions.
- Paid/affiliate links: `rel="sponsored"` preferred, `nofollow` acceptable.
- AI Overviews / AI Mode: "no additional requirements … nor other special optimizations"; no special schema; controls are the normal snippet/indexing ones (`nosnippet`, `data-nosnippet`, `max-snippet`, `noindex`); Google-Extended for training.

## Proposals (ranked)
**High**
- **P1 Paginated listings self-canonical** (`/reviews/page/N/`, `/reviews/<brand>/<type>/page/N/`). Direct conflict with Google's guidance; deep pages and the older reviews they link to can drop out. Low risk.
- **P2 Sitemap = indexable canonical URLs only**: drop categories, `noindex` type, thin authors (fix the query), published-but-redirected and 404 posts (set them to draft or exclude). `lastmod` from `rsg_content_modified` (real text changes, the same rule as the visible "updated" date).
- **P3 Honest month in listing titles**: use the month of the newest review in that listing (or no month), not the current month. Guides keep their last-edit month until they are redone (they will look old; that is accurate).

**Medium**
- **P4 Self-canonical on every indexable archive** (authors, brand/family/type, `/blog/`, post-type archives) and a real description on author pages (bio) — "Who" signal for reviews.
- **P5 Unique descriptions for listing pages**: generated from data ("23 Nike Pegasus reviews, latest: Pegasus 41 (Oct 2026). Tested by…"), replacing the 39 shared and the brand-reused texts.
- **P6 og:title/og:description = the SEO title/description**, escaped.
- **P7 `rel="sponsored"` on the Running Warehouse direct links** (if they earn commission).
- **P8 Fix broken titles**: date archives, one pagination format (" – Page 2"), the 2 duplicates.
- **P9 Slug redirect ignores post types that are not public** (no more 301 → 404 chains).
- **P10 Six hard-coded canonical overrides** (post IDs 23314, 24079, 24077, 26342, 26343, 23098): check each still makes sense; move to a per-post "canonical URL" field.

**Low / housekeeping (in the rewrite)**
- Drop dead settings/data: noodp/noydir, meta keywords, internal nofollow, comment-page rule, unused title placeholders, the plugin's admin pages and self-updater. Move per-post `title`/`description`/`robotsmeta` to prefixed keys, editable in the editor and via REST.
- Multiple h1 (template rebuild).

**Not proposed:** changing the title or description wording of reviews/guides (working, and Ruggero's call), `llms.txt`, special AI markup, rel next/prev.

## Done on staging (detail)
- **P1** `/reviews/page/N/`, `/reviews/<brand>/<type>/page/N/` self-canonical.
- **P2 by rule** (functions.php "XML sitemaps list only URLs we want indexed"): no tags; no categories while "noindex categories" is on; no `noindex` type; no posts marked noindex; no post whose own URL is a source in the redirect table; only authors with ≥ 6 counted posts (same rule as robots; fixes 0-post authors). `lastmod` = `rsg_content_modified` (real text change); text-change tracking now covers every indexable type (`rsg_cd_tracked_types()`), the visible dates stay on reviews only. Effect on staging: users 57 → 45, posts 1,525 → 1,495, content 191 → 174, pages 41 → 32, gps 21 → 18, `noindex` 4 → 0, categories 68 → 0; all removed URLs are redirects or noindex.
- **P4** canonical on `/blog/`, authors, term archives, post-type archives (+ their page N); author bio as description (89 users have one).
- **P6** og/twitter title = the `<title>` without site name (same rules, incl. "no title rewrite" pages such as /reviews/), description = the meta description; escaped; dead survey image override removed.
- **P7** "We purchased this pair at …" (road/trail/training/gps templates) and the list fallback button: `rel="sponsored nofollow noopener"`.
- **P8** one page format "… - Page N | Running Shoes Guru"; date archives "Archive: 2019".
- **P9** 404 slug redirect only to public types and never to a URL the redirect table sends on (stopped the flat-feet loop; that URL is now a plain 404 until the redirect table is fixed, below).
- **P10** the six override IDs (23314, 24079, 24077, 26342, 26343, 23098) don't exist any more: code removed, nothing to move.

## Applied on live 11 Nov ~23:55 CET (data, agreed by Ruggero)
- Redirect table (backup `~/rsg_deploy_backups/301_redirects-public_html-20261111-224956.json`, script `fix-redirects-20261111.php`): 15 rules to the draft `/guide/best-running-shoes-2024/` + the non-existent cheap guide → `/guide/best-running-shoes-for-beginners/` (latest live general guide); flat-feet (3 rules + new rule for `/guide/the-best-running-shoes-for-flat-feet/`) → `/content/flat-feet-in-runners-all-you-need-to-know/`; 13 chains pointed at their final URL; new `/gps/polar-m600/` → `/2017/07/polar-m600-review/` (the hands-on review; the GPS page is an empty data page). Same on staging. Checked: 34/34 one hop to a 200 (Polar after a full purge).
- Garmin Forerunner 265 (44394) SEO title → "Garmin Forerunner 265 Review" (old value saved next to the redirect backup).
- Still open: the 2015 gift-guide rule ends on a 404 (no decision); two similar flat-feet articles exist (`/content/flat-feet-all-you-need-to-know/`, `/content/flat-feet-in-runners-all-you-need-to-know/`) — possible duplicate for the content audit.

## Empty listings (staging, code)
Review listings with 0 reviews are noindex and left out of the brand/type/family sitemaps; the check runs the listing's own query (a first SQL version disagreed with the pages and was replaced). On staging: Ryka, Sketchers, Spikes, Walking out; everything else unchanged.

## Found while doing it (need Ruggero) — answered 11 Nov, see above
- **Redirect table (146 rules, Simple 301 Redirects):** 17 rules end on a 404 — **15 point to `/guide/best-running-shoes-2024/`, which is a draft** (old best-running-shoes, stability, cushioned, marathon, CrossFit, walking… URLs), 1 to `/guide/best-cheap-running-shoes-2024/` (no such guide), 1 to the 2016 gift guide (gone). 3 rules made a loop on the flat-feet guide (`/guide/the-best-running-shoes-for-flat-feet/` doesn't exist; `/content/the-best-running-shoes-for-flat-feet/` is published but redirected to it). ~10 rules point to a URL that redirects again (chains, e.g. old minimalist/lightweight/racing categories → `/reviews/all/minimalist-2/` → `/reviews/`). Proposal: publish or re-target the "best running shoes" guide; flat-feet URLs → `/content/flat-feet-in-runners-all-you-need-to-know/` (or un-redirect the content page); point chains at their final URL.
- **Garmin Forerunner 265 review (44394):** SEO title = title + start of the verdict pasted together (188 chars) → set to "Garmin Forerunner 265 Review".
- **Empty brand pages are indexable:** `/reviews/ryka/all/` "0 Ryka Running Shoes Reviews". Proposal: noindex + leave out of the sitemap any review listing with 0 reviews (rule).
- **Polar M600:** post 19387 (2017 review) and `gps` 22554 (`/gps/polar-m600/`) have the same title; keep one, redirect the other.

## P5 — listing descriptions (**live 12 Nov ~00:38 CET**, Ruggero: ok)
On staging all 87 listing/term pages got a description, no duplicates (the 27 family pages already had their own text, which the old code ignored in favour of the brand's). Brand pages: 25 generated (10 keep their own text); types: 8 generated (cushioning keeps its own); GPS brands 8 and content categories 7 generated. Proposal as approved:
Generated from data, only where no editorial text exists, and only changing when the data changes (a new review), so nothing fakes freshness:
- **Brand page** `/reviews/<brand>/all/`: keep the brand's own text where written; otherwise (today ~38 pages fall back to the /reviews/ text): "{N} {Brand} running shoe reviews. We buy every pair with our own money and run in it before we review it. Most recent: {newest model} ({Mon YYYY})."
- **Family page** `/reviews/<brand>/<family>/` (27, today = the brand's text): "{N} {Brand} {Family} reviews, from the {oldest model} to the {newest model}: what changed in each version, how it fits and which one to pick."
- **Type page** `/reviews/all/<type>/`: own text if written, else "{N} {type} running shoe reviews, tested by our runners. Most recent: {newest model} ({Mon YYYY})."
- **GPS brand / content category** (15, none today): own text if written, else "{N} {Brand} GPS watch reviews…" / "Articles on {category} for runners, most recent: {title}."
