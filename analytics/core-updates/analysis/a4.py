import pandas as pd, numpy as np, os, re
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
pd.set_option('display.width',250); pd.set_option('display.max_rows',600); pd.set_option('display.max_columns',60)
w=pd.read_csv(D+"wp_pages.csv.gz")
print(w.post_type.value_counts().to_dict()); print(w.status.value_counts().to_dict())
w['pub']=pd.to_datetime(w.published,errors='coerce')
p=w[w.status=='publish'].copy()
p['q']=p.pub.dt.to_period('Q').astype(str)
t=p[p.pub>='2021-01-01'].pivot_table(index='q',columns='post_type',values='id',aggfunc='count').fillna(0).astype(int)
print(t)
p['y']=p.pub.dt.year
print(p.pivot_table(index='y',columns='post_type',values='id',aggfunc='count').fillna(0).astype(int))
r=p[(p.post_type.str.contains('review'))&(p.pub>='2022-01-01')]
print(r.pivot_table(index=r.pub.dt.to_period('Q').astype(str),columns='author',values='id',aggfunc='count').fillna(0).astype(int).to_string())
print(w.columns.tolist()); print(p.groupby('post_type')[['words_content','words_fields','images','links_affiliate']].median())
# channel check Feb 2024
ch=pd.read_csv(D+"ga4_channel_day.csv.gz",parse_dates=['day'])
x=ch[(ch.day>='2024-01-29')&(ch.day<='2024-02-18')].pivot_table(index='day',columns='channel',values='sessions').fillna(0).astype(int)
print(x[['Organic Search','Direct','Email','Referral','Organic Social','Unassigned']].to_string())
