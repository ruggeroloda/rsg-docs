import pandas as pd, numpy as np, os, re
pd.set_option('display.width',260); pd.set_option('display.max_rows',900); pd.set_option('display.max_colwidth',70)
w=pd.read_pickle('wk.pkl')
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
wp=pd.read_csv(D+"wp_pages.csv.gz"); wp=wp[wp.status=='publish'].drop_duplicates('url').set_index('url')
wp['pub']=pd.to_datetime(wp.published)
B={'A1 Aug core (wk 14-27Aug vs 11-24Sep)':(('2023-08-14','2023-08-21'),('2023-09-11','2023-09-18')),
   'A2 HCU (11Sep wk vs 2Oct wk)':(('2023-09-04','2023-09-11'),('2023-09-25','2023-10-02')),
   'A3 Oct core (2Oct wk vs 23Oct-6Nov)':(('2023-09-25','2023-10-02'),('2023-10-23','2023-10-30')),
   'A all':(('2023-07-31','2023-08-20'),('2023-10-23','2023-11-12')),
   'B Feb24':(('2024-01-15','2024-01-29'),('2024-02-05','2024-02-19')),
   'C1 May24 step':(('2024-04-15','2024-04-29'),('2024-05-06','2024-05-20')),
   'C all':(('2024-04-08','2024-05-05'),('2024-09-30','2024-10-27')),
   'D':(('2025-04-07','2025-05-04'),('2025-06-09','2025-07-06')),
   'E':(('2025-07-07','2025-08-03'),('2025-10-27','2025-11-23')),
   'F':(('2025-10-27','2025-11-23'),('2026-01-05','2026-02-01')),
   'G':(('2026-04-06','2026-05-03'),('2026-06-08','2026-07-05')),
   'H':(('2026-08-03','2026-08-30'),('2026-10-12','2026-11-08'))}
rows=[]
for name,((a1,a2),(b1,b2)) in B.items():
    A=w[(w.wk>=a1)&(w.wk<=a2)]; Bf=w[(w.wk>=b1)&(w.wk<=b2)]
    a=A.groupby(['page','t']).s.sum()/A.wk.nunique(); b=Bf.groupby(['page','t']).s.sum()/Bf.wk.nunique()
    d=pd.concat([a.rename('before'),b.rename('after')],axis=1).fillna(0).reset_index()
    d=d[(d.before>=80)&(~d.t.isin(['notset']))]
    d['r']=d.after/d.before
    d['pub']=d.page.map(wp.pub); d['age']=(pd.Timestamp(a1)-d['pub']).dt.days/365.25
    d['ageb']=pd.cut(d.age,[-1,0.25,0.5,1,2,3,30],labels=['<3m','3-6m','6-12m','1-2y','2-3y','3y+'])
    q=d.r.quantile([.1,.25,.5,.75,.9]).round(2).tolist()
    print(f"\n== {name}: n={len(d)} pages>=80/wk; ratio p10/25/50/75/90 = {q}; weighted {d.after.sum()/d.before.sum():.2f}")
    g=d.groupby('t').agg(n=('r','size'),med=('r','median'),w=('after','sum'),b=('before','sum')); g['wr']=(g.w/g.b).round(2); print(g[g.n>=5][['n','med','wr']].round(2).T.to_string())
    rv=d[d.t.isin(['review','oldreview'])]
    g=rv.groupby('ageb',observed=True).agg(n=('r','size'),med=('r','median'),w=('after','sum'),b=('before','sum')); g['wr']=(g.w/g.b).round(2); print('reviews by age at start:'); print(g[['n','med','wr']].round(2).T.to_string())
    if name=='A all': d.to_pickle('A.pkl')
