# Research brief: what Google's updates penalised on RSG, and the way to 200k

_Prepared 20 Nov 2026 by Claude (Opus) for an independent analysis (planned: Claude Fable in its own chat). This file gives the question, the material and the access. It deliberately holds no findings._

## The question (Ruggero, 20 Nov 2026, his words)
- "we could correlate what those core algos targeted (both what google said and what SEO experts agreed on afterwards) with what dropped on my site. we could reverse-engineer what was wrong at each step, and potentially figure out a way to reverse the issues"
- "i understand things like ai overviews are non-reversible, but if runrepeat makes 1.6M, i can cut myself a 200k space (that needs to be the intention)"
- "i definitely don't want generic EEAT advice, but maybe we could see the way the website is now if we solved the issues that a particular core update penalised at the time"
- "i am worried that if you make a summary, that summary will influence the conclusions"

## What to deliver
1. For each period where RSG lost search traffic: what Google changed (Google's words vs the experts' later consensus, kept apart, with how sure each is), and the evidence **on RSG's own pages** for or against each explanation (which pages lost, which held, how they differed then).
2. For each explanation you keep: is the problem still on today's site (live pages, not the old ones)? What exactly would fix it, and how would we know it worked?
3. The path to "a 200k space": which part of the lost demand is still reachable, who holds it now and why, and what RSG would have to be to take it.
4. Alternatives you rejected and why; what data would settle what you couldn't.
No generic advice: every claim should point to RSG data, a page, or a source.

## Read with suspicion
Earlier work in this repo contains conclusions. Use it, but test it rather than take it as given:
- `../TRAFFIC-HISTORY-2026-11-20.md` (Claude Opus, 20 Nov: break dates and causes, written from the same GA4 data)
- `../../content/CONTENT-REVIEW-2026-11-20.md`, `../../content/content-review-2026-11-20/` (content review with Fable, 20 Nov)
- `../../theme/SEO-AUDIT-2026-11-17.md` (crawl-based SEO audit, 17 Nov)
- `../../content/ARTICLES-PRUNE-PLAN-2026-11-15.md` (planned removal of old articles)

## The material (all in `data/`, gzip CSV, one header row)
Source of truth: tables in the analytics database on rsgmedia (see "Access"); these files are exports of 20 Nov 2026. Months are calendar months; GA4 days are Amsterdam time, Search Console days are Google's (Pacific).

| File | Rows | What | Columns |
|---|---|---|---|
| `ga4_landing_organic_page_month` | 83.7k | Google-search visits by **landing page**, Feb 2023 – Oct 2026 (pages with ≥ 3 a month) | month, page (path, trailing slash added; `(not set)/` = visits without a page view), sessions, engaged_sessions, views_in_session, key_events |
| `ga4_views_page_month` | 101k | Page views by page, all sources | month, page (as GA4 gives it), views, key_events, engagement_sec_per_view |
| `ga4_events_page_month` | 33k | The site's own events by page (`affiliate_click`, `top_pick_click`, `comparison_test`, `view_search_results`, …) | month, page, event, events |
| `ga4_organic_country_device_month` | 13.5k | Google-search visits by country × device | month, country, device, sessions, engaged_sessions |
| `ga4_channel_day` | 9.6k | All visits by channel per day | day, channel, sessions, engaged_sessions, views |
| `gsc_page_month` | 43k | Search Console by page, **Jul 2025 – Nov 2026 only** (complete) | month, page (full URL), clicks, impressions, avg_position (impression-weighted) |
| `gsc_page_query_month` | 143k | Search Console page × query (**partial**: Google drops ~40 % of clicks here; use for shares, not totals) | month, page, query, clicks, impressions, avg_position |
| `gsc_country_device_day` | 13.6k | Search Console by country × device, main markets | day, country, device, clicks, impressions, position |
| `wp_pages` | 2.5k | Every post/page in WordPress **today** (published + drafts) | id, post_type, status, url, title, published, modified (mostly Nov 2026 bulk edits: ignore), text_changed (`rsg_content_modified`, backfilled 11 Nov 2026 from modified/publish dates), author, words_content (post body), words_fields (custom fields, where reviews/comparisons keep their text), images, links_internal, links_affiliate (pattern match), revisions, first_revision, last_revision, revision_months (WordPress text versions; 873 posts have some), noindex, primary_category |
| `wayback_rsg_snapshots` | 1,244 (168 of the 179 pages archived) | Archived copies of RSG pages, one per page per month (2023-01 – 2026-11) | page, type, breaks, month, timestamp, wayback_url |

