# Theme ghost cleanup — inventory (11 Nov 2026)

_**Status 11 Nov:** batch 0 done (archive + recovery note: `_private/server-cleanup-2026-11-11/`); batch 1 live (`0f29e4b`, tag `live-20261111-084106`; 62 staging pages compared before/after, 8 live pages checked). Batch 2 part 1 live 11 Nov ~09:06 UTC (`f260556`): items a-content (geni.us), c, d, e, h + fake counter + `log_country_code` route; old themes rsg_2018/rsg_2019 and the `log_country_code` IP table archived and deleted. Batch 2 part 2 live 11 Nov 09:38 UTC (`4ed3998`): items a (guides → Amazon button, retired API gone) and b (share counts). Item g done 11 Nov 11:20 UTC (`81a9f26`: `/feed/tca` + `/feed/tca-best` removed, deployed with `--no-purge`). Batch 3 ACF item done 11 Nov (live 13:20 UTC, `897e242`; `ACF-MOVE-BRIEF.md`): bundled ACF + acf-to-rest-api gone, SCF plugin; theme PHP 114k → ~62k lines. Open: f (widgets, after DFS Phase F), rest of batch 3 (`rsg-core`: audit + plan `RSG-CORE-AUDIT-2026-11-11.md`; security fixes and the empty ad filter live 12 Nov), site pass (`affiliate_product` erased 12 Nov; **rest done 12 Nov ~08:40 CET**: post types/terms/options/files of removed plugins, stale page templates, import-users plugin; details `RUNBOOK.md` gap 3; content audit stays a separate decision)._

_Inventory was read-only. Each batch: commit in `rsg_2022/` → `deploy.sh staging` → before/after page comparison → `deploy.sh live`. Approve per batch._

**Size today:** 114k lines of PHP in the theme, of which **52k (46%) is the bundled ACF Pro 5.7.5**. 436 asset files.

**How "dead" was decided (evidence, not guesses):** what WordPress actually uses (published post types, assigned page templates, taxonomies, widgets, shortcodes in published content), the include graph from `functions.php` and every template WordPress can pick, real (non-comment) function calls found with PHP's own tokenizer, the live HTML of key pages, and 6 weeks of origin access logs (30 Sep–11 Nov) for every theme file. Caveat: Cloudflare caches assets, so "never requested" alone is not proof; an asset is listed as dead only if it is **also** referenced nowhere.

