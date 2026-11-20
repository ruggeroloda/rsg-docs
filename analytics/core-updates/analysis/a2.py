import pandas as pd, numpy as np, re, os
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
pd.set_option('display.width',250); pd.set_option('display.max_rows',500); pd.set_option('display.max_columns',50)
ch=pd.read_csv(D+"ga4_channel_day.csv.gz",parse_dates=['day'])
o=ch[ch.channel=='Organic Search'].set_index('day').sessions
# same-weekday 7d comparisons around breaks
for a,b in [('2023-08-14','2023-11-12'),('2024-01-22','2024-03-17'),('2024-04-15','2024-06-09'),('2025-05-05','2025-07-27'),('2025-11-24','2026-01-18'),('2026-05-11','2026-06-21'),('2026-10-05','2026-11-19')]:
    s=o[a:b]
    print(a,b); print(' '.join(f"{d.strftime('%m%d')}{'*' if d.weekday()==0 else ''}:{v}" for d,v in s.items()))
def ptype(p):
    if p in ('/',): return 'home'
    if p.startswith('(not set)'): return 'notset'
    if re.match(r'^/reviews/[^/]+/[^/]+-review[^/]*/$',p): return 'review'
    if re.match(r'^/reviews/',p): return 'listing'
    if re.match(r'^/review/',p): return 'oldreview'
    if re.match(r'^/\d{4}/\d\d/.*review',p): return 'oldreview'
    if p.startswith('/comparison/'): return 'comparison'
    if p.startswith('/guide/'): return 'guide'
    if p.startswith('/top/'): return 'top'
    if p.startswith('/gps/'): return 'gps'
    if re.match(r'^/\d{4}/\d\d/',p) or p.startswith('/content/'): return 'article'
    return 'other'
l=pd.read_csv(D+"ga4_landing_organic_page_month.csv.gz")
l['t']=l.page.map(ptype)
pv=l.pivot_table(index='month',columns='t',values='sessions',aggfunc='sum').fillna(0).astype(int)
pv['total']=pv.sum(axis=1)
print(pv)
print((pv.div(pv.total,axis=0)*100).round(1))
print(l[l.t=='other'].groupby('page').sessions.sum().sort_values(ascending=False).head(40))
l.to_pickle('l.pkl')
c=pd.read_csv(D+"ga4_organic_country_device_month.csv.gz")
top=c.groupby('country').sessions.sum().sort_values(ascending=False).head(8).index
c['c']=c.country.where(c.country.isin(top),'other')
print(c.pivot_table(index='month',columns='c',values='sessions',aggfunc='sum').astype(int))
print(c.pivot_table(index='month',columns='device',values='sessions',aggfunc='sum').fillna(0).astype(int))
us=c[c.country=='United States'].pivot_table(index='month',columns='device',values='sessions',aggfunc='sum')
print(us.fillna(0).astype(int))
