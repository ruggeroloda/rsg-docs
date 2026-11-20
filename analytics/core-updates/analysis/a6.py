import pandas as pd, re, sys
pd.set_option('display.width',260); pd.set_option('display.max_rows',900); pd.set_option('display.max_columns',60); pd.set_option('display.max_colwidth',90)
w=pd.read_csv('wk_page.csv',parse_dates=['wk'])
def norm(p):
    p=str(p).split('?')[0]
    return p if p.endswith('/') else p+'/'
w['page']=w.page.map(norm)
def ptype(p):
    if p=='/': return 'home'
    if p.startswith('(not set)'): return 'notset'
    if re.match(r'^/reviews/[^/]+/[^/]+-review[^/]*/$',p): return 'review'
    if re.match(r'^/reviews/',p): return 'listing'
    if re.match(r'^/review/',p) or re.match(r'^/\d{4}/\d\d/.*review',p): return 'oldreview'
    if p.startswith('/comparison/'): return 'comparison'
    if p.startswith('/guide/'): return 'guide'
    if p.startswith('/top/'): return 'top'
    if p.startswith('/gps/'): return 'gps'
    if p.startswith('/overview/'): return 'overview'
    if re.match(r'^/\d{4}/\d\d/',p) or p.startswith('/content/'): return 'article'
    return 'other'
w['t']=w.page.map(ptype)
w=w.groupby(['wk','page','t'],as_index=False)[['s','e','k']].sum()
w.to_pickle('wk.pkl')
pv=w.pivot_table(index='wk',columns='t',values='s',aggfunc='sum').fillna(0).astype(int).iloc[1:-1]
pv['total']=pv.sum(axis=1)
pv.index=pv.index.strftime('%y-%m-%d')
print(pv.to_string())
