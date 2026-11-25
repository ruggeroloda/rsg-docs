# Earnings dashboard (template, 25 Nov 2026)

Ruggero 25 Nov: a page to check earnings, including the current month. Chosen: **a static page rebuilt nightly on rsgmedia** after the 06:55 UTC revenue job (no new service; password-protected). Status: **template only, not populated, not deployed.**

- `template.html`: the page. One `<script type="application/json" id="rsg-data">__DATA__</script>` block; the nightly job replaces `__DATA__` with JSON of the shape in `sample.json`. Everything else is static.
- `sample.json`: made-up numbers (`"sample": true` shows a "Sample numbers" banner). `preview-desktop-mobile.png`: desktop + phone render.
- Sections: month so far (vs same days last month and last year), per-day pace, month end at this pace, pending / confirmed; day-by-day stacked by source (Amazon has no daily rows: month totals only); by source table; last 13 months stacked; by page type (incl. "Not mapped yet"); data-through date per source.

Next (when Ruggero says go): build script reading `rsg.revenue_period` / `revenue_by_page_type` → JSON → page, served behind a login on rsgmedia; Amazon month-to-date = weeks loaded.
