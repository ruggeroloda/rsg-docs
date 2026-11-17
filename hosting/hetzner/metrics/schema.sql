-- Metrics layer for the analyst (schema rsg, database umami) — 16 Nov 2026. Idempotent: safe to run again.
-- Built nightly by metrics_build.py. Raw data stays in Umami's tables and rsg.gsc_*; these are small aggregates.

-- Bot score per Umami session (= visitor for a month: Umami rotates the session salt monthly). Replaces the 16 Nov view.
DO $$ BEGIN
  IF EXISTS (SELECT 1 FROM pg_views WHERE schemaname = 'rsg' AND viewname = 'session_quality') THEN DROP VIEW rsg.session_quality; END IF;
END $$;
CREATE TABLE IF NOT EXISTS rsg.session_quality (
  session_id uuid PRIMARY KEY,
  website_id uuid NOT NULL,
  score      int  NOT NULL,          -- sum of rule points
  quality    text NOT NULL,          -- bot (>=3) | suspect (2) | human (<2)
  reasons    text[] NOT NULL,        -- 'rule:points'
  rules_v    int  NOT NULL,
  built_at   timestamptz NOT NULL DEFAULT now());

-- Latest page type per URL path (from Umami page views); used for Web Vitals (sent without a tag) and Search Console pages.
CREATE TABLE IF NOT EXISTS rsg.page_type_map (
  url_path  text PRIMARY KEY,
  page_type text NOT NULL,
  last_seen timestamptz NOT NULL);

-- One row per day x source x metric x segment. dims = 'key=value;key=value' (keys in a fixed order), '' = whole site.
-- value = count, rate (0..1) or p75; n = sample size / denominator (rates, p75s), NULL for counts.
-- Umami days are UTC; Search Console days are Google's (Pacific time).
CREATE TABLE IF NOT EXISTS rsg.metric_day (
  day    date    NOT NULL,
  source text    NOT NULL,           -- umami | gsc
  metric text    NOT NULL,
  dims   text    NOT NULL DEFAULT '',
  value  numeric NOT NULL,
  n      integer,
  PRIMARY KEY (day, source, metric, dims));
CREATE INDEX IF NOT EXISTS metric_day_series ON rsg.metric_day (source, metric, dims, day);

-- Statistically unusual moves (good and bad), per as-of day and window (1d = day vs its baseline, 7d = week vs prior weeks).
CREATE TABLE IF NOT EXISTS rsg.flag (
  as_of    date    NOT NULL,
  source   text    NOT NULL,
  win      text    NOT NULL,         -- 1d | 7d
  metric   text    NOT NULL,
  dims     text    NOT NULL,
  value    numeric NOT NULL,
  expected numeric NOT NULL,
  change   numeric,                  -- relative change (value/expected - 1); absolute for position
  z        numeric,
  n        integer,
  good     boolean NOT NULL,         -- direction is good for RSG
  severity text    NOT NULL,         -- alert (strong, bad) | win (strong, good) | notable
  basis    text    NOT NULL,         -- how expected was computed
  built_at timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (as_of, source, win, metric, dims));

CREATE TABLE IF NOT EXISTS rsg.metric_run (
  run_at    timestamptz PRIMARY KEY DEFAULT now(),
  umami_from date, umami_to date, gsc_last_day date,
  sessions_scored int, metric_rows int, flags int, seconds numeric, note text);

-- Page type from the URL when Umami hasn't seen the page yet (Search Console pages). Patterns from rsg.page_type_map, 16 Nov.
-- /reviews/<type>/<slug>/ may really be an old_review (Umami's tag wins once the page is visited).
CREATE OR REPLACE FUNCTION rsg.guess_page_type(p text) RETURNS text LANGUAGE sql IMMUTABLE AS $$
  SELECT CASE
    WHEN p = '/' THEN 'home'
    WHEN p ~ '^/best-running-shoes-wizard' THEN 'shoe_finder'
    WHEN p ~ '^/comparison/' THEN 'comparison'
    WHEN p ~ '^/(reviews/)?gps/[^/]+/$' THEN 'gps_review'
    WHEN p ~ '^/reviews/(all/[^/]+/)?(page/[0-9]+/)?$' THEN 'review_list'
    WHEN p ~ '^/reviews/[^/]+/(all|trail|road|cushioning|stability|racing|minimalist|track|walking|neutral|lightweight|zero-drop)/(page/[0-9]+/)?$'
         AND p !~ '-review/$' THEN 'review_list'
    WHEN p ~ '^/reviews/[^/]+/[^/]+/$' THEN 'review'
    WHEN p ~ '^/reviews/[^/]+/$' THEN 'review_list'
    WHEN p ~ '^/review/' OR p ~ '^/[0-9]{4}/[0-9]{2}/.*-review/$' THEN 'old_review'
    WHEN p ~ '^/[0-9]{4}/[0-9]{2}/' OR p ~ '^/content/' THEN 'article'
    WHEN p ~ '^/top/' THEN 'top_list'
    WHEN p ~ '^/guide/' THEN 'guide'
    WHEN p ~ '^/(author|category|tag|page|blog)/' THEN 'archive'
    ELSE 'page' END $$;
