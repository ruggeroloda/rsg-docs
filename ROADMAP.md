# Roadmap

_Open work only, in priority order. Done items leave this file (they are in `DECISIONS.md`; older done lists: `_archive/ROADMAP-until-2026-11-11.md`). Last updated 2026-11-19 19:30 UTC (Raptive: listings sticky sidebar on and checked; old-review selectors updated, 1 sidebar ad there)._

## Dated, up to Black Friday (Fri 27 Nov; Cyber Monday 30 Nov)
| When | Item | Area | Next step |
|---|---|---|---|
| now | **Phase F: retire the old DFS** (planned from ~17 Nov) | dfs / hosting | `dfs/PLAN-STEP7.md`: no callers 7 days, `/go/` stub, strip `[ds]` from 76 old posts, deactivate `dstudio` (+ its 2 cron hooks), power off 1 week, cancel ($29/mo); Linode Managed monitor + `dfs-mon` off; `uploads/dfs-images-cache` (498 MB) after |
| now | **PHP warnings cleanup**: fatals solved 18 Nov (0 real since 15 Nov); 394 distinct warnings left in ~25 theme files | theme | Fix the ~100 'read on null/false' first, then the rest, one template per batch on staging with byte-identical HTML; timing vs Black Friday to decide (Ruggero) |
| now | **DFS fresh price check** (Ruggero 18 Nov: last check 10–11 Nov) | dfs | Old vs new on ~30 shoes, links land, per-country offers; merchant moves are in the DFS chat |
| now | **Australia: running/sport retailers** (Ruggero 18 Nov) | dfs | List AU running retailers, then their affiliate programs (Nike AU, Sportitude, The Iconic are dead ends) |
| ~18 Nov | Amazon slot review | dfs | "real N" per slot → drop the weakest slot or keep 4 (dropping 08:00 delays the morning feed changes to ~14:15) |
| ~22 Nov | Reskin 7-day watch ends | theme | Search Console, clicks per 1,000 views, Raptive RPM (`theme/AFTER-GO-LIVE-2026-11-15.md`) |
| ~25 Nov | Phone price bar G + review top on phones: 7-day watch (bar from 18 Nov 10:16 UTC, review top from 18:03 UTC: read together) | theme | `stick` clicks per view and drawer opens (Umami `shop_click`, `drawer_open`) vs the week before; Raptive RPM on reviews; `gallery_swipe` per phone review view (`theme/PHONE-BAR-G-2026-11-18.md`, `theme/REVIEW-TOP-PHONE-2026-11-18.md`). If numbers drop: revert the review top first |
| ~25 Nov | Lists/guides picks table: 7-day watch | theme | Raptive RPM and ads per view on the 5 lists (2 top ad candidates fewer), `header_card` clicks per view vs before (`theme/LISTS-TOP-2026-11-18.md`) |
| ~2 Dec | Lists/guides new shoe box (live 18 Nov 17:31 UTC, all 5 lists): watch | theme | Search Console position + clicks of the 5 lists vs the 2 weeks before; "Read the full review" clicks from lists (`theme/LISTS-TOP-2026-11-18.md`) |
| ~26 Nov | **Site search: 7-day watch** (live 19 Nov 15:25 UTC) | theme | Umami `search_result` outcomes (none/nothing share; was 51 % zero results), searches per visit, review opens after a search; top `not_tested` lines = review backlog (Novablast 6, Endorphin Azura, Cloudmonster 3, Hyperboost, 1080 v15, Superblast 3, Triumph 24, Kayano 33). Then decide typo tolerance (`theme/SITE-SEARCH-ROUTING-2026-11-19.md`) |
| ~3 Dec | **Listings reskin: watch** (live 19 Nov; Raptive sticky sidebar on from 19 Nov ~18:00 UTC) | theme | Same table as the baseline (review opens 17 %, another listing 41 %, leave 36 %, shop click 1.1 %) + Raptive listing RPM after 2 weeks; if RPM drops without more review opens: ask Raptive for closer in-list ads (`theme/LISTINGS-RESKIN-2026-11-19.md`) |
| ~22 Nov | **Old-article prune (D5)** | content | `content/ARTICLES-PRUNE-PLAN-2026-11-15.md`; needs a fresh Search Console export. Decide whether it waits until after Black Friday |
| ~24 Nov | Homepage re-check (a week to breathe) | theme | Desktop landings in the markets leave without a 2nd page 56–58 % vs 43 % old (17–18 Nov). Shorter desktop hero tried on staging 19 Nov and rejected (Ruggero). Look at the desktop journey instead: old hero = two big buttons (all reviews / Shoe Finder), now small text links; Ruggero: phone visitors scroll more (`theme/HOMEPAGE-BUILD-2026-11-17.md` "Ruggero's notes"). Nothing changes before |
| ~24–25 Nov | **Old reviews: only 1 sidebar ad** (Speedgoat 5: 14,662 px sidebar column, 1 `Sidebar_9` slot after Raptive's 19 Nov selector update; listings get 2 in ~4,400 px) | ads | Ruggero's call whether to ask Raptive for Enhanced Sticky Sidebar on `rsg-page-review-old`; can go in the same mail as the homepage ask (`ads/BASELINE-2026-11.md` 19 Nov 17:57 entry) |
| ~24–25 Nov | **Homepage sticky ad: ask Raptive** (after the homepage re-check, in time for Black Friday prices) | ads | Ruggero's call; not mentioned to Raptive yet (`ads/BASELINE-2026-11.md` Open 2) |
| ~26 Nov | **Byline date on refreshed pages**: Google (AI Mode card, 19 Nov) shows Winflo vs Pegasus as "4 Apr 2020" although the page has updated 18 Nov 2026 in byline, schema `dateModified` and `article:modified_time` (URL submitted in Search Console 18 Nov) | theme | Check the 5 refreshed comparisons in Google. If still 2020: top byline (same height) = "Updated 18 Nov 2026 · Originally published Apr 2020" (site date format, not xx/xx/xxxx), the updated date links to the bottom author note; that note gets a one-line "What changed: …" from a new field, shown only when filled (Ruggero 19 Nov: no extra space at the top). Staging first, mobile + desktop; extractcheck. No changelog page |
| ~21 Nov | **Rocket.net answer on the 19 Nov server stall** (10:40–10:44 UTC, emailed 19 Nov) | hosting | Read the answer (`hosting/RUNBOOK.md` Open 7); if stalls repeat, send them the new times |
| 25 Nov | Rocket.net charges $1,000 (Business annual) | hosting | Check the invoice and the credit for the unused Expert time (`hosting/RUNBOOK.md` Open 1) |
| 26 Nov | Delete the site-pass backups on Rocket.net (`~/rsg_deploy_backups/site-cleanup-*-20261112-*`, ~500 MB) | hosting | — |
| 27–30 Nov | **Black Friday → Cyber Monday** | all | Freeze rule to decide (what may still go live that week) |
| ~30 Nov | Old click/view logs vs Umami: 2-week parallel run ends | analytics | Compare, then switch the old logs off or sample them |
| after 30 Nov | Geo / edge-share measurement (geo rule off since 11 Nov; moved by Ruggero 18 Nov: deploy week isn't normal traffic) | hosting / dfs | Rocket.net edge share vs baseline 68.6 % (`dfs/GO-LIVE-2026-11-10.md`); then page-view log sampled + retention |

## Now / next (not dated)
| # | Item | Area | Next step |
|---|---|---|---|
| 1 | **Rotate exposed keys**: AWS ×3, Facebook, Google, AWeber, ip-api (theme `SECRETS.md`), Rocket SSH key, MailerLite, MailerSend (one token per system), network logins in the DFS `.env` | all | Ruggero revokes at the providers; Claude swaps them on the servers (incl. the `.env.bak-*` copies) |
| 2 | **SEO checklist** (`theme/SEO-AUDIT-2026-11-17.md`, tracker sheet linked there) | theme / content | 2.1 outdated comparisons: #1–#5 live 18 Nov, max 5 a day, next #6 Cumulus vs Novablast (rewrite from the 3 reviews + verdict/"Buy if" box, staging → Ruggero reads → live; process in the audit doc "2.1"). 1.3 year in the best-list title still open (Ruggero) |
| 3 | After-go-live leftovers (`theme/AFTER-GO-LIVE-2026-11-15.md`) | theme | GPS listing at `/reviews/gps/`; mega-menu keep/cut (46 links); price card 288 px; related cards use full-size cut-out PNGs; Rich Results test on a live review/comparison/author page; C1/C2 now that Raptive re-mapped reviews + comparisons (16 Nov); front-end modernization (`theme/MODERN-WEB-GUIDANCE-2026-11-15.md`) |
| 5 | Bank import → self-hosted n8n, then cancel n8n Cloud (€24/mo) | automations | Needs the working flow export from n8n Cloud + what failed |
| 6 | Email list cleanup, then platform decision | email | Define active = opened/clicked in 12 months → re-engagement → remove. Sign-up mechanism to change (idea: monthly contest) |
| 7 | DFS simplification: test bench as a stopped copy of production, nightly settings dump to git, matcher fixture tests | dfs | — |

## Bigger stages
| # | Item | Area | Next step |
|---|---|---|---|
| 11 | **Theme rebuild, in place and incremental** — reskin live 15 Nov; homepage, `/top/`, guides, `/reviews/` hub rebuilt 17 Nov; list/guide top of page (picks table) 18 Nov | theme | Left: widgets (after Phase F), remaining `rsg-core` items (`theme/RSG-CORE-AUDIT-2026-11-11.md`; remove `RSG_get_country_code`), listings' intro texts, Shoe Finder (weak: improve a lot or keep low-key) |
| 12 | **Content & distribution restart** — the review-format research is also what the review template waits for | content | `content/README.md` |
| 13 | DFS step 8: more merchants. Done: AU (15 Nov), RW EU FR/IT/ES/IE/GR/SE + Nike IT, Impact US (Brooks, Mizuno, adidas, New Balance), Running Room CA (18 Nov) | dfs | **Next:** Asics US (Rakuten, old DFS feed 86, file `40996_2333397_mp.txt.gz`); later RW NZ/SG/MY/HK; SportsShoes UK waits for Visualsoft. Done 18 Nov: no local offer → "Check price" Amazon search in the visitor's own store (live). |
| 14 | WP Rocket page cache on, after the DFS/theme/migration work; the DFS purge must also clear it | theme / hosting | Measure TTFB on cache misses before/after |
| 15 | Analytics next steps | analytics | Umami 1-day flags from ~22 Nov, 7-day from ~7 Dec; outside data (networks, Amazon, Raptive) later; unattended analyst only when Ruggero asks (`analytics/README.md`) |

## Small / waiting
- **Alt-text test** (live 11 Nov): read Search Console for the 10 test vs 10 control reviews ~9 Dec (4 weeks) and ~23 Dec (6 weeks): `theme/ALT-TEXT-TEST-2026-11.md`. The result decides "AI alt text at upload".
- Network sub-ID checks (AWIN Click References, CJ/Rakuten, Impact `subId1`, AvantLink `ctc`) at the first commissions.
- Brooks US thumbnails: brooksrunning.com refuses server image requests (403), so Brooks offers show "no image" (live 18 Nov). Options if it matters: another image source per shoe, or ask Brooks/Impact.
- Review cut-out photos uploaded before 5 Nov were not cropped by Pixian (remove.bg era): list the uneven ones and re-crop (18 Nov: Adrenaline GTS 25 cropped by hand; Nimbus 28 has uneven margins).
- Vomero 18 review: list price field $155 vs its text $150 (Ruggero to confirm). Cumulus 28 review: heel 42 mm in the specs vs 38.5 mm in the text (left).
- List picks: "Compare N shops" drawer per pick (proposal on the design canvas, `theme/LISTS-TOP-2026-11-18.md`): waiting for Ruggero. NL/BE have no DFS shops (all Amazon "Check price"): Running Warehouse EU to NL/BE is a DFS item.
- List data (`theme/LISTS-TOP-2026-11-18.md` "Open"): Evo SL plate field, "Hoka One One" name, Metaspeed same link men/women, long list intros (Puma, heavy runners).
- **Phone price bar: show it later in the scroll** (Ruggero 18 Nov, idea only, not to act on yet): after the review-header change and the bar G watch, test switching the header to the price bar further down the page, with a small animation that draws attention. Measure against bar G as it is.
- "Check price" wording in the other price spots (phone block `mob`, sidebar card `rail`, drawer) vs "No local price" / "Search Amazon" in the phone bar: Ruggero to decide whether to align (`theme/PHONE-BAR-G-2026-11-18.md`).
- **Non-US clicks, low priority** (17 Nov, humans): US 84 shop clicks / 792 visits; GB 2 / 244, AU 2 / 175, CA 8 / 143. Ruggero 18 Nov: UK visitors always bought mostly at Amazon ("no Amazon, no sales") and the money comes from the US, so GB is not a gap and this is no longer a pre-Black Friday item. AU/CA only if a cheap cause shows up.
- Bots left after rules v3: CN/SG/HK visits with normal screens and > 0 s (~165/day, 0 clicks); a country rule is the next step if they matter (`analytics/README.md`).
- Theme backlog: verdict/"Buy if" box on the ~130 comparisons outside the 39 refreshes (decision 18 Nov), quiz results page + the price-drop-alert copy (build or cut), SEO backlog, local list prices (`theme/MSRP-LOCAL-PRICING-BRIEF.md`), orange-button contrast, shoe spec typos case by case.
- Make: merge the 3 insert scenarios (write via `acf`), port accounting, then cancel Make.
- Delete rollback copies on rsgmedia (~20 Nov); SiteGround DNS leftovers; old Drive folders `Backups/servers` + `Backups/sites` (after 11 Dec, keep `Backups/nas`).
- Close the remove.bg account (its API ends 1 Dec 2026).
- Umami admin password and the `rsg_analyst` password into 1Password, then delete `_private/umami-admin.txt` (`analytics/README.md` Open 5).
- Resubmit the sitemap in Search Console (Ruggero).
- Listings cleanup (Ruggero to OK): unused `print_review_block_2020()` + `.rsg-rc` CSS, template leftovers, keyboard-safe filter menus (`theme/LISTINGS-RESKIN-2026-11-19.md` Open).
- Comment notifications: reader reply emails only if comments matter after the content restart.
