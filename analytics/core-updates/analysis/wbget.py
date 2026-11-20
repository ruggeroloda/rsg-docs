# usage: wbget.py <dir> <page> [months...] ; fetch raw archived html for a page's monthly snapshots
import pandas as pd, requests, os, sys, time
from concurrent.futures import ThreadPoolExecutor
D=os.environ['HOME']+"/mnt/rsg/analytics/core-updates/data/"
s=pd.read_csv(D+"wayback_rsg_snapshots.csv.gz",dtype=str)
out,page=sys.argv[1],sys.argv[2]; months=set(sys.argv[3:])
h=s[s.page==page]; 
if months: h=h[h.month.isin(months)]
os.makedirs(out,exist_ok=True)
def get(r):
    f=f"{out}/{r.month}.html"
    if os.path.exists(f): return
    for a in range(2):
        try:
            t=requests.get(r.wayback_url.replace(r.timestamp+'/',r.timestamp+'id_/'),timeout=45,headers={"User-Agent":"rsg-research/1.0 (site owner)"})
            if t.status_code==200: open(f,'w').write(t.text); return
            time.sleep(3)
        except Exception as e: time.sleep(2)
    print('fail',r.month)
with ThreadPoolExecutor(3) as ex: list(ex.map(get,[r for _,r in h.iterrows()]))
