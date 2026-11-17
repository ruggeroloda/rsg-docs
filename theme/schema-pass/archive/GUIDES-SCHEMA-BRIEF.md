# NEXT SESSION BRIEF — Guides schema pass (phase 2)

**Goal:** add valid, **frontend-invisible** JSON-LD to **guide** pages (`single-guide.php`),
the same way the review (2026-06-21) and comparison (2026-06-21) passes did. Looks/markup on
the page must not change. Additive only — PHP 8.3 is live & stable; guide template already
passed 8.3 fatal-hardening.

> Reuse the existing machinery in `includes/rsg-schema.php` (now also holds the comparison
> builders). Same pattern: build PHP arrays → `wp_json_encode()` → echo one
> `<script type="application/ld+json">` in `<head>`, guarded by post type.

## How to connect (same as before)
SSH key = `../.ssh-rocket-key` (gitignored). In the Linux sandbox copy to outputs + chmod 600:
```bash
cp "<rsg-redesign>/.ssh-rocket-key" /sessions/<session>/mnt/outputs/.rsgkey && chmod 600 /sessions/<session>/mnt/outputs/.rsgkey
ssh -i /sessions/<session>/mnt/outputs/.rsgkey -p 22 -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null jfx9wij@131.153.143.194
```
- Host/user/port: `131.153.143.194` · `jfx9wij` · `22` · key-based.
- Live theme: `~/public_html/wp-content/themes/rsg_2022`
- Staging theme: `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022`
- **ALL WORK ON STAGING FIRST.** Lint BOTH: `ea-php83 -l` and `ea-php74 -l` must stay clean.
- ⚠️ Staging is `robots: Disallow /` → validate in Rich Results Test **Code mode**, or live after deploy.
- WP-CLI: `wp …` (binary at `/usr/local/bin/wp`) — NOT `php wp`. Run from the relevant webroot.

## Two remote-bash quirks (bit us last time)
1. **Process substitution `< <(...)` / `diff <(...) <(...)` FAILS** over `ssh 'bash -s'` —
   write to temp files and `diff fileA fileB` instead.
2. **Don't pipe curl into `python3 - <<'PY'`** — the heredoc becomes stdin, so `sys.stdin.read()`
   gets the heredoc, not the page. Save curl output to a file first, then read the file in python.
3. Cache-bust validation curls with `?cb=$(date +%s%N)` (bypasses WP-Rocket; Cloudflare query-string
   key → MISS → fresh origin render). Confirm with `cf-cache-status: MISS` in headers.

## Guide page data (in `single-guide.php`, ~2,674 lines)
- Guides are multi-product roundup / "best of" listicles. 15 published (`post_type=guide`).
- Featured products live in an **ACF repeater `review_sections_2023`**; iterate with
  `have_rows('review_sections_2023')` / `the_row()`. Per row:
  - `get_sub_field('winner')` = the **review post ID** of that section's pick (the Product).
  - `get_sub_field('title')` = section/category label (e.g. "Best for long runs").
  - `get_sub_field('introduction_text')` = section blurb.
  - Some rows reference shoes via `get_sub_field($shoe_section['slug'])` and may use
    `amazon_link` / `override_amazon_tag` — handle gracefully (skip if no resolvable post).
- Page title: `get_field('in_page_title')` (+ modified `F Y`); subtitle `in_page_subtitle`.
- Count of picks: `count(get_field('review_sections_2023'))`.

## Recommended schema design (guides)
Emit an **`ItemList`** of the featured products (NOT two Products like comparisons). Per the
brief's phase-2 note. Suggested shape:
- One `ItemList` node: `name` = guide title, `itemListOrder` = "https://schema.org/ItemListOrderDescending"
  (picks are ranked), `numberOfItems`, and `itemListElement` = ordered `ListItem`s.
- Each `ListItem`: `position` (row order), and `item` = a **Product** node built by the EXISTING
  shared `rsg_build_product_node($winner_id)` (reuse it — that's why it was factored out). This
  gives each pick brand/image/specs/offers/aggregateRating for free, consistent with comparison.
- Add a `BreadcrumbList`: Home → Guides (`/guide/`) → "<guide title>" (permalink).
- Optionally also emit an `Article`/`mainEntity` wrapper — but keep scope tight; ItemList +
  Breadcrumb is the core. Decide during the pass.
- Guard the winner resolution: skip rows whose `winner` is empty / not a valid published post
  (Amazon-only picks may not have a review post — `rsg_build_product_node` returns `array()` and
  the row is simply omitted from the list).

## Files to change (staging) — minimal, mirror prior passes
1. `includes/rsg-schema.php` — ADD `rsg_build_guide_schema()` (iterates `review_sections_2023`,
   builds ItemList of `rsg_build_product_node()` items + Breadcrumb) and `rsg_emit_guide_schema()`
   (guard `is_singular('guide')`, no-op otherwise). Reuse `rsg_build_product_node()` as-is.
2. `header.php` — add `rsg_emit_guide_schema();` next to the review + comparison emit calls.

## Verification checklist (same bar)
- [ ] `ea-php83 -l` AND `ea-php74 -l` clean on both changed files.
- [ ] 3–4 staging guide URLs: extract ld+json, `python3 -m json.tool` valid; confirm
      `@type`s include `ItemList` (+ `BreadcrumbList`) with N `ListItem`→`Product` items.
- [ ] **Frontend provably unchanged**: visible-text similarity = 1.0000, 0 changed spans vs the
      emit-OFF render (toggle the header call, two cache-busted renders, normalise tokens).
- [ ] Google Rich Results Test (Code mode for staging, or live after deploy) = 0 errors;
      ItemList + Breadcrumb detected.

## Deploy (only after verification) — same method as the comparison pass
1. `diff` live vs staging for the 2 files → confirm ONLY the additive guide changes differ.
2. Back up live copies → `~/rsg_backups_predeploy_<ts>/` + snapshot-tar the live theme.
3. Copy 2 files staging → live; `cmp` byte-verify IDENTICAL; `ea-php83 -l` on live.
4. Purge WP-Rocket file cache (`wp-content/cache/wp-rocket/*`) + `wp cache flush`; then USER clicks
   **Cloudflare "Purge Everything"** in the Rocket.net panel.
5. Re-validate a live guide URL in Rich Results Test.
**Rollback:** restore the 2 backed-up files; functions are self-contained (no DB deps).

## Key references
- `COMPARISON-DEPLOY-LOG-2026-06-21.md` — the just-shipped pass to mirror (this folder).
- `SCHEMA-PASS-2026-06-21.md` + `rsg-schema.php` — the original review pass machinery.
- `theme/logs/DEPLOY-LOG-2026-06-21.md` — PHP-8 deploy + safe deploy/backup pattern.
- `../CONVENTIONS.md` — environment, rules, phased plan.

## Also still queued (separate, heavier, DFS project)
Dead Amazon-direct pricing path (GPS + `amazon_link` items) — `../dfs/AMAZON-PATH-HANDOFF.md`.
Best as its own DFS-focused session.
