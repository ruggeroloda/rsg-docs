# Core Web Vitals + obvious errors — deploy log, 7 Nov 2026 (LIVE)

Staging first (tested, measured), then **live 20:33 + 20:40 UTC**. Patch script and all file copies: `theme/_work/cwv-fix-20261107/` (`patch.py`, `live-orig/`, `live-new/`, `staging-orig/`, `staging-new/`). Live got only these fixes, **no DFS code** (checked).

## Fixes
| # | Fix | Files | Why |
|---|---|---|---|
| 1 | Gallery images get `width`/`height` + `.swipebox img.full-image{width:100%;height:auto}` | `single-reviews_{road,trail,training,gps}.php` | The 5 review photos at the top had no size: they started ~24 px tall and grew on load, pushing the page down (main cause of review CLS). |
| 2 | Native lazy images instead of the 2016 blurred-placeholder loader (article/ACF images keep WordPress core output; `build_progressive_picture()` outputs one `<img loading="lazy" decoding="async" width height>`; grey background on lazy JPEGs) | `includes/progressive-image-in-content.php`, `functions.php`, `css/base.css` | The blur→real swap shifted the page on scroll. Details: `LAZY-IMAGES-CLS-DEPLOY-LOG-2026-11-07.md`. |
| 3 | Font fallback: Arial scaled (`size-adjust` + ascent/descent overrides) to Proxima Nova / Source Sans Pro, inline `<style id="rsg-font-fallback">` after `wp_head()`; WP Rocket RUCSS told to keep it (`rocket_rucss_inline_atts_exclusions`) | `header.php`, `functions.php` | When the Typekit fonts arrived (~1.2 s), text rewrapped and moved the page (guide mobile CLS 0.20). Android has no Arial → unchanged there. |
| 4 | `base.css` versioned by file time (`filemtime`) instead of the WP version | `functions.php` | The edge and browsers kept the Dec 2024 `base.css` after edits (`?ver=7.0.8` never changed). |
| 5 | Old markup kept inside HTML comments no longer runs/prints (`<!-- … -->` → `<?php if ( false ) : ?> … <?php endif; ?>`) | 4 review templates (6 blocks) | 20–35 KB of HTML per review, old gallery + old price box code running on every view, 20 affiliate links inside comments on reviews. Visible output proven identical (text, links, images). |
| 6 | `window.ga` stub | `header.php` | Universal Analytics was removed long ago; ~90 affiliate links per page call `ga(...)` on click → `ga is not defined` error on every click. Real click tracking comes with DFS 6.4. |
| 7 | No empty `srcset=" 160w"` | `comments-2022.php` | One 404 request per page on guides/top/… |

Also done after deploy: WP Rocket used-CSS + minified CSS + domain cache cleared (`rocket_domain_options_changed`, `rocket_clean_minify('css')`, `rocket_clean_domain()`), Rocket.net HTML purged (API, tag `html`) — needed because live serves WP Rocket "Remove Unused CSS" inline styles and minified copies that did not include the new rules.

## Result (Playwright, US connection, cached pages; CLS = Google's session-window value after load + full scroll)
| Page | Mobile CLS before → after | Desktop CLS before → after | LCP mobile after |
|---|---|---|---|
| Road review (Vomero 18) | 0.349 → **0.021** | 0.172 → **0.004** | 1.4 s |
| Trail review (Cascadia 19) | 0.349 → **0.021** | 0.186 → **0.004** | 1.2 s |
| Training review (Nano X4) | 0.413 → **0.020** | 0.169 → **0.004** | 1.0 s |
| GPS review (Forerunner 165) | 0.515 → **0.024** | 0.168 → **0.004** | 1.4 s |
| Guide (best Hoka) | 0.201 → **0.001** | 0.003 → 0.000 | 0.9 s (was 4.8 s) |
| Top (Puma) | 0.074 → 0.003 | 0.021 → 0.001 | 0.9 s |
| Comparison | 0.031 → 0.001 | 0.012 → 0.001 | 1.2 s |
| Home | 0.002 → 0.001 | 0.004 → 0.032 (ads/top block, < 0.1) | 1.0 s |
| Reviews hub | 0.008 → 0.002 | 0.038 → 0.001 | 1.0 s |
Own errors: 404 `…/160w` on guide/top pages before → none after. Remaining console errors are ad-network ones (Raptive partners), not ours. Review HTML ~15 % smaller (road 289 → 246 KB).

## Search Console
Field data (CrUX) is a 28-day rolling window: the CWV report improves gradually over ~4 weeks. In GSC → Core Web Vitals, open each CLS issue and press "Validate fix" to have Google track it.

## Not fixed (known)
- ~20 templates still use hand-written `<picture class="progressive-picture">` + footer lazy script (review lists, shortcodes, related posts…); they did not show up as big CLS sources in the measured pages. Converted in the theme rebuild.
- Android: font fallback does not apply (no Arial); measured mobile emulation uses Arial-compatible fonts.
- Home desktop 0.03 from a top block/ad changing height.

## Rollback
Live backups next to each file: `*.bak-cwv-20261107-2033` (all 9 files) and `*.bak-cwv2-20261107-2040` (`header.php`, `functions.php`, `css/base.css` second round). Restore = copy back, then clear WP Rocket CSS (same `wp eval` as above) and purge Rocket.net HTML. Staging: `*.bak-cwv-20261107-2022`, `*.bak-cwv2-*`.

## Note for DFS step 7 / later deploys
Live `functions.php`, `header.php`, `css/base.css` and the 4 review templates now contain these fixes. Staging = live + DFS 6.3 code. When the DFS code goes live, patch the live files (add the `rsg-dfs-prices.php` require, schema line, template wraps) — never copy staging files over live (staging also has older drift in `archive.php` and `single-reviews_gps.php`).
