from gsc import q
import pandas as pd
pd.set_option('display.width',250); pd.set_option('display.max_rows',500)
months=pd.period_range('2025-08','2026-10',freq='M')
rows=[]
for m in months:
    a=str(m.start_time.date()); b=str(m.end_time.date())
    for name,f in [('mob_all',[{'dimension':'device','operator':'equals','expression':'MOBILE'}]),
                   ('mob_review_q',[{'dimension':'device','operator':'equals','expression':'MOBILE'},{'dimension':'query','operator':'contains','expression':'review'}]),
                   ('mob_vs_q',[{'dimension':'device','operator':'equals','expression':'MOBILE'},{'dimension':'query','operator':'contains','expression':' vs '}]),
                   ('mob_top3',None)]:
        if name=='mob_top3': continue
        r=q(a,b,[],filters=f)
        if r: rows.append((str(m),name,r[0]['clicks']/m.days_in_month,r[0]['impressions']/m.days_in_month,r[0]['ctr']*100,r[0]['position']))
d=pd.DataFrame(rows,columns=['m','seg','clicks_day','imp_day','ctr','pos'])
for s in d.seg.unique():
    print(s); print(d[d.seg==s].drop(columns='seg').round(1).to_string(index=False))
