# Guides + top lists cleanup — 15 Nov 2026

_Status: **done on live + staging 15 Nov ~10:08–10:36 UTC** (data, plus a 2-file live hotfix at 10:36). Approved by Ruggero 15 Nov: trail → redirect; brand guides → brand review listings; apply blocks 2 + 3 and clean the header menu so only kept pages stay; homepage brand section → "Reviews by brand", surgically fixed on live; live vs git tags sorted out. Sources: live WordPress (WP-CLI), redirect option `301_redirects`, live HTTP checks (≤1 req/s), GSC export "Performance on Search" last 16 months, filter `/best/|/guide/|/top/` (14 Jul 2025 – 13 Nov 2026, page-level clicks, `#anchor` rows ignored)._

## Context (Ruggero, 15 Nov)
- ~2016: #1 for "best running shoes (for X)" with thin 5-shoe Amazon-link guides. Sept 2023 HCU: `/guide/` stopped ranking; reviews/comparisons held. `/top/` created to move away; expanded guides never recovered.
- Content restart: ~18 reviewed shoes/year, mostly daily trainers → "best" pages only from shoes actually tested; brand guides become lineup explainers (Puma page = template); homepage "best right now" teaser → hub article.
- Direction: hub (`/top/best-running-shoes-right-now/`) + a few spokes, only where ≥4–5 tested shoes fit; keep URLs with history.

## Result
| ID | URL | GSC clicks 16 mo | Now |
|---|---|---|---|
| 48397 | /top/what-are-the-best-daily-trainers-right-now/ | 5,892 | **Kept** (flagship; now in menu + homepage) |
| 49387 | /top/puma-running-shoes/ | 2,444 | **Kept** (lineup-explainer template) |
| 44856 | /guide/best-running-shoes-for-heavy-runners-2024/ | 1,290 | **Kept** (spoke) |
| 48102 | /top/best-running-shoes-right-now/ | 879 | **Kept** = the hub |
| 29632 | /guide/best-running-shoes-for-beginners/ | 691 | **Kept** (spoke) |
| 24193 | /guide/best-asics-running-shoes/ | 354 | draft + 301 → /reviews/asics/all/ |
| 24717 | /guide/best-hoka-running-shoes/ | 353 | draft + 301 → /reviews/hoka-one-one/all/ |
| 45131 | /guide/best-trail-running-shoes-2024/ | 309 | draft + 301 → /reviews/trail/ |
| 24639 | /guide/best-mizuno-running-shoes/ | 192 | draft + 301 → /reviews/mizuno/all/ |
| 44787 | /guide/best-running-shoes-women-2024/ | 144 | draft + 301 → hub |
| 24048 | /guide/best-nike-running-shoes/ | 122 | draft + 301 → /reviews/nike/all/ |
| 24682 | /guide/best-new-balance-running-shoes/ | 122 | draft + 301 → /reviews/new-balance/all/ |
| 24179 | /guide/best-adidas-running-shoes/ | 106 | draft + 301 → /reviews/adidas/all/ |
| 41934 | /guide/best-on-running-shoes/ | 105 | draft + 301 → /reviews/on-running/all/ |
| 24448 | /guide/best-saucony-running-shoes/ | 93 | draft + 301 → /reviews/saucony/all/ |
| 45408 | /guide/best-puma-running-shoes/ | 63 | draft + 301 → /top/puma-running-shoes/ |
| 24456 | /guide/best-brooks-running-shoes/ | 31 | draft + 301 → /reviews/brooks/all/ |
| 24814 | /guide/best-gps-running-watches/ | 29 | draft + 301 → /reviews/gps/ |
| 44781, 47749 | drafts "best-running-shoes-2024", "test new guide" | – | trashed |

Retired guides keep their content (draft). When a brand lineup explainer is written, repoint that brand's rules to it.

## Redirect table: 147 → 156 rules (60 changes, same on staging with its own host)
- New rule for each of the 13 retired pages.
- Every rule that pointed at a retired page now points at its final destination (no chains; checked by the script).
- 12 old generic "best running shoes 2013/2014/2019/cheap/cushioned/stability/marathon" rules: beginners → hub.
- Removed 5 off-topic rules (crossfit, weightlifting, HIIT, walking, cross-training/gym) → 404.
- Broken absolute-URL rule `/best/best-nike-running-shoes-2024/` stored as a path → /reviews/nike/all/; new `/best/` → hub.
- Check 15 Nov: all 96 guide/brand-related sources 301 → 200 in one hop; 5 kept pages + home, /reviews/, /reviews/gps/, /reviews/trail/, /guide/, /top/, /comparison/ = 200; guide sitemap lists only heavy runners + beginners; top sitemap the 3 /top/ pages.

