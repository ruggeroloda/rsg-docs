# Theme + rsg-core live deploys

_One line per live deploy, from the git tags in `rsg_2022`. What each change does: `RUNBOOK.md` (current state) and `../DECISIONS.md` (why). Regenerate: `cd rsg_2022 && git tag --sort=creatordate | grep live-`. The tag `rolled-back-live-20261114-111243` is never a rollback target._

| Tag (UTC) | Commit | Last commit in the deploy |
|---|---|---|
| `live-20261111-080859` | `73ae0a3` | deploy.sh: ssh -n so the confirmation prompt gets its answer |
| `live-20261111-084106` | `0f29e4b` | Cleanup batch 1: remove never-loaded code and unused assets; lint with PHP 8.3 + 8.4 |
| `live-20261111-090551` | `f260556` | Cleanup batch 2 (1/2): remove dead features, own Amazon links instead of geni.us, comparison RRP always US |
| `live-20261111-093726` | `4ed3998` | GPS Amazon links: no repeated brand in the search fallback ('Coros Coros Pace 3'); guide summary table 'Amazon' link per country (was an empty link) |
| `live-20261111-112017` | `81a9f26` | Remove the /feed/tca and /feed/tca-best feeds (built for Amazon in-PLP content, never used; 0 requests in 6 weeks); deploy.sh --no-purge |
| `live-20261111-130539` | `90bde70` | Fix two PHP 8 fatals: review templates with no gallery yet (count(false)); SEO meta handler on the page edit screen (term_exists(WP_Post)) |
| `live-20261111-132003` | `897e242` | ACF move (B): theme no longer bundles ACF; fields come from the Secure Custom Fields plugin |
| `live-20261111-134243` | `b335c7c` | Guides and top lists in the REST API (show_in_rest), like every other content type |
| `live-20261111-143451` | `bba1f92` | Comment reply links without ?replytocom= (replaces the server-only mu-plugin rsg-seo-cleaner.php, same output) |
| `live-20261111-143716` | `6f35110` | comment_reply_link filter: #respond instead of ?replytocom= (core reply links too), so the mu-plugin can go |
| `live-20261111-152804` | `69cc5d8` | deploy.sh clears WP Rocket minified copies + used CSS itself after CSS/JS changes; search log pruned daily (30 days) |
| `live-20261111-161403` | `7411438` | Footer: remove the commented-out AWeber "Latest Reviews by Email" block (dead markup sent on every page) |
| `live-20261111-185645` | `0157c2b` | Verdict in a <p> (same look): text extractors dropped it as a loose styled div |
| `live-20261111-230124` | `d0ced45` | Remove the empty ad-insertion content filter (no output change), the debug alert after a user review, the WP-CLI HTTP_HOST warning |
| `live-20261111-231250` | `970f709` | getLatestModelId(): cycle-safe (a self-linked review looped forever, HTTP 500 after 30 s) |
| `live-20261111-231514` | `3e7f0fe` | /test-cache/ test page: X-Robots-Tag noindex (it has no <head>) |
| `live-20261111-232747` | `8772073` | Contact form: honeypot + JavaScript/time token + Akismet instead of reCAPTCHA (its secret was never on the server) |
| `live-20261111-233750` | `4d543d7` | Listing descriptions: model names without 'Review …', clearer category wording |
| `live-20261111-234900` | `5dbb23b` | Remove affiliate_product code paths: its two templates, the type in post-type lists, taxonomies and meta boxes (posts erased, Ruggero) |
| `live-20261113-011232` | `5696e17` | rsg-core step 1a: content model moves into the site plugin rsg-core (same repo, deployed by deploy.sh) |
| `live-20261113-011915` | `1a15e98` | rsg-core step 1b: WordPress housekeeping moves into rsg-core |
| `live-20261113-083550` | `0e518da` | Dead code out: 11 never-called functions (geo IP helpers, dimox breadcrumbs, feed srcset, rsg_excerpt, ckIfAuthor, Google+ authorship pair, RSGgetFavicon, is_giftguide, rsg_get_content_length); 245 lines |
| `live-20261113-085538` | `ae3d891` | rsg-core: custom-field logic out of the theme (REST rule for all field groups, Theme Settings options page, comparison redesign fields; unchanged). Field groups stay in SCF's database. |
| `live-20261113-100047` | `ec481fe` | Old blog-post carousel: first photo carries width/height in the template (live had them only through WP Rocket; staging, without its own photos, shifted 0.23 CLS) |
| `live-20261113-103552` | `3ae66c5` | deploy.sh: clear WP Rocket minified copies + used CSS on staging too (it runs Remove Unused CSS; stale used CSS made a staging check look wrong) |
| `live-20261113-115900` | `7462d98` | Affiliate click log for every link: GPS Amazon button carries data-s (post id), 'We purchased this pair at' links data-box=bought; dead Universal Analytics click handler on the GPS button removed (logging now in the mu-plugin rsg-dfs-clicks 1.3.0) |
| `live-20261115-103600` | `5d3118c` | Merchant logos for AU: Running Warehouse AU, adidas (optical set, dfs/app/tools/logos/optical.py; existing logos byte-identical) |
| `rolled-back-live-20261114-111243` | `3eceb59` | Reskin step 1, fix: page titles keep their template sizes (h1/h2 base sizes in base.css); the condensed face only on h1-h3 (old --font-family-header = Public Sans for menus, buttons, hero text) |
| `live-20261115-143049` | `4ac5ef4` | Hero polish: comparison subtitle moves under the title as the summary (review style); review summary → byline 24 px; proof item "No free samples" (fits on one line with How we review). |
| `live-20261116-092506` | `a1e40cb` | rsg-track.js: ?rsg-rec=1 records the session wherever it is (manual test switch). |
| `live-20261116-092659` | `fa0d473` | Keep the Umami tracker out of WP Rocket's JS minify (served from t.runningshoesguru.com, not a cached copy). |
| `live-20261116-112040` | `259d8aa` | Shoe Finder steps as Umami events (quiz) + only our own JS errors to Umami. |
| `live-20261116-115743` | `26c5cdb` | Recorder respects Global Privacy Control / Do Not Track; sticky-kit no longer throws on templates without its parent. |
| `live-20261116-145940` | `eb19711` | Trail (and training/GPS) user ratings are /10, not /5 |
| `live-20261116-153059` | `e996e10` | Similar shoes: training = just the latest 3 (no comparison-table exclusion); trail/training section title shows the model name |
| `live-20261117-071928` | `1607123` | home_section_seen: finer thresholds so tall phone sections report |
| `live-20261117-084127` | `7c9d7c6` | /top/: shoe name in the 'In short' label for machines (visually hidden) |
| `live-20261117-084915` | `c54d0bf` | Guides: no '(Month Year)' after the title (H1 + <title>); byline 'By <author>' without the edit date |
| `live-20261117-085221` | `8fbf8a2` | Header mega menu: last dead ga() onclicks removed; homepage picks photos with alt text |
| `live-20261117-094858` | `f311fe2` | Guides + /top/: TOC nav inside a div and FAQ as <section>, so Raptive's in-content selector again offers the slots after the TOC and after the last category |
| `live-20261117-105515` | `11d3061` | /reviews/ cards: permalink from the card's own ID |
| `live-20261117-212319` | `4c796d1` | Homepage hero: rotate daily through the 5 newest reviews (switch 02:00 UTC, shown after the morning Amazon purge); label 'Recent review' |
| `live-20261117-224059` | `b651a25` | Homepage hero: the review's featured image (same photo rule as the rest of the site) |
| `live-20261118-062703` | `b7ab7d8` | Price boxes: no local offer -> 'Check price' Amazon search in the visitor's own store (GB/IE/CA/EU also when the country has no data); includes `df41069` guides archive brand links |
| `live-20261118-070424` | `be9db00` | deploy.sh overwrite guard; also: price copies for one screen width only (`286c0e7`), `/guide/` heading 'Reviews by brand' (`ee7838b`), lists + guides picks table (lists chat, `bb8072c`) |
| `live-20261118-080605` | `cebd1d7` | List picks button "Why we like it" (was "Why it won"); right-now intro wording to match (content) |
| `live-20261118-083347` | `1340c13` | Merchant logos Brooks, Mizuno, New Balance, Running Room (DFS step 8; new files only, `--no-purge`) |
| `live-20261118-101624` | `655a4e0` | Phone price bar design G (name + Jump to row, reading line, price + at shop, All N prices, See deal; No local price + Search Amazon); staging `staging-20261118-101054`; Ruggero: "make it go to staging, then deploy live once you did your checks" |
