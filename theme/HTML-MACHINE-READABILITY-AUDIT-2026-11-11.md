# Review page HTML — machine readability audit (2026-11-11)

Sample: https://www.runningshoesguru.com/reviews/road/asics-gel-nimbus-28-review/ (Googlebot UA, Cloudflare HIT; same HTML for browser UA)

## Outcome (live 11 Nov 2026, theme `0157c2b`)
| # | Finding | Result |
|---|---|---|
| 1 | "Best for" / sliders | **Done**: only selected uses are text; scale reads "on a scale from Responsive to Plush" |
| 2 | Data contradictions | **Dates done** (Published + Updated, Updated = last text change). Spec typos: not touched (Ruggero: check case by case) |
| 3 | Landmarks | **Done** on existing elements (`nav`, `main.container`, `article.col-lg-8`, `aside`) — no wrapper, Raptive's ad selectors depend on the structure |
| 4 | Price box ×3 | **Partly**: copies keep their look but lose their `<h2>`; rendering once = rebuild |
| 5 | Headings | **Done**: one h1, same look via classes; popups no headings; "X?" spacing. "Similar shoes" cards still h4 (shared block) |
| 6 | Comparison | **Done**: real `<table>`, internal links followed, "No ratings yet" |
| 7 | Facts/ratings/fit/cushioning | **Done**: `<dl>` |
| 8 | Structured data | **Done**: no post ID as SKU/MPN, WebPage node + `@id`s, clean FAQ text, `og:type` article + times, breadcrumb aligned (visible "Home › Reviews › X") |
| 9 | Alt text | **Test running**: 10 test vs 10 control reviews, `ALT-TEXT-TEST-2026-11.md` |
| 10 | Noise/weight | Rebuild (HTML −15 % from removed comments/markup) |
| 11 | Small leftovers | **Done** except duplicate `#comments` (kept: Raptive's below-post ad uses it) |
| 12 | Byline | **Done**: linked author, `<time>` dates |
| — | Verdict paragraph | **Done** (found in the test): in a `<p>`, trafilatura dropped it as a styled div |

Measured before/after (trafilatura, the extractor behind many AI datasets/RAG tools): main text extracted +55–85 % (Nimbus 5.9k → 9.5k chars), ratings/facts/fit/comparison now included; Markdown conversion gives a real comparison table. Screenshots at 390/820/1366 px identical except the agreed text changes; Raptive ad count unchanged (8 phone / 9 desktop). No ranking effect expected from markup alone.

## Numbers (before)
- 265 KB HTML, ~15.7 KB visible text (6%). `<body>` at 27%, `<h1>` at byte ~106k (40%), review prose starts at 53%.
- 49 KB inline CSS, 61 KB inline JS (incl. 18 KB rangeslider lib), 374 `style=""` attrs, 115 spacer divs, 27 HTML comments.
- 87 `<img>`: 66 empty alt. 185 links.

## High — bots get wrong or ambiguous data
1. **"Best for" chips**: all 5 options are in the text; the selected ones differ only by inline style + aria-hidden icon. Text read = "Best for Racing Speedwork Daily training Long distance racing Ultra distance racing". Fix: output only selected values (or mark unselected semantically).
   Sliders (cushioning type/amount, stability, flexibility): value is in `<strong>` (OK), but scale endpoints ("Responsive … Plush") are loose text; position is CSS only.
2. **Data contradictions**: Drop 8 mm (facts + schema) vs stack 42/24 → "18 mm drop"; weight 9.6 oz (facts + schema) vs 281 g / 9.9 oz in the intro; byline "03rd February" (no year) vs schema datePublished 2026-01-26 / dateModified 2026-02-03.
3. **No landmarks**: no `<main>`, `<article>`, `<header>`, `<nav>`, `<aside>`. Main-content extractors can't separate the review from menu/price boxes/popups.
4. **Price box rendered 3×** (mobile, sidebar, compact) + sticky mobile copy; "Where to buy" h2 twice + "price comparison" h2. Render once.

## Medium
5. Headings: 2nd `<h1>` ("Similar shoes…"); products in comparison are `<h6>`; "Why you can trust us" `<h4>`; popup `<h2>`s (giveaway, training plans) and hidden-modal `<h3>` "Leave a Reply"; "Nimbus 28 ?" spacing; "Asics" vs "ASICS".
6. Comparison is a div grid, not `<table>` — column ↔ shoe association only by position. "User score 0" for a shoe with no ratings. Internal review links there are `rel="nofollow"`.
7. Facts / Rating breakdown / Size & fit / Cushioning are styled divs → use `<dl>` or `<table>`.
8. Structured data: `sku`/`mpn` = WP post ID (50068) — wrong; `og:type=website` (→ article + published/modified time); FAQ answer contains `&#8217;` entity; no Article/WebPage node or `@id` graph; schema breadcrumb (Home › Reviews › X) ≠ visible (Home › … › X), visible breadcrumb not `<nav>`.
9. Images: all review photos share alt "Picture of ASICS Gel Nimbus 28", no `<figcaption>` — the own-test-photo evidence is invisible to machines.

## Low
10. Noise/weight (numbers above) — theme rebuild item.
11. Duplicate ids (`comments`, `email-notes`); `meta keywords`; `user-scalable=no`; legacy comments ("platinum seo pack").
12. Byline: author in `<b>`, not linked; no `<time>` for publish/update.

## Fine as is
- robots.txt (only scrapers blocked), hidden mobile menu, per-country DFS JSON blob (US box is server-rendered), Product+Review+pros/cons+FAQ JSON-LD is a good base.
