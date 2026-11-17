# Technical SEO Backlog — runningshoesguru.com

_Created 2026-06-23. Technical/code/design SEO only — content rewrites tracked separately._

## Context

Rankings dropped after a Helpful Content Update (HCU). Guides get ~zero traffic / barely
appear in SERP. **Audit conclusion: guides are technically sound** (crawlable, indexed,
internally linked, server-rendered, single H1, valid rich schema, correct canonical). Their
invisibility is primarily a **content quality + freshness** problem (HCU), not a technical
block. The items below are the technical levers that genuinely *help* HCU recovery + general
hygiene. Items #1 and #2 are the highest-leverage technical changes for the HCU/guides issue.

Access: SSH key in `.ssh-rocket-key` → `jfx9wij@131.153.143.194`; theme `rsg_2022`; web runs
PHP 8.3; no SEO plugin (theme/core handle meta, canonical, sitemap, schema).

Status key: ⬜ not started · 🔄 in progress · ✅ done · ⏭️ deferred/declined

---

## HIGH impact

### #1 ✅ Reduce index bloat — author archives (LIVE 2026-06-23)
Indexable surface includes ~96 author pages, ~158 taxonomy archives (category, brands,
shoes_type, shoes_family, content_category, gps_brands), plus 1,525 posts. Post-HCU, Google
judges quality at the site level; mass thin archives drag the whole domain (guides included).
- **DECISION (2026-06-23): Surgical scope, via Path B (combined with #5).**
- **Affected — noindex 43 thin author archives** (≤5 published posts): keeps the 53 real
  contributors (ruggero 587, brandonlaw 403, giulio 350, …). Noindexing an author *archive*
  does NOT deindex that author's posts — only the thin listing page.
- **Affected — noindex 9 empty taxonomy terms:** category `2013-gift-guide-for-runners`,
  `asides`, `run-strong`, `run-wild`, `vibram-running-shoes-reviews`; brands `montrail`;
  content_category `test`; gps_brands `soleus`, `tomtom`.
- **Tags (606):** already `noindex,follow` via PSP `psp_tags_noindex` — no action.
- **Engine:** robots/canonical/title come from vendored Platinum SEO Pack
  `includes/plugins/seo.php` (global per-type toggles only; surgical logic needs custom code).
- **Implementation:** single `wp_robots` filter in `functions.php` (see #5) holds the
  per-author/per-term noindex logic. Threshold = ≤5 posts (tunable).
- **Files/where:** `functions.php` (new `wp_robots` filter), retire robots output in `seo.php`.
- **STAGING DONE (2026-06-23):** built + verified. See #5 for implementation detail.
  Verified live-vs-staging matrix: guide/brand-archive/kept-author indexable; thin authors
  (convertica, andy) now noindex; date/search/category/tag noindex preserved; title +
  description + canonical intact; lint clean PHP 8.3 + 7.4. Backups in
  `~/rsg_backups_20260623_seo/`.
- **LIVE 2026-06-23:** deployed (live verified: convertica/andy noindex, ruggero indexable).
  Users sitemap 96 → 57 (thin authors excluded via `rsg_sitemap_exclude_thin_authors`).
  Live backup: `~/rsg_backups_20260623_seo/live-predeploy-052139/`.
- **⚠️ PENDING USER ACTION:** Cloudflare "Purge Everything" in the Rocket.net panel — edge
  cache is `s-maxage` 30d, so already-cached pages keep the old robots tag until purged.
- **Threshold knob:** `RSG_AUTHOR_MIN_POSTS` (currently 6 ⇒ ≤5 posts noindexed).
- **Remaining for #1 (taxonomy side):** category archives already globally noindex
  (`psp_category_noindex=on`); empty custom-tax terms handled by the filter. Bigger lever
  still open = pruning/auditing the 1,525 posts for thin/outdated content (mostly content work).

### #2 ⬜ Remove hard-coded date/staleness signals on guides
Titles read "(August 2024)", slugs bake in `-2024`, lastmods ~Aug 2024.
- **Action:** drive title month/year dynamically; drop dates from slugs and 301 old → new
  (the `simple-301-redirects` path already works); ensure `lastmod` reflects real updates.
- **Files/where:** `single-guide.php`, post slugs, title logic in `functions.php`.
- **Depends on:** content refresh (coordinate so dates aren't "updated" while text is stale).
- **Notes/decisions:** _(to fill)_

### #3 ⏭️ Ad-stack weight / CWV — DEPRIORITIZED (measured 2026-06-23)
Original concern: guide HTML ~237KB + heavy Raptive/AdThrive ad stack (pub.network, quantcast,
amazon-adsystem, btloader, confiant) = assumed dominant LCP/CLS/INP liability.
- **FIELD DATA (CrUX, mobile, 28-day) for a guide: Core Web Vitals PASSED.**
  LCP 1.7s ✅ · INP 94ms ✅ · CLS 0.03 ✅ · FCP 1.3s ✅ · TTFB 0.9s (amber). SEO score 100.
- **Conclusion:** real-user experience already passes CWV. The ugly Lighthouse *lab* score (Perf
  32) is synthetic/cold-load and NOT a ranking signal. Earlier "high impact" rating was wrong —
  taming ads would chase a number Google doesn't rank on. **Do not invest here for SEO.**
- **Minor residuals (optional, low payoff):** TTFB 0.9s amber; lab Best Practices 50 /
  Accessibility 82 (ad console errors, image aspect ratios, a11y polish) — UX not ranking.
- **Implication:** a page that passes CWV + scores SEO 100 yet gets ~0 traffic confirms the
  guides issue is CONTENT/freshness/E-E-A-T (HCU), not technical.

---

## MEDIUM impact

### #4 ⬜ Fix multiple `<h1>` on homepage
9+ `<h1>` tags; section titles styled as H1. Should be one H1, rest H2.
_11 Nov 2026: review pages now have one h1; the shared "Similar shoes" block (`include-related-reviews-fullwidth.php`) is an `h2.h1` everywhere. The homepage's own section titles are still h1._
- **Files/where:** home template + `.block-title` markup.

### #5 ✅ Consolidate duplicate `robots` meta + drop deprecated directives (LIVE 2026-06-23)
Every page emits two `robots` metas: WP core `wp_robots` (`max-image-preview:large`) + vendored
Platinum SEO Pack (`seo.php` → `index,follow,noodp,noydir,...`). The PSP one carries dead
`noodp,noydir`. **Done together with #1 (Path B):** retired PSP robots output, emit one tag
via a `wp_robots` filter in `functions.php`, dropped `noodp/noydir`.
- **Files/where:** `includes/plugins/seo.php` (robots emission at line ~350 commented out),
  `functions.php` (new `rsg_robots_filter` + `rsg_author_public_post_count`).
- **Design:** filter reads PSP options (`psp_*_noindex`) so wp-admin toggles still work; honors
  per-post overrides (`psp_disable`, `robotsmeta`, `psp_noarchive`, `psp_nosnippet`); keeps
  `max-snippet:-1 / max-image-preview:large / max-video-preview:-1`. Only PSP's *robots* output
  retired — its title/description/canonical untouched.
- **STAGING DONE + verified 2026-06-23. NOT yet on LIVE.**

### #6 ⬜ Serve next-gen image formats
Pages reference 60 JPEG / 38 PNG / 9 JPG / 12 GIF, zero WebP/AVIF.
- **Action:** convert + serve WebP/AVIF (build step or plugin-free `<picture>`/CDN).
- **Files/where:** uploads + theme image output.

### #7 ⬜ Exclude `noindex` post type from XML sitemap
The `noindex` post type appears in the sitemap (4 URLs) — contradictory signal.
- **Files/where:** sitemap filter in `functions.php`.

---

## LOW impact

### #8 ⬜ Collapse http→https→www redirect chain
`http://` non-www does 2 hops. Make it one 301.
- **Files/where:** Cloudflare rules / `.htaccess`.

### #9 ⬜ Set `og:type=article` on guides
Currently `website`.
- **Files/where:** `header.php` OG block.

### #10 ⬜ Modernize legacy `wp_title()` handling
Works today; migrate to `title-tag` theme support eventually. Cosmetic.

---

## Verified good (no action needed)
HTTPS + www canonicalization · robots.txt · WP core XML sitemap · server-side rendering ·
valid rich schema on guides · single H1 on guides · Brotli compression · Cloudflare 30-day
edge cache · proper 404s · PHP 8.3 on web · only 2 render-blocking stylesheets.
