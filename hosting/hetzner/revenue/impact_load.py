#!/usr/bin/env python3
"""Impact partner API (Actions) -> SQL on stdout (pipe into psql as umami). Stdlib only.

Every action is kept in rsg.impact_action (upsert by Impact id, nothing deleted); Impact's rows in
rsg.revenue_period are then rebuilt from it: day = event (order) date UTC, account = campaign (brand),
tag = SubId1 ('' = none), amounts in the account currency (EUR) with the ECB rate of that day -> earnings_usd.
PENDING -> commission_pending, APPROVED -> commission_confirmed, REVERSED -> commission_reversed (negative, not in earnings).
API limits: 45 days per call, start at most 3 years back.

env: IMPACT_ACCOUNT_SID, IMPACT_AUTH_TOKEN (scoped token: Actions, Campaigns, Deals, Promo Codes, Reports)
usage: impact_load.py               last 90 days by event date (daily)
       impact_load.py backfill      everything the API allows (3 years)
"""
import os, sys, json, time, base64, urllib.request, urllib.parse, datetime as dt

SID, TOK = os.environ['IMPACT_ACCOUNT_SID'], os.environ['IMPACT_AUTH_TOKEN']
API = f'https://api.impact.com/Mediapartners/{SID}/'
HDR = {'Accept': 'application/json', 'Authorization': 'Basic ' + base64.b64encode(f'{SID}:{TOK}'.encode()).decode()}

def get(path, params=None):
    url = API + path + ('?' + urllib.parse.urlencode(params) if params else '')
    for attempt in range(4):
        try:
            return json.load(urllib.request.urlopen(urllib.request.Request(url, headers=HDR), timeout=120))
        except Exception as ex:
            if attempt == 3: raise SystemExit(f'Impact error {path} {params}: {ex}')
            time.sleep(20 * (attempt + 1))

def q(v):
    if v in (None, ''): return 'NULL'
    if isinstance(v, (int, float)): return repr(v)
    return "'" + str(v).replace("'", "''") + "'"

COLS = ['id', 'campaign_id', 'campaign', 'state', 'amount', 'payout', 'currency', 'event_at', 'locking_at',
        'cleared_at', 'sub_id1', 'sub_id2', 'customer_country', 'promo_code', 'referring_domain']

def main(argv):
    now = dt.datetime.now(dt.timezone.utc).replace(tzinfo=None, microsecond=0)
    a = now - dt.timedelta(days=3 * 365 - 2) if argv[:1] == ['backfill'] else now - dt.timedelta(days=90)
    out, n, first = ['BEGIN;'], 0, a
    while a < now:
        b = min(a + dt.timedelta(days=44), now); page = 1
        while True:
            r = get('Actions', {'ActionDateStart': a.strftime('%Y-%m-%dT%H:%M:%SZ'), 'ActionDateEnd': b.strftime('%Y-%m-%dT%H:%M:%SZ'),
                                'PageSize': 1000, 'Page': page})
            if 'Actions' not in r: raise SystemExit(f'Impact unexpected reply {a:%Y-%m-%d}: {str(r)[:200]}')
            for t in r['Actions']:
                vals = [t['Id'], t['CampaignId'], t['CampaignName'], t['State'], t['Amount'], t['Payout'], t['Currency'],
                        t['EventDate'], t.get('LockingDate'), t.get('ClearedDate'), t.get('SubId1') or '', t.get('SubId2'),
                        t.get('CustomerCountry'), t.get('PromoCode'), t.get('ReferringDomain')]
                out.append('INSERT INTO rsg.impact_action (' + ','.join(COLS) + ') VALUES (' + ','.join(q(v) for v in vals) +
                           ') ON CONFLICT (id) DO UPDATE SET ' + ','.join(f'{c}=EXCLUDED.{c}' for c in COLS[1:]) + ', loaded_at=now();')
                n += 1
            if int(r.get('@numpages') or 1) <= page: break
            page += 1
        print(f'{a:%Y-%m-%d}..{b:%Y-%m-%d}', file=sys.stderr)
        a = b
    out.append("""DELETE FROM rsg.revenue_period WHERE source='impact';
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,
  items_ordered,ordered_revenue,commission_pending,commission_confirmed,commission_reversed,earnings,currency,usd_rate,load_ref)
SELECT 'impact', campaign, 'day', d, d, 'order', coalesce(sub_id1,''), '',
  count(*) FILTER (WHERE state <> 'REVERSED'),
  sum(amount) FILTER (WHERE state <> 'REVERSED'),
  coalesce(sum(payout) FILTER (WHERE state = 'PENDING'),0),
  coalesce(sum(payout) FILTER (WHERE state = 'APPROVED'),0),
  -coalesce(sum(payout) FILTER (WHERE state = 'REVERSED'),0),
  coalesce(sum(payout) FILTER (WHERE state <> 'REVERSED'),0),
  min(currency), rsg.usd_rate(min(currency), d),
  'impact api ' || to_char(max(loaded_at) AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM (SELECT *, (event_at AT TIME ZONE 'UTC')::date d FROM rsg.impact_action) t
GROUP BY campaign, d, coalesce(sub_id1,'');
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('impact', %s, %s, %d);
COMMIT;""" % (q(first.isoformat()), q(now.isoformat()), n))
    print('\n'.join(out)); print(f'{n} actions', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
