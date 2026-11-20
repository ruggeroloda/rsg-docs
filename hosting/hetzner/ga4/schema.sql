-- GA4 history next to Search Console and Umami (database umami, schema rsg). 20 Nov 2026.
-- One-off pull (re-runnable for a range); GA4 property 250633127, real data from 1 Feb 2023.
-- Days are GA4's (Europe/Amsterdam). Only hosts www.runningshoesguru.com + runningshoesguru.com (copies on
-- translate.goog, scraper domains and Rocket.net staging hosts were ~0.6 % of views and are left out).
-- No user counts: users don't add up across days or pages; take them from GA4 itself when needed.
CREATE SCHEMA IF NOT EXISTS rsg AUTHORIZATION umami;
CREATE TABLE IF NOT EXISTS rsg.ga4_page_day (      -- per page path (no query string): what was viewed
  day date NOT NULL, page text NOT NULL,
  views int NOT NULL, key_events int NOT NULL, engagement_sec bigint NOT NULL,
  PRIMARY KEY (day, page));
CREATE TABLE IF NOT EXISTS rsg.ga4_landing_day (   -- per landing page x channel (Organic Search, Direct, ...): where visits start
  day date NOT NULL, page text NOT NULL, channel text NOT NULL,
  sessions int NOT NULL, engaged_sessions int NOT NULL, views int NOT NULL, key_events int NOT NULL,
  PRIMARY KEY (day, page, channel));
CREATE TABLE IF NOT EXISTS rsg.ga4_site_day (      -- whole site: channel x device x country (session-scoped, adds up)
  day date NOT NULL, channel text NOT NULL, device text NOT NULL, country text NOT NULL,
  sessions int NOT NULL, engaged_sessions int NOT NULL, views int NOT NULL, key_events int NOT NULL,
  PRIMARY KEY (day, channel, device, country));
CREATE TABLE IF NOT EXISTS rsg.ga4_event_day (     -- the site's own events per page (affiliate_click, top_pick_click, ...);
  day date NOT NULL, page text NOT NULL, event text NOT NULL,  -- GA4's automatic events (page_view, scroll, ...) left out
  events int NOT NULL, key_events int NOT NULL,
  PRIMARY KEY (day, page, event));
CREATE INDEX IF NOT EXISTS ga4_page_day_page ON rsg.ga4_page_day (page);
CREATE INDEX IF NOT EXISTS ga4_landing_day_page ON rsg.ga4_landing_day (page);
CREATE INDEX IF NOT EXISTS ga4_event_day_page ON rsg.ga4_event_day (page);
CREATE TABLE IF NOT EXISTS rsg.ga4_load_log (      -- one row per loaded month; other_rows > 0 = GA4 lumped rows into "(other)"
  month date PRIMARY KEY, loaded_at timestamptz NOT NULL DEFAULT now(), rows jsonb, other_rows jsonb);
