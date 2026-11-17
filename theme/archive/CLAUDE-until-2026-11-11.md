# RSG redesign — session primer

> **Part of the RSG project** (`../README.md`): the theme is one of seven workstreams (costs, hosting, DFS, theme, content, email, automations). Read `../README.md`, `../CONTEXT.md` and `../DECISIONS.md` first. Note: Rocket.net bot blocking and plan changes are tracked in `../hosting/`; the DFS rebuild in `../dfs/`.


Single source of truth for starting a session. Detail lives in the docs mapped at the bottom.
Project: modernise the live WordPress theme `rsg_2022` behind **runningshoesguru.com**.

_Last updated: 2026-11-11._

---

## Current state (what's LIVE in production)
- **PHP 8.3** + hardened `rsg_2022` theme (all fatals fixed) — since 2026-06-21.
- **Structured data (JSON-LD), frontend-invisible**, on every type:
  - Reviews road/trail/gps/training — `includes/rsg-schema.php`.
  - Comparison pages, guides (Article + ItemList + FAQ), reviews-hub CollectionPage.
  - **Family hubs `/reviews/{brand}/{family}/` made self-canonical / indexable** (27 hubs).
  - **Tester-assessment properties** (size/fit, cushioning, usage) nested in each review's `Review` node.
  - **Schema validity fixes (2026-06-23, GSC validation in progress):** every comparison `Product`
    now carries one of offers/review/aggregateRating — legacy `post`-type reviews score via the
    `stars` meta (0-10), and a safety net skips any data-less node instead of emitting an invalid one;
    `/top/` moved off the broken legacy hand-built JSON-LD (`Missing ',' or '}'`) onto the guide builder
    (`rsg_emit_guide_schema()` now also guards `top`). See `schema-pass/SCHEMA-FIX-DEPLOY-LOG-2026-06-23.md`.
- **Technical SEO:** author-archive index bloat noindexed (thin authors ≤5 posts) + single consolidated
  `robots` meta (dropped `noodp/noydir`) — both LIVE 2026-06-23. See `TECHNICAL-SEO-BACKLOG.md`.
- **Front-end specs/facts redesign — LIVE on road + trail + training (2026-06-23).** Replaced the dark
  `#1C2226` specs panel with a light, native block: objective "facts" + a side-profile stack graphic;
  a "tester's take" with a shoe diagram (fit zones), named-pole cushioning spectrums, and usage chips;
  brand/family/previous-model internal links preserved. GPS keeps its old panel (no fit/cushioning data).
  Block = `includes/review-assessment-block.php`, included by `single-reviews_{road,trail,training}.php`.
