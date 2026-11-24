#!/usr/bin/env python3
"""Raptive Creator API (beta) -> SQL on stdout (pipe into psql as umami). Stdlib only.

One API call per report per day (single-day ranges), so every table is per day even where the
dashboard export only gives range totals. Each day in the window is replaced whole.
  rsg.raptive_site_day       day: earnings, pageviews, sessions, page RPM, RPS, video earnings + ads per view
  rsg.raptive_page_day       day x page path: earnings, pageviews, impressions, RPM, CPM, viewability, ads per view
  rsg.raptive_breakdown_day  day x kind x key x sub: device | ad_unit (sub = device or 'all') | video_player
                             (sub = player category) | country | traffic_source | category
  rsg.raptive_day_revision   site totals of every load, to see whether Raptive still changes "final" days
  rsg.revenue_period         source 'raptive', grain day, page = path, tag '' (page rows only; their sum is
                             within ~0.5 % of the site total)
Window end = the earlier of Raptive's analytics and earnings bounds (page views lag earnings by a day).
API: OAuth client credentials, token lives 300 s (refreshed after 240 s); up to 500 rows a page.
Monetized RPM is not in the API (dashboard Earnings export only).

env: RAPTIVE_CLIENT_ID, RAPTIVE_CLIENT_SECRET, optional RAPTIVE_SITE_ID
usage: raptive_load.py                         last 7 available days (daily)
       raptive_load.py 2026-09-01 [2026-09-30] a range (backfill); end defaults to the last available day
"""
import os, sys, json, time, base64, urllib.request, urllib.parse, urllib.error, datetime as dt

BASE = 'https://publisher-api.raptive.com'
CID, SEC = os.environ['RAPTIVE_CLIENT_ID'], os.environ['RAPTIVE_CLIENT_SECRET']
SITE = os.environ.get('RAPTIVE_SITE_ID', '56f01024a1cbb74635a25c23')  # Running Shoes Guru
_tok = {'v': None, 'at': 0.0}

def token():
    if _tok['v'] and time.time() - _tok['at'] < 240: return _tok['v']
    req = urllib.request.Request(BASE + '/oauth/token', b'grant_type=client_credentials', {
        'Authorization': 'Basic ' + base64.b64encode(f'{CID}:{SEC}'.encode()).decode(),
        'Content-Type': 'application/x-www-form-urlencoded'})
    _tok['v'] = json.load(urllib.request.urlopen(req, timeout=60))['access_token']; _tok['at'] = time.time()
    return _tok['v']

def get(path, params=None):
    url = f'{BASE}/creator-api/v1/sites/{SITE}{path}' + ('?' + urllib.parse.urlencode(params) if params else '')
    for attempt in range(5):
        try:
            return json.load(urllib.request.urlopen(urllib.request.Request(url, headers={'Authorization': 'Bearer ' + token()}), timeout=120))
        except urllib.error.HTTPError as ex:
            if ex.code == 401: _tok['v'] = None
            elif ex.code not in (429, 500, 502, 503, 504) or attempt == 4:
                raise SystemExit(f'Raptive error {ex.code} {path} {params}: {ex.read()[:300]!r}')
        except Exception as ex:
            if attempt == 4: raise SystemExit(f'Raptive error {path} {params}: {ex}')
        time.sleep(15 * (attempt + 1))

def paged(path, params):
    rows, n = [], 1
    while True:
        r = get(path, {**params, 'page[size]': 500, 'page[number]': n})
        rows += r['data']
        if not r.get('meta', {}).get('page', {}).get('next'): return rows
        n += 1

def q(v):
    if v is None: return 'NULL'
    if isinstance(v, bool): return 'true' if v else 'false'
    if isinstance(v, (int, float)): return repr(v)
    return "'" + str(v).replace("'", "''") + "'"

def val(x):  # some metrics come as {"value": .., "score": ..}
    return x.get('value') if isinstance(x, dict) else x

def ins(table, cols, rows):
    return [f'INSERT INTO rsg.{table} ({",".join(cols)}) VALUES ' + ',\n'.join(
        '(' + ','.join(q(v) for v in r) + ')' for r in rows[i:i + 500]) + ';' for i in range(0, len(rows), 500)]

BCOLS = ['day', 'kind', 'key', 'sub', 'earnings', 'pageviews', 'sessions', 'impressions', 'viewable_impressions',
         'rpm', 'rps', 'cpm', 'viewability', 'impressions_per_pageview']

