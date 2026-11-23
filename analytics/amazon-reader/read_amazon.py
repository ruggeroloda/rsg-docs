#!/usr/bin/env python3
"""Read Amazon US Associates reports from data-drops/amazon/ (Mac only, not in git).

Checks every file's name against its contents, maps tracking IDs to page type
(scheme valid until 10 Nov 2026, analytics/amazon-tags-2026-11-23/PLAN.md part A)
and prints clicks / orders / earnings / $ per click per period and page type.

usage: python3 -W ignore read_amazon.py <data-drops/amazon dir> [monthly|weekly]
"""
import sys, re, glob, os, datetime as dt
from collections import defaultdict
import openpyxl

def num(v):
    return 0.0 if v in (None, '-', '') else float(v)

def rows(path):
    ws = openpyxl.load_workbook(path).active
    rs = [r for r in ws.iter_rows(values_only=True) if any(c is not None for c in r)]
    head = list(rs[0])
    return [dict(zip(head, r)) for r in rs[1:]]

# Old scheme (until 10 Nov 2026). "hidden" = Amazon's "Other" row: tags with < ~4 items in the period.
def page_type(tag, start='2026-11-16'):
    if tag == 'Other': return 'hidden'
    if tag == 'pimeagi-20': return 'untagged'
    if tag == 'rsg-comp-20': return 'comparisons'
    if tag in ('rsg-single-20', 'rsg-single-trail-20'): return 'reviews'
    if tag.startswith('rsg-rev') and tag != 'rsg-reviewlist-20': return 'reviews'  # 16-23 Nov 2026 per-spot review tags
    if tag == 'rsg-bestof-20': return 'lists' if start >= '2026-11-16' else 'rest'  # from 16 Nov 2026: lists/guides/home/related/text links; before: article shortcodes
    if tag.startswith('rsg-best') and tag not in ('rsg-bestof-20', 'rsg-bestold-20'): return 'lists'
    return 'rest'   # wizard, gps, tube, reviewlist, bestof, overview, unknown

FN = re.compile(r'amazon_(tracking-id|category)_(\d{4}-\d{2}-\d{2})_(\d{4}-\d{2}-\d{2})\.xlsx$')

def main(base, kind):
    files = sorted(glob.glob(os.path.join(base, kind, 'amazon_*.xlsx')))
    cat = {}   # (start,end) -> (date in file, clicks, earnings)
    trk = {}
    for f in files:
        m = FN.search(os.path.basename(f))
        if not m: continue
        rep, s, e = m.groups()
        rs = rows(f)
        if rep == 'category':
            dates = {r['Date'] for r in rs}
            cat[(s, e)] = (dates, sum(num(r['Clicks']) for r in rs), sum(num(r['Total Earnings']) for r in rs))
        else:
            trk[(s, e)] = rs
    print('## File check')
    bad = []
    for (s, e), rs in sorted(trk.items()):
        c = sum(num(r['Clicks']) for r in rs); g = sum(num(r['Total Earnings']) for r in rs)
        note = 'no category file (weekly: check against month)'
        if (s, e) in cat:
            dates, cc, cg = cat[(s, e)]
            want = dt.date.fromisoformat(e).strftime('%m-%d-%Y')
            ok = dates == {want} and abs(cc - c) < 1 and abs(cg - g) < 0.05
            note = 'OK' if ok else f'MISMATCH: category date {dates} (want {want}), clicks {cc:.0f} vs {c:.0f}, $ {cg:.2f} vs {g:.2f}'
            if not ok: bad.append((s, e))
        print(f'{s}..{e}  clicks {c:>7.0f}  ${g:>9.2f}  {note}')
    seen = {}
    for k, rs in trk.items():
        sig = tuple(sorted((r['Tracking Id'], num(r['Clicks'])) for r in rs))
        if sig in seen: print(f'DUPLICATE content: {k} == {seen[sig]}'); bad.append(k)
        seen[sig] = k
    print('\n## By period and page type  (clicks / orders / $ / $ per click)')
    types = ['reviews', 'comparisons', 'lists', 'untagged', 'rest', 'hidden']
    for (s, e), rs in sorted(trk.items()):
        if (s, e) in bad: continue
        agg = defaultdict(lambda: [0, 0, 0])
        for r in rs:
            a = agg[page_type(r['Tracking Id'], s)]
            a[0] += num(r['Clicks']); a[1] += num(r['Items Ordered']); a[2] += num(r['Total Earnings'])
        T = [sum(agg[t][i] for t in types) for i in range(3)]
        cells = [f"{t} {agg[t][0]:.0f}/{agg[t][1]:.0f}/{agg[t][2]:.0f}/{(agg[t][2]/agg[t][0] if agg[t][0] else 0):.2f}" for t in types if agg[t][0] or agg[t][2]]
        print(f"{s[:7] if kind=='monthly' else s}  TOTAL {T[0]:.0f}/{T[1]:.0f}/{T[2]:.0f}/{T[2]/T[0]:.3f} | " + ' | '.join(cells))
    unknown = sorted({r['Tracking Id'] for rs in trk.values() for r in rs if page_type(r['Tracking Id']) == 'rest'})
    print('\n"rest" tags:', ', '.join(unknown))

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2] if len(sys.argv) > 2 else 'monthly')
