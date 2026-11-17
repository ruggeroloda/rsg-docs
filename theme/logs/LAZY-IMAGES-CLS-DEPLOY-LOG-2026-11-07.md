# Lazy images + CLS — staging deploy log, 7 Nov 2026

**Where:** STAGING only (jfx92wmcr3-staging.onrocket.site). Live untouched.

## Why
The theme's own 2016 lazy-loader showed a 23 px blurred copy of each image, then swapped in the real one (footer script). The copy's proportions never matched the real image, so every swap moved the page. WP Rocket's lazy-load is OFF on this site (not involved).

## Change (backups `*.bak-lazy-20261107-1943` next to each file)
1. `includes/progressive-image-in-content.php`: `wpse_235266_image_html()` returns early on pages (`if ( ! is_feed() ) return $content;`), so article and ACF images keep WordPress core output: `width`/`height`, `loading="lazy"`, `decoding="async"`, `srcset` (`wp_filter_content_tags` already runs on `the_content` and `acf_the_content`). The feed clean-up still runs.
2. `functions.php` `build_progressive_picture()`: outputs one `<img>` with `src`, `srcset`, `sizes`, `width`/`height`, `loading="lazy"`, `decoding="async"`, inside the same `<picture class="progressive-picture processed">` wrapper (existing CSS unchanged; the footer script ignores it). JPEGs get class `rsg-ph`.
3. `css/base.css`: `img.rsg-ph, img[class*="wp-image-"][src*=".jp"] { background-color: #eef0f2 }` (grey while a photo loads; JPEG only, so transparent cut-outs never show it).
Not changed: the footer script (still needed by ~20 templates that write `<picture class="progressive-picture">` by hand: GPS/trail/training review headers, review lists, shortcodes, related posts; convert them when those templates are touched).

## Result (Playwright, same script before/after; "scroll" = after scrolling the whole page)
| Page | Before load / scroll / LCP | After load / scroll / LCP |
|---|---|---|
| Review Vomero 18, mobile | 0.002 / 0.059 / 2.9 s | 0.002 / **0.014** / **1.5 s** |
| Review, desktop | 0.012 / 0.033 / 2.7 s | 0.012 / 0.019 / 2.7 s |
| Top (puma), mobile | 0.074 / 0.074 | **0.022** / 0.022 |
| Guide (best hoka), mobile | 0.230 | 0.230 (not images, see below) |
| Home, desktop | 0.036 | ~0.04–0.09 (varies with ads, not images) |
Images look identical (guide cards 0.0 % aspect difference; review photos unchanged).
Together with the gallery fix of the same day (`../dfs/DEPLOY-LOG-STEP6.md`): review desktop load CLS 0.13–0.22 → 0.011.

## Found, not fixed
- **Guide/top pages, mobile, ~0.20 at ~6.6 s**: H1 rewraps and the content width changes when the Raptive ad script and the Typekit fonts finish (featured image `attachment-post-thumbnail` resizes). Same before/after. Needs its own look (font loading / ad slot reservation).
- **Homepage, desktop**: a top block changes height ~3.9 s (fonts/ads).
- `comments-2022.php:126` prints `srcset=" 160w"` when the thumbnail is empty → one 404 request per page.

## Rollback
Copy the three `.bak-lazy-20261107-1943` files back.

## Live
**Deployed to live 7 Nov 2026 20:33 UTC** together with the other CWV fixes: see `CWV-FIX-DEPLOY-LOG-2026-11-07.md` (live results, rollback).
