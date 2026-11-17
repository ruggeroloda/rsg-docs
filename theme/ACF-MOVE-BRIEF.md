# Brief — move ACF out of the theme (11 Nov 2026)

_First step of the in-place theme rebuild (`THEME-REBUILD-NEXT-STAGE.md`, batch 3 of `CLEANUP-INVENTORY-2026-11-11.md`). Research and site facts gathered 11 Nov._

## ✅ DONE 11 Nov 2026 — live (the rest of this brief is the original plan, kept as history)
- **Secure Custom Fields 6.9.8** (plugin) active on live since 13:09 UTC, staging before; the theme no longer bundles ACF (13:20 UTC). Theme commits `5311929` (guard), `d9080f1` (WYSIWYG), `90bde70` (2 PHP 8 fatals), `897e242` (bundled ACF + acf-to-rest-api removed), `631f2da` + `b335c7c` (REST API, see below). All live deploys with `--no-purge` (geo-cache measurement).
- **Tests (staging, then live):** 87-page crawl before/after (identical apart from random related cards, `&` → `&#038;` inside links, a caption id counter); all 18,326 field values printed escaped vs unescaped; 15 admin edit screens; Pixian save (test mode); Make-style insert (the 51 keys) + admin save + render; error log before/after; `--skip-plugins` WP-CLI runs.
- **Found and fixed:** (1) the theme's `acf/settings/dir` filter would have pointed SCF's admin JS/CSS at the old copy → the whole old ACF block is guarded, then removed; (2) SCF escapes `the_field()` → **video embeds in WYSIWYG fields were stripped** (legacy review/GPS pages, some comparisons) → WYSIWYG fields print unescaped as before (`acf/the_field/allow_unsafe_html`, WYSIWYG only); (3) options page now registered on `acf/init`; (4) two old PHP 8 fatals: review templates with no gallery yet (`count(false)`), SEO handler on the page edit screen (`term_exists(WP_Post)`).
- **Corrections to this brief:** (a) ACF does **not** resolve Make's raw meta by name (5.7.5 neither): Make drafts get their field references when saved in wp-admin (all 367 published reviews have them); unchanged by the move. (b) `acf-to-rest-api` was not only `/acf/v3`: this copy (plugin 3.3.2, key renamed `acf` → `custom_fields`, rest stripped) added **`custom_fields`** to every `/wp-json/wp/v2/` response. Nothing active used it (Make's API traffic = `wp/v2/comparison`, title/link only; 9 **inactive** Make scenarios mapped `custom_fields.*`). Replaced by SCF's own REST support for **every** field group (`acf` key, read + write, see `CLAUDE.md`). (c) Sub-pages: only one options page exists (the 2 sub-pages were commented out). (d) `the_field()` calls: 37 + 2 `the_sub_field()`.
- **Database:** 66 trashed field groups and the 2 Amazon fields deleted (staging + live). **Kept:** disabled group "Saved NOT USED fields" (holds real data on 9 guides). 22 orphaned field rows pre-date the move (left alone).
- **Not done (decided):** local JSON / field definitions in git. The database stays the single source; the clean way to version fields is registering them in code in `rsg-core` (rebuild).
- **Backups:** `_private/acf-move-2026-11-11/` (field-group JSON + SQL of `acf-field*` posts and options, staging + live; the 2 Amazon add-on key files) and `~/rsg_acf_backup_20261111/` on the server (+ test logs). Rollback of the theme: previous `live-*` tag; SCF can be deactivated only together with a theme that bundles ACF again.

