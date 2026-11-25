#!/usr/bin/env python3
"""Earnings dashboard: rsg revenue tables + Umami shop clicks -> JSON -> template.html -> one static page.

Runs on rsgmedia (reads Postgres through `docker exec postgres psql`). No writes to the database.
usage: python3 build_dashboard.py <template.html> <out.html> [--json out.json]

Rules (analytics/earnings-dashboard/README.md):
- Month = the month of yesterday (Europe/Amsterdam). Each source is compared over its own covered days.
- Networks (CJ, Awin, Rakuten, Impact): day rows by order date, through yesterday.
- Raptive: day rows, through its latest loaded day. Not loaded before RAPTIVE_FROM -> null, never 0.
- Amazon US: weekly files only. Month so far = weeks fully inside the month; last month = the same
  number of weeks starting in the previous month; last year = none (no 2025 weekly files).
- Clicks: Umami shop_click (since 16 Nov 2026) mapped to network accounts with CLICK_MAP; Amazon
  clicks come from Amazon's own report. EPC = earnings / clicks.
"""
import sys, json, subprocess, datetime as dt
from zoneinfo import ZoneInfo

NETS = ['cj', 'awin', 'rakuten', 'impact']
LABEL = {'raptive': 'Raptive', 'amazon_us': 'Amazon US', 'cj': 'CJ', 'awin': 'Awin', 'rakuten': 'Rakuten', 'impact': 'Impact'}
ORDER = ['raptive', 'amazon_us', 'cj', 'awin', 'rakuten', 'impact']
UMAMI_FROM = dt.date(2026, 11, 16)   # shop_click with merchant exists from 16 Nov 2026 09:18 UTC
# Umami shop_click merchant -> (source, account in rsg.revenue_period). Networks per dfs/RUNBOOK.md.
CLICK_MAP = {
    'Nike': ('cj', 'NIKE'), 'Puma': ('cj', 'PUMA US'), 'Puma UK': ('cj', 'PUMA UK'),
    'Zappos': ('rakuten', 'Zappos.com'), 'Hoka': ('rakuten', 'HOKA US'),
    'Nike UK': ('awin', 'Nike UK'), 'Nike DE': ('awin', 'Nike DE'), 'Nike IT': ('awin', 'Nike IT'),
    'Hoka UK': ('awin', 'Hoka UK'),
    'Brooks': ('impact', 'Brooks Running'), 'adidas': ('impact', 'adidas'),
}
PAGE_GROUP = {'reviews': 'Reviews', 'comparisons': 'Comparisons', 'lists': 'Lists & guides', 'untagged': 'Untagged (Amazon)',
              'hidden': 'Hidden by Amazon', 'rest': 'Other pages', 'unmapped': 'Not mapped yet'}

def q(sql):
    out = subprocess.run(['docker', 'exec', '-i', 'postgres', 'psql', '-U', 'umami', '-d', 'umami', '-At', '-v', 'ON_ERROR_STOP=1'],
                         input=sql, capture_output=True, text=True, check=True).stdout.strip()
    return json.loads(out) if out else None

def lit(d): return "'%s'" % d.isoformat()
def r2(v): return None if v is None else round(float(v), 2)
def month_add(d, n):
    y, m = divmod(d.month - 1 + n, 12)
    return dt.date(d.year + y, m + 1, 1)
def clamp_day(first, day):
    nxt = month_add(first, 1)
    return min(first + dt.timedelta(days=day - 1), nxt - dt.timedelta(days=1))
def fmt_range(a, b):
    return f"{a.day}–{b.day} {b.strftime('%b %Y')}" if a != b else f"{a.day} {a.strftime('%b %Y')}"

