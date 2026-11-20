import json,os,sys
from google.oauth2 import service_account
from google.auth.transport.requests import AuthorizedSession
p=os.environ['HOME']+'/mnt/rsg/_private/gsc-service-account.json'
S=AuthorizedSession(service_account.Credentials.from_service_account_file(p,scopes=['https://www.googleapis.com/auth/webmasters.readonly']))
def q(start,end,dims,typ='web',filters=None,limit=25000):
    body={'startDate':start,'endDate':end,'dimensions':dims,'type':typ,'rowLimit':limit,'dataState':'final'}
    if filters: body['dimensionFilterGroups']=[{'filters':filters}]
    r=S.post('https://www.googleapis.com/webmasters/v3/sites/sc-domain%3Arunningshoesguru.com/searchAnalytics/query',json=body,timeout=60)
    r.raise_for_status(); return r.json().get('rows',[])
if __name__=='__main__':
    import pandas as pd
    pd.set_option('display.width',250); pd.set_option('display.max_rows',500)
    for a,b in [('2025-11-17','2025-11-23'),('2025-12-03','2025-12-09')]:
        for typ in ['web','discover','googleNews','image']:
            try:
                rows=q(a,b,['searchAppearance'],typ)
                print(a,typ,[(r['keys'][0],r['clicks'],r['impressions'],round(r['position'],1)) for r in rows])
                tot=q(a,b,[],typ); print('  total',[(r['clicks'],r['impressions'],round(r.get('position',0),1)) for r in tot])
            except Exception as e: print(typ,'err',str(e)[:100])
    for a,b in [('2025-11-17','2025-11-23'),('2025-12-03','2025-12-09')]:
        rows=q(a,b,['device']); print(a,[(r['keys'][0],r['clicks'],r['impressions'],round(r['position'],1)) for r in rows])
