# Alt-text ranking test (set up 11 Nov 2026)

Question: do accurate, descriptive alt texts on our own test photos change Google visibility (mainly image search) for a review?

## Groups
Last 20 reviews whose photos still had the generic alt "Picture of <shoe>" (the 4 newest reviews — Cumulus 28, Pegasus 42, Glycerin 23, Ride 19 — already had descriptive alts, so they are excluded). Ordered newest first, alternating: odd = test (new alts), even = control (unchanged).

| # | Group | Post ID | Review | Photos |
|---|---|---|---|---|
| 1 | test | 50164 | Nike Structure Plus Review | 19 |
| 2 | control | 50068 | ASICS Gel Nimbus 28 Review | 16 |
| 3 | test | 50052 | Brooks Adrenaline GTS 25 Review | 12 |
| 4 | control | 50034 | PUMA MagMax Nitro 2 Review | 16 |
| 5 | test | 49988 | Brooks Hyperion 3 Review | 15 |
| 6 | control | 49979 | On Cloudflow 5 Review | 8 |
| 7 | test | 49966 | Mizuno Wave Rebellion Flash 3 Review | 12 |
| 8 | control | 49954 | HOKA Mach X 3 Review | 11 |
| 9 | test | 49896 | Saucony Hurricane 25 Review | 18 |
| 10 | control | 49879 | Saucony Triumph 23 Review | 14 |
| 11 | test | 49861 | New Balance Fresh Foam X More v6 Review | 15 |
| 12 | control | 49827 | Brooks Hyperion Max 3 Review | 18 |
| 13 | test | 49782 | Brooks Cascadia 19 Review | 19 |
| 14 | control | 49740 | Nike Vomero Premium Review | 15 |
| 15 | test | 49718 | HOKA Arahi 8 Review | 12 |
| 16 | control | 49704 | On Cloudsurfer Max Review | 13 |
| 17 | test | 49672 | Nike Structure 26 Review | 15 |
| 18 | control | 49671 | HOKA Challenger 8 Review | 12 |
| 19 | test | 49650 | ASICS MEGABLAST Review | 17 |
| 20 | control | 49615 | HOKA Mafate 5 Review | 14 |

## What changed (test group only)
- 154 attachment alts (`_wp_attachment_image_alt`) written by hand after looking at every photo: view + visible feature, no keyword stuffing. Source: `_work/alt-test-20261111/alts.tsv` + `set-alts.php` (Mac folder).
- Theme: in-section images take the attachment alt (filter in `includes/rsg-machine-readable.php`); trail/training galleries use each photo's own alt.
- Nothing else changes on test pages that doesn't also change on control pages (the markup update applies to all reviews). Alt writes don't move the "Updated" date.

## How to read it
Google Search Console → Performance → Search type: Image (and Web), filter by page, compare test vs control: 4 weeks before the live date vs 4–6 weeks after. Low traffic = noisy; look at the group totals, not single pages.

## Status
- 11 Nov 2026: alts on staging, then **live 11 Nov 2026 ~19:00 UTC** (theme `0157c2b`). Measure from ~9 Dec (4 weeks) to ~23 Dec (6 weeks) vs the 4 weeks before 11 Nov.