**How the Wayback pages were chosen (fixed rule, not by hand):** for each of five periods (before Sep 2023, before May 2024, before May 2025, before Dec 2025, before May 2026), the top 10 pages by Google-search visits in the two months before, per page type (home, review, old review, comparison, article, guide, top list, review list, GPS review; 3 for "other"): 179 pages. List: `data/wayback_urls.csv.gz`. Any other page or competitor: same method (`wb.py`).

**Known data issues** (checked 20 Nov; details in `../README.md` "GA4 history"):
- GA4 starts 3 Feb 2023: no year-before comparison for 2023. Search Console in the database starts Jul 2025 (Google keeps 16 months).
- GA4 marks its page-level data Feb 2023 – Nov 2024 as partly lumped: monthly totals match, the long tail of small pages may be thin.
- `affiliate_click` starts Jan 2024 (glitch: ~615k events on 22–23 Jan 2024, leave out) and **stops on 11 Nov 2026** (old price box retired). Key events = affiliate clicks, from Jan 2024.
- GA4 depends on cookie consent; sessions are estimates (breakdowns differ 1–3 %).
- From 4 Nov 2026, GA4 "Direct" traffic has bot waves; Search Console UK clicks on 31 Oct, 1, 3, 4, 5 Nov 2026 look automated.
- Page types are not in the data: derive them from the URL. Patterns RSG used: `/reviews/<type>/<model>-review/` (reviews since ~2022), `/review/…` and `/YYYY/MM/…-review/` (older reviews), `/comparison/…`, `/guide/…`, `/top/…` (best-of lists since Nov 2026; before, many lived in `/guide/`), `/YYYY/MM/…` and `/content/…` (articles), `/reviews/<brand>/all/` etc. (listings), `/gps/…`. The database function `rsg.guess_page_type(path)` does this (expects a trailing slash).
- Pages deleted or redirected before today are in the traffic data but not in `wp_pages`.

## Not in the material (ask or fetch)
- **What RSG itself changed, and when** (templates, ads, authors, publishing stops, pruning, the old price box) before Nov 2026: only Ruggero knows most of it. Publishing dates are in `wp_pages`; changes from Nov 2026 are in `../../DECISIONS.md` and `rsg.change_log`. **Ask Ruggero for a timeline before attributing a drop to Google.**
- The old templates: use the Wayback snapshots. Reviews and comparisons today are close to their 2023–2025 design; guides and top lists changed heavily in Nov 2026 (`../../theme/RUNBOOK.md`).
- Competitor data (RunRepeat and others): no SISTRIX/Semrush access. Their Wayback history is reachable the same way.
- Revenue: Raptive, Amazon and network reports are not imported.

## Access (from a session linked to Ruggero's Mac; the cloud shell reaches none of these)
- **Analytics database** (read-only): `ssh -i <rsg>/_private/claude-hetzner-key -p 2222 ruggero@116.203.146.177`, then `echo "select …" | rsg-sql` (add `--csv` for CSV). Tables `rsg.ga4_page_day`, `rsg.ga4_landing_day`, `rsg.ga4_site_day`, `rsg.ga4_event_day`, `rsg.gsc_page_day`, `rsg.gsc_query_day`, `rsg.gsc_country_day`, Umami (from 16 Nov 2026) per `../README.md`. 120 s statement limit.
- **WordPress** (read-only use only; never change the live site): `cp <rsg>/theme/.ssh-rocket-key ~/.rsgkey && chmod 600 ~/.rsgkey`, `ssh -i ~/.rsgkey jfx9wij@194.39.148.145`, `cd ~/public_html && wp …`. Exporter used for `wp_pages`: `wp_pages.php` here.
- **Wayback Machine**: works from the Mac shell and from rsgmedia (`curl`/Python), not from the cloud shell or WebFetch. CDX API: `https://web.archive.org/cdx/search/cdx?url=<url>&output=json&from=2023&to=2026&filter=statuscode:200&collapse=timestamp:6`.
- **Sources**: `SOURCES.md` (links only, no summaries).
- The live site: https://www.runningshoesguru.com
- Rules for any change: `../../CLAUDE.md` (staging first; never live without Ruggero's "deploy to live").