def main(tpl, out, json_out=None):
    now = dt.datetime.now(ZoneInfo('Europe/Amsterdam'))
    yday = now.date() - dt.timedelta(days=1)
    m0 = yday.replace(day=1); pm0 = month_add(m0, -1); ly0 = month_add(m0, -12)

    cov = q("""select json_build_object(
      'raptive_max', (select max(period_start) from rsg.revenue_period where source='raptive'),
      'raptive_min', (select min(period_start) from rsg.revenue_period where source='raptive'),
      'amazon_week_end', (select max(period_end) from rsg.revenue_period where source='amazon_us' and grain='week'),
      'loads', (select json_object_agg(source, d) from (select source, max(loaded_at)::date d from rsg.revenue_load_log group by 1) x))""")
    loads = cov['loads'] or {}
    end = {}
    for s in NETS:   # through yesterday if today's load ran, else the day before the last load
        ld = dt.date.fromisoformat(loads[s]) if s in loads else None
        end[s] = min(yday, ld - dt.timedelta(days=1)) if ld else yday
    rap_min = dt.date.fromisoformat(cov['raptive_min']); end['raptive'] = min(yday, dt.date.fromisoformat(cov['raptive_max']))

    # ---- day sources: month so far, same days last month / last year, per source and account
    def day_window(s, first):
        e = end[s]
        if e < m0: return None
        return first, clamp_day(first, e.day)
    rows = {}
    for s in ['raptive'] + NETS:
        w = {'this': (m0, end[s]) if end[s] >= m0 else None, 'last_month': day_window(s, pm0), 'last_year': day_window(s, ly0)}
        acc = {}
        for k, win in w.items():
            if not win: continue
            if s == 'raptive' and win[0] < rap_min:   # not loaded that far back
                continue
            res = q(f"""select coalesce(json_agg(x),'[]') from (select account,
                  sum(earnings_usd) e, sum(commission_pending*usd_rate) p, sum(commission_confirmed*usd_rate) c
                  from rsg.revenue_period where source='{s}' and grain='day' and period_start between {lit(win[0])} and {lit(win[1])}
                  group by 1) x""")
            for r in res:
                a = acc.setdefault(r['account'], {})
                a[k] = r['e'] or 0
                if k == 'this': a['pending'] = r['p']; a['confirmed'] = r['c']
        rows[s] = (w, acc)

    # Umami clicks per (source, account): this month and per week
    clicks = q(f"""select coalesce(json_agg(x),'[]') from (select m, (created_at at time zone 'UTC')::date d, count(*) n from (
        select we.event_id, we.created_at, max(ed.string_value) filter (where ed.data_key='merchant') m
        from website_event we join event_data ed on ed.website_event_id=we.event_id
        where we.event_name='shop_click' and we.created_at >= {lit(month_add(m0, -4))}
        group by 1,2) c group by 1,2) x""")
    def clicks_for(s, a, d1, d2):
        if d2 < UMAMI_FROM: return None
        n = sum(c['n'] for c in clicks if CLICK_MAP.get(c['m']) == (s, a) and d1 <= dt.date.fromisoformat(c['d']) <= d2)
        return n

    cw0 = max(m0, UMAMI_FROM)
    ce = q(f"""select coalesce(json_agg(x),'[]') from (select source, account, sum(earnings_usd) e from rsg.revenue_period
              where grain='day' and source in ('cj','awin','rakuten','impact') and period_start between {lit(cw0)} and {lit(yday)} group by 1,2) x""")
    ce = {(r['source'], r['account']): r['e'] or 0 for r in ce}
    mtd = []
    for s in ['raptive'] + NETS:
        w, acc = rows[s]
        if not acc and s != 'raptive': pass
        kids = []
        for a, v in sorted(acc.items(), key=lambda kv: -(kv[1].get('this') or 0)):
            if not any(v.get(k) for k in ('this', 'last_month', 'last_year')): continue
            c_this = clicks_for(s, a, m0, end[s]) if w['this'] else None
            c_lm = clicks_for(s, a, *w['last_month']) if w['last_month'] and w['last_month'][0] >= UMAMI_FROM else None
            kids.append(dict(name=a, this=r2(v.get('this', 0)), last_month=r2(v.get('last_month')) if w['last_month'] else None,
                             last_year=r2(v.get('last_year')) if w['last_year'] and not (s == 'raptive' and w['last_year'][0] < rap_min) else None,
                             pending=r2(v.get('pending')), confirmed=r2(v.get('confirmed')), clicks=c_this, clicks_last_month=c_lm,
                             epc_earnings=r2(ce.get((s, a), 0)) if c_this is not None else None))
        def tot(k):
            vals = [x[k] for x in kids if x[k] is not None]
            if k == 'last_year' and (not w['last_year'] or (s == 'raptive' and w['last_year'][0] < rap_min)): return None
            if k == 'last_month' and not w['last_month']: return None
            return r2(sum(vals)) if vals else (0 if k in ('this', 'last_month', 'last_year') else None)
        row = dict(source=LABEL[s], this=tot('this') or 0, last_month=tot('last_month'), last_year=tot('last_year'),
                   pending=None if s == 'raptive' else tot('pending'), confirmed=None if s == 'raptive' else tot('confirmed'))
        if s == 'raptive':
            row['note'] = f"estimate until month close · to {end[s].day} {end[s].strftime('%b')}"
            if row['last_year'] is None: row['note'] += f" · no data before {rap_min.day} {rap_min.strftime('%b %Y')}"
        else:
            cl = [x['clicks'] for x in kids if x['clicks'] is not None]
            row['clicks'] = sum(cl) if cl else None
            cll = [x['clicks_last_month'] for x in kids if x['clicks_last_month'] is not None]
            row['clicks_last_month'] = sum(cll) if cll else None
            row['epc_earnings'] = r2(sum(x['epc_earnings'] or 0 for x in kids if x['clicks'] is not None))
            row['children'] = kids
            row['note'] = f"orders to {end[s].day} {end[s].strftime('%b')}"
        mtd.append(row)

    # ---- Amazon: weeks inside the month
    wk = q(f"""select coalesce(json_agg(x order by period_start),'[]') from (select period_start, period_end, tag, clicks c, earnings_usd e
              from rsg.revenue_period where source='amazon_us' and grain='week' and period_start >= {lit(pm0)}) x""")
    this_w = sorted({r['period_start'] for r in wk if dt.date.fromisoformat(r['period_start']) >= m0 and dt.date.fromisoformat(r['period_end']) <= yday})
    prev_w = sorted({r['period_start'] for r in wk if pm0 <= dt.date.fromisoformat(r['period_start']) < m0})[:len(this_w)]
    def amz(ws):
        t = {}
        for r in wk:
            if r['period_start'] in ws:
                a = t.setdefault(r['tag'], {'e': 0, 'c': 0}); a['e'] += r['e'] or 0; a['c'] += r['c'] or 0
        return t
    at, ap = amz(this_w), amz(prev_w)
    kids = []
    for tag in sorted(set(at) | set(ap), key=lambda k: -(at.get(k, {}).get('e', 0))):
        kids.append(dict(name=tag if tag != 'Other' else 'Other (hidden by Amazon)', this=r2(at.get(tag, {}).get('e', 0)),
                         last_month=r2(ap.get(tag, {}).get('e', 0)) if prev_w else None, last_year=None, pending=None, confirmed=None,
                         clicks=at.get(tag, {}).get('c', 0), clicks_last_month=ap.get(tag, {}).get('c', 0) if prev_w else None))
    a_end = max((dt.date.fromisoformat(r['period_end']) for r in wk if r['period_start'] in this_w), default=None)
    amz_row = dict(source='Amazon US', this=r2(sum(k['this'] for k in kids)), last_month=r2(sum(k['last_month'] or 0 for k in kids)) if prev_w else None,
                   last_year=None, pending=None, confirmed=None, clicks=sum(k['clicks'] for k in kids),
                   clicks_last_month=sum(k['clicks_last_month'] or 0 for k in kids) if prev_w else None, children=kids,
                   note=(f"{len(this_w)} weekly files, {fmt_range(dt.date.fromisoformat(this_w[0]), a_end)}; vs first {len(prev_w)} weeks of {pm0.strftime('%b')}" if this_w else "no weekly file for this month yet"))
    mtd.insert(1, amz_row)

    # ---- daily chart (day sources)
    days = [m0 + dt.timedelta(days=i) for i in range((month_add(m0, 1) - m0).days)]
    dd = q(f"""select coalesce(json_agg(x),'[]') from (select source, period_start d, sum(earnings_usd) e from rsg.revenue_period
              where grain='day' and source in ('raptive','cj','awin','rakuten','impact') and period_start >= {lit(m0)} and period_start < {lit(month_add(m0,1))} group by 1,2) x""")
    daily = {}
    for s in ['raptive'] + NETS:
        m = {r['d']: r['e'] for r in dd if r['source'] == s}
        daily[LABEL[s]] = [r2(m.get(d.isoformat(), 0)) if d <= end[s] else None for d in days]

    # ---- 13 months
    months = [month_add(ly0, i) for i in range(13)]
    mm = q(f"""select coalesce(json_agg(x),'[]') from (select source, to_char(period_start,'YYYY-MM') m, sum(earnings_usd) e from rsg.revenue_period
              where ((grain='day' and source <> 'amazon_us') or (grain='month' and source='amazon_us')) and period_start >= {lit(ly0)} group by 1,2) x""")
    monthly = {}
    for s in ORDER:
        m = {r['m']: r['e'] for r in mm if r['source'] == s}
        vals = []
        for mo in months:
            k = mo.strftime('%Y-%m')
            if s == 'raptive' and month_add(mo, 1) <= rap_min: vals.append(None); continue
            if s == 'amazon_us' and mo == m0: vals.append(amz_row['this']); continue
            vals.append(r2(m.get(k, 0)))
        monthly[LABEL[s]] = vals

    # ---- page types (month so far vs same days last month; Amazon by its weeks)
    def pt(win_day, weeks):
        if not win_day: return {}
        res = q(f"""select coalesce(json_agg(x),'[]') from (select page_group g, sum(earnings_usd) e from rsg.revenue_by_page_type
                  where ((grain='day' and period_start between {lit(win_day[0])} and {lit(win_day[1])})
                         or (grain='week' and source='amazon_us' and period_start in ({','.join(lit(dt.date.fromisoformat(w)) for w in weeks) or 'NULL'})))
                  group by 1) x""")
        return {r['g']: r['e'] or 0 for r in res}
    net_end = max(end[s] for s in NETS)
    pthis = pt((m0, net_end), this_w); plast = pt((pm0, clamp_day(pm0, net_end.day)), prev_w)
    page_types = [dict(type=PAGE_GROUP.get(g, g), this=r2(pthis.get(g, 0)), last_month=r2(plast.get(g, 0)))
                  for g in sorted(set(pthis) | set(plast), key=lambda g: -(pthis.get(g, 0)))]

    # ---- one merchant over time: last 13 weeks (Mon-Sun)
    w0 = yday - dt.timedelta(days=yday.weekday()) - dt.timedelta(weeks=12)
    weeks = [w0 + dt.timedelta(weeks=i) for i in range(13)]
    we = q(f"""select coalesce(json_agg(x),'[]') from (select source, account, date_trunc('week', period_start)::date w, sum(earnings_usd) e
              from rsg.revenue_period where grain='day' and source in ('cj','awin','rakuten','impact') and period_start >= {lit(w0)} group by 1,2,3) x""")
    merchants = {}
    for s in NETS:
        accts = sorted({r['account'] for r in we if r['source'] == s} | {a for (src, a) in CLICK_MAP.values() if src == s})
        for a in accts:
            e = {r['w']: r['e'] for r in we if r['source'] == s and r['account'] == a}
            cl = [clicks_for(s, a, w, w + dt.timedelta(days=6)) if w + dt.timedelta(days=6) >= UMAMI_FROM else None for w in weeks]
            ev = [r2(e.get(w.isoformat(), 0)) if w <= end[s] else None for w in weeks]
            if not any(ev) and not any(cl): continue
            merchants[f"{LABEL[s]} · {a}"] = dict(network=LABEL[s], name=a, weeks=[w.isoformat() for w in weeks], clicks=cl, earnings=ev)
    aw = q(f"""select coalesce(json_agg(x),'[]') from (select tag, period_start w, clicks c, earnings_usd e from rsg.revenue_period
              where source='amazon_us' and grain='week' and period_start >= {lit(w0)}) x""")
    for tag in sorted({r['tag'] for r in aw}):
        e = {r['w']: r for r in aw if r['tag'] == tag}
        name = tag if tag != 'Other' else 'Other (hidden by Amazon)'
        merchants[f"Amazon US · {name}"] = dict(network='Amazon US', name=name, weeks=[w.isoformat() for w in weeks],
            clicks=[(e[w.isoformat()]['c'] if w.isoformat() in e else None) for w in weeks],
            earnings=[(r2(e[w.isoformat()]['e']) if w.isoformat() in e else None) for w in weeks])
    # Amazon first in the picker, then networks
    merchants = dict(sorted(merchants.items(), key=lambda kv: (ORDER.index({v: k for k, v in LABEL.items()}[kv[1]['network']]), kv[0])))

    data = {
        'sample': False,
        'generated': dt.datetime.now(dt.timezone.utc).strftime('%Y-%m-%dT%H:%M:%SZ'),
        'currency': 'USD',
        'month': {'label': m0.strftime('%B %Y'), 'start': m0.isoformat(), 'through': net_end.isoformat(),
                  'days_in_month': (month_add(m0, 1) - m0).days,
                  'last_month_label': fmt_range(pm0, clamp_day(pm0, net_end.day)),
                  'last_year_label': fmt_range(ly0, clamp_day(ly0, net_end.day)),
                  'last_year_missing': [r['source'] for r in mtd if r['last_year'] is None and (r['this'] or 0) > 0]},
        'coverage': [{'source': 'Raptive', 'through': end['raptive'].isoformat(), 'note': f"from {rap_min.strftime('%-d %b %Y')}"},
                     {'source': 'Amazon US', 'through': (a_end or dt.date.fromisoformat(cov['amazon_week_end'])).isoformat(), 'note': 'weekly download'}]
                    + [{'source': LABEL[s], 'through': end[s].isoformat()} for s in NETS],
        'sources': [LABEL[s] for s in ORDER],
        'mtd': mtd,
        'daily': {'dates': [d.isoformat() for d in days], 'series': daily},
        'monthly': {'months': [m.strftime('%Y-%m') for m in months], 'series': monthly},
        'page_types': page_types,
        'merchants': merchants,
        'clicks_from': UMAMI_FROM.isoformat(),
        'epc_from': cw0.isoformat(),
    }
    js = json.dumps(data, separators=(',', ':')).replace('</', '<\\/')
    html = open(tpl).read()
    assert html.count('__DATA__') == 1
    open(out, 'w').write(html.replace('__DATA__', js))
    if json_out: open(json_out, 'w').write(json.dumps(data, indent=1))

if __name__ == '__main__':
    a = sys.argv[1:]
    j = a[a.index('--json') + 1] if '--json' in a else None
    main(a[0], a[1], j)
