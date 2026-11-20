import pandas as pd, numpy as np, os
pd.set_option('display.width',220); pd.set_option('display.max_rows',200)
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
l=pd.read_csv(D+"ga4_landing_organic_page_month.csv.gz")
wp=pd.read_csv(D+"wp_pages.csv.gz"); wp=wp[wp.status=='publish']; wp['pub']=pd.to_datetime(wp.published)
for kind,types in [('review',['reviews_road','reviews_trail']),('comparison',['comparison'])]:
    r=wp[wp.post_type.isin(types)&(wp.pub>='2024-07-01')][['url','pub','title']]
    for mth in ['2026-09','2026-10']:
        x=l[l.month==mth][['page','sessions']]
        m=r.merge(x,left_on='url',right_on='page',how='left').fillna({'sessions':0})
        m['age']=((pd.Timestamp(mth+'-15')-m.pub).dt.days/30.4)
        m=m[m.age>=0.5]
        m['b']=pd.cut(m.age,[0,3,6,9,12,18,28],labels=['1-3','4-6','7-9','10-12','13-18','19-28'])
        g=m.groupby('b',observed=True).sessions.agg(['count','median','mean',lambda s:s.quantile(.75),'max']).round(0)
        print(kind,mth); print(g.to_string())
    if kind=='review':
        print(m.sort_values('sessions',ascending=False)[['url','pub','sessions']].head(15).to_string())
# how many comparisons per review historically (2024-07+)
print('reviews since 2024-07',(wp.post_type.isin(['reviews_road','reviews_trail'])&(wp.pub>='2024-07-01')).sum(),'comparisons',((wp.post_type=='comparison')&(wp.pub>='2024-07-01')).sum())
# first-3-month take by launch timing unavailable; trend of existing stock: pages published before 2025-11, sessions Jun..Oct 2026
old=set(wp[wp.pub<'2026-01-01'].url)
t=l[l.page.isin(old)&(l.month>='2026-03')].groupby('month').sessions.sum(); print(t.to_string()); print((t.pct_change()*100).round(1).to_string())
