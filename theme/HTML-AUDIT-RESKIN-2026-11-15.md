# Review page HTML after the reskin — audit (15 Nov 2026)

Staging = theme `c66d78c` (reskin through 5.2) vs live `7462d98`. Samples: Pegasus 42, Cumulus 28 (Googlebot UA); after the fixes also Cascadia 19, Nano X4, Forerunner 165. Extractors used as proxies: trafilatura (Common Crawl / FineWeb-style datasets) and readability-lxml (reader-mode / many agent fetch tools). Google renders pages itself and does not depend on these. Script: `checks/extractcheck.py` (Mac `theme/checks/`).

## Headline
Wire size fine (~48 KB brotli, live 49). JSON-LD unchanged and solid. Gains: compare table now extracted as a real table, expert score extracted. **Three reskin regressions** in what extractors keep (Pegasus: trafilatura kept 46/69 page items on staging vs 65/69 on live) — **fixed on staging 15 Nov (`d9c1e11`), now 66/69**; menu headings fixed too (`b444ddf`). Biggest efficiency problem is not the HTML: full-size shop thumbnails (Nike 3.6 MB PNG for a 64×48 thumb), also on live.

## A. Reskin regressions (fix before go-live)
| # | Finding | Fix | Status |
|---|---|---|---|
| A1 | Trust/proof layer gone from extracted text: "Why you can trust us" was `<aside class="rsg-trustbox">` (dropped as complementary); author box: classes contained "author" + bio in a `<div>` (dropped). Staging extraction had no reviewer name and no "bought with our own money"; live had both | trust box `<section>`; author box `rsg-reviewer*` classes, bio in `<p>` | **done** `d9c1e11` |
| A2 | Rating breakdown values gone: class `rsg-ratings` matches trafilatura's rating-widget filter; each `dt` also had an empty `aria-hidden` `<dd>` for the bar | `dl.rsg-scores`; bar drawn by `.rsg-rate::after` from `--v` on the row (one dt, one dd) | **done** `d9c1e11` |
| A3 | Stack height gone: its `<dl>` was inside `<figure>/<figcaption>` | `div.rsg-stack` + `div.rsg-stack-cap` (same look) | **done** `d9c1e11` |
| A4 | Printed mega-menu: 12 `<h6>` (2 empty) before the `<h1>`; 108 links, 40 KB raw (+1.8 KB gz, OK for no admin-ajax). Also: 4 menu links are 301s; 46 links newly visible on every page | titles `<p class="h6">` labels, each column a `<ul aria-labelledby>`, empty titles = empty spacing div, brand icons `alt=""`, cache key `rsg_mega_menu_v2_`. **Links cleanup + redirects = pre-go-live checklist** (`archive/RESKIN-GO-LIVE-CHECKLIST.md`, Ruggero) | **markup done** `b444ddf` |

Checks after A1–A3 (15 Nov): element screenshots (trust box, reviewer box, ratings, facts at 1,366 / 390 px) pixel-identical before/after; new rules present in the used CSS (Pegasus, Forerunner); no new PHP warnings. trafilatura items staging vs live: Pegasus 66/69 vs 65/69, Cumulus 66/69 vs 65/69, Cascadia 64/70 vs 66/72 (same misses: comments heading, "Best for" checklist label), Nano X4 62/66 vs 63/65 (staging has more facts), Forerunner 165 37/40 vs 56/62 (trust box, reviewer, purchase proof now kept).

Checks after A4 (15 Nov): 5 menu panels at 1,366 / 1,180 px pixel-identical before/after; phone menu shows no panels (unchanged); no headings left in the nav, outline starts at the h1; `.list-unstyled` / `.mb-0` in the used CSS; no PHP warnings from `mega_menu.php`.

**Readability (informational, not a gate):** keeps only the review prose on every reskinned review (17–21 items vs 33–51 on live). Cause: its sibling scoring around the top block (`.review-sections`) — the reskinned cards score below its threshold. Fix = C2 (one content container), after Raptive re-maps.

