import pandas as pd, os
pd.set_option('display.width',260); pd.set_option('display.max_rows',900); pd.set_option('display.max_colwidth',85)
w=pd.read_pickle('wk.pkl')
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
wp=pd.read_csv(D+"wp_pages.csv.gz"); live=set(wp[wp.status=='publish'].url)
B={'A 2023 Aug-core/HCU/Oct-core':(('2023-07-31','2023-08-20'),('2023-10-23','2023-11-12')),
   'B 2024 Feb':(('2024-01-15','2024-02-04'),('2024-02-05','2024-02-25')),
   'C 2024 May-Oct':(('2024-04-08','2024-05-05'),('2024-09-30','2024-10-27')),
   'C1 2024 May step':(('2024-04-15','2024-05-05'),('2024-05-06','2024-05-26')),
   'D 2025 May-Jun':(('2025-04-07','2025-05-04'),('2025-06-09','2025-07-06')),
   'D2 2025 Jul bump':(('2025-06-09','2025-07-06'),('2025-07-07','2025-08-03')),
   'E 2025 Aug-Nov':(('2025-07-07','2025-08-03'),('2025-10-27','2025-11-23')),
   'F 2025 Dec':(('2025-10-27','2025-11-23'),('2026-01-05','2026-02-01')),
   'G 2026 May-Jun':(('2026-04-06','2026-05-03'),('2026-06-08','2026-07-05')),
   'H 2026 Sep-Oct':(('2026-08-03','2026-08-30'),('2026-10-12','2026-11-08'))}
for name,((a1,a2),(b1,b2)) in B.items():
    A=w[(w.wk>=a1)&(w.wk<=a2)]; Bf=w[(w.wk>=b1)&(w.wk<=b2)]
    na=A.wk.nunique(); nb=Bf.wk.nunique()
    a=A.groupby(['page','t']).s.sum()/na; b=Bf.groupby(['page','t']).s.sum()/nb
    d=pd.concat([a.rename('before'),b.rename('after')],axis=1).fillna(0).reset_index()
    d['diff']=d.after-d.before
    t=d.groupby('t')[['before','after']].sum(); t['chg%']=((t.after/t.before-1)*100).round(0); t['share_of_loss%']=((t.after-t.before)/(t.after-t.before).sum()*100).round(0)
    tot=d[['before','after']].sum()
    print(f"\n=== {name}: weekly {tot.before:.0f} -> {tot.after:.0f} ({(tot.after/tot.before-1)*100:.0f}%)")
    print(t.round(0).astype(int).T.to_string())
    big=d[d.before>=150]
    print(f"pages >=150/wk before: {len(big)}; lost>50%: {(big.after<big.before*0.5).sum()}; lost>90%: {(big.after<big.before*0.1).sum()}; gained: {(big.after>big.before).sum()}")
    x=d.sort_values('diff').head(14); x['live']=x.page.isin(live)
    print(x[['page','before','after','live']].round(0).to_string(index=False))
    g=d[d.before>=100].copy(); g['r']=g.after/g.before
    x=g.sort_values('r',ascending=False).head(6); print('held/gained:'); print(x[['page','before','after']].round(0).to_string(index=False))
