# Tested queries (run with `rsg-sql`; replace the :dates)

Conventions: Umami live website `31037802-afa8-48d7-9ff0-badf153a16d8`; Umami days UTC; Search Console days Pacific and
only ≤ today − 4. `rsg.metric_day` is humans-only; raw Umami tables need the `rsg.session_quality` join.
Usage: `echo "<sql>" | rsg-sql` or `rsg-sql -c "<sql>"`. Set dates with psql variables, e.g.
`rsg-sql -v d="'2026-11-23'" -f q.sql` and write `:'d'` in the query.

## Health
```sql
SELECT run_at, umami_from, umami_to, gsc_last_day, sessions_scored, metric_rows, flags, seconds
FROM rsg.metric_run ORDER BY run_at DESC LIMIT 3;
SELECT source, max(day) last_day, count(DISTINCT day) days FROM rsg.metric_day GROUP BY 1;
SELECT day, value::numeric(4,3) bot_share, n sessions FROM rsg.metric_day
WHERE source = 'umami' AND metric = 'bot_share' ORDER BY day DESC LIMIT 8;
```

## Flags for a run
```sql
SELECT source, win, severity, good, metric, dims, round(value, 4) value, round(expected, 4) expected,
       round(change * 100) pct, round(z, 1) z, n, basis
FROM rsg.flag WHERE (source = 'umami' AND as_of = :'d') OR (source = 'gsc' AND as_of = :'g')
ORDER BY severity = 'notable', abs(coalesce(z, change * 10)) DESC;
```

## Open findings
```sql
SELECT id, status, kind, priority, key, title, last_seen, seen_count, snooze_until
FROM rsg.finding WHERE status NOT IN ('done','dismissed','rejected')
  AND (snooze_until IS NULL OR snooze_until <= current_date) ORDER BY priority, last_seen DESC;
```

