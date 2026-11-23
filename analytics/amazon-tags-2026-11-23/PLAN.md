# Amazon US tracking IDs: one per page type (plan, 23 Nov 2026)

Status: **agreed by Ruggero 23 Nov; not built.** Ruggero 23 Nov: switch **now** (before Black Friday), not on 1 Dec. Target: staging 23–24 Nov, live on Ruggero's "deploy to live" by **Wed 25 Nov**. The post-link rewrite (part C) waits until after Cyber Monday (from 1 Dec).

## Why
Amazon hides a tracking ID's orders and earnings when it has fewer than **~4 items ordered** in the downloaded period (they move into the "Other" row). Checked on the 2025 monthly reports: the smallest shown was 4 items; hidden rows had up to 96 clicks. The 16 Nov per-spot review tags (revside, revmob, revbar, revdrw, revlow) are too small to show anything at ~300 items a month. Page type is also the unit of the strategy's revenue per 1,000 views. Spot detail stays in our click log and Umami (Ruggero: a per-spot money estimate is overkill; data is kept anyway).

## A. Tag history (US visitors only; other countries never had per-page tags)
| Period | Page type | Tag | 2025 clicks / earnings |
|---|---|---|---|
| until 10 Nov | road + training reviews | none → old DFS default `rsg-single-20` (`rsg-road-top/middle-20` were passed to the sidebar code but the prices were fetched without a tag, so they never reached a link) | 47.8k / $5,331 |
| until 10 Nov | trail reviews | `rsg-single-trail-20` | 905 / $56 |
| until 10 Nov | GPS reviews / GPS sections in lists | `rsg-gps-single-20` / `rsg-gps-20` | 775 / $0 shown |
| always | comparisons | `rsg-comp-20` (`single-comparison.php`) | 26.4k / $8,162 |
| until 10 Nov | best lists + guides (`single-best.php`) | `rsg-bestnew-20`, or per page via ACF `override_amazon_tag` (bestasics, bestheavy, bestwmns, bestmizuno, bestnewbal, besttrail, bestbrooks) | bestnew 929 / $101; the 7 per-list tags ~500 / $6 |
| until 8 Nov | shoe finder | `rsg-wizard-20` | 1,528 / $171 |
| — | review lists | `rsg-reviewlist-20` switched off in code → default | 42 |
| — | article shortcodes / old affiliate template | `rsg-bestof-20` (`rsg-bestold-20` A/B on post 16723) / `rsg-overview-20` | 0 |
| — | old short links in posts | `rsg-tube-20`, `rsg-gps-20`, `pimeagi-20` (see C) | tube 87, pimeagi 12.6k / $711 |
| 10–16 Nov | all DFS boxes | `rsg-single-20` (comparisons stay `rsg-comp-20`) | |
| since 16 Nov 09:25 UTC | reviews by spot; lists/guides/home/related/text links `rsg-bestof-20`; old reviews, GPS, articles default | rsg-core 0.6.0 `inc/tracking.php` | |

Sources: theme repo first commit `85a088b` (live theme 11 Nov), old DFS `dfs/legacy-snapshot/dfs-code-2026-11-04.tgz` (`import_amazon_creators_us.php` default tag, `override_tag` applied at click), Amazon monthly reports 2025 (Mac `data-drops/amazon/monthly/`).

## B. New map (Ruggero 23 Nov): the page decides the tag, never the spot
| Tag | Pages |
|---|---|
| `rsg-single-20` | new reviews (2023+ templates: road, trail, training; no trail/training split planned for 12 months) |
| `rsg-single-old-20` | old reviews (pre-2023 templates) |
| `rsg-gps-single-20` | GPS reviews, old and new, incl. the "Check price on Amazon" button |
| `rsg-comp-20` | comparisons |
| `rsg-bestnew-20` | top lists (`/top/`) |
| `rsg-guide-20` | brand guides (`/guide/`) |
| `rsg-reviewlist-20` | `/reviews/` hub, brand / family / type listings |
| `rsg-home-20` | homepage |
| `rsg-article-20` | articles |
| `rsg-wizard-20` | shoe finder |
| `rsg-other-20` | everything else (search, archives, 404, pages) |

