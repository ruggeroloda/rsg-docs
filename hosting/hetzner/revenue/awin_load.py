#!/usr/bin/env python3
"""Awin publisher transactions API -> SQL on stdout (pipe into psql as umami). Stdlib only.

Every transaction is kept in rsg.awin_transaction (upsert by Awin id, so nothing is ever deleted);
Awin's rows in rsg.revenue_period are then rebuilt from it: day = transaction (order) date UTC,
account = advertiser, tag = clickRef ('' = none), amounts in the advertiser's currency with the ECB
rate of that day (rsg.usd_rate) -> earnings_usd. pending -> commission_pending, approved ->
commission_confirmed, declined/deleted -> commission_reversed (negative, not in earnings).
API limits: 31 days per call, 20 calls a minute.

env: AWIN_PUBLISHER_ID (131905 = runningshoesguru), AWIN_TOKEN (personal access token, expires 23 Nov 2027)
usage: awin_load.py                  last 90 days by transaction date + last 60 by validation date (daily)
       awin_load.py 2010-01 2026-11  backfill by transaction date
"""
import os, sys, json, time, urllib.request, urllib.parse, datetime as dt

API = 'https://api.awin.com'
def get(path, params=None):
    url = API + path + ('?' + urllib.parse.urlencode(params) if params else '')
    req = urllib.request.Request(url, headers={'Authorization': 'Bearer ' + os.environ['AWIN_TOKEN']})
    for attempt in range(4):
        try:
            time.sleep(3.2)   # 20 calls a minute
            return json.load(urllib.request.urlopen(req, timeout=120))
        except Exception as ex:
            if attempt == 3: raise SystemExit(f'Awin error {path} {params}: {ex}')
            time.sleep(20 * (attempt + 1))

def windows(a, b):
    while a < b:
        e = min(a + dt.timedelta(days=30), b); yield a, e; a = e

def q(v):
    if v is None: return 'NULL'
    if isinstance(v, bool): return 'true' if v else 'false'
    if isinstance(v, (int, float)): return repr(v)
    return "'" + str(v).replace("'", "''") + "'"

COLS = ['id', 'advertiser_id', 'status', 'sale_amount', 'sale_currency', 'commission_amount', 'currency',
        'transaction_at', 'validation_at', 'click_at', 'click_ref', 'customer_country', 'type', 'decline_reason', 'paid']

def main(argv):
    pub = os.environ['AWIN_PUBLISHER_ID']
    now = dt.datetime.now(dt.timezone.utc).replace(tzinfo=None, microsecond=0)
    if len(argv) == 2:
        y, m = map(int, argv[0].split('-')); y2, m2 = map(int, argv[1].split('-'))
        a = dt.datetime(y, m, 1); b = min(dt.datetime(y2 + (m2 == 12), m2 % 12 + 1, 1), now)
        plan = [('transaction', s, e) for s, e in windows(a, b)]
    else:
        plan = [('transaction', s, e) for s, e in windows(now - dt.timedelta(days=90), now)] + \
               [('validation', s, e) for s, e in windows(now - dt.timedelta(days=60), now)]
    names = {}
    for rel in ('joined', 'pending', 'suspended', 'rejected'):
        for p in get(f'/publishers/{pub}/programmes', {'relationship': rel}) or []:
            names[p['id']] = p['name']
    out, n = ['BEGIN;'], 0
    for kind, s, e in plan:
        txs = get(f'/publishers/{pub}/transactions/', {'startDate': s.strftime('%Y-%m-%dT%H:%M:%S'),
                  'endDate': e.strftime('%Y-%m-%dT%H:%M:%S'), 'timezone': 'UTC', 'dateType': kind})
        if not isinstance(txs, list): raise SystemExit(f'Awin unexpected reply {s:%Y-%m-%d}: {str(txs)[:200]}')
        n += len(txs)
        for t in txs:
            ref = (t.get('clickRefs') or {}).get('clickRef')
            vals = [t['id'], t['advertiserId'], t['commissionStatus'], t['saleAmount']['amount'], t['saleAmount']['currency'],
                    t['commissionAmount']['amount'], t['commissionAmount']['currency'], (t['transactionDate'] or '') + '+00',
                    t['validationDate'] and t['validationDate'] + '+00', t['clickDate'] and t['clickDate'] + '+00', ref,
                    t.get('customerCountry'), t.get('type'), t.get('declineReason'), t.get('paidToPublisher')]
            out.append('INSERT INTO rsg.awin_transaction (' + ','.join(COLS) + ') VALUES (' + ','.join(q(v) for v in vals) +
                       ') ON CONFLICT (id) DO UPDATE SET ' + ','.join(f'{c}=EXCLUDED.{c}' for c in COLS[1:]) + ', loaded_at=now();')
        print(f'{kind} {s:%Y-%m-%d}..{e:%Y-%m-%d} {len(txs)}', file=sys.stderr)
    for i, nm in names.items():
        out.append(f"UPDATE rsg.awin_transaction SET advertiser = {q(nm)} WHERE advertiser_id = {i} AND advertiser IS DISTINCT FROM {q(nm)};")
    out.append("""DELETE FROM rsg.revenue_period WHERE source='awin';
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,
  items_ordered,ordered_revenue,commission_pending,commission_confirmed,commission_reversed,earnings,currency,usd_rate,load_ref)
SELECT 'awin', coalesce(advertiser, 'awin ' || advertiser_id), 'day', d, d, 'order', coalesce(click_ref,''), '',
  count(*) FILTER (WHERE status IN ('pending','approved')),
  sum(sale_amount) FILTER (WHERE status IN ('pending','approved')),
  coalesce(sum(commission_amount) FILTER (WHERE status = 'pending'),0),
  coalesce(sum(commission_amount) FILTER (WHERE status = 'approved'),0),
  -coalesce(sum(commission_amount) FILTER (WHERE status NOT IN ('pending','approved')),0),
  coalesce(sum(commission_amount) FILTER (WHERE status IN ('pending','approved')),0),
  min(currency), rsg.usd_rate(min(currency), d),
  'awin api ' || to_char(max(loaded_at) AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM (SELECT *, (transaction_at AT TIME ZONE 'UTC')::date d FROM rsg.awin_transaction) t
GROUP BY coalesce(advertiser, 'awin ' || advertiser_id), d, coalesce(click_ref,'');
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('awin', %s, %s, %d);
COMMIT;""" % (q(plan[0][1].isoformat()), q(plan[-1][2].isoformat()), n))
    print('\n'.join(out)); print(f'{n} transactions in {len(plan)} windows', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
