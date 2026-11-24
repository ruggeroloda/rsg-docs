# Phone price bar — design G (18 Nov 2026)

**DONE — live 18 Nov 2026 10:16 UTC (`655a4e0`, tag `live-20261118-101624`).** Ruggero: "make it go to staging, then let's deploy live once you did your checks".

## Why
Ruggero: the old navy bar (price, See deal, "4 shops", ☰) read as header chrome, had four look-alike tap targets, and nobody could tell what "4 shops" meant. Seven variants were drawn on a design canvas (https://claude.ai/artifact/DY2DS4EUWncGzMiWVrms3F: A–D, then E/F, then G); Ruggero chose G and adjusted "Sections" → "Jump to" and the "no local price" copy.

## What it is (phones < 768 px; road, trail, training reviews; box code `stick` unchanged)
- Row 1 (navy, 36 px): shoe name (`rsg_prices_name()`), "Jump to ▾"; the section `<select>` covers the whole row.
- Reading-progress line (3 px, blue on ink-2), `--rsg-read` set by js/rsg-dfs.js.
- Row 2 (white, 57 px): "from $X" with "at <shop>" under it (shop cut with … when long), "All N prices" (opens the shops drawer; only with 2+ shops for the gender shown), yellow "See deal".
- No offer for the visitor's country: "No local price" + "Search Amazon" (same link as the "Check price" fallback: the visitor's own Amazon store search).
- Total 96 px (old bar 59 px). Section anchors get `scroll-margin-top: 104px` on phones.
- Code: `rsg_prices_bar()`, `rsg_prices_bar_price()`, `rsg_prices_bar_cta()` (includes/rsg-dfs-prices.php) + JS twins `barPrice()`, `barCta()`; `countLabel(n, 'all')`; CSS in css/rsg-dfs.css.

## Checks (staging, then live; Playwright 390 × 763 and 1366 px, script `_work/phone-bar-g-2026-11-18/bar.py`)
US men/women, GB, NL, CA, AU, DE; Pegasus 42, NB Fresh Foam X More v6 (long name), Adizero Prime X Strung 3.0, Cascadia 19 (trail), Nano X4 (training): bar 96 px, no overflow, drawer opens, Jump to lands below the bar (104 px), fallback links go to amazon.de / .ca / .com, desktop unchanged (sidebar card), no JS errors, no new PHP warnings. Screenshots in `_work/phone-bar-g-2026-11-18/`.

## Open
- The other price spots still say "Check price … See deal" for the same fallback: align with the bar or not (Ruggero).
- 7-day watch from ~25 Nov: `stick` clicks per view, drawer opens, Raptive RPM on reviews.

- **7-day read 24 Nov 2026:** `../analytics/STATS-READ-2026-11-24.md` ("Watches").
