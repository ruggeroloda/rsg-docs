# /top/ lists — HTML, SEO and machine-readability audit + rebuild (17 Nov 2026)

Pages: /top/best-running-shoes-right-now/ (48102), /top/what-are-the-best-daily-trainers-right-now/ (48397), /top/puma-running-shoes/ (49387). Template `single-top.php`. Checked with `checks/seo_check.py`, `checks/extractcheck.py` and an outline/markup script.

## Status: LIVE since 17 Nov 2026 08:41 UTC (tag `live-20261117-084127`; content script applied on live 08:42 UTC)
Ruggero 17 Nov: do the rebuild (looks must not change; Raptive ad counts only after meaningful changes) and the content/title fixes; "120+ a year" → "more than 1,300 reviewed so far"; "one more SEO and AI check, then push to live".

Final check before go-live (staging) and after (live): all pages 200, one H1, self canonical, JSON-LD valid (headline without date), titles 49/64/64 characters, descriptions 159/150/145; no broken in-page anchors, no duplicate ids, no JS errors; every shoe photo has alt (the only empty alt is the author avatar, decorative); Raptive's content-ad candidate count (`#main .col-lg-8 > *` minus its exclusions) identical before/after (21 / 13 / 28) — ad placement unchanged; extractors on live: trafilatura 208/211, 205/209, 210/214 items, readability 11k/10k/18k characters; each shoe block reads "#### In short: <shoe name>" followed by the verdict, pros, cons, who for, specs and ratings. PHP warnings on the page are the old ones (same set as before the change).

### Template (same look; before/after screenshots compared at 1366 and 390 px: identical except the byline)
- "(Month Year)" removed after the title (H1, breadcrumb, fallback `<title>`).
- `<main id="main">` (Raptive still selects `#main .col-lg-8 > *`), breadcrumb and table of contents as `<nav>` (TOC label no longer an H2), each category a `<section id="Best+for+…">` (old anchor links still work), shoe anchors as `<span id>`.
- Labels inside a pick (In short, Pros, Cons, Who should (not) buy it, Main Specs, Rating breakdown, Team review) are `<h4 class="h6">` (were 48 `<h6>`); "In short" carries the shoe name as visually hidden text (extractors drop the name heading inside the photo/price box); trust box label and top-pick card name/category are styled paragraphs, not headings; empty headings are skipped (daily trainers had an empty H2).
- Verdict and FAQ answers in `<p>`; specs and rating breakdown as `<dl>`; shoe photos have alt = shoe name; byline "By <author>" linked with rel=author (was "Written on 25th April" = the last edit date).
- **Top-pick cards are `<div>`, no longer `<article>`**: the six small `<article>` cards made extractors take the cards for the page and drop the whole list.
- Dead alternative layout removed (2,575 → ~1,170 lines). Header menu: dead `ga()` onclicks removed site-wide (every menu click threw an error).

### Content and titles (`theme/_work/top-content-2026-11-17/fix-content.php`, idempotent)
- "120 a year" → "more than 1,300 … so far": best list intro + description, daily trainers intro, beginners guide intro, heavy runners guide intro, "How we review running shoes" page (14822); mega menu "More than 1,300 reviewed so far". "starting 15 years ago" → "starting in 2009"; FAQ "After 15 years and 1200+ running shoes tests" → "After 17 years and more than 1,300 running shoe reviews".
- Daily trainers title "Right Now ?" → "Right Now?" (post title + SEO title); Puma SEO title (91 chars) → "Puma Running Shoes: Which Nitro Shoe to Buy"; descriptions now 159/150/145 characters.
- Left as is: two dated 2024 articles (44709, 46172) that say "more than 120 … each year" (true when written; pruning candidates).

## Remaining (not done)
- `single-guide.php` adds the same "(Month Year)" to guide titles; beginners guide description is 179 characters.
- Comments block ("User feedback (0)" H2, "Leave a Reply") is shared with other templates.
- Raptive ad count on the lists: at the next meaningful change (Ruggero).
