-- Search Console data next to Umami (database umami, own schema rsg; Umami only migrates schema public). 16 Nov 2026.
-- Sized for ~30k rows/day: page x country x device was 24k + 49k rows/day (too big); country only where it matters.
CREATE SCHEMA IF NOT EXISTS rsg AUTHORIZATION umami;
DROP TABLE IF EXISTS rsg.gsc_page_day, rsg.gsc_query_day, rsg.gsc_load_log, rsg.gsc_page_country_day, rsg.gsc_country_day;
CREATE TABLE rsg.gsc_page_day (          -- complete per-page totals (= Search Console's Pages report). Google drops
  day date NOT NULL, page text NOT NULL,  -- ~40 % of clicks as soon as page is combined with ANY other dimension, so
  clicks int NOT NULL, impressions int NOT NULL, position real NOT NULL, PRIMARY KEY (day, page)); -- splits live in the other tables
CREATE TABLE rsg.gsc_page_country_day (  -- PARTIAL (see gsc_page_day); per page, only the countries we have price boxes / audiences for
  day date NOT NULL, page text NOT NULL, country text NOT NULL,
  clicks int NOT NULL, impressions int NOT NULL, position real NOT NULL, PRIMARY KEY (day, page, country));
CREATE TABLE rsg.gsc_country_day (       -- whole site, every country x device
  day date NOT NULL, country text NOT NULL, device text NOT NULL,
  clicks int NOT NULL, impressions int NOT NULL, position real NOT NULL, PRIMARY KEY (day, country, device));
CREATE TABLE rsg.gsc_query_day (         -- PARTIAL: query x page (Google drops rare/anonymised queries and more)
  day date NOT NULL, page text NOT NULL, query text NOT NULL,
  clicks int NOT NULL, impressions int NOT NULL, position real NOT NULL, PRIMARY KEY (day, page, query));
CREATE INDEX gsc_query_day_query ON rsg.gsc_query_day (query);
CREATE INDEX gsc_query_day_page ON rsg.gsc_query_day (page);
CREATE INDEX gsc_page_day_page ON rsg.gsc_page_day (page);
CREATE TABLE rsg.gsc_load_log (day date PRIMARY KEY, loaded_at timestamptz NOT NULL DEFAULT now(), rows jsonb);
-- 16 Nov 2026: one row per load of a day (totals from gsc_country_day + Google's firstIncompleteDate at that moment),
-- to see whether "final" days still change on later reloads.
CREATE TABLE IF NOT EXISTS rsg.gsc_day_revision (day date NOT NULL, loaded_at timestamptz NOT NULL DEFAULT now(),
  clicks int NOT NULL, impressions int NOT NULL, first_incomplete date, PRIMARY KEY (day, loaded_at));
