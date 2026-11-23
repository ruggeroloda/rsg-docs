#!/usr/bin/env python3
"""Amazon US Associates files (data-drops/amazon/{monthly,weekly}/) -> SQL for rsg.revenue_period.

Prints SQL on stdout; pipe it into psql on rsgmedia (analytics/README.md "Revenue data").
Each file's period is replaced whole (re-downloads overwrite). Checks before loading:
  monthly: name = amazon_tracking-id_<first>_<last of month>.xlsx and a Category file of the same
           name whose Date column is the month end and whose clicks + earnings equal the Tracking-Id file
  weekly:  name = amazon_tracking-id_<Mon>_<Sun>.xlsx (7 days)
A file that fails is skipped and reported on stderr.

usage: python3 -I -W ignore load_amazon.py <data-drops/amazon dir> [file-name filter]
"""
import sys, os, re, glob, calendar, datetime as dt
import openpyxl

FN = re.compile(r'^amazon_tracking-id_(\d{4}-\d{2}-\d{2})_(\d{4}-\d{2}-\d{2})\.xlsx$')

def num(v):
    return None if v in (None, '-', '') else float(v)

def rows(path):
    ws = openpyxl.load_workbook(path).active
    rs = [r for r in ws.iter_rows(values_only=True) if any(c is not None for c in r)]
    return [dict(zip(rs[0], r)) for r in rs[1:]]

def tot(rs, col):
    return sum(num(r[col]) or 0 for r in rs)

def q(v):
    if v is None: return 'NULL'
    if isinstance(v, str): return "'" + v.replace("'", "''") + "'"
    return repr(v)

def check(path, grain, s, e):
    ds, de = dt.date.fromisoformat(s), dt.date.fromisoformat(e)
    trk = rows(path)
    if grain == 'week':
        if ds.weekday() != 0 or (de - ds).days != 6: return None, 'not a Mon-Sun week'
        return trk, None
    if ds.day != 1 or de.day != calendar.monthrange(ds.year, ds.month)[1] or (ds.year, ds.month) != (de.year, de.month):
        return None, 'not a whole month'
    cat_path = path.replace('amazon_tracking-id_', 'amazon_category_')
    if not os.path.exists(cat_path): return None, 'no Category file to check against'
    cat = rows(cat_path)
    want = de.strftime('%m-%d-%Y')
    if {r['Date'] for r in cat} != {want}: return None, f"Category date {sorted({r['Date'] for r in cat})} != {want}"
    if abs(tot(cat, 'Clicks') - tot(trk, 'Clicks')) >= 1 or abs(tot(cat, 'Total Earnings') - tot(trk, 'Total Earnings')) >= 0.05:
        return None, 'Category and Tracking-Id totals differ'
    return trk, None

def main(base, filt=''):
    out = ['BEGIN;']
    loaded = 0
    for grain, folder in (('month', 'monthly'), ('week', 'weekly')):
        for path in sorted(glob.glob(os.path.join(base, folder, 'amazon_tracking-id_*.xlsx'))):
            name = os.path.basename(path)
            m = FN.match(name)
            if not m or filt not in name: continue
            s, e = m.groups()
            trk, err = check(path, grain, s, e)
            if err:
                print(f'SKIP {name}: {err}', file=sys.stderr); continue
            out.append(f"DELETE FROM rsg.revenue_period WHERE source='amazon_us' AND account='pimeagi-20' "
                       f"AND grain='{grain}' AND period_start='{s}' AND period_end='{e}';")
            for r in trk:
                c = num(r['Clicks'])
                vals = ['amazon_us', 'pimeagi-20', grain, s, e, 'amazon', r['Tracking Id'], '',
                        None if c is None else int(c),
                        *(None if num(r[k]) is None else int(num(r[k])) for k in ('Items Ordered', 'Items Shipped', 'Items Returned')),
                        num(r['Ordered Revenue']), num(r['Items Shipped Revenue']), num(r['Items Returned Revenue']),
                        num(r['Total Earnings']), 'USD', 1, name]
                out.append('INSERT INTO rsg.revenue_period (source,account,grain,period_start,period_end,date_basis,tag,page,'
                           'clicks,items_ordered,items_shipped,items_returned,ordered_revenue,shipped_revenue,returned_revenue,'
                           'earnings,currency,usd_rate,load_ref) VALUES (' + ','.join(q(v) for v in vals) + ');')
            loaded += 1
            print(f'ok   {name}: {len(trk)} rows, {tot(trk, "Clicks"):.0f} clicks, ${tot(trk, "Total Earnings"):.2f}', file=sys.stderr)
    out.append('COMMIT;')
    print('\n'.join(out))
    print(f'{loaded} files', file=sys.stderr)

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2] if len(sys.argv) > 2 else '')