## Scorecard: a week vs the 4 weeks before (source, metric, dims)
`:'end'` = last day of the week (Umami: yesterday's Sunday; Search Console: today − 4).
```sql
WITH w AS (
  SELECT source, metric, dims,
    sum(value) FILTER (WHERE day >  :'end'::date - 7) cur,
    sum(value) FILTER (WHERE day <= :'end'::date - 7 AND day > :'end'::date - 35) / 4.0 ref,
    count(DISTINCT day) FILTER (WHERE day <= :'end'::date - 7 AND day > :'end'::date - 35) ref_days
  FROM rsg.metric_day WHERE day > :'end'::date - 35 AND day <= :'end'::date
    AND metric IN ('visits','pageviews','seen','clicks','drawer_open','impressions')
    AND dims IN ('', 'source=search') GROUP BY 1,2,3)
SELECT *, round((cur / nullif(ref, 0) - 1) * 100) pct FROM w ORDER BY source, metric, dims;
-- rates and p75s: weight by n
SELECT source, metric, dims,
  round(sum(value * n) FILTER (WHERE day > :'end'::date - 7) / nullif(sum(n) FILTER (WHERE day > :'end'::date - 7), 0), 4) cur,
  round(sum(value * n) FILTER (WHERE day <= :'end'::date - 7) / nullif(sum(n) FILTER (WHERE day <= :'end'::date - 7), 0), 4) ref
FROM rsg.metric_day WHERE day > :'end'::date - 35 AND day <= :'end'::date
  AND ((metric IN ('ctr','position','bot_share') AND dims = '') OR (metric IN ('lcp_p75','inp_p75') AND dims = 'layout=phone'))
GROUP BY 1,2,3 ORDER BY 1,2;
```
(Weekly figures for a week with missing days are not comparable: check `ref_days` = 28.)

## Contribution: which segments explain a change (example: shop clicks by spot × layout)
```sql
WITH s AS (
  SELECT dims, sum(value) FILTER (WHERE day > :'end'::date - 7) cur,
         sum(value) FILTER (WHERE day <= :'end'::date - 7) / 4.0 ref
  FROM rsg.metric_day WHERE source = 'umami' AND metric = 'clicks'
    AND dims ~ '^component=[^;]+;layout=[^;]+$' AND day > :'end'::date - 35 AND day <= :'end'::date GROUP BY dims)
SELECT dims, coalesce(cur,0) cur, round(coalesce(ref,0),1) ref, round(coalesce(cur,0) - coalesce(ref,0),1) delta,
       round(100 * (coalesce(cur,0) - coalesce(ref,0)) / nullif(sum(coalesce(cur,0) - coalesce(ref,0)) OVER (), 0)) pct_of_total_delta
FROM s ORDER BY abs(coalesce(cur,0) - coalesce(ref,0)) DESC LIMIT 10;
```
Same shape for other metrics/dims: change `metric` and the `dims` pattern (`^page_type=[^;]+$`, `^source=[^;]+$`,
`^country=[^;]+$`, `^layout=[^;]+$`).

## Affiliate decomposition for a page type (clicks = page views × seen per view × clicks per seen)
```sql
SELECT day,
  max(value) FILTER (WHERE metric = 'pageviews' AND dims = 'page_type=' || :'pt') pv,
  sum(value) FILTER (WHERE metric = 'seen'   AND dims ~ ('^page_type=' || :'pt' || ';component=[^;]+;layout=')) seen,
  sum(value) FILTER (WHERE metric = 'clicks' AND dims ~ ('^page_type=' || :'pt' || ';component=[^;]+;layout=')) clicks
FROM rsg.metric_day WHERE source = 'umami' AND day > :'end'::date - 35 AND day <= :'end'::date GROUP BY day ORDER BY day;
```
seen per view = seen / pv; clicks per seen = clicks / seen. (Set `:'pt'` like `"'review'"`.)

## Raw Umami, humans only — hourly timing of an event for a spot
```sql
SELECT date_trunc('hour', we.created_at) h, count(*)
FROM website_event we
JOIN rsg.session_quality q ON q.session_id = we.session_id AND q.quality = 'human'
JOIN event_data d ON d.website_event_id = we.event_id AND d.data_key = 'component' AND d.string_value = :'comp'
WHERE we.website_id = '31037802-afa8-48d7-9ff0-badf153a16d8' AND we.event_name = :'ev'
  AND we.created_at >= :'from' AND we.created_at < :'to'
GROUP BY 1 ORDER BY 1;
```

## Early search signal: Umami search visits per landing page (yesterday vs the same weekday a week before)
```sql
WITH v AS (
  SELECT DISTINCT ON (we.visit_id) we.visit_id, we.created_at::date d, we.url_path, we.referrer_domain
  FROM website_event we JOIN rsg.session_quality q ON q.session_id = we.session_id AND q.quality = 'human'
  WHERE we.website_id = '31037802-afa8-48d7-9ff0-badf153a16d8' AND we.event_type = 1
    AND (we.created_at::date = :'d'::date OR we.created_at::date = :'d'::date - 7)
  ORDER BY we.visit_id, we.created_at)
SELECT url_path, count(*) FILTER (WHERE d = :'d'::date) yday, count(*) FILTER (WHERE d = :'d'::date - 7) week_before
FROM v WHERE referrer_domain ~* '(google|bing|duckduckgo|yahoo|ecosia|brave)'
GROUP BY 1 ORDER BY greatest(count(*) FILTER (WHERE d = :'d'::date), count(*) FILTER (WHERE d = :'d'::date - 7)) DESC LIMIT 20;
```

## Search Console: one page (clicks, impressions, position by day; top queries — partial)
```sql
SELECT day, clicks, impressions, round(position::numeric, 1) pos
FROM rsg.gsc_page_day WHERE page = 'https://www.runningshoesguru.com' || :'path' ORDER BY day DESC LIMIT 42;
SELECT query, sum(clicks) clicks, sum(impressions) impr,
       round((sum(position * impressions) / nullif(sum(impressions), 0))::numeric, 1) pos
FROM rsg.gsc_query_day WHERE page = 'https://www.runningshoesguru.com' || :'path' AND day > :'g'::date - 28 AND day <= :'g'::date
GROUP BY 1 ORDER BY clicks DESC, impr DESC LIMIT 15;
```

## Search Console: changed vs untouched page groups (by page type), weekly
```sql
SELECT date_trunc('week', day)::date wk, dims, sum(value) FILTER (WHERE metric = 'clicks') clicks,
       round(sum(value) FILTER (WHERE metric = 'impressions')) impr
FROM rsg.metric_day WHERE source = 'gsc' AND dims LIKE 'page_type=%' AND day > :'g'::date - 70 AND day <= :'g'::date
GROUP BY 1, 2 ORDER BY 2, 1;
```

## Change log around a date
```sql
SELECT id, at, until_at, kind, title, planned, ref, expect FROM rsg.change_log
WHERE at BETWEEN :'d'::date - 3 AND :'d'::date + 1                                   -- changes around the day
   OR (until_at IS NOT NULL AND at <= :'d'::date + 1 AND until_at >= :'d'::date - 3)     -- periods overlapping it
   OR (kind = 'external' AND until_at IS NULL AND at BETWEEN :'d'::date - 28 AND :'d'::date)  -- updates still rolling out
ORDER BY at;
```

## Writing to the notebook
```sql
-- new finding (or update the open one with the same key)
INSERT INTO rsg.finding (created_by, kind, priority, title, summary, evidence, causes, proposal, key, first_seen, last_seen, change_ids)
VALUES ('analyst', 'alert', 1, '...', '...', '...', '...', '...', 'umami:clicks|component=sticky_bar;layout=phone', :'d', :'d', '{12,34}')
ON CONFLICT (key) WHERE key IS NOT NULL AND status NOT IN ('done','dismissed','rejected')
DO UPDATE SET last_seen = EXCLUDED.last_seen, seen_count = rsg.finding.seen_count + 1
RETURNING id;
INSERT INTO rsg.finding_note (finding_id, author, body) VALUES (:id, 'analyst', '...');
INSERT INTO rsg.experiment (finding_id, title, hypothesis, change, metric, success_rule, min_days) VALUES (...);
INSERT INTO rsg.report (kind, period_start, period_end, subject, body, finding_ids) VALUES ('weekly', :'from', :'to', '...', '...', '{1,2}');
-- an external event you confirmed
INSERT INTO rsg.change_log (at, until_at, kind, title, ref, source) VALUES ('...', NULL, 'external', '...', 'url', 'analyst');
```
