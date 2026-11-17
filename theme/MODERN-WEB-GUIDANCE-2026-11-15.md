# Modern Web Guidance — theme check (15 Nov 2026)

_Google Chrome + Edge + Firefox + MDN released **Modern Web Guidance 1.0** on 10 Nov 2026: expert-reviewed guides (about 250 web features, 200 use cases) that coding agents read while writing front-end code, plus a CLI (`npx modern-web-guidance@1.0.0 search | retrieve <id> | legacy-scan`). Checked against the live theme `4ac5ef4` (tag `milestone-2026-11-15`). Next chat: prompt at the end._

## The tool's own scan is narrow
`legacy-scan` on the theme: **1 finding** (`css/base.css:1343`, `max-height` transition → `interpolate-size`). It checks only 33 guides with fixed rules (~20 % of the guidance) and reads only `.js`/`.css` files, not the inline `<script>`/`<style>` in our 98 PHP templates. A test file full of obvious old patterns (XMLHttpRequest, scroll listeners, jQuery, floats, padding-top aspect hack) got 1 flag. **Use the guides (search/retrieve) while building, not the scan as a grade.**

## Hand count (theme incl. inline code in PHP)
| Pattern | Count |
|---|---|
| jQuery calls (`$(` / `jQuery(`) | 881 in 31 files (jQuery 3.5.1 = 89 KB, every page) |
| Dead Universal Analytics `ga(` calls | 359 in 29 files (stubbed since 7 Nov, do nothing) |
| Inline `onclick=`/`onkeyup=` handlers | 154 in 22 files |
| Bootstrap `data-toggle/ride/slide` | 34 in 13 files (`collapse.js` 20 KB + `util.js` on every page) |
| `float` layout / `clearfix` | 46 / 51 |
| `padding-top: x%` aspect hack vs `aspect-ratio` | 7 vs 5 |
| Scroll listeners vs IntersectionObserver | 20 vs 9 |
| `!important` | 1,047 |

## Candidates, biggest payoff first (guide IDs = `npx modern-web-guidance@1.0.0 retrieve <id>`)
1. **Old reviews load ~270 KB of 2018 JavaScript:** `js/review_single.js` (249 KB, jQuery Mobile 2018 bundle) + `js/carousel.js` (18 KB), enqueued in `functions.php` `RSG_ajax_comments_scripts()` for `is_review()` pages that aren't the 4 new review types = the 937 dated reviews (~10 % of views) and legacy GPS. Photo slider → CSS scroll-snap (`carousel`), swipebox lightbox (`css/swipebox.css` on every page) → native `<dialog>` (`dialog`). Check first what else in that bundle the old template uses.
2. **Hidden text findable:** "Show N more" (article shoes box) and other `hidden` content → `hidden="until-found"` (`search-hidden-content`): Ctrl+F and search engines treat it as page content. Small, SEO-relevant.
3. **Dead code:** 359 `ga(` calls (Universal Analytics is gone; stub `window.ga` in `header.php`), then the stub. No guide needed.
4. **Shops drawer** (`rsg_prices_drawer_print()`, `js/rsg-dfs.js`): own focus trap, Escape, scrim and scroll lock → native `<dialog>` + `showModal()` (`dialog`, `prevent-page-scroll-under-modal-dialog`). Keep box code `drw`, the opener tracking and Raptive z-order behaviour. **Only after click spots is live** (`CLICK-SPOTS-BRIEF-2026-11-15.md` changes the same files: `includes/rsg-dfs-prices.php`, `js/rsg-dfs.js`, drawer opener codes); rebuild the drawer on top of that version.
5. **Bootstrap 3-era scripts on every page:** `collapse.js`, `util.js`, `ajax_mega_menu.js` (menu is printed in the page since step 2: likely only hover code left) → `<details>` / popover (`accordion`, `declarative-dialog-popover-control`).
6. **Comment-form slider** `rangeslider.js` (~18 KB inline in every review) → styled native range (`slider`; audit B3).
7. **Layout leftovers** (floats, clearfix, aspect hacks) → grid/flex + `aspect-ratio` (`responsive-design`), with the template rebuild.
8. **jQuery itself:** remove template by template in the redesign; not worth a rewrite on its own.
9. **Hero image priority:** `fetchpriority="high"` on the first gallery photo (`optimize-image-priority`; audit B4).

Rules for any of this: staging first, one item per deploy, same look, extractor check after markup changes, ad counts after structural changes on ad pages (Raptive places by HTML structure), no `:is()` / selector lists in `:not()` (WP Rocket Remove Unused CSS), browser support decided from GA's browser/OS share (older phones still visit).

## Prompt for the next chat
```
RSG — modernize the theme front-end with Modern Web Guidance (staging first)

Read first (project docs): theme/MODERN-WEB-GUIDANCE-2026-11-15.md (the list), theme/RUNBOOK.md (Mac copy is current: deploy,
caches, rules), theme/AFTER-GO-LIVE-2026-11-15.md, theme/HTML-AUDIT-RESKIN-2026-11-15.md (B2–B4), claude/DECISIONS.md (15 Nov).
Mac folder: ~/Documents/claude/projects/rsg (theme repo theme/rsg_2022/, main = live = 4ac5ef4, tag milestone-2026-11-15).

Setup: use Modern Web Guidance 1.0.0 as a reviewed local copy (npm install --ignore-scripts modern-web-guidance@1.0.0 in a
scratch folder; `npx modern-web-guidance search/retrieve <id>`), not the auto-install into my agents. Before item 1, pull GA4
browser + OS share (or our view log) and propose the browser policy (what must still work on older phones).

Step 1 — short plan: confirm or correct the list in the doc against the real code (what review_single.js is actually used for
on the old-review template, whether ajax_mega_menu.js still does anything, where `hidden` content is), with before numbers per
item (JS/CSS bytes on the affected pages, requests, CLS/LCP lab). Order by payoff / risk. Wait for my OK.

Step 2 — one item at a time, staging only, waiting for my OK each time: git + deploy.sh staging, one staging deploy per item,
same look (element screenshots before/after), extractor check after markup changes (checks/extractcheck.py, no new trafilatura
MISSING items), ad counts after structural changes on pages with ads, no :is() / selector lists in :not(), new CSS rules present
in the used CSS. Retrieve the matching guide before writing code and follow it.

Step 3 — after each approved batch, a short go-live plan; nothing goes live until I write "deploy to live". Rollback = previous
live tag. Docs: RUNBOOK "What runs where", DECISIONS line, this doc's list ticked.

Order with click spots (fixed): click spots (theme/CLICK-SPOTS-BRIEF-2026-11-15.md) goes first and touches
includes/rsg-dfs-prices.php + js/rsg-dfs.js. Until click spots is live, do NOT touch those two files or the drawer (item 4);
start with items that don't (2, 3, 1, 5, 6, 9). Before touching them, check DECISIONS / the click-spots brief status line says
"live"; if not, skip to the next item and tell me.
Coordination: check git status, GitHub main and the last staging tag before starting and before every deploy; commit only your
own hunks; deploy from a clean worktree of the exact commit; never deploy over another chat's staging work.

My working style: short summary first, then one item at a time waiting for my input. Be thorough, verify against the real
systems, change only what I approve.
```