- **Comparison-page REDESIGN — LIVE on 3 PILOT pages only (2026-06-24).** New full-page layout, flag-gated
  per post (`comparison_redesign` meta = 1, or `?compare=1` preview): visible breadcrumb + anchor nav,
  author "verdict" TL;DR (`quick_verdict`), a **unified shoe module** (card header row-aligned over its own
  spec column — long names can't break alignment; preserves brand/type/review links), a data-driven
  **"what matters most" chooser** + **"where each wins"** panel, and a sub-score **radar** (6 axes,
  +traction on trail). Reuses theme components (score ticket, `btn-warning`, CSS vars); editorial
  (intro/sole/upper/which-to-buy) + "Best price online" unchanged and still lead. Drop treated as neutral.
  Block = `includes/comparison-redesign-block.php`, conditionally included by `single-comparison.php`
  (old table wrapped in `if(!$rsg_new_compare)`). **Pilots:** `hoka-clifton-vs-hoka-bondi` (28982),
  `asics-nimbus-vs-asics-kayano` (28311), `brooks-ghost-vs-brooks-adrenaline` (28070) — chosen from GSC
  (page-1-not-#1, evergreen). New meta set via WP-CLI (flag + `quick_verdict`/`buy_if_1`/`buy_if_2`).
  All other comparisons render unchanged (only harmless heading `id`s added). See
  `comparison-redesign-20260624/COMPARISON-REDESIGN-DEPLOY-LOG-2026-06-24.md`.
  **Pilot measurement: ✅ DONE 2026-09-10** (46 days late — the 2026-07-22 date came and went unchecked). Via GSC UI (no API/export in-session): site-wide traffic fell hard this period (clicks -41%, impressions -51%) and `-vs-` comparisons as a category fell *more* (-42%/-60%) — looks like a broader site/algo issue, not the redesign. Against that backdrop the 3 pilots beat 2 matched non-redesigned controls (same product families, similar tier): pilots avg -33% clicks/-38% impressions/position ~flat vs controls -47%/-50%/position worse by ~0.45. Modest but consistent (3/3 beat their controls on clicks/impressions). **Decision: roll out to a next batch of ~10-15 pages**, then measure again. Full numbers + methodology + batch-2 candidate list + deploy steps → `comparison-redesign-20260624/COMPARISON-REDESIGN-ROLLOUT-BATCH2-BRIEF.md`. **Blocked on:** Ruggero writing `quick_verdict`/`buy_if_1`/`buy_if_2` copy for the batch-2 pages (no code deploy needed — ACF fields already live-editable, see 2026-06-28 log).
  **Rollback per page:** `wp post meta delete <id> comparison_redesign`.
- **Comparison-page PHOTOS — LIVE on the same 3 pilots (2026-06-24).** Real human photos reused from
  each linked review, side-by-side ("compare band"), flag-gated like the redesign: **sole-unit** + **upper**
  bands (first image from the review's `sole_unit`/`upper` ACF field) and a **verdict** band (each review's
  WP featured/hero image) in "Which one to buy". Responsive `wp_get_attachment_image('large')` (srcset/lazy,
  633px) + contextual alt. **Gotcha fixed:** parse RAW `get_post_meta` (not `get_field`) — the frontend
  WYSIWYG filters strip `wp-image-{ID}` and mangle the src (→ 23px). 5 helper fns added to the block;
  3 flag-guarded one-line calls in `single-comparison.php` (before the sole/upper/which-to-buy editorial
  divs). Non-pilot pages unchanged. See `comparison-redesign-20260624/COMPARISON-PHOTOS-DEPLOY-LOG-2026-06-24.md`.
- **Shoe Finder QUIZ — algo fix LIVE (2026-06-26).** The live ranking was a `WP_Query` with no recency
  weighting and **no fallback** (thin/empty results); the "1-year date gate" the old briefs feared was
  dead commented code. Fix in `template-wizard-2024.php`: rank on **decayed score** `vote − age_penalty`
  (0–10 scale; ≤12mo:0…49mo+:−2.5) + **relax-until-3 fallback** (road relaxes cushioning_type→carbon_plate
  →stability, protects usage; trail relaxes rockplate→water_resistance→usage). Never stale, never empty.
  Both road+trail rank on `vote` (not `stars`, which is legacy `post`-type only). Gender is display-only
  (swaps each pick to the women's CTA, lines 1700–1702). See `logs/QUIZ-ALGO-DEPLOY-LOG-2026-06-26.md`.
- **Shoe Finder QUIZ — email capture LIVE (2026-06-26).** Give-first **single opt-in** module after the
  podium (picks shown free on page; the email box IS the newsletter join — no picks-only tier) →
  WP REST `rsg/v1/wizard-subscribe` → **logs every submission to local table `{prefix}_rsg_wizard_signups`**
  (the reliable count) **+ MailerLite Classic** (one group `from_wizard` 113205636 via const
  `RSG_ML_GROUP_SUBS`; **opt-in model since superseded — now geo-conditional, see the 2026-07-26 bullet
  below**; user's ML automation moves confirmed subs to the autoresponder). **Stores ONLY country in ML** (built-in field;
  CF-IPCountry), rest stays local. **DOI gotcha:** `@runningshoesguru.com` addresses auto-`active` (same-
  domain skips DOI) — external addresses get the confirmation email (verified). API call isolated in
  `rsg_ml_subscribe()` (one-spot swap to new/Power API later). Files: NEW
  `includes/rsg-wizard-capture.php`, `functions.php` (1 require), `template-wizard-2024.php` (module),
  `wp-config.php` (3 constants, token). See `MAILERLITE-CAPTURE-BRIEF.md` +
  `logs/QUIZ-MAILERLITE-CAPTURE-DEPLOY-LOG-2026-06-26.md`.
- **Shoe Finder QUIZ — "your 3 picks" EMAIL LIVE (2026-07-05).** The handler now also sends a
  **transactional** picks email via **MailerSend** (MailerLite's transactional arm) right at submit —
  reaches 100% of submitters, **independent of MailerLite DOI** (an ML automation would only fire after
  confirm). MailerLite still owns the newsletter DOI + series, unchanged. Domain `runningshoesguru.com`
  verified in MailerSend (SPF merged w/ existing `_spf.mlsend.com`, DKIM `mlsend2._domainkey`,
  Return-Path `mta`; **DNS is in AWS Route 53**, CF only proxies). Send isolated in `rsg_ms_send_picks()`
  → POST `api.mailersend.com/v1/email`; result stored in new **`email_status`** column (DB v2→3).
  From/Reply-To = `newsletter@runningshoesguru.com` (set in code; no mailbox needed to send, alias exists
  for replies). Email = branded (real logo, site blue `#2189E1` + amber CTA), conversational, with a
  **"You told us" recap** of the runner's quiz answers + the 3 shoe cards (photo/name/review link) +
  reader-supported disclosure footer. Test landed in Gmail **Promotions** (not spam). **Name greeting
  deferred** (needs a form field; greets "Hi there," for now); future = per-shoe verdict/price. Only
  file changed: `includes/rsg-wizard-capture.php` (+ `wp-config.php` token). See
  `logs/QUIZ-PICKS-EMAIL-DEPLOY-LOG-2026-07-05.md`.
- **Shoe Finder QUIZ — funnel instrumentation LIVE (2026-07-06).** First-party server-side funnel
  counters (Phase 0 of the results-page redesign — see Next/open #1). New REST route
  `POST rsg/v1/wizard-event` (`__return_true`, no-nonce) + new aggregate table
  **`{prefix}_rsg_wizard_funnel`** (`day, stage, quiz_type, hits`; **no PII**; DB v3→4). Template fires a
  non-blocking `navigator.sendBeacon` (`window.rsgFunnel(stage)`) at the 5 existing gtag points:
  **start → completed → email_shown → email_submitted / email_skipped** (`quiz_type` logged as `''` in
  v1 = road+trail aggregate; per-type split is a cheap fast-follow). Built because access logs showed
  ~450–900 quiz-page origin hits/day but only **4 submit attempts in 6 days** → the problem is
  **conversion, not the backend**, and GA4 can't recover the historical stage breakdown (stages are
  unregistered event params). Endpoint + table work server-side immediately; **Rocket.net cache purged
  2026-07-06** so the beacons now fire for real visitors — **data collection started 2026-07-06**.
  **To read:** `wp db query "SELECT day,stage,SUM(hits) h FROM {prefix}_rsg_wizard_funnel GROUP BY day,stage
  ORDER BY day,FIELD(stage,'start','completed','email_shown','email_submitted','email_skipped');"` — then
  compute `completed/start`, `email_shown/completed`, `email_submitted/email_shown` vs `_skipped`. Files:
  `includes/rsg-wizard-capture.php` (route+table), `template-wizard-2024.php` (beacon). See
  `logs/QUIZ-FUNNEL-INSTRUMENTATION-DEPLOY-LOG-2026-07-06.md`.
  **➡️ FUNNEL READ 2026-07-26** (20 days): **start 869 → completed 767 (88%) → email_shown 767 →
  submitted 117 (15% of completers) → skipped 586.** Quiz *completion is healthy (88%)* — the leak is
  the **email ask** (76% skip) and, deeper, the **double-opt-in** step (see next bullet).
- **Shoe Finder QUIZ — GEO-CONDITIONAL opt-in LIVE (2026-07-26).** The MailerLite DOI step was the
  deepest leak: of ~99 signups only **13 confirmed** (13 active · 82 unconfirmed · 5 unsub · 0 bounced ·
  0 junk) — people don't confirm because the MailerSend picks email already delivers the value at submit.
  Legal check (2 passes): GDPR/UK-GDPR do **not** require DOI (provable consent is enough, and we log
  `created_at`+`ip`+`consent_series`); only DE/AT strictly do (UWG §7 + court precedent). Fix in
  `rsg_ml_subscribe()`: it now takes `$country` and picks `type` per country — **EU-27 + EEA-3 (IS/LI/NO)
  + Switzerland (31 countries) → `unconfirmed` (DOI kept)**; **everyone else (US, CA, UK, rest of world,
  unknown) → `active` (single opt-in = instant newsletter member)**. Per-subscriber `type` overrides the
  account DOI setting, so it's all in our code (no account toggle). Also: pre-results popup fine print now
  states the newsletter subscription explicitly (informed consent for single opt-in; post-results card
  already said "Join the newsletter"). ~75% of historical signups (incl. all US+CA+UK) would be single
  opt-in. NOT done: the 82 already-unconfirmed are not retroactively activated; picks email still has no
  unsubscribe (one-time/transactional — the newsletter's ML one-click unsub honors the promise). Files:
  `includes/rsg-wizard-capture.php`, `template-wizard-2024.php`. See `logs/QUIZ-OPTIN-GEO-DEPLOY-LOG-2026-07-26.md`.

- **Professional-image background removal → Pixian.AI — LIVE (2026-11-05).** `includes/remove_bg.php` hook (`rsg_pixian_cutout_on_save`, `acf/save_post`) replaces remove.bg (API ends 1 Dec 2026): removes background + crops to 1280×960 with 1% margin; on any API failure the image is left untouched (error log). Credentials `RSG_PIXIAN_API_ID/SECRET` in `wp-config.php`. See `logs/PIXIAN-BG-REMOVAL-DEPLOY-LOG-2026-11-05.md`.

- **Core Web Vitals fixes — LIVE (2026-11-07).** Review gallery image sizes, native lazy-loading (no blurred-placeholder swap), font fallback scaled to the Typekit fonts (inline `#rsg-font-fallback`, excluded from WP Rocket RUCSS), `base.css?ver=<filemtime>`, commented-out PHP blocks no longer run, `window.ga` stub, no empty `srcset`. Live CLS: reviews mobile 0.35–0.52 → ~0.02, desktop ~0.17 → 0.004, guide mobile 0.20 → 0.001. **Live uses WP Rocket "Remove Unused CSS" + minify: after any CSS change, clear them (`wp eval 'do_action("rocket_domain_options_changed"); rocket_clean_minify("css"); rocket_clean_domain();'`) and purge Rocket.net HTML.** See `logs/CWV-FIX-DEPLOY-LOG-2026-11-07.md`.

- **CLS batch — LIVE (2026-11-08).** `review`-type gallery sizes, h1 subtitle + `h1.full-page-title` size-matched fallback (inline `#rsg-font-fallback` in header.php), comparison score as plain text "★ 7/10" (`rsg_cmp_score()` in single-comparison.php, ticket gone), quiz photo/icon sizes + heading fallback, guide header-box image sizes. Comparison pilots mobile 0.041 → 0.0003, quiz 0.06 → 0.001, old posts 0.059 → 0. Main-layout guide h1 font swap (≤0.07, intermittent) left for the rebuild. See `logs/CLS-DEPLOY-LOG-2026-11-08.md`, `CLS-FIX-LIST.md`.
- **Live 500 on the `[dfscat]` post fixed (2026-11-08)** — one-line guard in `rsg_dfs_category()`. See `logs/FIX500-DEPLOY-LOG-2026-11-08.md`.
- **Speed/cleanup batch — LIVE (2026-11-09).** (1) Dead Amazon Native Shopping Ads widget removed (`include-amazon-native.php` emptied, footer block gone; `logs/AMAZON-NATIVE-ADS-REMOVAL-DEPLOY-LOG-2026-11-09.md`). (2) All Freestar/Quantcast leftovers removed from 25 files (15 dead preconnects incl. `typekit.com`; live slots threw `freestar is not defined` on old GPS/post pages); "Ads test page" 39842 trashed; script `theme/_work/freestar-cleanup-20261109.py` (`logs/FREESTAR-CLEANUP-DEPLOY-LOG-2026-11-09.md`). (3) jQuery 3.5.1 self-hosted as `js/jquery-3.5.1.min.js` (same file as Google's; **not** WP core's copy — it runs `noConflict()` and breaks `$`) (`logs/JQUERY-SELFHOST-DEPLOY-LOG-2026-11-09.md`). (4) **WordPress lazy loading re-enabled** (`wp_lazy_loading_enabled` false removed); `wp_get_loading_optimization_attributes` filter: on reviews/gps/post/content, images from `the_content` + `acf_the_content` always lazy, never fetchpriority; guide header `the_post_thumbnail` eager + fetchpriority high. Review pages 55–85 % fewer bytes on load (`logs/LAZY-LOADING-DEPLOY-LOG-2026-11-09.md`). Open from it: `sizes` fix so 2× screens get the 768 px file; `/reviews/` hub desktop first-row card lazy (pre-existing, theme loader).
- **New DFS price box — LIVE (2026-11-10).** All DFS reviews, comparisons, guides, lists, quiz picks and shortcodes use the new box/button (`includes/rsg-dfs-prices.php`, `css/rsg-dfs.css`, `js/rsg-dfs.js`, `images/merchants/`), switched on by site option `rsg_prices_default=1`; US prices in the HTML, the visitor's country swapped in by the script. Rollback: `wp option delete rsg_prices_default` + purge. Every theme file the DFS touched: `../dfs/app/wordpress/theme/README.md`; go-live account and lessons: `../dfs/GO-LIVE-2026-11-10.md`. The old `dstudio` plugin is still active until DFS Phase F.
- **GPS reviews: "Check price on Amazon" button — LIVE (2026-11-10).** One button per country store, no price; old dstudio/PA-API calls removed from both GPS templates (`includes/rsg-gps-amazon.php`; `logs/GPS-AMAZON-DEPLOY-LOG-2026-11-10.md`).
- **WP Rocket on live:** page cache off (on purpose until the Rocket.net geo rule is gone), Remove Unused CSS on with a safelist for the DFS box, minify on. After a CSS/JS change: clear used CSS, refresh the minified copy in `wp-content/cache/min/`, purge (`../dfs/GO-LIVE-2026-11-10.md` → Lessons).
- **Purge from SSH (found 2026-11-08):** `cd ~/public_html && wp cdn purge` = Rocket.net "Purge Everything" (host mu-plugin `cdn-cache-management`); Ruggero OK'd Claude purging itself. Old/CLS fixes to do: `CLS-FIX-LIST.md`.
- **Custom fields = Secure Custom Fields plugin (SCF 6.9.8) — LIVE 2026-11-11.** The theme no longer bundles ACF PRO 5.7.5 or `acf-to-rest-api` (theme PHP 114k → ~62k lines). Field groups unchanged, in the database (30 active + 1 disabled "Saved NOT USED fields", kept: it holds data on 9 guides) + the code-registered comparison group. In `functions.php` (ACF section): WYSIWYG fields print unescaped as before (SCF strips video iframes otherwise), every field group is in the REST API, options page on `acf/init`. **REST:** every field of every content type under `acf` on `/wp-json/wp/v2/<type>/<id>` (guides/top lists added to the API too), read + write, `?acf_format=standard` for formatted values; how to use it: `../automations/README.md`. Make inserts unchanged (51 raw keys; field references written when the draft is saved in wp-admin). Update SCF from wp-admin like any plugin (test on staging first for big versions). See `ACF-MOVE-BRIEF.md` (status + findings).

## ⚠️ Pending USER manual actions
- ~~Purge the Rocket.net cache by hand~~ — since 2026-11-08 Claude purges itself after a deploy (`wp cdn purge` over SSH). Still purge before requesting GSC re-indexing of the 3 comparison pilots if anything was deployed to them.
- **Rotate the SSH key** (shared in plaintext) and the **MySQL password** (was in dead code) at go-live.
  _A one-off reminder to rotate the SSH key is scheduled for 2026-06-30._
- **Rotate the MailerLite API key** — shared in plaintext 2026-06-26; now only in `wp-config.php`
  (`RSG_MAILERLITE_TOKEN`). Re-key when convenient and update the constant.
- **Rotate the MailerSend API key** — shared in plaintext 2026-07-05; now in `wp-config.php`
  (`RSG_MAILERSEND_TOKEN`) + gitignored `.credentials-local.md`, and the DFS servers use the same token. Planned with DFS Phase F (`../dfs/PLAN-STEP7.md`).
- Confirm wp-admin login works (`wp-login.php` 403s external IPs — likely host login protection).

## Next / open (priority)
0. **Theme rebuild = the next main stage** (agreed 7 Nov; DFS went live 10 Nov, so it can start) — `THEME-REBUILD-NEXT-STAGE.md`, audit first. Until it replaces the old theme: only the "fix now" list (CWV/obvious errors), no deeper polishing of old templates.
1. **Email strategy** (picked 2026-07-26; the email workstream now lives in `../email/README.md`, this is the quiz-side context). Now that opt-in no longer strangles
   list growth, the question is what to *do* with the list: welcome/nurture series, cadence, content mix,
   list monetization (affiliate, price-drop alerts), and how the MailerSend picks email + MailerLite series
   fit together. Context in hand below — start here fresh next session.
   **Funnel read 2026-07-26** (20 days since 2026-07-06): **start 869 → completed 767 (88%) → email_shown
   767 → submitted 117 (15% of completers) → skipped 586.** Quiz completion is *healthy (88%)* — NOT the
   leak. The leak was the **email ask** (76% skip) and, deeper, the **double-opt-in step**: only 13/99
   confirmed = **1.5% of quiz starts**. **✅ FIXED 2026-07-26 — geo-conditional opt-in** (single opt-in for
   US/CA/UK + rest of world, DOI kept for EU/EEA+CH; see Current state). Expect ~75% of new subs to land
   `active` immediately — **measure the lift** after a few days (query in `logs/QUIZ-OPTIN-GEO-DEPLOY-LOG-2026-07-26.md`).
   **Results-page redesign (conversion) — still the follow-on work:** **Phase 0 — funnel ✅ DONE**;
   **Phase 1 — the email ask** (rework the give-first popup: the big easy "just show my results" skip bleeds
   76%; test framing/skip prominence — but picks already show free on-page); **Phase 2 — results-page
   visual/UX** (dated podium → clearer hierarchy, #1 pick emphasised, per-shoe "why this matched *you*",
   fold email capture into the design vs bolt-on card, mobile-first); **Phase 3 — conversion/monetization**
   (sticky buy-bar, "best price + retailer", price-freshness); **cross-cutting — stop over-promising**: the
   hook implies price-drop alerts the cron **isn't built** — build it or cut the copy (picks email already sends).
   Files: `template-wizard-2024.php` (quiz slides + `#wizard-popup` pre-results email step ~line 1624; results
   block `#wizard-results` ~line 1712; post-results `.rsg-emailcard` ~line 1770), backend
   `includes/rsg-wizard-capture.php`. Keep the working algo + capture + funnel beacons intact.
2. **Comparison-redesign pilot follow-ups:** (a) ✅ **DONE 2026-09-10** — measured pilots vs controls, signal supports rolling forward; **now rolling out to batch 2 (~10-15 pages)**, blocked on Ruggero's verdict/buy-if copy — see `comparison-redesign-20260624/COMPARISON-REDESIGN-ROLLOUT-BATCH2-BRIEF.md` for candidates + exact deploy steps, then measure again before going wider; (b) deferred **conversion pass** = sticky mobile buy-bar, "best price + retailer" in card,
   "price checked today" trust/freshness line; (c) ✅ **DONE 2026-06-28 — ACF fields registered**: the
   redesign controls (`comparison_redesign` toggle + `quick_verdict`, `buy_if_1/2`) are now editable in
   the comparison editor via a **code-registered local field group** (`includes/rsg-comparison-acf-fields.php`,
   required in `functions.php`; ACF Pro 5.7.5, no DB change). To publish a new comparison in the new format:
   edit the comparison → toggle "Use new comparison design" ON → fill verdict + buy-if → publish → purge
   Rocket.net. Pilots verified intact (no value loss). See
   `comparison-redesign-20260624/COMPARISON-ACF-FIELDS-DEPLOY-LOG-2026-06-28.md`; (d) site-wide
   multiple-H1 fix (= backlog #4) — comparison editorial headings are still `<h1>` on all pages.
3. **SEO backlog** `TECHNICAL-SEO-BACKLOG.md`: #2 guide date/staleness signals, #4 homepage multiple H1,
   #7 exclude `noindex` PT from sitemap, #6 WebP, #8–#10 low. (#1, #3-deprioritised, #5 resolved.)
4. **Bigger phases** (`CONVENTIONS.md` plan): ~~ACF Pro extraction~~ (**done 2026-11-11**: Secure Custom Fields plugin, `ACF-MOVE-BRIEF.md`) → ~~pricing/caching rework~~ (**done differently with the new DFS, live 2026-11-10**: US prices in the HTML, country swapped in the browser)
   → broader style redesign.
   - **(idea, 2026-06-24) Multi-country MSRP** — capture MSRP for UK/AU/EU/CA (USA already stored: `rrp_us`;
     other `rrp_*` fields exist: `rrp_au/ca/gb/de`), both **retroactively** (backfill) and **going forward**
     (at review creation). Mostly additive + low-risk. **DFS has NO MSRP at all — not per model, not per
     country** (confirmed 2026-06-24). Source it another way: brand official sites / local-retailer RRP
     (strikethrough "was" price in each country's affiliate feed) / manual entry. Capture **actual local RRP**,
     not USD converted. Ships independently of any caching/architecture work. **This is the live MSRP thread.**
     **PROBED live DFS feed 2026-06-24 (Clifton 10, US/GB/AU/CA/DE):** the feed's per-offer `RRP_PRICE` is
     **empty/`0.00` almost everywhere** (one non-zero value just mirrored the sale price) → the "affiliate was"
     source is **not viable**. (Also: thin per-country merchant coverage — GB returned 0 items, AU/DE only 1.)
     → Remaining paths: (1) **going forward (cheap)** — hand-enter `rrp_gb/au/ca` (fields exist) + add `rrp_eu`
     at review time, taken from brand local sites, like `rrp_us` already is; (2) **backfill** — research-assisted
     per model/country, batchable but needs accuracy checks (prioritise high-traffic models). No free auto feed.
     **DECISION (2026-06-24): scrape the brands' local sites** — official RRP per country, in fetchable HTML
     (validated: HOKA US $155 vs GB £140, same product ID across `/en/us/`↔`/en/gb/`). **Kickoff brief →
     `MSRP-LOCAL-PRICING-BRIEF.md`** (start the new session there). DFS RRP_PRICE empty; manual + multiplier both rejected.
   - ~~(idea, 2026-06-24) Geo-driven pricing architecture — PARKED~~ **Superseded (Nov 2026):** the new DFS box renders US prices in the HTML and swaps in the visitor's country in the browser (live 2026-11-10, CLS ~0). The June claim that the per-locale cache "cannot be re-enabled" was **wrong**: Rocket.net confirmed on 2026-11-10 that the geo rule can be removed and re-added; removal requested 2026-11-11. See `../DECISIONS.md` and `../dfs/GO-LIVE-2026-11-10.md`.
5. **AI alt-text (deferred, user "decide later" 2026-06-24)** — review images largely lack alt (stored alt is
   generic `Picture of <model>`). Plan: context-aware backfill (brand/model + section + vision) over existing
   media, optional on-upload hook going forward. The new comparison photo bands already emit good contextual
   alt at render; the **stored** attachment alt is still the highest-value backfill target.

---

## Connect & access

- **GitHub push:** port 22 is blocked from the device shell; push over 443 with the deploy key: `GIT_SSH_COMMAND="ssh -i ~/.ssh/github-rsg-theme -o IdentitiesOnly=yes -p 443" git push ssh://git@ssh.github.com:443/ruggeroloda/rsg-theme.git main --tags` (copy `_private/github-rsg-theme-deploy-key` to `~/.ssh/github-rsg-theme`, `chmod 600`). `deploy.sh`'s own tag push fails there ("tag not pushed"); push the tags afterwards.
- **SSH (current, 2026-11-05):** from the **Mac (device-bridge shell)** only; the cloud shell can't reach Rocket.net. Copy the key first (`chmod` fails inside the mounted folder): `cp <rsg>/theme/.ssh-rocket-key ~/.rsgkey && chmod 600 ~/.rsgkey`, then `ssh -i ~/.rsgkey jfx9wij@65.181.124.237` (server `dal20.onrocket.com`). The old IP `131.153.143.194` is dead (server move ~2026-08-26); older logs still mention it.
- Fallback without SSH: the **Rocket.net panel** (File Manager, WP-CLI console where `wp eval` is blocked, phpMyAdmin, cache purge); panel gotchas in `logs/RSG-SESSION-LOG-2026-09-09-10.md`.
- Sep 2026: live `dstudio.php` carried an `ios=yes` bypass of the old DFS IP allowlist (Rocket.net outbound-IP change). It goes away with the dstudio plugin in DFS Phase F.
- ⚠️ **Read live files, not `~/Downloads/rsg_2022/`** (January 2026 copy; caused three wrong diagnoses).
- Edge: Cloudflare Enterprise, managed by Rocket.net (hence `CF-IPCountry`); purge via `wp cdn purge` (= Rocket.net "Purge Everything").
- **Live theme:** `~/public_html/wp-content/themes/rsg_2022`
- **Staging theme:** `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022`
- **Lint:** `ea-php83 -l <file>` (what runs) **and** `ea-php84 -l <file>` (next upgrade); `deploy.sh` does both. 7.4 dropped 11 Nov 2026. **WP-CLI:** `wp …` from the webroot.
- **Staging is `robots: Disallow`** → validate schema via Rich Results **Code mode**; bypass cache with `?cb=<ts>`.
- **Backups on server:** `~/rsg_backups_20260621/` (PHP8 + schema), `~/rsg_backups_20260623_seo/` (SEO),
  plus per-file `*.bak-*` next to each edited file. Each deploy log lists its exact backup path + rollback.

## Working rules
- **Since 11 Nov 2026 the theme is in git** (private GitHub `ruggeroloda/rsg-theme`, working copy `rsg_2022/` in this folder, `main` = live). **Never edit theme files on a server.**
- Deploy flow: branch/commit → `./deploy.sh staging` → verify rendered output (`?cb=` to dodge cache) → `./deploy.sh live` (only the commit last deployed to staging) → verify live. The script exports committed files only, lints PHP 8.3 + 8.4 on the server, lists the changes and asks "yes", keeps file modes 755/644 and unchanged files' mtimes, backs up replaced files to `~/rsg_deploy_backups/`, tags, purges. After CSS/JS changes on live: also WP Rocket used CSS + the minified copy (it prints a reminder). Rollback: check out the previous `live-*` tag and deploy. Commit messages + tags replace hand-written deploy logs and `.bak` files.
- **No credentials in git.** Files with hard-coded keys are listed in `.gitignore` and the repo's `SECRETS.md`; `deploy.sh` never uploads or deletes them. `gitleaks git .` must be clean before a push.
- Prefer SSH/WP-CLI/MCP over the browser. Concise updates; flag risks early.
- DB/uploads are NOT touched by deploys (staging clone has drifted) — theme files only.

---

## Doc map
- **`CLAUDE.md`** (this) — session primer. Start here.
- **`CONVENTIONS.md`** — durable reference: project goals, env, theme audit, known bloat, pricing/caching
  target architecture, phased plan, and the detailed PHP-8 + schema work logs (2026-06-21).
- **`THEME-REBUILD-NEXT-STAGE.md`** — plan for the next stage (audit, `rsg-core` plugin, new theme). Start here for the rebuild.
- **`CLS-FIX-LIST.md`** — CLS findings and what was fixed (8 Nov batch).
- **`../dfs/app/wordpress/theme/README.md`** — every theme file the new DFS price box touched (with backups).
- **`TECHNICAL-SEO-BACKLOG.md`** — living SEO backlog (#1–#10 with status). Current.
- **`../dfs/DFS-Consumer-Contract-Report.md`** — how the theme consumed the **old** DFS (legacy; archive after DFS Phase F).
- **`MSRP-LOCAL-PRICING-BRIEF.md`** — kickoff brief for the local per-country RRP work (scrape brand sites). Start a new session here.
- **`comparison-redesign-20260624/COMPARISON-REDESIGN-ROLLOUT-BATCH2-BRIEF.md`** — batch-2 rollout brief (2026-09-10): pilot-vs-control measurement, ~9 candidate pages, blocked on verdict/buy-if copy, exact deploy steps. Start a new session here to resume.
- **`MAILERLITE-CAPTURE-BRIEF.md`** — quiz email-capture build brief (Classic now, Power later; give-first,
  country via CF-IPCountry, local log + ML). The quiz/newsletter thread's current source of truth.
- **`NEWSLETTER-QUIZ-BRIEF.md`** — original strategic frame ("own the audience"); capture specifics
  superseded by `MAILERLITE-CAPTURE-BRIEF.md`.
- **Deploy logs (rollback records):**
  - `logs/DEPLOY-LOG-2026-06-21.md` — PHP 7.4→8.3 + theme hardening.
  - `schema-pass/SCHEMA-PASS-2026-06-21.md`, `GUIDES-REVIEWS-DEPLOY-LOG-2026-06-21.md`,
    `COMPARISON-DEPLOY-LOG-2026-06-21.md`, `REVIEW-ASSESSMENTS-DEPLOY-LOG-2026-06-22.md`,
    `SCHEMA-FIX-DEPLOY-LOG-2026-06-23.md` — the schema passes.
  - `frontend-redesign-20260623/FRONTEND-DEPLOY-LOG-2026-06-23.md` — the specs/facts redesign (road+trail+training).
  - `comparison-redesign-20260624/COMPARISON-REDESIGN-DEPLOY-LOG-2026-06-24.md` — comparison-page redesign (3-page pilot).
  - `logs/QUIZ-ALGO-DEPLOY-LOG-2026-06-26.md` — Shoe Finder recency-decay + relax-fallback ranking.
  - `logs/QUIZ-MAILERLITE-CAPTURE-DEPLOY-LOG-2026-06-26.md` — quiz email capture (local log + MailerLite Classic).
  - `logs/QUIZ-PICKS-EMAIL-DEPLOY-LOG-2026-07-05.md` — transactional "your 3 picks" email via MailerSend (branded, recap).
  - `logs/QUIZ-FUNNEL-INSTRUMENTATION-DEPLOY-LOG-2026-07-06.md` — first-party funnel counters (`rsg_wizard_funnel` +
    `rsg/v1/wizard-event` beacon); Phase 0 of the results-page redesign. Includes the read-the-funnel query.
  - `logs/QUIZ-OPTIN-GEO-DEPLOY-LOG-2026-07-26.md` — geo-conditional opt-in (single opt-in for US/CA/UK + rest of
    world, DOI kept for EU/EEA+CH); the DOI leak (13/99 confirmed) diagnosis + legal rationale + measure query.
  - Nov 2026 (all in `logs/`): `PIXIAN-BG-REMOVAL-…-11-05`, `CWV-FIX-…-11-07`, `LAZY-IMAGES-CLS-…-11-07`, `CLS-…-11-08`, `FIX500-…-11-08`, `AMAZON-NATIVE-ADS-REMOVAL-…-11-09`, `FREESTAR-CLEANUP-…-11-09`, `JQUERY-SELFHOST-…-11-09`, `LAZY-LOADING-…-11-09`, `GPS-AMAZON-…-11-10`. DFS go-live: `../dfs/DEPLOY-LOG-STEP7.md`.
- `logs/php83-smoke-test-2026-06-21.md` — historical PHP-8 crawl results.
- `schema-pass/archive/` — superseded pre-work briefs (kept for history).
