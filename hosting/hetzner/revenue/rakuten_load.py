#!/usr/bin/env python3
"""Rakuten Advertising (LinkShare) Signature Orders report -> SQL on stdout (pipe into psql as umami).

Source: the Reporting Platform API link of the "Signature Orders" report (token in ~/revenue/.env as
RAKUTEN_REPORT_TOKEN; the report refuses start dates older than ~4 years and long ranges, so windows of
<= 365 days). Windows are by transaction (order) date, GMT; each window's rows in rsg.rakuten_order are
replaced whole, then Rakuten's rows in rsg.revenue_period are rebuilt from that table.
No clicks and no pending/confirmed status in this report.

usage: rakuten_load.py                         last 90 days (daily run)
       rakuten_load.py 2022-11-24 2026-11-23   backfill a date range
"""
import os, sys, csv, io, time, urllib.request, urllib.parse, datetime as dt

BASE = 'https://ran-reporting.rakutenmarketing.com/en/reports/signature-orders-report/filters'

def windows(a, b):
    while a <= b:
        e = min(a + dt.timedelta(days=364), b); yield a, e; a = e + dt.timedelta(days=1)

def fetch(s, e):
    qs = urllib.parse.urlencode({'start_date': s.isoformat(), 'end_date': e.isoformat(), 'include_summary': 'N',
        'network': '1', 'tz': 'GMT', 'date_type': 'transaction', 'date_format': 'yyyy-mm-dd'})
    url = f"{BASE}?{qs}&token={os.environ['RAKUTEN_REPORT_TOKEN']}"   # token is already URL-encoded
    for attempt in range(4):
        try:
            body = urllib.request.urlopen(url, timeout=170).read().decode('utf-8-sig'); break
        except Exception:
            if attempt == 3: raise
            time.sleep(10 * (attempt + 1))
    if body.lstrip().startswith('{'): raise SystemExit(f'Rakuten error {s}..{e}: {body[:200]}')
    return list(csv.DictReader(io.StringIO(body)))

def q(v):
    return 'NULL' if v in (None, '', 'null') else "'" + str(v).replace("'", "''") + "'"

def main(argv):
    today = dt.datetime.now(dt.timezone.utc).date()
    a, b = (dt.date.fromisoformat(argv[0]), dt.date.fromisoformat(argv[1])) if len(argv) == 2 else (today - dt.timedelta(days=90), today)
    out, n = ['BEGIN;'], 0
    for s, e in windows(a, b):
        rows = fetch(s, e); n += len(rows)
        w = f"transaction_at >= '{s}' AND transaction_at < '{e + dt.timedelta(days=1)}'"
        out.append('CREATE TEMP TABLE IF NOT EXISTS rk_new (LIKE rsg.rakuten_order INCLUDING DEFAULTS); TRUNCATE rk_new;')
        for r in rows:
            vals = [r['MID'], r['Advertiser Name'], r['Order ID'], f"{r['Transaction Date']} {r['Transaction Time']}+00",
                    r['SKU'], r['Sales'], r['# of Items'], r['Total Commission'], f"{r['Process Date']} {r['Process Time']}+00",
                    r['Member ID (U1)']]
            out.append('INSERT INTO rk_new (mid,advertiser,order_id,transaction_at,sku,sales_usd,items,commission_usd,process_at,u1) VALUES ('
                       + ','.join(q(v) for v in vals) + ');')
        # Guard: never replace a window with a download that has less than half the rows already stored
        # (an empty or cut-off report would otherwise wipe good data). The exception rolls back the whole run.
        out.append(f"""DO $$ DECLARE o int; n int; BEGIN
  SELECT count(*) INTO o FROM rsg.rakuten_order WHERE {w}; SELECT count(*) INTO n FROM rk_new;
  IF o >= 5 AND n < o / 2 THEN RAISE EXCEPTION 'Rakuten {s}..{e}: % rows downloaded vs % stored, nothing replaced', n, o; END IF; END $$;
DELETE FROM rsg.rakuten_order WHERE {w};
INSERT INTO rsg.rakuten_order SELECT * FROM rk_new;""")
        print(f'{s}..{e} {len(rows)}', file=sys.stderr)
    out.append("""DELETE FROM rsg.revenue_period WHERE source='rakuten';
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,
  items_ordered,ordered_revenue,commission_reversed,earnings,currency,usd_rate,load_ref)
SELECT 'rakuten', advertiser, 'day', (transaction_at AT TIME ZONE 'UTC')::date, (transaction_at AT TIME ZONE 'UTC')::date, 'order',
  coalesce(u1,''), '', sum(items), sum(sales_usd), coalesce(sum(commission_usd) FILTER (WHERE commission_usd < 0),0),
  sum(commission_usd), 'USD', 1, 'rakuten report ' || to_char(max(loaded_at) AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM rsg.rakuten_order GROUP BY 2,4,7;
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('rakuten', %s, %s, %d);
COMMIT;""" % (q(a.isoformat()), q(b.isoformat()), n))
    print('\n'.join(out))
    print(f'{n} rows', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
