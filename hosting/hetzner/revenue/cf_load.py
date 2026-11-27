#!/usr/bin/env python3
"""Commission Factory affiliate API (Transactions) -> SQL on stdout (pipe into psql as umami). Stdlib only.

Every transaction is kept in rsg.cf_transaction (upsert by Commission Factory id, nothing deleted; raw JSON kept);
Commission Factory's rows in rsg.revenue_period are then rebuilt from it: day = DateCreated (order time as CF records it),
account = merchant, tag = UniqueId (our sub-ID, '' = none), amounts in the account currency (AUD) with the ECB rate.
Status Pending -> commission_pending, Approved -> commission_confirmed, Void -> commission_reversed (negative, not in earnings).
Built 27 Nov 2026 (affiliate 85747; only Pace Athletic joined, 6 %; no transactions yet), tested inside a rolled-back
transaction; not in run_daily.sh until switched on.

env: CF_API_KEY (affiliate API key; _private/commission_factory.txt line 2)
usage: cf_load.py            last 90 days (daily)
       cf_load.py backfill   last 3 years
"""
import os, sys, json, time, urllib.request, urllib.parse, datetime as dt

KEY = os.environ['CF_API_KEY']
API = 'https://api.commissionfactory.com/V1/Affiliate/'

def get(path, params):
    url = API + path + '?' + urllib.parse.urlencode(dict(params, apiKey=KEY))
    for attempt in range(4):
        try:
            return json.load(urllib.request.urlopen(urllib.request.Request(url, headers={'Accept': 'application/json'}), timeout=120))
        except Exception as ex:
            if attempt == 3: raise SystemExit(f'Commission Factory error {path} {params}: {str(ex).replace(KEY, "<key>")}')
            time.sleep(20 * (attempt + 1))

def q(v):
    if v in (None, ''): return 'NULL'
    if isinstance(v, bool): return 'true' if v else 'false'
    if isinstance(v, (int, float)): return repr(v)
    return "'" + str(v).replace("'", "''") + "'"

COLS = ['id', 'merchant_id', 'merchant', 'order_id', 'status', 'status2', 'sale_value', 'commission', 'currency',
        'reported_sale_value', 'unique_id', 'customer_country', 'tracking_method', 'created_at', 'modified_at', 'raw']

def main(argv):
    today = dt.date.today()
    a = today - dt.timedelta(days=3 * 365 if argv[:1] == ['backfill'] else 90)
    first, out, n = a, ['BEGIN;'], 0
    while a <= today:
        b = min(a + dt.timedelta(days=30), today)
        rows = get('Transactions', {'fromDate': a.isoformat(), 'toDate': b.isoformat()})
        if not isinstance(rows, list): raise SystemExit(f'Commission Factory unexpected reply {a}: {str(rows)[:200]}')
        for t in rows:
            vals = [t['Id'], t.get('MerchantId'), t.get('MerchantName'), t.get('OrderId'), t.get('Status'), t.get('Status2'),
                    t.get('SaleValue'), t.get('Commission'), 'AUD', t.get('ReportedSaleValue'), t.get('UniqueId') or '',
                    t.get('CustomerCountryCode'), t.get('TrackingMethod'), t.get('DateCreated'), t.get('DateModified'), json.dumps(t)]
            out.append('INSERT INTO rsg.cf_transaction (' + ','.join(COLS) + ') VALUES (' + ','.join(q(v) for v in vals) +
                       ') ON CONFLICT (id) DO UPDATE SET ' + ','.join(f'{c}=EXCLUDED.{c}' for c in COLS[1:]) + ', loaded_at=now();')
            n += 1
        print(f'{a}..{b}: {len(rows)}', file=sys.stderr)
        a = b + dt.timedelta(days=1)
    out.append("""DELETE FROM rsg.revenue_period WHERE source='commission_factory';
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,
  items_ordered,ordered_revenue,commission_pending,commission_confirmed,commission_reversed,earnings,currency,usd_rate,load_ref)
SELECT 'commission_factory', merchant, 'day', d, d, 'order', coalesce(unique_id,''), '',
  count(*) FILTER (WHERE status <> 'Void'),
  sum(sale_value) FILTER (WHERE status <> 'Void'),
  coalesce(sum(commission) FILTER (WHERE status = 'Pending'),0),
  coalesce(sum(commission) FILTER (WHERE status = 'Approved'),0),
  -coalesce(sum(commission) FILTER (WHERE status = 'Void'),0),
  coalesce(sum(commission) FILTER (WHERE status <> 'Void'),0),
  min(currency), rsg.usd_rate(min(currency), d),
  'commission factory api ' || to_char(max(loaded_at) AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM (SELECT *, created_at::date d FROM rsg.cf_transaction) t
GROUP BY merchant, d, coalesce(unique_id,'');
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('commission_factory', %s, %s, %d);
COMMIT;""" % (q(first.isoformat()), q(today.isoformat()), n))
    print('\n'.join(out)); print(f'{n} transactions', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
