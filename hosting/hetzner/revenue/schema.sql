-- Revenue data (database umami, schema rsg). 23 Nov 2026, plan: analytics/REVENUE-DATA-2026-11-23.md
-- Money in US dollars (Ruggero 23 Nov: $ everywhere, he is paid in $); original currency kept.
-- Safe to re-run: creates what is missing, replaces the view and the tag map.

CREATE TABLE IF NOT EXISTS rsg.revenue_period (
  source        text    NOT NULL,             -- amazon_us | cj | awin | rakuten | avantlink | impact | raptive
  account       text    NOT NULL,             -- store / publisher id (Amazon: pimeagi-20 store)
  grain         text    NOT NULL CHECK (grain IN ('day','week','month','other')),  -- never sum across grains
  period_start  date    NOT NULL,
  period_end    date    NOT NULL,             -- inclusive; = period_start for daily sources
  date_basis    text    NOT NULL,             -- order | booking | amazon (Amazon summary: clicks/orders by order date, earnings by ship date)
  tag           text    NOT NULL,             -- tracking ID / sub-ID; Amazon 'Other' = tags hidden under ~4 items
  page          text    NOT NULL DEFAULT '',  -- URL path when the source passes it, '' otherwise
  clicks        int,
  items_ordered int,                          -- NULL = hidden by the source ('-'), not zero
  items_shipped int,
  items_returned int,
  ordered_revenue  numeric(12,2),             -- shop revenue, original currency
  shipped_revenue  numeric(12,2),
  returned_revenue numeric(12,2),
  commission_pending   numeric(12,2),         -- networks; Amazon leaves these NULL
  commission_confirmed numeric(12,2),
  commission_reversed  numeric(12,2),
  earnings      numeric(12,2),                -- what we are paid for the row, original currency (Amazon: Total Earnings)
  currency      text    NOT NULL DEFAULT 'USD',
  usd_rate      numeric(12,6) NOT NULL DEFAULT 1, -- USD per 1 unit of currency (ECB rate of the day for non-USD)
  earnings_usd  numeric(12,2) GENERATED ALWAYS AS (round(earnings * usd_rate, 2)) STORED,
  load_ref      text    NOT NULL,             -- file name or API pull id
  loaded_at     timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (source, account, grain, period_start, period_end, date_basis, tag, page)
);
CREATE INDEX IF NOT EXISTS revenue_period_start ON rsg.revenue_period (period_start);

-- Which page type a tag / sub-ID stood for, and when. valid_to is exclusive; NULL = still valid.
-- Changes happen during a day: a period is mapped by its start date (Amazon: compare per tag only from 24 Nov 2026).
CREATE TABLE IF NOT EXISTS rsg.revenue_tag_map (
  source     text NOT NULL,
  tag        text NOT NULL,
  valid_from date NOT NULL,
  valid_to   date,
  page_type  text NOT NULL,   -- review old_review gps_review comparison top_list guide review_list home article shoe_finder other | untagged hidden offsite mixed
  note       text,
  PRIMARY KEY (source, tag, valid_from)
);

DELETE FROM rsg.revenue_tag_map WHERE source = 'amazon_us';
INSERT INTO rsg.revenue_tag_map (source, tag, valid_from, valid_to, page_type, note) VALUES
-- always
 ('amazon_us','Other',            '2000-01-01', NULL,        'hidden',     'Amazon row for tags under ~4 items in the period'),
 ('amazon_us','pimeagi-20',       '2000-01-01', NULL,        'untagged',   'main store tag: old amzn.to links, no-offer fallback, links outside the site'),
 ('amazon_us','rsg-comp-20',      '2000-01-01', NULL,        'comparison', NULL),
 ('amazon_us','rsg-tube-20',      '2000-01-01', NULL,        'offsite',    'YouTube / old short links'),
