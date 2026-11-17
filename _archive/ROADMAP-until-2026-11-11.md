# Roadmap

_Ordered by saving vs effort. Update status as things move. Last updated 2026-11-11._

## Open
| # | Item | Workstream | Saving / value | Effort | Status | Next step |
|---|---|---|---|---|---|---|
| 1 | Rocket.net: custom Expert (~$370) → **Business yearly + 15 GB ($113/mo)**, standard cache | hosting | ~$257/mo | Low | Agreed in principle 10 Nov (ticket 122395); **geo rule switched off 11 Nov ~08:45 UTC** (measuring vs baseline, `dfs/GO-LIVE-2026-11-10.md`) | Decide lean staging (item 16) → send the Business plan mail; check the geo measurement after a few days |
| 2 | Retire the old DFS (step 7 Phase F) | dfs / hosting | Linode ~$29/mo | Low | Old DFS no longer feeds the review boxes since 10 Nov; Linode still running as rollback | From ~17 Nov: confirm no callers for 7 days, `/go/` stub live, deactivate dstudio, power off (1 week), cancel; remove Linode Managed monitor + `dfs-mon`; rotate network logins + MailerSend token (`dfs/PLAN-STEP7.md` Phase F) |
| 3 | DFS step 8: more merchants | dfs | Revenue (8 of 15 "new higher" prices come from missing merchants) | Medium | Not started; SportsShoes (Partnerize) feeds broken, Ruggero emailed Visualsoft 10 Nov (no reply yet) | Asics US, Brooks/Mizuno (Impact), SportsShoes UK, CA/AU merchants |
| 4 | ~~DFS step 7b: GPS US prices from the stored ASINs~~ **Dropped 11 Nov** unless click data argues for it | dfs / theme | Black Friday deal traffic | Low–medium | GPS has the Amazon button (live 10 Nov) | After Phase F |
| 5 | DFS after go-live checks | dfs | Correct attribution | Low | Waiting for first commissions | AWIN Click References + CJ/Rakuten sub-IDs |
| 6 | Automations: bank import off paid n8n Cloud → cancel it; later leave Make | automations | €24/mo n8n Cloud + ~$10/mo Make | Medium | Hetzner n8n backed up, patched, secured (5–6 Nov) | Bank import move (step 4): needs the working flow export from n8n Cloud and what failed |
| 7 | Email list cleanup (re-engagement first) | email | Smaller bill, better deliverability | Low | Not started | Define "active" = opened or clicked in 12 months |
| 8 | Email platform decision | email | MailerLite ~$1,058/yr today; main gain = API + list management | Low–medium | Weighing a modern ESP since 9 Nov | After the list cleanup; confirm the MailerLite cost |
| 9 | **Theme rebuild** (next main stage), **in place and incremental** (11 Nov): git (item 13) → cleanup → ~~ACF out of the theme + upgrade~~ (done 11 Nov: SCF) → `rsg-core` → one review template (after the review-format research) → other page types; design after | theme | Solid base for the restart; CWV/SEO; faster changes | High | Agreed 7 Nov; DFS done 10 Nov, so it can start | Audit: `theme/THEME-REBUILD-NEXT-STAGE.md` |
| 10 | Content & distribution restart | content | Revenue | Ongoing | Idea | Review-format research, then the plan (`content/README.md`) |
| 11 | Theme backlog (comparison batch 2, quiz results, SEO, MSRP) | theme | Revenue / SEO | Varies | See `theme/CLAUDE.md` | — |
| 12 | ~~WP Rocket page cache on~~ **Superseded 11 Nov** by item 14 (fewer cache layers) | theme / hosting | Speed | Low | Off on purpose | After the geo-cache rule is removed (decision 10 Nov) |
| 13 | Theme in git + `deploy.sh` | theme | Safe deploys, history, rollback | Low | **Done 11 Nov**: repo, deploy key, staging + live deployed from git (`live-20261111-080859`) | Dead-code cleanup (start of item 9) |
| 14 | **WP Rocket kept** (11 Nov, Rocket.net recommends it): `deploy.sh` clears its caches itself, CDN option off (done 11 Nov). Page cache on **after** DFS/theme/migration work, once the DFS purge also clears it; delay JS: no | theme / hosting | Faster cache misses (24–41 % of views) | Low | Partly done 11 Nov | Later: page cache + DFS purge clears WP Rocket; measure TTFB on misses |
| 15 | DFS push + purge only after the Amazon jobs (search 02:00, re-price 08/14/20 UTC); check times **stay** in the change hash (else valid Amazon prices would age past the browser's 24 h rule) | dfs | Full-site purges 9+/day → 4/day | Low | **Live 11 Nov ~10:02 UTC** (rsg-dfs `cec0349`); each push now logs "real N" = shoes with real price/link changes, check times ignored | ~18 Nov: compare real changes per slot; drop the weakest slot (08:00 UTC the likely candidate) or keep 4 |
| 16 | Lean staging (uploads from live) → drop the 15 GB add-on | hosting | ~$30/mo | Low | Agreed 11 Nov | Decide before the Business plan mail |
| 17 | Rotate exposed keys: theme hard-coded keys (`SECRETS.md`), Rocket SSH key, MailerLite, MailerSend (one token per system) | all | Security | Low | Agreed 11 Nov | Then fix/delete the key files |
| 18 | One DFS (retire rsgmedia test bench) + nightly settings dump to git; matcher fixture tests; page-view log off/sampled + retention | dfs | Simpler, safer changes | Low–medium | Agreed 11 Nov | — |
| 19 | Docs: one short current-state runbook per system, history archived | all | Faster troubleshooting, lighter sessions | Medium | **DFS + theme runbooks done 11 Nov** (`dfs/RUNBOOK.md`, `theme/RUNBOOK.md`). Not needed (11 Nov): Linode (goes in Phase F), automations (write when the bank import moves), email (README is enough until the platform decision) | (1) **Hosting runbook** (Rocket.net + rsgmedia: cache, purges, staging vs live) after the Business plan mail; (2) **trim CONTEXT.md** to current facts, build history to an archive file, after Phase F. DECISIONS stays whole |

## Done
| Item | When |
|---|---|
| Bot blocks: robots.txt (6 Nov), IP/UA blocks by Rocket.net (9 Nov). Hotlink protection parked (overage is only $0.08/GB) | 6–9 Nov |
| Storage cleanup to < 40 GB: not needed (Business + 15 GB = 55 GB; staging stays a full copy) | 10 Nov |
| Linode tidy: unused disk + volume deleted, resized 8 → 4 GB | 5 Nov |
| Hetzner rsgmedia: backups, patching, n8n 2.46.6, security | 5–6 Nov |
| Pixian replaces remove.bg (API ends 1 Dec) | 5 Nov |
| DFS rebuild steps 1–7 Phase E: new DFS live, production server rsg-dfs | 6–10 Nov |
| CWV/CLS "fix now" list on the old theme | 7–9 Nov |
| GPS reviews: "Check price on Amazon" button per country | 10 Nov |
