import pandas as pd, numpy as np, os
pd.set_option('display.width',260); pd.set_option('display.max_rows',900)
w=pd.read_pickle('wk.pkl'); w=w[w.t!='notset']
weeks=pd.date_range(w.wk.min(),w.wk.max(),freq='7D')
big=w.groupby('page').s.max(); pages=big[big>=60].index
x=w[w.page.isin(pages)].pivot_table(index='wk',columns='page',values='s',aggfunc='sum').reindex(weeks).fillna(0)
med=x.rolling(9,center=True,min_periods=4).median()
# baseline = min(actual, 1.5*median + 30); burst = rest
base=np.minimum(x, med*1.5+30); burst=(x-base).clip(lower=0)
tmap=w.drop_duplicates('page').set_index('page').t
out=pd.DataFrame({'total':x.sum(1),'burst':burst.sum(1)})
for t in ['review','oldreview','article','comparison','guide']:
    cols=[c for c in x.columns if tmap[c]==t]
    out[t+'_base']=base[cols].sum(1); out[t+'_burst']=burst[cols].sum(1)
out['burst%']=(out.burst/out.total*100).round(0)
out['base']=out.total-out.burst
m=out.resample('MS').mean().round(0).astype(int)
print(m.to_string())
out.to_pickle('burst.pkl')
