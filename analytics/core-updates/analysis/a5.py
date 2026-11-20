import pandas as pd
pd.set_option('display.width',260); pd.set_option('display.max_rows',600); pd.set_option('display.max_columns',60)
w=pd.read_csv('wk_type.csv',parse_dates=['wk'])
pv=w.pivot_table(index='wk',columns='t',values='s',aggfunc='sum').fillna(0).astype(int)
pv['total']=pv.sum(axis=1)
pv=pv.iloc[1:-1]
print(pv.columns.tolist())
cols=[c for c in pv.columns if pv[c].max()>3000]
x=pv[cols]; x.index=x.index.strftime('%y-%m-%d')
print(x.to_string())
