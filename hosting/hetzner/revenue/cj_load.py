#!/usr/bin/env python3
"""CJ Commission Detail API -> SQL on stdout (pipe into psql as umami). Stdlib only.

Every commission is kept in rsg.cj_commission (upsert by commission id); CJ's rows in
rsg.revenue_period are then rebuilt from that table (day = order/event date UTC,
account = advertiser, tag = sub-ID / shopperId, '' when none).

env: CJ_CID, CJ_TOKEN (~/revenue/.env)
usage: cj_load.py                 last 90 days of posting dates (daily run)
       cj_load.py 2010-01 2026-11 every month in the range (backfill)
"""
import os, sys, json, time, urllib.request, datetime as dt

URL = 'https://commissions.api.cj.com/query'
FIELDS = ('commissionId actionTrackerName actionType actionStatus advertiserId advertiserName eventDate '
          'postingDate lockingDate original originalActionId orderId correctionReason saleAmountUsd '
          'pubCommissionAmountUsd shopperId websiteId websiteName clickDate country')

def windows(argv):
    now = dt.datetime.now(dt.timezone.utc).replace(microsecond=0)
    if len(argv) == 2:
        y, m = map(int, argv[0].split('-')); y2, m2 = map(int, argv[1].split('-'))
        while (y, m) <= (y2, m2):
            s = dt.datetime(y, m, 1, tzinfo=dt.timezone.utc)
            y, m = (y + 1, 1) if m == 12 else (y, m + 1)
            yield s, min(dt.datetime(y, m, 1, tzinfo=dt.timezone.utc), now)
    else:
        s = now - dt.timedelta(days=90)
        while s < now:
            e = min(s + dt.timedelta(days=30), now); yield s, e; s = e

def fetch(s, e):
    iso = lambda d: d.strftime('%Y-%m-%dT%H:%M:%SZ')
    q = ('{ publisherCommissions(forPublishers: ["%s"], sincePostingDate: "%s", beforePostingDate: "%s") '
         '{ count payloadComplete records { %s } } }') % (os.environ['CJ_CID'], iso(s), iso(e), FIELDS)
    req = urllib.request.Request(URL, json.dumps({'query': q}).encode(),
        {'Authorization': 'Bearer ' + os.environ['CJ_TOKEN'], 'Content-Type': 'application/json'})
    for attempt in range(4):
        try:
            d = json.load(urllib.request.urlopen(req, timeout=60)); break
        except Exception as ex:
            if attempt == 3: raise
            time.sleep(5 * (attempt + 1))
    if d.get('errors'): raise SystemExit(f'CJ error {s:%Y-%m-%d}: {d["errors"]}')
    p = d['data']['publisherCommissions']
    if not p['payloadComplete']: raise SystemExit(f'CJ payload incomplete {s:%Y-%m-%d}..{e:%Y-%m-%d}: split the window')
    return p['records']

def q(v):
    if v is None: return 'NULL'
    if isinstance(v, bool): return 'true' if v else 'false'
    return "'" + str(v).replace("'", "''") + "'"

COLS = ['commission_id', 'action_tracker', 'action_type', 'action_status', 'advertiser_id', 'advertiser',
        'event_at', 'posting_at', 'locking_at', 'original', 'original_action_id', 'order_id', 'correction_reason',
        'sale_usd', 'commission_usd', 'sub_id', 'website_id', 'website', 'click_at', 'country']

def main(argv):
    out, n, ws = ['BEGIN;'], 0, list(windows(argv))
    for s, e in ws:
        recs = fetch(s, e); n += len(recs)
        for r in recs:
            vals = [r[k] for k in FIELDS.split()]
            out.append('INSERT INTO rsg.cj_commission (' + ','.join(COLS) + ') VALUES (' + ','.join(q(v) for v in vals) +
                       ') ON CONFLICT (commission_id) DO UPDATE SET ' +
                       ','.join(f'{c}=EXCLUDED.{c}' for c in COLS[1:]) + ', loaded_at=now();')
        print(f'{s:%Y-%m-%d}..{e:%Y-%m-%d} {len(recs)}', file=sys.stderr)
        time.sleep(0.5)
    out.append("""DELETE FROM rsg.revenue_period WHERE source='cj';
INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,
  items_ordered,ordered_revenue,commission_pending,commission_confirmed,commission_reversed,earnings,currency,usd_rate,load_ref)
SELECT 'cj', advertiser, 'day', (event_at AT TIME ZONE 'UTC')::date, (event_at AT TIME ZONE 'UTC')::date, 'order',
  coalesce(sub_id,''), '',
  count(*) FILTER (WHERE original),
  sum(sale_usd) FILTER (WHERE original),
  coalesce(sum(commission_usd) FILTER (WHERE action_status IN ('new','extended') AND commission_usd >= 0),0),
  coalesce(sum(commission_usd) FILTER (WHERE action_status IN ('locked','closed') AND commission_usd >= 0),0),
  coalesce(sum(commission_usd) FILTER (WHERE commission_usd < 0),0),
  sum(commission_usd), 'USD', 1, 'cj api ' || to_char(max(loaded_at) AT TIME ZONE 'UTC','YYYY-MM-DD HH24:MI')
FROM rsg.cj_commission GROUP BY 2,4,7;
INSERT INTO rsg.revenue_load_log (source, window_from, window_to, records) VALUES ('cj', %s, %s, %d);
COMMIT;""" % (q(ws[0][0].isoformat()), q(ws[-1][1].isoformat()), n))
    print('\n'.join(out))
    print(f'{n} commissions in {len(ws)} windows', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1:])
