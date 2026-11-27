#!/usr/bin/env python3
"""AvantLink (Canada) AffiliateReport API, report 8 'commission detail' -> SQL on stdout (pipe into psql as umami). Stdlib only.

Every row is kept in rsg.avantlink_commission (upsert by AvantLink transaction id, e.g. SALE-167962621; nothing deleted);
AvantLink's rows in rsg.revenue_period are then rebuilt from it: day = transaction date, account = merchant (Running Room
Canada, MEC), tag = Custom Tracking Code (our sub-ID, the DFS 'ctc'), amounts in CAD (Canadian merchants; the report prints
'$') with the ECB rate. Status 'Pending…' -> commission_pending; any other status -> commission_confirmed; negative
total (returns/adjustments) -> commission_reversed. The report returns nothing for long ranges: windows of 30 days.
Built 27 Nov 2026 (affiliate 222669, website 279417), tested inside a rolled-back transaction; not in run_daily.sh until switched on.

env: AVANTLINK_AFFILIATE_ID, AVANTLINK_AUTH_KEY (_private/avant_link_ca.txt)
usage: avantlink_load.py            last 90 days (daily)
       avantlink_load.py backfill   last 3 years
"""
import os, re, sys, csv, io, time, urllib.request, urllib.parse, datetime as dt

AID, KEY = os.environ['AVANTLINK_AFFILIATE_ID'], os.environ['AVANTLINK_AUTH_KEY']

def get(a, b):
    url = 'https://classic.avantlink.com/api.php?' + urllib.parse.urlencode(dict(module='AffiliateReport', affiliate_id=AID, auth_key=KEY,
          report_id=8, date_begin=a.isoformat(), date_end=b.isoformat(), output='csv'))
    for attempt in range(4):
        try:
            body = urllib.request.urlopen(url, timeout=120).read().decode('utf-8', 'replace')
            if not body.startswith('Merchant,'): raise ValueError(body[:200])
            return list(csv.DictReader(io.StringIO(body)))
        except Exception as ex:
            if attempt == 3: raise SystemExit(f'AvantLink error {a}..{b}: {str(ex).replace(KEY, "<key>")}')
            time.sleep(20 * (attempt + 1))

def q(v):
    if v in (None, ''): return 'NULL'
    if isinstance(v, (int, float)): return repr(v)
    return "'" + str(v).replace("'", "''") + "'"

def money(s):
    s = (s or '').strip(); neg = s.startswith('-') or s.startswith('(')
    v = re.sub(r'[^0-9.]', '', s)
    return (-float(v) if neg else float(v)) if v else None

COLS = ['txn_id', 'merchant_id', 'merchant', 'website_id', 'order_id', 'amount', 'base_commission', 'incentive_commission',
        'total_commission', 'currency', 'status', 'txn_type', 'txn_at', 'last_click_at', 'tracking_code', 'coupon_code', 'item_count']

def main(argv):
    today = dt.date.today()
    a = today - dt.timedelta(days=3 * 365 if argv[:1] == ['backfill'] else 90)
    first, out, n = a, ['BEGIN;'], 0
    while a <= today:
        b = min(a + dt.timedelta(days=29), today)
        rows = get(a, b)
        for r in rows:
            g = lambda k: (r.get(k) or '').strip()
            vals = [g('AvantLink Transaction Id'), g('Merchant Id') or None, g('Merchant'), g('Website Id') or None, g('Order Id'),
                    money(g('Transaction Amount')), money(g('Base Commission')), money(g('Incentive Commission')),
                    money(g('Total Commission')), 'CAD', g('Commission Status'), g('Transaction Type'), g('Transaction Date') or None,
                    g('Last Click Through') or None, g('Custom Tracking Code'), g('Coupon Code'), int(g('Item Count') or 0)]
            out.append('INSERT INTO rsg.avantlink_commission (' + ','.join(COLS) + ') VALUES (' + ','.join(q(v) for v in vals) +
                       ') ON CONFLICT (txn_id) DO UPDATE SET ' + ','.join(f'{c}=EXCLUDED.{c}' for c in COLS[1:]) + ', loaded_at=now();')
            n += 1
        print(f'{a}..{b}: {len(rows)}', file=sys.stderr)
        a = b + dt.timedelta(days=1)
    out.append("""DELETE FROM rsg.revenue_period WHERE source='avantlink';
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,
  items_ordered,ordered_revenue,commission_pending,commission_confirmed,commission_reversed,earnings,currency,usd_rate,load_ref)
SELECT 'avantlink', merchant, 'day', d, d, 'order', coalesce(tracking_code,''), '',
  sum(item_count) FILTER (WHERE total_commission >= 0),
  sum(amount) FILTER (WHERE total_commission >= 0),
  coalesce(sum(total_commission) FILTER (WHERE total_commission >= 0 AND status ILIKE 'pending%%'),0),
  coalesce(sum(total_commission) FILTER (WHERE total_commission >= 0 AND status NOT ILIKE 'pending%%'),0),
  coalesce(sum(total_commission) FILTER (WHERE total_commission < 0),0),
  coalesce(sum(total_commission),0),
  min(currency), rsg.usd_rate(min(currency), d),
  'avantlink api ' || to_char(max(loaded_at) AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM (SELECT *, txn_at::date d FROM rsg.avantlink_commission) t
GROUP BY merchant, d, coalesce(tracking_code,'');
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('avantlink', %s, %s, %d);
COMMIT;""" % (q(first.isoformat()), q(today.isoformat()), n))
    print('\n'.join(out)); print(f'{n} rows', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
