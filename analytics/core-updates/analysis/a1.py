import pandas as pd, numpy as np
D="/".join([__import__('os').environ['HOME'],"mnt/rsg/analytics/core-updates/data/"])
ch=pd.read_csv(D+"ga4_channel_day.csv.gz",parse_dates=['day'])
print(ch.groupby('channel').sessions.sum().sort_values(ascending=False))
o=ch[ch.channel=='Organic Search'].set_index('day').sessions
m=o.resample('MS').sum()
days=o.resample('MS').count()
t=pd.DataFrame({'org':m,'days':days,'perday':(m/days).round(0)})
t['yoy']=(t.perday/t.perday.shift(12)-1).round(2)
t['mom']=(t.perday/t.perday.shift(1)-1).round(2)
allm=ch.groupby(pd.Grouper(key='day',freq='MS')).sessions.sum()
t['all']=allm
pd.set_option('display.width',200); pd.set_option('display.max_rows',500)
print(t)
# weekly
w=o.resample('W-SUN').sum()
w=w[(w.index>'2023-02-10')]
ww=pd.DataFrame({'w':w,'chg4':(w/w.shift(4)-1).round(2)})
print(ww.to_string())
