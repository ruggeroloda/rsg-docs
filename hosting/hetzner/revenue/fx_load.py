#!/usr/bin/env python3
"""ECB euro reference rates -> SQL for rsg.fx_ecb_day (stdout; pipe into psql as umami). Stdlib only.

usage: fx_load.py        last 30 days (daily)
       fx_load.py all    full history since 1999 (backfill)
"""
import sys, io, csv, zipfile, urllib.request, datetime as dt
URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.zip'
data = urllib.request.urlopen(URL, timeout=120).read()
z = zipfile.ZipFile(io.BytesIO(data))
rows = list(csv.DictReader(io.StringIO(z.read(z.namelist()[0]).decode())))
since = dt.date(1999, 1, 1) if sys.argv[1:] == ['all'] else dt.date.today() - dt.timedelta(days=30)
if len(rows) < 5000 or 'USD' not in rows[0]: raise SystemExit('ECB file looks wrong: nothing loaded')
out, n = ['BEGIN;'], 0
for r in rows:
    d = dt.date.fromisoformat(r['Date'])
    if d < since: continue
    vals = [f"('{d}','{c.strip()}',{v.strip()})" for c, v in r.items() if c and c.strip() not in ('Date', '') and v and v.strip() not in ('N/A', '')]
    if vals:
        out.append('INSERT INTO rsg.fx_ecb_day (day, currency, per_eur) VALUES ' + ','.join(vals) +
                   ' ON CONFLICT (day, currency) DO UPDATE SET per_eur = EXCLUDED.per_eur;'); n += len(vals)
out.append("INSERT INTO rsg.fx_ecb_day (day, currency, per_eur) SELECT DISTINCT day, 'EUR', 1 FROM rsg.fx_ecb_day ON CONFLICT DO NOTHING;")
out.append(f"INSERT INTO rsg.revenue_load_log (source, window_from, records) VALUES ('fx_ecb', '{since}', {n});")
out.append('COMMIT;'); print('\n'.join(out)); print(f'{n} rates since {since}', file=sys.stderr)