All 11 exist in Associates Central (Ruggero, screenshot 23 Nov). Retired: revside, revmob, revbar, revdrw, revlow, bestof. No per-list tags. Non-US Amazon stores unchanged (`runshogur-21`, `runshogur01-21`, `runningsho053-20`). Typo ID `rag-bestold-20` exists, unused (Ruggero may remove it in Associates Central).

## Build steps (theme repo `theme/rsg_2022`, staging first)
1. `rsg-core/inc/tracking.php`: `rsg_track_amazon_tags()` keys = the 11 groups above (option `rsg_amazon_us_tags` still overrides; **check the live and staging option values first**, they may hold the old keys); `rsg_track_amazon_tag()` = page type → tag only, no spot map.
2. Check `rsg_track_page_type()` really separates: `/top/` vs `/guide/`, new vs old review templates, GPS old + new, articles, listings, home, shoe finder; map anything unknown to `other`. Verify on real pages, don't assume.
3. Every other Amazon US link must take the page's tag: GPS button (`includes/rsg-gps-amazon.php`, US `rsg-single-20`), "Check price" search fallback (`includes/rsg-dfs-prices.php` ~line 174) and its JS twin (`js/rsg-dfs.js` lines ~47/75), in-content `data-amz-k` links, `includes/shortcodes.php` (`rsg-bestof-20` ~32, ~712), `includes/widgets/widgets.php` (`rsg-reviewlist-20` ~2351, `rsg-wizard-20` ~2875), `single-comparison.php` (~159), and the **"no offer" fallback** `get_not_found_link()` (functions.php ~1327; ACF option `not_found_links`, US row = `amzn.to/3RQo9IO` → `pimeagi-20`): US → Amazon search with the page's tag. `git grep "rsg-single-20\|-20'" ` before and after.
4. Staging check, US visitor, one page each: review (sidebar price card, phone price block, sticky bar, drawer, lower list), old review, GPS review button, comparison, top list, guide, review list, homepage picks, article shoes box, shoe finder, a search page; plus a no-offer shoe ("Check price"). Every Amazon US `tag=` must match the table. No JS errors; click-log and Umami `shop_click` still arrive.
5. Show Ruggero what changed (by what he sees on the page), then "deploy to live". After live: same spot check on live, `rsg.change_log` row, theme `RUNBOOK.md` + `logs/DEPLOYS.md`, `analytics/README.md` tag line, `DECISIONS.md`, `theme/CLICK-SPOTS-BRIEF-2026-11-15.md` (tag section superseded).
6. Next day: the new IDs appear in Amazon's Tracking-Id report for that day; `pimeagi-20` clicks should drop once part C is done.

## C. Untagged links (read-only scan 23 Nov; files in this folder)
- `amz-links.csv`: every Amazon link in post text (post id, type, status, host, tag, url, title): 521 links in 82 posts.
- `amzn-to-resolved.csv`: the 296 unique `amzn.to` short links resolved: `pimeagi-20` 231, `rsg-tube-20` 21, `rsg-gps-20` 18, `runshogur-21` 14, `rsg-bestof-20` 5, `rsg-single-20` 4, none 2, `pimeagi-2` 1.
- 65 posts carry 409 links with `pimeagi-20`, old tags or none (39 posts, 20 content, 3 noindex, 3 pages; mostly 2013–2023: GPS lists, headphones, bras, Prime Day/Black Friday 2023). ~640 views in 30 days together. Our click log: ~14 "Amazon (short link)" clicks a week vs 22–57 `pimeagi-20` clicks a week in Amazon → the rest is likely the US "no offer" fallback (step 3) and links outside the site (YouTube, old emails), which can't be fixed.
- Also in ACF fields: 45020 Casio G-SHOCK (GPS), 24177 Polar Vantage M, 44555 UA HOVR Turbulence 2, 27459 + 27874 + 48197 (`purchased_from_url`).
- **After Cyber Monday:** rewrite each to `https://www.amazon.com/dp/<ASIN>?tag=<page tag>` (UK `.co.uk` + `runshogur-21` where the short link pointed there) by SQL, backup first, modified dates unchanged (same method as 13 Nov). Open question to Ruggero: all 65 posts, or only those not on the prune list (`content/PRUNE-ANALYSIS-2026-11-20.md`)?
