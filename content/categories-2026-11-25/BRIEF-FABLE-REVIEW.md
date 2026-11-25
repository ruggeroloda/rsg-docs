# Brief: independent review of the shoe-categories plan (for a new Fable chat)

_Written 25 Nov 2026. Ruggero wants the plan tested by a model that did not take part in making it, **before anything is built**._

## Your task
Review `content/categories-2026-11-25/PLAN.md` critically and independently. You did not see the chat that produced it; judge the plan on its merits. **Do not build, deploy, edit WordPress, Airtable or n8n, and do not change `PLAN.md`.** Write your review to `content/categories-2026-11-25/REVIEW-FABLE.md` and end with a short list of questions for Ruggero.

## Read first
1. `README.md`, `ROADMAP.md`, `STRATEGY.md` (the three goals: every change says which goal it serves).
2. `content/categories-2026-11-25/PLAN.md` (the plan and every decision with its reason).
3. `content/categories-2026-11-25/category-mapping.csv` (1,359 old reviews: old tags, proposed surface, category, Stability flag, reason) and `map.py` (the rules that made it).
4. For context only: `content/REVIEW-FORMAT-2026-11-22.md` (categories were parked there), `theme/SEO-SETTINGS-REVIEW-2026-11-11.md`, `theme/REVIEWS-HUB-AUDIT-2026-11-17.md`.

## Questions to answer
1. **Scheme:** is "Surface + one category + Stability flag" with road = Daily trainer / Max cushion / Performance trainer / Race and trail = Daily / Trail – Max cushion / Trail – Race the right, future-proof structure? Check it against how brands, shops and review sites group shoes today (look them up live; don't rely on the plan's summary). What would break it in 2–3 years?
2. **Max cushion, narrow rule + "brand's explicit max tier wins":** is this consistent and explainable to a reader? Test it on at least Nimbus, Glycerin, Triumph, 1080, Clifton, Vomero 17/18, Superblast, Megablast, Cloudmonster.
3. **Mapping:** sample at least 60 rows of the CSV (spread over years and categories, include every "by name" row) and list clear errors. Are the precedence rules sound (Racing > by-name > Lightweight > max line > Daily)? Is "Cushioning + Lightweight → Daily trainer" right? Is "Minimalist → Daily trainer" right?
4. **URLs and SEO:** check the URL plan against the live site (read-only: fetch the pages, look at canonicals, redirects, titles; Search Console data is in the `rsg` database, `analytics/README.md`). Is reusing slugs (`racing`, `stability`, `trail`, `training`) while renaming labels right? Are the 301 targets right (e.g. `lightweight` → `performance-trainer`, `minimalist` → `daily-trainer`)? Anything that would lose clicks that the plan misses (sitemaps, internal links, site search routing, schema, breadcrumbs)?
5. **Index rule:** "type or family listing with < 3 reviews → noindex, follow; no rule for brand pages; 0-review brand pages → 301". Sound? Better threshold or criterion?
6. **Timing:** go live 2–3 Dec, ahead of the preannounced December 2026 core update and before the old-article prune (week of 7 Dec). Argue both sides with evidence, then give your view.
7. **What's missing:** dependencies, risks, measurement, rollback. Is the rollback real (terms deleted, relations replaced)?

## Rules (from `CLAUDE.md`)
- Verify against the real systems; don't write a fact you haven't checked; say what you could not check.
- Plain language; name page spots as a reader sees them.
- Live site: read only. Nothing reaches the site.
- End the session with the `rsg-close-out` skill (commit your review, update `ROADMAP.md`).