def day_rows(d):
    p = {'startDate': d, 'endDate': d}
    e = get('/earnings', {**p, 'aggregationPeriod': 'daily'})['data']
    v = get('/video/earnings', {**p, 'aggregationPeriod': 'daily'})
    e = e[0] if e else {}; vd = v['data'][0] if v.get('data') else {}
    site = [d, e.get('earnings'), e.get('pageviews'), e.get('sessions'), e.get('rpm'), e.get('rps'), vd.get('earnings'), vd.get('ippv')]
    pages = [[d, x['pageUrl'], x.get('earnings'), x.get('pageviews'), x.get('impressions'), x.get('rpm'), val(x.get('cpm')),
              val(x.get('viewability')), val(x.get('impressionsPerPageview')), x.get('author')] for x in paged('/pages/performance', p)]
    b = []
    for x in get('/devices/performance', p)['data']:
        b.append([d, 'device', x['type'].lower(), '', x.get('earnings'), x.get('pageviews'), x.get('sessions'), None, None, x.get('rpm'), x.get('rps'), None, None, None])
    for dev, units in get('/ad-units/performance', p)['data'].items():
        for x in units:
            b.append([d, 'ad_unit', x['adUnit'], dev, x.get('earnings'), None, None, x.get('impressions'), x.get('viewableImpressions'), None, None, x.get('ecpm'), x.get('viewability'), None])
    for x in v.get('videoPlayerBreakdown') or []:
        b.append([d, 'video_player', x['playerType'], x.get('playerCategory') or '', x.get('earnings')] + [None] * 9)
    for x in paged('/countries/performance', p):
        b.append([d, 'country', x['country'], '', x.get('earnings'), x.get('pageviews'), x.get('sessions'), x.get('impressions'), None, x.get('rpm'), x.get('rps'), x.get('cpm'), None, x.get('impressionsPerPageview')])
    for x in get('/traffic-sources/performance', p)['data']:
        b.append([d, 'traffic_source', x['trafficSource'], '', x.get('earnings'), x.get('pageviews'), x.get('sessions'), None, None, x.get('rpm'), x.get('rps'), None, None, None])
    for x in paged('/categories/performance', p):
        b.append([d, 'category', x['category'], '', x.get('earnings'), x.get('pageviews'), x.get('sessions'), None, None, x.get('rpm'), x.get('rps'), x.get('cpm'), None, None])
    for r in b:  # Raptive sends rows without a name (e.g. country unknown): keep them, labelled
        r[2] = r[2] if r[2] not in (None, '') else '(unknown)'; r[3] = r[3] or ''
    for r in pages: r[1] = r[1] or '(unknown)'
    return site, pages, b

def main(argv):
    bounds = get('/date-bounds')['data']
    last = min(dt.date.fromisoformat(bounds[k]['range']['endDate']) for k in ('analyticsDateBounds', 'earningsDateBounds'))
    if argv:
        a = dt.date.fromisoformat(argv[0]); b = min(dt.date.fromisoformat(argv[1]), last) if len(argv) > 1 else last
    else:
        a, b = last - dt.timedelta(days=6), last
    if a > b: raise SystemExit(f'nothing to load: {a} > {b} (last available {last})')
    days = [(a + dt.timedelta(days=i)).isoformat() for i in range((b - a).days + 1)]
    sites, pages, br = [], [], []
    for d in days:  # every API call first; SQL only once all succeeded
        s, p, x = day_rows(d); sites.append(s); pages += p; br += x
        print(f'{d}: ${s[1]} {s[2]} pv, {len(p)} pages, {len(x)} breakdown rows', file=sys.stderr)
    w = f"day BETWEEN {q(days[0])} AND {q(days[-1])}"
    out = ['BEGIN;',
           f'DELETE FROM rsg.raptive_site_day WHERE {w};', f'DELETE FROM rsg.raptive_page_day WHERE {w};',
           f'DELETE FROM rsg.raptive_breakdown_day WHERE {w};']
    out += ins('raptive_site_day', ['day', 'earnings', 'pageviews', 'sessions', 'rpm', 'rps', 'video_earnings', 'video_impressions_per_pageview'], sites)
    out += ins('raptive_page_day', ['day', 'page', 'earnings', 'pageviews', 'impressions', 'rpm', 'cpm', 'viewability', 'impressions_per_pageview', 'author'], pages)
    out += ins('raptive_breakdown_day', BCOLS, br)
    out += ins('raptive_day_revision', ['day', 'earnings', 'pageviews', 'sessions'], [s[:4] for s in sites])
    out.append(f"""DELETE FROM rsg.revenue_period WHERE source='raptive' AND period_start BETWEEN {q(days[0])} AND {q(days[-1])};
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,earnings,currency,usd_rate,load_ref)
SELECT 'raptive', {q(SITE)}, 'day', day, day, 'raptive', '', page, earnings, 'USD', 1, 'raptive api ' || to_char(now() AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM rsg.raptive_page_day WHERE {w} AND earnings IS NOT NULL;
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('raptive', {q(days[0])}, {q(days[-1])}, {len(pages)});
COMMIT;""")
    print('\n'.join(out)); print(f'{len(days)} days, {len(pages)} page rows, {len(br)} breakdown rows', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
