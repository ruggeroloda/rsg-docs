from gsc import q
import pandas as pd
pd.set_option('display.width',250); pd.set_option('display.max_rows',500)
# search appearance by month
rows=[]
for m in pd.period_range('2025-08','2026-10',freq='M'):
    a=str(m.start_time.date()); b=str(m.end_time.date())
    for r in q(a,b,['searchAppearance']): rows.append((str(m),r['keys'][0],round(r['clicks']/m.days_in_month),round(r['impressions']/m.days_in_month),round(r['ctr']*100,2),round(r['position'],1)))
    t=q(a,b,[])[0]; rows.append((str(m),'ALL',round(t['clicks']/m.days_in_month),round(t['impressions']/m.days_in_month),round(t['ctr']*100,2),round(t['position'],1)))
d=pd.DataFrame(rows,columns=['m','app','clicks','imp','ctr','pos'])
print(d.pivot(index='m',columns='app',values='clicks').to_string())
print(d.pivot(index='m',columns='app',values='imp').to_string())
# daily around 2 dec
r=q('2025-11-24','2025-12-08',['date','searchAppearance'])
x=pd.DataFrame([(k['keys'][0],k['keys'][1],k['clicks'],k['impressions']) for k in r],columns=['d','app','c','i'])
print(x.pivot(index='d',columns='app',values='i').to_string())
