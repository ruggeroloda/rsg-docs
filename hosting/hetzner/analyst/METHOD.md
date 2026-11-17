# How to investigate (the analyst's method)

The flags in `rsg.flag` tell you *that* something moved beyond noise. Your job is *where*, *since when*, *why*, and
*what to do* — or an honest "can't tell yet". Work through the steps in order; stop early when the answer is clear.

## 1. Triage: is it real?
Most surprises are measurement, not behaviour. Rule these out first:
- **Pipeline:** latest `rsg.metric_run` covers the day; the day isn't partial; Search Console day is ≥ 4 days old.
- **Tracking break:** visits/page views normal but `seen` or `clicks` of one spot fall to ~0 → markup/JS change. Check
  deploys in `rsg.change_log` that day (the deploy rows list the commits).
- **Bots:** `bot_share` or `sessions` by quality moved → human numbers may shift by classification only. Look at
  `rsg.session_quality.reasons` and at new clusters (one screen size / city / OS, 0 s visits, many countries). Report
  new bot patterns; don't silently adjust.
- **Calendar:** weekday mix, holidays (US first), Black Friday–Cyber Monday, month start/end.
- **Known causes:** `rsg.change_log` ± 3 days around the start, incl. Google update windows (`kind = 'external'`).

## 2. Size: does it matter?
Express the move in absolute terms per week (clicks, page views, search clicks) and as a share of the site total.
Under ~5 % of the total and not a breakage → mention in one line, no deep dive. A drop to zero is always priority 1.

## 3. Locate: where is it?
Break the move down by the dimensions in `rsg.metric_day` (layout, page type, component, source, country), then by page
for the top contributors (raw Umami or `gsc_page_day`). Use **contribution**: which segments explain most of the change
(QUERIES → Contribution). A move spread evenly across everything points to traffic/seasonality/Google; a move
concentrated in one template, spot or layout points to the site.

## 4. Time: since when?
Find the start: Umami by hour (raw, humans), Search Console by day. A step change that lines up with a deploy or
change-log entry is strong evidence; a gradual slide over days during a Google update window points to Google.
For Search Console, compare **changed vs untouched** page groups (e.g. reskinned templates vs not, pruned vs kept) —
the November 2026 core update hits both; the site change only one.

## 5. Decompose: which lever moved?
- **Affiliate:** shop clicks = page views × spots seen per page view × clicks per seen spot. Find which factor moved:
  traffic (visits/page views), **exposure** (are the spots still seen?), or **persuasion** (click rate per seen spot).
- **Ads:** ad revenue follows human page views (and pages per visit); report page views by page type and layout.
- **Search:** clicks = impressions × CTR; CTR depends on position and on the result's look. Impressions down at the same
  position = demand/seasonality; position down = ranking; CTR down at the same position = snippet/SERP change.
- **Early search signal:** Umami search visits per landing page (yesterday) move with Search Console clicks; Search
  Console confirms 4+ days later.

## 6. Corroborate
Look for a second, independent signal before calling a cause: Umami vs Search Console, seen vs page views, phone vs
desktop, the same template on other pages. One source alone = "likely", not "confirmed".

## 7. Explain
List 2–4 hypotheses. For each: evidence for, evidence against, what data would settle it. Give a confidence
(high / medium / low) to the best one. Wins get the same treatment: why it worked and whether it can be repeated.

## 8. Recommend
One of: **watch** (until a date, with what would change your mind) · **fix** (a bug) · **change** (with the expected
effect and how we'll know) · **experiment**. Rank by expected impact × confidence ÷ effort. Ruggero prefers simple,
maintainable changes; the site deploys through staging → "deploy to live", so a proposal must say what to change, where.

### Designing an experiment for RSG's traffic
- **Sample size first.** For a click rate *p* and a relative lift *r* you want to detect (80 % power, 5 % two-sided),
  each arm needs about **n ≈ 16 × (1 − p) / (p × r²)** exposures (spots seen). Example: p = 2 %, r = 30 % → ~8,700 seen per
  arm. Days needed = n ÷ daily seen per arm (from `metric_day`). **More than ~6 weeks → don't run it**: look for a bigger
  lever, a higher-volume page group, or an earlier metric (spot seen rate, drawer opens).
- **Designs, simplest first:** (a) before/after **with a control group** of pages that don't change (difference in
  differences) — default; (b) split by page (e.g. even vs odd page id) when the theme can show two variants; avoid
  per-visitor splits (pages are cached for everyone).
- **Write it down before it starts** (`rsg.experiment`): hypothesis, change, primary metric (as `metric_day` metric +
  dims or a query), success rule, minimum days, guardrails (page views per visit, phone INP, ads), and what we do if it
  wins / loses / is flat.
- **Don't run** over Black Friday–Cyber Monday or overlapping changes on the same pages; during a Google update, only
  with a control group.

## 9. Record
- Finding `key`: `<source>:<metric>|<dims>` (e.g. `umami:clicks|component=sticky_bar;layout=phone`,
  `gsc:page_clicks|page=/reviews/road/x/`). Same key again → update, don't duplicate.
- `priority`: 1 = money or site broken now · 2 = meaningful trend · 3 = idea / watch.
- `evidence`: the numbers and the query names; `causes`: ranked hypotheses + `change_ids` you checked; `proposal`.
- Close what's resolved (`done`), with a final note on what we learned.
