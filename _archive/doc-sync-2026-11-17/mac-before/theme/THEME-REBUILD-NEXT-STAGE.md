# Theme rebuild — next stage after DFS (agreed 7 Nov 2026)

## Order
1. **DONE 7 Nov 2026 (live, `logs/CWV-FIX-DEPLOY-LOG-2026-11-07.md`):** "fix now" list on staging, then live with Ruggero's OK: lazy images + gallery CLS theme-wide, `srcset` 404 in `comments-2022.php`, PHP running inside HTML comments, dead Universal Analytics `ga()` calls. No deeper polishing of the old templates.
2. **DFS to the end** (steps 6.3–7: price box on all templates, click beacon, logos, production Linode, old DFS switched off). The price box (`rsg-dfs-prices.php`, css, js) moves into the new theme unchanged; only the hooks in old templates are thrown away.
3. **Theme rebuild** (this document), then design on top of it.
   - Before the review template is built: the review-format research (`../content/README.md`, 8 Nov). Redesign order afterwards: layout (wireframes) first, UI last; homepage principles in the same note.

## Step 0 of the rebuild: investigation (before any code)
A written audit of the current `rsg_2022` theme against today's standards, with findings ranked by impact:
- **Code:** template structure (4 near-identical ~2,600-line review templates), dead code, jQuery and inline JS/CSS, functionality living in `functions.php`, old dependencies (ACF Pro 5.7.5, Bootstrap 4, Typekit), PHP 8.3 warnings, security (escaping, nonces, exposed endpoints).
- **Output / web standards:** valid semantic HTML, heading structure, accessibility (WCAG 2.2 AA), Core Web Vitals per page type (LCP, CLS, INP; lab + field data from Search Console), page weight, fonts, third-party scripts (Raptive, analytics), caching (per-locale cache no longer needed).
- **SEO:** URLs, titles/meta, canonicals, indexation (noindex/robots/sitemaps), structured data validity and coverage, internal linking, image SEO (real `src`, alt, sizes), pagination/archives, duplicate content, freshness signals.
- **AI readability (LLM search and answer engines):** content in server-rendered HTML (no JS dependency), clean text order and headings, facts machine-readable (specs, scores, prices, dates, author/expertise in schema), concise answer blocks, stable anchors, AI crawler policy (robots.txt blocks of 6 Nov), `llms.txt` (assess value), citations/E-E-A-T signals.
- **Inventory:** every page type, template, shortcode, widget, ACF field group, REST route, cron and integration (quiz, MailerLite/MailerSend, funnel beacons, comparison flags, Pixian, schema, DFS receiver) that must survive.
Output: audit doc + rebuild plan (scope, order, parity checks, risks) for Ruggero's approval.

## Approach changed 11 Nov 2026 (adversarial review, agreed by Ruggero)
**Same theme, rebuilt in place and step by step**, not a new theme switched over with a site-wide parity harness: theme in git (done 11 Nov) → cleanup (backup files, dead templates such as `single-best.php`, old themes `rsg_2018`/`rsg_2019`) → ACF Pro out of the theme (bundled 5.7.5 in `includes/plugins/acf3`) and upgraded (**done 11 Nov: Secure Custom Fields plugin**, `ACF-MOVE-BRIEF.md`), logic into `rsg-core` (incl. the field groups registered in code, so field definitions live in git, and the REST/escaping rules now in `functions.php`) → one review template replacing the 4–5 copies (after the review-format research) → other page types; old files deleted once unused. Each step: staging → check → live via `deploy.sh`. The audit below stays the input; the 7 Nov points below hold where they don't conflict.

## Approach agreed (from the 7 Nov discussion)
- Move non-presentation code into a site plugin (`rsg-core`) first: schema, quiz/email capture, ACF fields, Pixian hook, price logic. Makes the theme swappable.
- New classic PHP theme (not a block theme), no jQuery, no build tools, one stylesheet, native lazy-loading, font loading without shifts, reserved ad slots for Raptive.
- Page type by page type behind a switch on staging: one review template for road/trail/training/gps, then guides/best/top, comparisons, home/archives.
- Each page type switches only after automated parity checks against the old output (same URLs, titles, meta, headings, schema, internal links, visible text) and Core Web Vitals measurements.
- Design work starts after, on top of the new base (CSS variables/components).

## Done on the old templates and constraints learned (11 Nov 2026)
- **Machine-readable review markup is live** on road/trail/training (audit + findings: `HTML-MACHINE-READABILITY-AUDIT-2026-11-11.md`; what and why: `../DECISIONS.md` 11 Nov). The new review template keeps these patterns: landmarks, one h1, `<dl>` for facts/fit/ratings/cushioning, a real `<table>` for comparisons, "Best for" = selected uses only, verdict in a `<p>`, "Published … · updated …" with `<time>` from `rsg_content_modified`, schema with `@id`s and no fake SKU. Check extraction with trafilatura + a markdown converter, not only by eye.
- **Raptive's ad slots follow the HTML structure** (`body > div > .container > .row > .col-lg-8 > *`, `.review-sections > *`, `.col-lg-8+.col-4 > div`, `#comments`). Any new template must keep these hooks or the ad count drops (seen on staging: 8–9 → 4). Count ads before/after on staging for every structural change; Ruggero chose not to ask Raptive to change the selectors.
- **WP Rocket used CSS is per URL and generated from the HTML**: staging has none, so staging shows the look *after* regeneration; `deploy.sh` clears it on every live PHP/CSS/JS upload.

## Known issues to carry into the audit
- (fixed 7 Nov: guide mobile CLS 0.20 was the font swap.) Homepage desktop: top block height changes ~3.9 s (CLS ~0.03).
- ~20 templates still use hand-written `<picture class="progressive-picture">` markup + footer lazy script.
- Live price box: wrong country from the per-locale cache, "£0.00" list prices (solved by the DFS rollout).
- Merchant logos in the new price box feel too big (Ruggero, 7 Nov).