### A4 notes (menu)
- Google doesn't penalise headings in menus; the change is outline/accessibility hygiene (screen readers get "list, 8 items" per column). Inline styles left as they are (look guarantee; small HTML weight).
- The bigger SEO point is the links: when the panels loaded on hover, Googlebot never saw them; printed, they are sitewide links on every page. 46 of the 95 targets are new to Google's view of the menu, incl. brand guides, 2018/2019 posts, "2024" guides and old watches (Suunto Ambit 3, Forerunner 645). Sitewide links tell Google what the site thinks matters → curate before go-live. 4 links 301: `/gps/garmin-forerunner-265/`, `/gps/coros-pace-3/`, `/gps/huawei-gt-3-review/`, `/content/best-gps-running-watch-guide/`.

## B. Efficiency (pre-existing, mostly DFS)
| # | Finding | Fix |
|---|---|---|
| B1 | Shop thumbnails at source size: Nike `h_2500,w_2500,f_png` = 3.6 MB each (18 refs on Pegasus; the sidebar card shows one above the fold on desktop); Amazon `_SR960,720_` 66 KB | rewrite at import/render: Nike `c_limit,h_200,w_200,f_auto` ≈ 3 KB; Amazon `_SR160,120_` ≈ 3 KB; RW `nw=165` fine |
| B2 | Drawer prints a second full shop list (10.9 KB); same best price printed 5× (mob, rail, stick, cmp, drw) | render the drawer from `#rsg-dfs-data` on first open (JS already repaints boxes) |
| B3 | rangeslider.js (18 KB) inline in `<article>` on every review, only for the comment form's rating inputs | load on form open, or native `<input type=range>` styled with CSS |
| B4 | Hero photo: theme sets no `fetchpriority` (live gets it from WP Rocket's LCP feature) | set it on the first gallery photo. (Staging srcset → originals = uploads redirect to live, not code) |

## C. After Raptive re-maps (structure)
| # | Finding | Fix |
|---|---|---|
| C1 | `<style id="rsg-dfs-css">` 16 KB inside `<article>` (direct child), repeated per page | move to `<head>` or a cached file |
| C2 | h1 + hero outside `<article>`; content split across many sibling blocks → readability keeps only the review prose | hero inside the article; article body in one content container |

## Why it happened
- A1, A2: mistakes. `<aside>` chosen because the box looks like a side note; bio left in a `<div>` although the 11 Nov audit had shown trafilatura drops styled divs; `rsg-ratings` was a reasonable name that hits an extractor filter. None of it was caught because the per-step reskin checks were ads, CLS, PHP warnings, used CSS and screenshots: **no extractor re-run**.
- A3: deliberate, correct HTML (a drawing with a caption = `<figure>`), untested side effect.
- A4: old menu markup; printing the menu in the page (to drop admin-ajax on hover) brought it into every page's HTML. Trade-off made knowingly, markup and link set not reviewed.
- B2: deliberate (drawer works instantly without a JS render path); a judgement call, not an error.
- B1: since the DFS build (8–10 Nov) shop image URLs are used as delivered; never weighed. The 4.1 card put one above the fold.

## Going forward (rules, also in `RUNBOOK.md` and `../DECISIONS.md` 15 Nov)
- Every theme step that changes markup: `checks/extractcheck.py OUT <staging URL> <live URL>` on the sample reviews; **no new trafilatura MISSING items vs live**, or the step isn't done (readability reported, not a gate, until C2).
- Content we want read: text in `<p>` / `<dl>` / `<table>` / lists, never bare `<div>`/`<span>`; no boilerplate words in its class names (`rating`, `author`, `comment`, `share`, `related`, `sidebar`, `footer`, `menu`, `ad-`); not inside `<aside>` or `<figure>`.
- Headings only for page sections; menus, popups and cards use styled non-heading elements.
- Parallel chats share one working tree: commit only your own hunks; deploy from a clean `git worktree` (never stash another chat's work).

## Fine
Landmarks, `<dl>`, `<table>`, `<time>` present; no invalid nesting found; JSON-LD (WebPage, Product + Review, Breadcrumb, FAQ) unchanged.