## Batch 0 — server housekeeping (no code change)
- ✅ **99 `.bak`/`.OLD` copies inside the live theme folder** (117 on staging); git + `~/rsg_deploy_backups/` replace them.
- ✅ **~25 old folders/scripts in the server's home** (`box_upload`, `cwv_upload`, `dfs63_upload`, `lazy_upload`, `ph_deploy`, `backfill_*.php`, `*.tsv`, `rsg_backups_2026062*`…) and a 1.9 MB `error_log` last written 26 Aug.
- ✅ **Inactive plugins:** classic-editor, rsg-cvs-to-gps, rsg-cvs-to-rrp, rsg-professional-import, wp-sweep.
- ✅ **Default themes:** twentytwentyone/two/three/four (keep twentytwentyfive as WordPress's fallback).
- ✅ `rsg_2018` / `rsg_2019`: deleted 11 Nov after batch 2e (archive `9-…` in `_private/server-cleanup-2026-11-11/`).

## Batch 1 — never loaded (lowest risk) ✅ live 11 Nov
**24 theme PHP files, ~11,900 lines**, not reachable from any template WordPress can use:
- Page templates no page uses: `template-home-2018.php`, `template-plans.php`, `template-plans-2-alt.php`, `template-wizard.php`, `template-wizard-2020.php` (plus their `is_page_template()` checks in header/footer/functions).
- Templates for post types no longer registered: `single-best.php` (2,470 lines), `single-gift-guide.php`.
- Includes commented out in `functions.php`: `buying_guides_post_type`, `gift_guides_post_type`, `external_product_post_type`, `trail_post_type`, `theme_options` ×3, `minit`, `mobile_menu`, `widgets/popular-posts`, `fonts/gxs-modules/`.
- Orphans: `includes/simple_html_dom.php` (1,720 lines), `include-single-post-fullwidth.php`, `include-single-gps-css.php`, `includes/amazoncontent-post-type.php`, `includes/widgets/youtube_box.php`.
- **AWeber plugin** `includes/plugins/rsg_aweber/` (live only, not loaded since June; **contains keys**).
- **72 asset files, 1.8 MB**, referenced nowhere and never requested: `index_files/` (stock demo images), `images/survey2018/`, `images/home-2018/`, `images/home-2020/test*.jpg`, `images/plans/`, `images/book-*`, `css/base_OLD.css`, `css/bootstrap.min.css` (unminified one is used), `js/jquery.min.js` (1.10.1), `swipe.js`, `js/rsg_2015_madhu.js`, attention-grabber images, old icons… (full list from the inventory script).

## Batch 2 — loaded, but dead features (remove call sites + code; check each on staging)
- **a. Old Amazon Product Advertising API** (`includes/amazon-api.php`, **AWS keys**): still called on guides/top pages (`single-guide`, `single-top`, `template-parts/guide-boxes`, `widgets.php`) for products with an `amazon_link`. It returns nothing (0 Amazon links on /guide/best-gps-running-watches/) and that page takes **2.5 s** to render uncached. Replace with the GPS-style Amazon button or nothing.
- **b. Share counters** (`includes/share_count.php`, **Facebook token + Google key**): Facebook/Twitter/Google+ count APIs, synced every 6 h per post from legacy post/affiliate templates. Those APIs are dead or locked down. Keep plain share links or drop the bar.
- **c. Attention Grabber** (bundled plugin, ~5351 lines of PHP/JS/CSS + images): loaded, no front-end output on the pages checked, its CSS/JS never requested.
- **d. Old ad/tracking leftovers:** Sortable `deployads.js` / `rsg_sortable.js` (+ the `deployads` check in footer), `adtech/`, Facebook pixel (commented), geni.us scripts (never requested), Zarget A/B-test loader (option-driven, company gone), the commented ip-api block in `footer.php`.
- **e. Assets borrowed from inactive themes:** `rsg_2018/images/book-open-small5.png`, `youtube.svg`, `icons/video-play.svg`, `rsg_2019/images/icons.svg`, `wizard2020/bg_shoe*.png` (used by the live wizard page and legacy posts). Copy into `rsg_2022`, repoint → then `rsg_2018`/`rsg_2019` can be deleted.
- **f. Widgets:** no widget is placed in any sidebar, yet `widgets.php` (3,460 lines), `facebook_box`, `author_image` and a post-loop widget are registered. `widgets.php` also holds functions templates call → split at function level first.
- **g. Custom feeds** `/feed/tca` and `/feed/tca-best` (`feed-tca.php`): 0 requests in 6 weeks. Remove unless something (Make/n8n/social) reads them.
- **h. `[dfscat]`:** 1 post, shows nothing since the new DFS (10 Nov) → remove from that post, then the code. (The other 3 theme shortcodes are used: `[rsg_review_row_test]` in 11 posts despite its name, `[rsg_review_row_in_content]` 10, `[rsg_table_seo]` 1.)

## Batch 3 — larger moves (already in the rebuild plan)
- ✅ **ACF Pro 5.7.5 bundled in the theme** → Secure Custom Fields plugin, field groups unchanged (11 Nov, `ACF-MOVE-BRIEF.md`).
- `includes/plugins/seo.php` (2,464 lines, own SEO layer), schema, quiz/email capture → `rsg-core` plugin.
- ✅ `includes/plugins/acf-to-rest-api` removed 11 Nov: its `custom_fields` REST block was used by no active automation; replaced by SCF's `acf` REST block for every field group.

## Second pass — the site, not the theme (decisions, mostly content)
- **Post types left by removed plugins/code:** web-story 16, OptinMonster (optin 12, omapi, amn_om), snp_popups, tablepress, ninja-table, ilj_customlinks, external_product, best 1; gift-guide 3 and buying_guide 4 published but **their registration is commented out** (not reachable).
- Pages assigned to templates that no longer exist (`category-448.php` ×5 old contest pages, `single_ebook.php`, `page_noads.php`, `full-page.php`) fall back to `page.php`.
- Options left by old plugins/themes (`w3tc_minify`, `rsg_theme_2019_switcher`, …) and the autoload size.
- Active plugins to question: import-users-from-csv-with-meta, subscribe-to-comments-reloaded, disable-gutenberg, wp-crontrol; dstudio goes in DFS Phase F.
- Content: 1,525 legacy `post`, 284 `affiliate_product`, 191 `content` → a content audit, separate from code.
