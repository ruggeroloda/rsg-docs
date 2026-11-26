# Reskin — pre-go-live checklist (started 15 Nov 2026)

_**Live since 15 Nov 2026 14:30 UTC** (`4ac5ef4`, tag `live-20261115-143049`; Ruggero: "deploy to live"). Plan, results, rollback: `GO-LIVE-PLAN-2026-11-15.md`. [x] done, [~] moved to after go-live (Ruggero's go/no-go, 15 Nov), [ ] still open. Project copy: `theme/RESKIN-GO-LIVE-CHECKLIST.md`._

## Content / SEO
- [~] **Menu links cleanup** (Ruggero, 15 Nov): since step 2 the mega-menu is printed in every page, so all its links are sitewide links Google now sees (before, panels loaded on hover and Googlebot never saw them). 46 of the 95 targets are new to Google's view of the menu: brand guides, 2018/2019 posts, "2024" guides, old watches (Suunto Ambit 3, Forerunner 645 …). Draft a keep/cut/replace list → Ruggero decides → edit the mega menus (content, not code). — **after go-live** (editorial keep/cut; all 92 header/footer links are 200 + indexable + self-canonical except `/privacy-policy/` noindex on purpose and `/reviews/gps/`, see below)
- [x] **Menu links that redirect (301)** → point to the final URLs: `/gps/garmin-forerunner-265/` → `/reviews/gps/garmin-forerunner-265/`, `/gps/coros-pace-3/` → `/reviews/gps/coros-pace-3/`, `/gps/huawei-gt-3-review/` → `/reviews/gps/huawei-gt-3/`, `/content/best-gps-running-watch-guide/` → `/guide/best-gps-running-watches/`. Re-crawl the menu links after the edit: all 200, no noindex, self-canonical. — **15 Nov 12:22 UTC**, live + staging data: the 3 GPS links point at `/reviews/gps/…` (the 4th was already gone); backup `~/rsg_deploy_backups/menu-links-20261115/`. Found: header "GPS" → `/reviews/gps/` shows all shoe reviews with canonical `/reviews/` (also 7 redirects) → GPS listing after go-live
- [x] robots.txt `Sitemap:` line (server file) at go-live. — 15 Nov 14:36 UTC: `Sitemap: https://www.runningshoesguru.com/wp-sitemap.xml`
- [x] `checks/extractcheck.py` live (after) vs live (before) on the 5 sample reviews: no new trafilatura MISSING items. — 15 Nov: 16 content pages, live after = staging; no text lost (comparisons 0/4 → 10/10)
- [x] Schema snapshot before/after (`checks/schema_snap.py`) on the sample pages: unchanged except intended. — 15 Nov: 21 pages, only intended changes; canonical/robots/titles unchanged

## Ads / layout
- [~] Raptive confirms the re-map to the new markers (Ruggero sends Raptive the staging link + note). — **after go-live** (old structure kept, ads place as before)
- [x] Final ad counts (`checks/adcount.py`) on the sample reviews, comparison, guide, blog post (only at the end, Ruggero 14 Nov). — 15 Nov: staging = live after (Pegasus 42 10/11, Clifton vs Bondi 5/11, Pegasus 39 12/13, Nimbus 26 article 4/5, beginners guide 4/10 phone/desktop); before: 9/9, 7/7, 12/12, 4/5, 4/10
- [ ] Ruggero: phone price bar on a real phone with ads (nothing from Raptive at the top over the bar). — Ruggero, on live (phone check in `GO-LIVE-PLAN-2026-11-15.md`)
- [~] Price card 288 px (last pass, item 4 skipped 14 Nov). — **after go-live**
- [~] **Click spots + Amazon IDs** (`archive/CLICK-SPOTS-BRIEF-2026-11-15.md`): built on staging, one click per spot × device checked in the log; the 5 new Amazon US tracking IDs exist (4 reused); the box table `ALTER` runs on live with the deploy. — **after go-live, own deploy** (Ruggero 15 Nov)

## Performance / caches
- [x] CLS + LCP on live after the deploy (`checks/cwv.py`, `checks/shots.py`); compare with the 13 Nov baseline. — 15 Nov 14:50 UTC, 12 pages × 390/1366: CLS 0–0.009 (baseline worst 0.31 Pegasus listing, now 0), lab LCP 0.62–1.29 s (baseline 0.54–1.97 s)
- [x] New CSS rules present in the used CSS (Remove Unused CSS) of every page type; stale used-CSS rows deleted after the deploy (RUNBOOK, Caches). — 15 Nov: fresh rows on every sample page (staging + live), `:has()` rule kept; 0 rows older than the deploy
- [x] Shop thumbnails at small sizes (audit B1) — if done before go-live. — done 15 Nov by the DFS chat (192×144 WebP from our server)

## Comparisons (added 15 Nov, comparison reskin C1–C6)
- [x] After the live deploy: share images on live — `wp rsg-share build --all --dry-run` (expect ~173 build, 0 skip), then `wp rsg-share build --all`, then `wp cdn purge`. Writes the database (featured images) and uploads (`uploads/rsg-share/`): part of Ruggero's "deploy to live", not of `deploy.sh`. Undo: `wp rsg-share restore --all`. — 15 Nov 14:35 UTC: dry run 173, built 173, purged
- [x] `checks/extractcheck.py` live (after) vs live (before) on Clifton vs Bondi, Ghost vs Adrenaline, Pegasus vs Clifton: no new trafilatura MISSING items (staging 15 Nov: 10/10 items on all three). — 10/10 on all three
- [~] Raptive markers for comparisons with the ads work: main content `rsg-content` is there; a text marker must not contain "text" or "body" (readability then keeps only the prose, tested 15 Nov). — with the Raptive work, after go-live

## Old reviews + articles (added 15 Nov, `content/archive/POSTS-CONTENT-INVENTORY-2026-11-15.md`)
- [x] Old-review template (`include-single-review.php`, 937 dated reviews, 10% of views) was not in the sample set: add `/2022/05/nike-air-zoom-pegasus-39-review/` and `/review/salomon-speedcross-5-review/` to the screenshot/CWV/ad-count/extract checks. — in the final checks 15 Nov (Pegasus 39, Speedcross 5)
- [x] "New version" notice (theme `85ca6d3`, `4fa27fa`, `0afcd73`, staging): after the live deploy, Pegasus 39 → Pegasus 42, Adrenaline 15 → GTS 25, Speedcross 5 → Speedcross 6 on live. — 15 Nov on live: Pegasus 39 → Pegasus 42, Adrenaline GTS 15 → GTS 25, Speedcross 5 → Speedcross 6, each with a price
- [x] Mac repo: `main` is behind GitHub (`0afcd73`): fast-forward before the next commit/deploy. — 15 Nov: fast-forwarded; Mac = GitHub = `4ac5ef4`

## Byline v3, author schema, "How we review" (added 15 Nov)
- [x] **About page on live** (content, not deploy.sh): with the theme deploy, `wp post get 116 --field=post_content > before.html` (backup to `~/rsg_deploy_backups/`), then `python3 content/about-2/update.py before.html new.html` and `wp post update 116 new.html`. Adds `#how-we-review` (target of every byline "How we review" link), current numbers, encoded email. Staging already updated 15 Nov (backup `~/rsg_deploy_backups/about-2-page116-staging-before-20261115.html`). — 15 Nov 14:33 UTC (backup `~/rsg_deploy_backups/golive-20261115/about-116-before.html`)
- [ ] Rich Results test (live URL) on one review, one comparison and one author page: Person/ProfilePage/Article valid, `sameAs` present for writers with links.
- [x] Writer profile links (rsg_* user meta) are already on live (15 Nov); fields appear in Users → Edit → Contact Info once rsg-core 0.5.1 is live. Missing writers: reminder set for 15 Dec. — rsg-core 0.5.1 live
- [~] After go-live (not a blocker): **C2** — hero (h1 + summary/subtitle) inside the article block, one content container, so extractors keep the comparison subtitle (lost since 15 Nov) and the h1; check Raptive positions (`HTML-AUDIT-RESKIN-2026-11-15.md`). — after go-live
