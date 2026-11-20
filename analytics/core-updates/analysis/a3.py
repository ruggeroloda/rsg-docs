import pandas as pd, numpy as np, os
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
pd.set_option('display.width',250); pd.set_option('display.max_rows',600)
g=pd.read_csv(D+"gsc_country_device_day.csv.gz",parse_dates=['day'])
print(g.country.unique(), g.device.unique(), g.day.min(), g.day.max())
g['pi']=g.position*g.impressions
def wk(df):
    w=df.groupby(pd.Grouper(key='day',freq='W-SUN')).agg(clicks=('clicks','sum'),imp=('impressions','sum'),pi=('pi','sum'))
    w['ctr']=(w.clicks/w.imp*100).round(2); w['pos']=(w.pi/w.imp).round(1); return w.drop(columns='pi')
print("ALL"); print(wk(g).to_string())
for c in ['usa','gbr']:
    for d in ['MOBILE','DESKTOP']:
        print(c,d); print(wk(g[(g.country==c)&(g.device==d)]).to_string())
