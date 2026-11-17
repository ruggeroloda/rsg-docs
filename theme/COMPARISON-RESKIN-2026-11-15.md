# Comparison page reskin (15 Nov 2026)

_Staging only; live only with Ruggero's "deploy to live" for the whole reskin. Design: A r4 comparison mockup (`_work/review-mockups-2026-11-11/site/compare-a-r4.html`) + Ruggero's feedback 15 Nov. Code: `single-comparison.php`, `includes/comparison-snapshot.php`, `rsg-core/inc/share-image.php`, CSS `css/rsg-skin.css` (sections C1–C6). Why: `../DECISIONS.md` 15 Nov._

## Ruggero's feedback (15 Nov) → what was done
| Feedback | Done |
|---|---|
| Sidebar layout like reviews | 760 px article + sidebar from 1,140 px; sticky two-shoe price card ("Compare all shops" jumps to the price boxes) |
| No stock featured image on the page; need one for shares + listings | Page: photo band of our own test photos (first gallery photo of each review) at the top; shares + listings: automatic 1200x630 image from the same photos (C6) |
| "VS" image at the top, sidebar starting below it | Band full width above article + sidebar |
| Author without date | Trust line with published / updated; writer box at the end |
| Four intros / short verdicts | Intro (subtitle + introduction fields) stays first, same order, body text (SEO: unchanged order); "Our verdict" = top row of the snapshot card; "Buy if" inside the card; separate verdict box gone |
| Remove "What matters most to you?" | Removed (never tracked) |
| Score breakdown: bars, closer together | Two-sided segmented bars as a table (labels in the middle), "where each wins"; on every comparison with sub-scores |
| Photos full width, text half | One 760 px column; photo pairs 4:3 side by side |
| Template must work with and without the redesign fields | One template for all comparisons; rows without data are skipped |

## Status (staging)
| Step | What | Commit |
|---|---|---|
| C1 | Review layout, breadcrumb, title, trust line, intro as body text, two-shoe sidebar card | `258d552` |
| C2a | Photo band (own photos), snapshot card (verdict row + 2-shoe `table.rsg-cmp`) for all comparisons | `357599d` |
| C2b | Score bars table + "where each wins", jump links on all comparisons; redesign block retired | `417a919` |
| C3 / C3b | Sections h2 (were h1), reading rhythm, photo pairs; class `rsg-cmp-sec` (no "text": readability) | `8caa8d9`, `911a925` |
| C4 | "Best price online" h2 | `8391a34` |
| C5 | Writer box (no hidden pop-up), "Latest comparisons" cards | `7585774` |
| C6 | Share + listing image (`wp rsg-share`), cards uncropped | `dbb7dbc`, `4e1f8a3`, `696231b` |
| C7 | Photos for reviews published as blog posts (photos in the text) | `f96b87d` |

## Checks (staging, 15 Nov; `checks/extractcheck.py` live vs staging)
| Page | trafilatura live | trafilatura staging | readability staging |
|---|---|---|---|
| Clifton vs Bondi | 3,258 chars, 0/4 items | 6,197 chars, 10/10 | 4,709 chars, 8/10 |
| Pegasus vs Clifton | 3,320, 0/3 | 6,733, 10/10 | 4,594, 8/10 |
| Ghost vs Adrenaline | 2,692, 0/4 | 5,947, 10/10 | 4,476, 8/10 |
Outline: one h1, h2 sections. HTML ~131 KB (live ~160–170). No new PHP warnings (old comparison warnings only move lines). Share images: all 173 built (photos from the gallery, else from the review text), 0 errors.

## Open
- Share image: optional per-shoe photo picker later (the first gallery photo is sometimes a close-up).
- Sidebar card height (~550 px) → last pass with the review card (288 px target).
- Raptive: comparison markers with the ads work (`rsg-content` exists; text marker without "text"/"body").
- Go-live steps: `RESKIN-GO-LIVE-CHECKLIST.md` (share images on live after the deploy).
