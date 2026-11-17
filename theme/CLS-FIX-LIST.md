# CLS + "fix now" list (old theme) — started 8 Nov 2026

Found during the DFS 6.3 work; every value is the **same with the DFS box off**, so none is caused by DFS. Agreed (DECISIONS 8 Nov): fix as separate small theme changes (staging → check → live, own deploy log), packed with the comparison score-badge change, not inside DFS edits. Measured on staging (Playwright, ads blocked, 2–3 runs).

| Page / template | Desktop | Mobile | Source of the shift | Found in |
|---|---|---|---|---|
| ✅ **live 8 Nov** → 0.0003 · Comparison, redesign layout (`?compare=1` pilots) | 0.0005 | 0.041 | author block | DFS DEPLOY-LOG, comparisons |
| ⏸ **theme rebuild** (h1 font-swap, under 0.1; see CLS-DEPLOY-LOG item "Not fixed") · Main-layout guides (e.g. best-nike-running-shoes) | 0.014 | 0.002–0.069 (intermittent) | featured image | DFS DEPLOY-LOG, guides |
| ✅ **live 8 Nov** → 0.0014 / 0.0013 · Quiz results (`best-running-shoes-wizard` submitted) | 0.06 (one run 0.12) | 0.063 | result panels shift ~1.2 s in | widgets |
| ✅ **live 8 Nov** → 0 / 0.0001 · Old posts with review-row shortcodes (e.g. altra guide 2018) | 0.001 | 0.059 | `col-lg-10` text block | shortcodes |
| ✅ **live 8 Nov** → 0.004 · `review`-type reviews (`include-single-review-trail.php`, 62 trail reviews) | 0.027 | 0.004 | gallery images without size (the 7 Nov fix covered only the 4 main review templates) | review templates |
| ✅ **live 8 Nov** → 0.003 / 0.0006 · Mizuno guide (old guide layout) | 0.04 | 0.08 | header boxes + text | old guide layout |

## Fix now (not CLS)
- ✅ **DONE 8 Nov 18:46 UTC (live, `logs/FIX500-DEPLOY-LOG-2026-11-08.md`)** — **Live HTTP 500**: `/2013/12/choosing-a-lightweight-running-shoe/` (post 12094, the only page with `[dfscat]`): the old DFS category API returns nothing and `rsg_dfs_category()` (`includes/widgets/widgets.php` ~line 2491) calls `count()` on null (PHP 8 fatal). Fix: guard (`if ( empty( $price_json->datafeed->items ) ) return '';`) or unpublish/redirect the post. With the new DFS on, the shortcode prints nothing and the page renders.
- ✅ **DONE 8 Nov (live)** — Comparison score badge → plain text "Expert score 7/10" (DECISIONS 8 Nov).

**Status 8 Nov 20:00 (Amsterdam):** all items done on live except the main-layout guides (deferred to the theme rebuild, see `logs/CLS-DEPLOY-LOG-2026-11-08.md`). Purges done by Claude (`rocket_clean_domain()` + Rocket.net API).