## Today
- **ACF PRO 5.7.5 (2018) is bundled inside the theme** (`rsg_2022/includes/plugins/acf3/`, loaded by `functions.php`). No licence registered (`acf_pro_license` empty) → no updates for ~8 years. 52k of the theme's 114k PHP lines.
- **Field groups live in the database** (30 published + ~70 trashed "Guide header"/"(copy)" groups) plus one local PHP group (`includes/rsg-comparison-acf-fields.php`, comparison redesign fields).
- **PRO features in use:** 21 repeater fields (pros/cons, review sections, guide rows), 2 gallery fields, options pages registered in `functions.php` (~line 1325: `acf_add_options_page` + 2 sub-pages; e.g. `not_found_links`, `geo_uk_link`). No flexible content or clone found. 664 `get_field()` calls, 28 `the_field()` calls.
- **Custom field types** `acf_amazon` / `acf_amazon_image` (bundled add-on `acf3/addon/`, its 2 PHP files hold an AWS key pair and are kept out of git): used by 2 fields (`amazon`, `amazon_img`) in the "Products" group, **0 saved values** → drop the add-on and those 2 fields.
- **`acf-to-rest-api`** (bundled, `includes/plugins/acf-to-rest-api/`, loaded in `functions.php` ~line 1382) publishes ACF fields at `/wp-json/acf/v3/…`. Last 6 weeks: ~29 calls, all browser user agents from random IPs (scrapers). Nothing of ours found using it → candidate to drop (confirm with Ruggero).
- **Hooks on ACF:** Pixian background removal runs on `acf/save_post` (`includes/remove_bg.php`).
- **Make writes reviews without ACF.** Active scenarios "Insert Road Shoe" (637210), "Insert Trail Shoe" (718279), "Insert Training Shoe" (1664888): WordPress module `createPost` with **51 raw post-meta keys** (e.g. `model`, `vote`, `rrp_us`, repeater storage `cons`=3 + `cons_0_cons_line`…, `pros`=5 + `pros_N_pro_line`), via the integromat-connector plugin. No `_field` key references are written; ACF resolves the fields by name. Storage format is the same in ACF 5, ACF 6 and SCF → Make should keep working unchanged. **Must be proven on staging.**

## Options
| | Cost | Notes |
|---|---|---|
| **Secure Custom Fields (SCF)** | free | WordPress.org fork of ACF (Oct 2024), 100k+ installs, updated weekly (6.9.8, 3 Nov 2026), backports ACF fixes within days. WordPress.org developer docs list Repeater, Flexible Content, Gallery, Clone and options pages. One 2026 review says SCF only backports fixes for the former PRO fields (no new features for them) and treats them as outside its stable API. Same database format and `acf_` functions. Activating SCF deactivates ACF/ACF PRO. Politically born from the WordPress.org vs WP Engine dispute. |
| **ACF PRO** (WP Engine) | $49/yr, 1 site (staging/dev URLs don't count) | Original vendor, 2M+ installs, licence gives updates. |
| Keep 5.7.5 | free | No security fixes; blocks PHP 8.4+; not an option long term. |

**Leaning:** SCF if the staging test passes (repeaters, galleries, options pages, admin screens, Make-style insert); otherwise ACF PRO.

## Known behaviour changes vs 5.7.5 (both SCF and current ACF)
- **HTML escaping (ACF 6.2.5+):** `the_field()` / `the_sub_field()` and the ACF shortcode pass output through `wp_kses`; scripts/iframes/some attributes get stripped. ACF logs affected output and shows an admin notice; per-field opt-out filter `acf/the_field/allow_unsafe_html`. `get_field()` is not escaped. → review the 28 `the_field()` calls (WYSIWYG/textarea fields, anything printed inside JS or attributes).
- Admin UI is different (ACF 6 field group editor). Old internal functions may be deprecated — grep the theme for `acf_` internals beyond the public API.

## Safe sequence (do not activate the plugin while the theme still `require`s its own ACF — two `ACF` classes = fatal error)
1. Backups: export all field groups to JSON (and DB backup of `acf-field*` posts + `wp_options` `options_*`).
2. Theme commit A: load the bundled ACF only `if ( ! class_exists( 'ACF' ) )` (and the same guard for acf-to-rest-api if kept). Deploy staging.
3. On staging: install + activate SCF (or ACF PRO). The theme now skips its copy.
4. Test on staging: crawl before/after (all page types, `deploy.sh` crawl routine), admin edit screens of a road/trail/training review, guide, comparison, options pages; Pixian hook on save; a Make-style insert (create a draft with the same 51 meta keys via REST or WP-CLI, render it); PHP error log; `the_field()` escaping notices.
5. Theme commit B: remove `includes/plugins/acf3/` (incl. the Amazon add-on) and, if agreed, `acf-to-rest-api`; delete the 2 dead Amazon fields and the ~70 trashed field groups; turn on local JSON (`acf-json/`) so field groups are versioned in git.
6. Live: plugin install + commit A, then commit B, each verified; no change needed in Make.

## Gains
Security (maintained code), PHP 8.4+ path, fields independent of the theme (rebuild can't lose them), theme ~46% smaller, field definitions in git.