## Menus (live + staging data)
- Header "Best" → hub (was `/guide/` archive); "GPS" → `/reviews/gps/` (was a content page that redirected to the GPS guide).
- Mega menu 15116 "Buying Guides": column 1 = Best Running Shoes (hub), Best Daily Trainers, for Heavy Runners, for Beginners (removed women + 3 empty rows that linked to the current page); column 3 "Brand Guides" (8 brand guides) → "Brand Lineups" = Puma Running Shoes Explained. Column 2 unchanged.
- Homepage guides menu 741 → hub, daily trainers, heavy runners, beginners, Puma lineup (was draft 2024 guide, women, heavy, GPS, trail).
- Live + staging homepage/header now link only to the 5 kept pages.

## Incident: brand listings HTTP 500, 15 Nov 10:08:57–10:11:58 UTC
Brand listings feature a post set in term option `rsg_taxonomy_<term_id>['custom_term_meta']` (9 pointed at the brand guides). With those guides in draft, `reviews_search.php:632` assigns to `$query_brand->posts[0]` = null → fatal on `/reviews/<brand>/all/`. healthchecks `rsg-site` + `rsg-php` DOWN mails 10:10:33 UTC. Fix (data, live + staging): cleared `custom_term_meta` on the 9 options (Puma → /top/puma-running-shoes/ and Altra → a published content page kept). Old values: `~/rsg_deploy_backups/guides-cleanup-20261115/term-options-{live,staging}.txt`. 0 fatals after 10:12 UTC; `rsg-php` clears 60 min after the last fatal (~11:12 UTC). Side effect: the homepage "Brands buying guides" cards read the same option → all 8 linked to an old 2014 GPS post until the 10:36 hotfix. Lesson: before unpublishing anything, search options + meta for its ID.

## Backups
`~/rsg_deploy_backups/guides-cleanup-20261115/` on Rocket.net: `301_redirects-{live,staging}.json`, `db-{live,staging}.sql.gz` (posts, postmeta, term tables before the change), `term-options-*.txt`, the script `guides-cleanup-20261115.php`. Hotfix originals: `~/rsg_deploy_backups/live-hotfix-guides-20261115/`.

## Theme follow-up (staging 15 Nov; on live by hand 15 Nov 10:36 UTC)
- `d3ef666`: null guard at `reviews_search.php:632` (tested on staging with Nike pointed at the drafted guide: 200, no fatal); `/reviews/` intro link → hub (was the retired 2024 guide = 404); homepage "+ View all guides" → hub.
- `8fee4dd`: homepage "Brands buying guides" → **"Reviews by brand"**: the 8 cards link to `/reviews/<brand>/all/` (`get_term_link`), "See the reviews"; bottom button "+ All reviews" → `/reviews/`. On live since 10:36 UTC (Ruggero: "surgically fix on live"): only `template-home-2020.php` + `reviews_search.php` copied by hand (both live files were byte-identical to the base before; the files are the same as in `d3ef666`/`8fee4dd`). Originals: `~/rsg_deploy_backups/live-hotfix-guides-20261115/`. Checked: homepage cards → `/reviews/<brand>/all/`, brand listings 200, `/reviews/` intro → hub, 0 fatals.
- **Live vs tag (sorted out 15 Nov):** the latest tag `live-20261114-111243` was the reskin step-1 deploy that went to live by mistake on 14 Nov and was rolled back by file copy at 11:29 UTC (DECISIONS 14 Nov), so it never described live. What is on live = `5d3118c` (tag `live-20261115-103600`, branch `live-actual-20261115`) = `7462d98` + `d3ef666` + `8fee4dd` + AU logos `9c0444a`; `deploy.sh live --dry-run` of it = no changes (theme + rsg-core). Old tag renamed `rolled-back-live-20261114-111243`; my first hotfix branch/tag (built on the wrong tag) deleted. All four commits are on `main`, so the next normal live deploy keeps them. `theme/RUNBOOK.md` "What runs where" updated.

## Still open
1. "More than 120 reviews a year" in the mega menu (`includes/mega_menu.php`) and "120+ a year" in the daily-trainers + heavy-runners intros — left for later (Ruggero 15 Nov).
2. Unused theme option `options_3rd_block` = 24048 (old Nike guide); read by no template.
3. Content: refresh picks on the kept pages when 2026 reviews exist.
