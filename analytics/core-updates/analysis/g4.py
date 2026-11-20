from gsc import q
import pandas as pd, re, pickle
pd.set_option('display.width',250); pd.set_option('display.max_rows',500)
F=[{'dimension':'device','operator':'equals','expression':'MOBILE'}]
def cls(s):
    if re.search(r'running ?shoes? ?guru|runningshoesguru|rsg',s): return 'brand'
    if ' vs ' in s or ' vs. ' in s or ' versus ' in s or ' or ' in s: return 'vs'
    if 'review' in s: return 'review'
    if re.search(r'\bbest\b|\btop\b',s): return 'best'
    return 'other'
out={}
for lab,a,b in [('Oct25','2025-10-01','2025-10-31'),('Oct26','2026-10-01','2026-10-31')]:
    rows=q(a,b,['query'],filters=F); tot=q(a,b,[],filters=F)[0]
    d=pd.DataFrame([(r['keys'][0],r['clicks'],r['impressions'],r['position']) for r in rows],columns=['q','c','i','p'])
    out[lab]=d
    d['cls']=d.q.map(cls); d['pb']=pd.cut(d.p,[0,3,5,10,20,200],labels=['1-3','3-5','5-10','10-20','20+'])
    print(lab,'rows',len(d),'query-level clicks',d.c.sum(),'of total',tot['clicks'],'imp',d.i.sum(),'of',tot['impressions'])
    g=d.groupby('cls').agg(clicks=('c','sum'),imp=('i','sum')); g['ctr']=(g.clicks/g.imp*100).round(1); g['pos']=d.assign(pi=d.p*d.i).groupby('cls').pi.sum()/g.imp; print(g.round(1))
    g=d[d.cls!='brand'].groupby(['cls','pb'],observed=True).agg(clicks=('c','sum'),imp=('i','sum'),n=('q','size')); g['ctr']=(g.clicks/g.imp*100).round(1); print(g)
pickle.dump(out,open('gq.pkl','wb'))
d=out['Oct26']; print(d.sort_values('i',ascending=False).head(40).to_string())
