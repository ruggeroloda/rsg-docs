# Earnings dashboard (template, 25 Nov 2026)

Ruggero 25 Nov: a page to check earnings, including the current month. Chosen: **a static page rebuilt nightly on rsgmedia** after the 06:55 UTC revenue job (no new service; password-protected). Status: **builder runs on rsgmedia with real data (25 Nov); page not served yet, no nightly run yet.**

- `template.html`: the page. One `<script type="application/json" id="rsg-data">__DATA__</script>` block; the nightly job replaces `__DATA__` with JSON of the shape in `sample.json`. Everything else is static.
- `sample.json`: made-up numbers (`"sample": true` shows a "Sample numbers" banner). `preview-desktop-mobile.png`: desktop + phone render.
- **Added 25 Nov (Ruggero):** ▸ on each source row opens its merchants (Amazon: tracking IDs; Raptive has none); Clicks + EPC (= earnings ÷ affiliate clicks; clicks from our click log for the networks, Amazon's own report for Amazon; this month and the same days last month); "One merchant over time": pick a merchant (or click its name in the table) → affiliate clicks and earnings per week, last 13 weeks, two charts on one time axis (traffic = affiliate clicks, Ruggero 25 Nov).
- Data needed for that: per-merchant rows (`mtd[].children`) and weekly clicks + earnings per merchant (`merchants`) — network `account` = merchant in `rsg.revenue_period`; clicks from Umami `shop_click` (merchant name → network account mapping still to build).
- Sections: month so far (vs same days last month and last year), per-day pace, month end at this pace, pending / confirmed; day-by-day stacked by source (Amazon has no daily rows: month totals only); by source table; last 13 months stacked; by page type (incl. "Not mapped yet"); data-through date per source.

Next (when Ruggero says go): build script reading `rsg.revenue_period` / `revenue_by_page_type` → JSON → page, served behind a login on rsgmedia; Amazon month-to-date = weeks loaded.

## Builder (25 Nov 2026)
- `build_dashboard.py` (copy on rsgmedia `~/revenue/dashboard/`, output `out/earnings.html`): reads `rsg.revenue_period`, `revenue_by_page_type`, `revenue_load_log` and Umami `shop_click`; read-only. `python3 build_dashboard.py template.html out/earnings.html [--json out/earnings.json]`.
- Month = month of yesterday (Amsterdam). Networks: orders through yesterday (or the day before their last load). Raptive: through its last loaded day; before its first loaded day = no data (null, never 0). Amazon: weeks fully inside the month vs the same number of weeks starting in the previous month; no last-year weeks (2025 files are monthly).
- Clicks: Umami `shop_click` merchant → network account via `CLICK_MAP` in the script (from `dfs/RUNBOOK.md` networks; Running Warehouse, Running Room, Pace Athletic have no earnings feed, so not mapped). Merchant clicks exist from 16 Nov 2026 → network EPC uses earnings from the same days. Amazon clicks/EPC from Amazon's report.
- First real run 25 Nov: Nov 1–24 $1,446 (Raptive $901 to 22 Nov, Amazon $383 = 3 weeks to 22 Nov, CJ $115, Rakuten $40, Awin $8, Impact $0).
