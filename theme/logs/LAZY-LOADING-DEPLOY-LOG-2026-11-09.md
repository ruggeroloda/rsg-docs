# Deploy log — native lazy loading re-enabled (2026-11-09)

**Why:** `functions.php` had `add_filter( 'wp_lazy_loading_enabled', '__return_false' );` (WP Rocket lazyload also off), so every photo inside an article downloaded at page load. The 7 Nov lazy-images work assumed WordPress core added `loading="lazy"` to article/ACF images; this line prevented it. WordPress also gave `fetchpriority="high"` to a photo deep in the article (e.g. 4,800 px down on the Pegasus 42 review), competing with the real top photo.

**Changes (staging, then live; staging/live blocks verified identical):**
1. `functions.php`: the filter line removed (WordPress default lazy loading back on, also for iframes).
2. `functions.php`: `wp_get_loading_optimization_attributes` filter — on single `reviews_road/trail/training/gps`, `review`, `gps`, `post`, `content`: images from `the_content` **and `acf_the_content`** (review text lives in ACF fields) always `loading="lazy"`, never `fetchpriority`. Reason: on these templates the article starts below the theme's header gallery/photo; WordPress's default keeps the first 3 article images eager.
3. `single-guide.php`: header photo `the_post_thumbnail( 'post-thumbnail', array( 'loading' => false, 'fetchpriority' => 'high' ) )` — with lazy loading on, WordPress had made the guide's main image (LCP) lazy.

**Checks:**
- 14 templates × mobile/desktop (staging + live): no lazy image in the first screen, main (LCP) image never lazy. Only exception: desktop `/reviews/` hub, one card lazy in the first screen — **pre-existing** (theme's own 7 Nov loader, same on live before), left for the theme rebuild.
- Sample of 20 posts/content articles (2009–2025), mobile + desktop: 40/40 OK.
- Live CLS after full scroll 0–0.038 (good < 0.1); no broken images.

**Result (live, Pixel 7 emulation, Lighthouse-like slow 4G + 4× CPU, ads blocked, cached pages, median of 5, page load without scrolling):**

| Page | Own KB before → after | Image KB | LCP before → after | Load event |
|---|---|---|---|---|
| Road review (Pegasus 42) | 3,116 → 518 | 2,965 → 367 | 3.46 → 3.23 s | 16.7 → 4.8 s |
| Review-type (Speedgoat 5) | 1,249 → 344 | 1,112 → 208 | 2.92 → 2.33 s | 7.5 → 3.7 s |
| Training review (Nano X4) | 773 → 348 | 625 → 200 | 2.56 → 2.34 s | 5.2 → 3.5 s |
| GPS review (FR 165) | 737 → 331 | 585 → 178 | 2.10 → 2.03 s | 5.3 → 3.4 s |
| Post (Structure Plus) | 614 → 300 | 479 → 163 | 2.52 → 1.68 s | 4.5 → 3.3 s |
| Guide (best Nike) | 365 → 371 | 219 → 224 | 3.48 → 1.86 s | 3.5 → 3.5 s |
| Home / top / comparison | unchanged | unchanged | unchanged (noise) | unchanged |

**Backups:** `functions.php.bak-lazy-20261109`, `single-guide.php.bak-lazy-20261109` (live + staging). **Rollback:** copy both back, `wp eval 'rocket_clean_domain();'` + `wp cdn purge`.

**Next (not done):** `sizes` attribute fix so 2× screens get the 768 px file (no new files); hub card lazy flag in the theme rebuild; road review LCP (~3.2 s on slow 4G) is the header gallery photo itself.
