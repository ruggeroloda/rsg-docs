import pandas as pd, os, numpy as np
pd.set_option('display.width',260); pd.set_option('display.max_rows',900)
w=pd.read_pickle('wk.pkl')
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
wp=pd.read_csv(D+"wp_pages.csv.gz"); wp=wp[wp.status=='publish']
wp['pub']=pd.to_datetime(wp.published)
for kind,types in [('reviews',['reviews_road','reviews_trail']),('comparisons',['comparison'])]:
    r=wp[wp.post_type.isin(types)&(wp.pub>='2023-02-15')&(wp.pub<='2026-07-31')][['url','pub']]
    m=w.merge(r,left_on='page',right_on='url')
    m['age']=((m.wk-m.pub).dt.days//7)
    m['q']=m.pub.dt.to_period('Q').astype(str)
    def agg(lo,hi):
        x=m[(m.age>=lo)&(m.age<hi)].groupby(['q','url']).s.sum().reset_index()
        allp=r.assign(q=r.pub.dt.to_period('Q').astype(str))[['q','url']].merge(x,how='left').fillna({'s':0})
        return allp.groupby('q').s.agg(['count','median','mean'])
    a=agg(0,13); b=agg(13,26); c=agg(26,52)
    out=pd.concat([a.add_suffix('_w0-12'),b[['median','mean']].add_suffix('_w13-25'),c[['median','mean']].add_suffix('_w26-51')],axis=1).round(0)
    print(kind); print(out.to_string())
# share of review landings by review age
rv=wp[wp.post_type.isin(['reviews_road','reviews_trail','review','post'])][['url','pub']]
m=w[w.t.isin(['review','oldreview'])].merge(rv,left_on='page',right_on='url',how='left')
m['agey']=((m.wk-m.pub).dt.days/365.25)
m['b']=pd.cut(m.agey,[-1,0.5,1,2,3,30],labels=['<6m','6-12m','1-2y','2-3y','3y+']).astype(str)
m['h']=m.wk.dt.year.astype(str)+'H'+((m.wk.dt.month-1)//6+1).astype(str)
p=m.pivot_table(index='h',columns='b',values='s',aggfunc='sum').fillna(0)
n=m.groupby('h').wk.nunique()
print((p.div(n,axis=0)).round(0).astype(int).to_string())