-- scheme 1: old DFS, until 10 Nov 2026
 ('amazon_us','rsg-single-20',    '2000-01-01', '2026-11-10','review',     'road + training reviews, old and new (old DFS default)'),
 ('amazon_us','rsg-single-trail-20','2000-01-01', NULL,      'review',     'trail reviews (retired 10 Nov 2026)'),
 ('amazon_us','rsg-gps-single-20','2000-01-01', NULL,        'gps_review', NULL),
 ('amazon_us','rsg-gps-20',       '2000-01-01', NULL,        'gps_review', 'GPS sections in lists / old short links'),
 ('amazon_us','rsg-bestnew-20',   '2000-01-01', NULL,        'top_list',   NULL),
 ('amazon_us','rsg-bestasics-20', '2000-01-01', NULL,        'top_list',   'per-list tag (ACF override_amazon_tag)'),
 ('amazon_us','rsg-bestbrooks-20','2000-01-01', NULL,        'top_list',   'per-list tag'),
 ('amazon_us','rsg-bestheavy-20', '2000-01-01', NULL,        'top_list',   'per-list tag'),
 ('amazon_us','rsg-bestmizuno-20','2000-01-01', NULL,        'top_list',   'per-list tag'),
 ('amazon_us','rsg-bestnewbal-20','2000-01-01', NULL,        'top_list',   'per-list tag'),
 ('amazon_us','rsg-besttrail-20', '2000-01-01', NULL,        'top_list',   'per-list tag'),
 ('amazon_us','rsg-bestwmns-20',  '2000-01-01', NULL,        'top_list',   'per-list tag'),
 ('amazon_us','rsg-bestnike-20',  '2000-01-01', NULL,        'top_list',   'per-list tag (seen in reports, not in the theme scan)'),
 ('amazon_us','rsg-bestadidas-20','2000-01-01', NULL,        'top_list',   'per-list tag (seen in reports, not in the theme scan)'),
 ('amazon_us','rsg-bestsaucony-20','2000-01-01', NULL,       'top_list',   'per-list tag (seen in reports, not in the theme scan)'),
 ('amazon_us','rsg-bestrunshoe-20','2000-01-01', NULL,       'top_list',   'per-list tag (seen in reports, not in the theme scan)'),
 ('amazon_us','rsg-wizard-20',    '2000-01-01', NULL,        'shoe_finder',NULL),
 ('amazon_us','rsg-reviewlist-20','2000-01-01', NULL,        'review_list',NULL),
 ('amazon_us','rsg-bestof-20',    '2000-01-01', '2026-11-16','article',    'article shortcodes'),
 ('amazon_us','rsg-bestold-20',   '2000-01-01', NULL,        'article',    'A/B on post 16723'),
 ('amazon_us','rsg-overview-20',  '2000-01-01', NULL,        'article',    'old affiliate template'),
-- scheme 2: new DFS, 10-16 Nov 2026: every DFS box rsg-single-20 (comparisons keep rsg-comp-20)
 ('amazon_us','rsg-single-20',    '2026-11-10', '2026-11-16','mixed',      'all DFS boxes'),
-- scheme 3: per spot, 16 Nov 09:25 - 23 Nov 13:11 UTC
 ('amazon_us','rsg-single-20',    '2026-11-16', '2026-11-23','mixed',      'default: old reviews, GPS, articles'),
 ('amazon_us','rsg-revside-20',   '2026-11-16', '2026-11-23','review',     'sidebar price card'),
 ('amazon_us','rsg-revmob-20',    '2026-11-16', '2026-11-23','review',     'phone price block'),
 ('amazon_us','rsg-revbar-20',    '2026-11-16', '2026-11-23','review',     'sticky price bar'),
 ('amazon_us','rsg-revdrw-20',    '2026-11-16', '2026-11-23','review',     'price drawer'),
 ('amazon_us','rsg-revlow-20',    '2026-11-16', '2026-11-23','review',     'lower price list'),
 ('amazon_us','rsg-bestof-20',    '2026-11-16', '2026-11-23','mixed',      'lists, guides, home, related, text links'),
-- scheme 4: one tag per page type, from 23 Nov 13:11 UTC (analytics/amazon-tags-2026-11-23/PLAN.md part B)
 ('amazon_us','rsg-single-20',    '2026-11-23', NULL,        'review',     'new reviews (2023+ templates)'),
 ('amazon_us','rsg-single-old-20','2026-11-23', NULL,        'old_review', NULL),
 ('amazon_us','rsg-guide-20',     '2026-11-23', NULL,        'guide',      NULL),
 ('amazon_us','rsg-home-20',      '2026-11-23', NULL,        'home',       NULL),
 ('amazon_us','rsg-article-20',   '2026-11-23', NULL,        'article',    NULL),
 ('amazon_us','rsg-other-20',     '2026-11-23', NULL,        'other',      'search, archives, 404, pages');

-- Report view: page type x period. Pick ONE grain when reading (weeks and months overlap).
CREATE OR REPLACE VIEW rsg.revenue_by_page_type AS
SELECT r.source, r.grain, r.period_start, r.period_end,
       coalesce(m.page_type, 'unmapped') AS page_type,
       CASE WHEN m.page_type IN ('review','old_review','gps_review') THEN 'reviews'
            WHEN m.page_type = 'comparison' THEN 'comparisons'
            WHEN m.page_type IN ('top_list','guide','review_list') THEN 'lists'
            WHEN m.page_type IN ('untagged','hidden') THEN m.page_type
            WHEN m.page_type IS NULL THEN 'unmapped'
            ELSE 'rest' END AS page_group,
       sum(r.clicks) AS clicks, sum(r.items_ordered) AS items_ordered,
       sum(r.earnings_usd) AS earnings_usd,
       round(sum(r.earnings_usd) / nullif(sum(r.clicks), 0), 3) AS usd_per_click
FROM rsg.revenue_period r
LEFT JOIN rsg.revenue_tag_map m
  ON m.source = r.source AND m.tag = r.tag
 AND r.period_start >= m.valid_from AND (m.valid_to IS NULL OR r.period_start < m.valid_to)
GROUP BY 1,2,3,4,5,6;

GRANT SELECT ON rsg.revenue_period, rsg.revenue_tag_map, rsg.revenue_by_page_type TO rsg_analyst;
