# Reskin go-live plan — 15 Nov 2026

_Written after the final staging checks (15 Nov, 13:20–14:45 UTC). Runs only after Ruggero writes **"deploy to live"**. Rollback tested. Project copy: `theme/GO-LIVE-PLAN-2026-11-15.md`._

## What goes live
- **Commit `4ac5ef4`** (= GitHub `main` = staging = last staging tag `staging-20261115-131737`). Live today: `5d3118c` (tag `live-20261115-103600`).
- `deploy.sh live --dry-run`: lint OK (98 PHP files), **52 changes**: rsg-core plugin 0.4.0 → 0.5.1 (`people.php`, `share-image.php`, fonts, `ads.php`) + theme (templates, CSS, fonts, images; `includes/comparison-redesign-block.php` removed).
- Not in it: click spots + Amazon IDs (tomorrow, own deploy), GPS listing at `/reviews/gps/` (after go-live).

## Final checks on staging (done)
| Check | Result |
|---|---|
| 21 pages × 390/1366 px, staging vs live (reviews ×4, GPS new + old, old review, legacy trail, 3 articles, guide, hub, 3 comparisons, home, listings, author, About) | All 200, no page-level overflow, no new JS errors (only "failed to find stick parent" on GPS, also on live). "Broken images" = edge rate limit during the run; every one loads 200 when fetched alone |
| Screenshots looked at | Pegasus 42 (desktop + phone), Pegasus 39 newer-version card with price, Structure Plus article + "Shoes in this article", Clifton vs Bondi, home, Forerunner 165 phone: as designed |
| Money links | Price boxes render; staging prices = live (429 shoes, same checksum); every Amazon link `tag=rsg-single-20` |
| Structured data (21 pages) | Only intended changes: new JSON-LD on old reviews (Product + Review /10), legacy `/review/`, articles (BlogPosting/Article), Article on comparisons, ProfilePage on authors, richer Person (jobTitle, image, sameAs). Canonical, robots, titles unchanged everywhere. No parse errors |
| Text extraction (16 content pages) | No text lost vs live. Comparisons 0/4 → 10/10 items; articles = live; reviews ±1 item (different item sets, accepted in the 15 Nov audit) |
| PHP log | 0 warning signatures on staging that live doesn't already have; 0 fatals on staging today |
| Used CSS (21 pages) | Fresh rows (all after the deploy), the `:has()` rule kept, no needed rule missing |
| Ad slots (phone / desktop, staging vs live) | Pegasus 42 10/9 · 11/9; Cascadia 19 13/11 · 13/12; Nano X4 10/9 · 11/9; Forerunner 165 8/8 · 9/7; Pegasus 39 12/12 · 13/12; Nimbus 26 article 4/4 · 5/5; beginners guide 4/4 · 10/10; comparisons phone 5/7, 5/4, 5/6 · desktop 11/7, 10/5, 10/7 (sidebar) |
| Plugin/theme switch window | No function clashes: new rsg-core runs with the old theme (deploy order) and old theme with new rsg-core (rollback order) |

## Steps (Claude runs them; ~45 min)
1. **Pre-flight (2 min):** staging = `4ac5ef4` = GitHub `main` = last staging tag; no other chat deploying. Fresh snapshot of the live theme + rsg-core → `~/rsg_deploy_backups/golive-20261115/snapshot-before-reskin.tgz`; live DB export (`db-before.sql.gz`); About page 116 content (`about-116-before.html`); `robots.txt` copy.
2. **Deploy (3 min):** `echo yes | ./deploy.sh live` from a clean worktree at `4ac5ef4`: lint, rsg-core then theme, tag `live-<stamp>` (pushed), WP Rocket minified + used CSS cleared, edge purged.
3. **Data steps (10 min):**
   - About page: `update.py` on live's content (tested: output = staging's page), `wp post update 116`. Adds `#how-we-review`, the target of every byline link.
   - Share images: `wp rsg-share build --all --dry-run` (expect ~173), then `build --all`, then `wp cdn purge`.
   - `robots.txt`: add `Sitemap: https://www.runningshoesguru.com/wp-sitemap.xml`.
   - Used CSS: delete rows submitted before the deploy, run the queue, warm the sample pages (1 request/s).
4. **Checks on live (20–30 min):** the 21 pages at 390/1366 (status, JS errors, screenshots); newer-version notice on Pegasus 39 → Pegasus 42, Adrenaline 15 → GTS 25, Speedcross 5 → Speedcross 6; prices + Amazon tags; schema and extraction vs this afternoon's live snapshots (only the intended changes); PHP log (no new fatals or warning types); healthchecks `rsg-site` / `rsg-php` green; CLS/LCP vs the 13 Nov baseline; ad counts once.
5. **Docs:** checklist ticked, RUNBOOK "What runs where", DECISIONS line, status table, after-go-live watch list.

Note: a deploy copies files over a few seconds; a request in that window can hit a half-copied theme and log one fatal, which turns `rsg-php` red for 60 min. If that happens I'll say so; it is not a site problem.

## Rollback (< 2 min, tested)
If something is badly wrong after the deploy:
1. Only if step 3 ran the share images: `cd ~/public_html && wp rsg-share restore --all` (needs rsg-core 0.5, so before step 2 here).
2. `~/rsg_deploy_tmp/rollback-reskin-20261115.sh --apply`: puts back the exact theme + rsg-core files from the pre-deploy snapshot (theme first, then plugin), clears WP Rocket minified + used CSS, purges. Tested 15 Nov: applied to a copy of the reskin it gives a byte-identical copy of today's live files.
3. Optional: About page back with `wp post update 116 ~/rsg_deploy_backups/golive-20261115/about-116-before.html` (the new text works with the old theme too). The robots.txt line can stay.
4. Proof: `deploy.sh live --dry-run` of `5d3118c` shows no changes.

Not undone by the rollback (all live already and work with the old theme): today's data fixes from other chats (redirects, broken links, Speedgoat, previous/next model fields) and the 3 GPS menu links.

## Ruggero's phone check after go-live (5 min)
1. Pegasus 42 review: scroll down → navy price bar at the top; tap "N shops" (sheet opens), "Sections" (jumps). **Do Raptive ads sit over the bar?**
2. Tap a "See deal": the shop opens.
3. Clifton vs Bondi: photo band, comparison table scrolls sideways.
4. Pegasus 39 (old review): "Newer version: Pegasus 42" card with a price.
5. An article (e.g. Nike Structure Plus first look): "Shoes in this article" box.
6. Menu (hamburger) and search. On a laptop: sidebar price card + "Compare N shops" drawer.

## Known and accepted
- About one more ad slot on reviews; comparisons phone −2 to +1, desktop +3 to +5 (sidebar).
- Header "GPS" link and 7 GPS redirects land on `/reviews/gps/` (= all shoe reviews, canonical `/reviews/`): live since this morning; GPS listing after go-live.
- Comparison subtitle not kept by text extractors until C2 (after go-live).
- Pre-existing, not from the reskin: homepage 11 h1s, legacy trail reviews 13–14 h1s, GPS reviews' "All running shoes Reviews" back link, related-review cards use full-size cut-out PNGs (400–580 KB each, lazy-loaded).
