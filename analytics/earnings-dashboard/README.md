# Earnings dashboard (template, 25 Nov 2026)

Ruggero 25 Nov: a page to check earnings, including the current month. Chosen: **a static page rebuilt nightly on rsgmedia** after the 06:55 UTC revenue job (no new service; password-protected). Status: **template only, not populated, not deployed.**

- `template.html`: the page. One `<script type="application/json" id="rsg-data">__DATA__</script>` block; the nightly job replaces `__DATA__` with JSON of the shape in `sample.json`. Everything else is static.
- `sample.json`: made-up numbers (`"sample": true` shows a "Sample numbers" banner). `preview-desktop-mobile.png`: desktop + phone render.
- **Added 25 Nov (Ruggero):** ▸ on each source row opens its merchants (Amazon: tracking IDs; Raptive has none); Clicks + EPC (= earnings ÷ affiliate clicks; clicks from our click log for the networks, Amazon's own report for Amazon; this month and the same days last month); "One merchant over time": pick a merchant (or click its name in the table) → affiliate clicks and earnings per week, last 13 weeks, two charts on one time axis (traffic = affiliate clicks, Ruggero 25 Nov).
- Data needed for that: per-merchant rows (`mtd[].children`) and weekly clicks + earnings per merchant (`merchants`) — network `account` = merchant in `rsg.revenue_period`; clicks from Umami `shop_click` (merchant name → network account mapping still to build).
- Sections: month so far (vs same days last month and last year), per-day pace, month end at this pace, pending / confirmed; day-by-day stacked by source (Amazon has no daily rows: month totals only); by source table; last 13 months stacked; by page type (incl. "Not mapped yet"); data-through date per source.

Next (when Ruggero says go): build script reading `rsg.revenue_period` / `revenue_by_page_type` → JSON → page, served behind a login on rsgmedia; Amazon month-to-date = weeks loaded.
