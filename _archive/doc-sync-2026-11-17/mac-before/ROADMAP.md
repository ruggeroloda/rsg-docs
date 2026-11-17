# Roadmap

_Open work only, in priority order. Done items leave this file (they are in `DECISIONS.md`; the old done list: `_archive/ROADMAP-until-2026-11-11.md`). Last updated 2026-11-12 (night)._

## Now / next
| # | Item | Area | Value | Next step |
|---|---|---|---|---|
| 1 | **Rocket.net → Business yearly ($83/mo, no storage add-on)** | hosting | ~$287/mo | Lean staging done (11 Nov, ~27 GB used); plan mail sent 11 Nov (ticket 122395): wait for the reply, then update costs |
| 2 | ~~Lean staging~~ | hosting | — | Done 11 Nov |
| 3 | Geo measurement after the geo rule went off (11 Nov) | hosting / dfs | Confirms the cache change | Box country + clicks checked 12 Nov: unchanged vs baseline, 0 wrong boxes (`dfs/GO-LIVE-2026-11-10.md`). Left: Rocket.net edge share ~18 Nov (baseline 68.6 %); then page-view log sampled + retention (#9) |
| 4 | **Rotate exposed keys**: AWS ×3, Facebook, Google, AWeber, ip-api (theme `SECRETS.md`), Rocket SSH key, MailerLite, MailerSend (one token per system), network logins in the DFS `.env` | all | Security | Ruggero revokes at the providers; Claude swaps them on the servers (incl. the `.env.bak-*` copies) |
| 5 | **Phase F: retire the old DFS** | dfs / hosting | $29/mo | From ~17 Nov (`dfs/PLAN-STEP7.md`): no callers 7 days, `/go/` stub, deactivate `dstudio` (and its 2 cron hooks), power off 1 week, cancel; Linode Managed monitor + `dfs-mon` off; strip `[ds]` from 76 old posts before `dstudio` goes off (prints raw otherwise); `uploads/dfs-images-cache` (498 MB) after |
| 6 | Amazon slot review | dfs | Fewer full purges | ~18 Nov: "real N" per slot → drop the weakest slot or keep 4. Note: dropping 08:00 makes the morning feed changes (06:30–07:10) wait until ~14:15 instead of ~08:15 |
| 7 | Bank import → self-hosted n8n, then cancel n8n Cloud | automations | €24/mo | Needs the working flow export from n8n Cloud + what failed |
| 8 | Email list cleanup, then platform decision | email | Smaller bill, deliverability, API | Define active = opened/clicked in 12 months → re-engagement → remove |
| 9 | DFS simplification: test bench as a stopped copy of production, nightly settings dump to git, matcher fixture tests, page-view log sampled + retention | dfs | Simpler, safer changes | — |

## Bigger stages
| # | Item | Area | Next step |
|---|---|---|---|
| 11 | **Theme rebuild, in place and incremental**: cleanup (widgets after Phase F) → `rsg-core` (logic + field groups in code, rewritten where weak; remove `RSG_get_country_code`) → one review template → other page types; design after | theme | Security fixes, SEO batch, listing descriptions, `affiliate_product` erase live 12 Nov. `rsg-core` step 1a (plugin + content model) live 13 Nov. Step 1b (housekeeping module) live 13 Nov. 13 Nov live: Raptive ads module, S7/S8, dead code. Field logic (REST rule, options page, comparison fields) in `rsg-core` 13 Nov; field groups stay in SCF's database (decision 13 Nov). Native images/videos (no JS loader) and GPS template markup live 13 Nov. Next: the reskin (design direction A, self-hosted fonts); Link ID once Raptive answers. Plan: `theme/RSG-CORE-AUDIT-2026-11-11.md`, handover `theme/NEXT-SESSION-2026-11-12.md` |
| 12 | **Content & distribution restart** — the review-format research is also what the review template waits for | content | `content/README.md` |
| 13 | DFS step 8: more merchants (Asics US, Brooks/Mizuno via Impact, SportsShoes UK via Partnerize once Visualsoft fixes the feeds, CA; AU done 15 Nov) | dfs | Waiting for Visualsoft |
| 14 | WP Rocket page cache on, after the DFS/theme/migration work; the DFS purge must also clear it | theme / hosting | Measure TTFB on cache misses before/after |

## Small / waiting
- **Alt-text test** (live 11 Nov): read Search Console (Image + Web) for the 10 test vs 10 control reviews ~9 Dec (4 weeks) and ~23 Dec (6 weeks): `theme/ALT-TEXT-TEST-2026-11.md`. The result decides "AI alt text at upload" below.
- Machine-readability leftovers: shoe spec typos case by case (e.g. Nimbus 28 drop vs stack): theme `RUNBOOK.md` gap 14.
- Network sub-ID checks (AWIN Click References, CJ/Rakuten) at the first commissions.
- Theme backlog: comparison redesign batch 2 (waits for verdict copy), quiz results page + the price-drop-alert copy (build or cut), SEO backlog, local list prices (`theme/MSRP-LOCAL-PRICING-BRIEF.md`), AI alt text, orange-button contrast, comparison click tracking (`theme/CONVERSION-TRACKING-PHASE1-BRIEF.md`, plan not built).
- Make: merge the 3 insert scenarios (write via `acf`), port accounting, then cancel Make.
- Delete rollback copies on rsgmedia (~20 Nov); SiteGround DNS leftovers; old Drive folders `Backups/servers` + `Backups/sites` (after 11 Dec, keep `Backups/nas`).
- Site pass backups on Rocket.net (`~/rsg_deploy_backups/site-cleanup-*-20261112-*`, ~500 MB incl. photo-gallery and the comment-subscriber export): delete ~26 Nov.
- Comment notifications: reviewers still get WordPress core's new-comment email; readers no longer get reply emails (plugin removed 12 Nov) — a reply-only checkbox in `rsg-core` step 3 only if comments matter after the content restart.
- Resubmit the sitemap in Search Console (Ruggero; sitemaps changed 12 Nov).
- Footer/sidebar newsletter sign-up (none on the site besides the quiz) — decide.
