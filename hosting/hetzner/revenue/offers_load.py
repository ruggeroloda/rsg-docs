#!/usr/bin/env python3
"""Active offers (promotions, coupons) from joined advertisers on Awin, Rakuten, CJ, Impact and Commission Factory -> SQL on stdout
(pipe into psql as umami). Stdlib only. Upsert into rsg.offer by (source, offer_id): first_seen stays,
last_seen = this run; nothing is deleted (an offer missing from today's pull keeps its old last_seen).
A network that fails is skipped (its offers keep their old last_seen); the others still load.

env: AWIN_PUBLISHER_ID, AWIN_TOKEN, IMPACT_ACCOUNT_SID, IMPACT_AUTH_TOKEN, RAKUTEN_SID, RAKUTEN_CLIENT_ID, RAKUTEN_SECRET, CJ_TOKEN, CJ_WEBSITE_ID,
     CF_API_KEY (Commission Factory, 27 Nov 2026; network skipped when the key is not set)
"""
import os, re, sys, json, time, base64, hashlib, urllib.request, urllib.parse, xml.etree.ElementTree as ET

def http(url, headers, data=None, method=None):
    req = urllib.request.Request(url, data=data, headers=headers, method=method)
    for attempt in range(3):
        try:
            return urllib.request.urlopen(req, timeout=90).read()
        except Exception:
            if attempt == 2: raise
            time.sleep(10 * (attempt + 1))

def awin():
    pub, tok = os.environ['AWIN_PUBLISHER_ID'], os.environ['AWIN_TOKEN']
    out, page = [], 1
    while True:
        body = json.dumps({'filters': {'membership': 'joined', 'status': 'active'}, 'pagination': {'page': page, 'pageSize': 100}}).encode()
        d = json.loads(http(f'https://api.awin.com/publisher/{pub}/promotions/', {'Authorization': 'Bearer ' + tok, 'Content-Type': 'application/json'}, body, 'POST'))
        for p in d['data']:
            v = p.get('voucher') or {}
            reg = p.get('regions') or {}
            out.append(dict(offer_id=str(p['promotionId']), advertiser=p['advertiser']['name'], title=p.get('title'),
                description=p.get('description'), code=v.get('code'), type=p.get('type'), start_at=p.get('startDate'),
                end_at=p.get('endDate'), regions='all' if reg.get('all') else ','.join(r.get('countryCode', '') for r in reg.get('list', [])),
                url=p.get('urlTracking') or p.get('url'), terms=p.get('terms')))
        if page * 100 >= d['pagination']['total']: return out
        page += 1; time.sleep(3.2)

def rakuten():
    key = base64.b64encode(f"{os.environ['RAKUTEN_CLIENT_ID']}:{os.environ['RAKUTEN_SECRET']}".encode()).decode()
    tok = json.loads(http('https://api.linksynergy.com/token', {'Authorization': 'Bearer ' + key, 'Content-Type': 'application/x-www-form-urlencoded'},
                          f"grant_type=client_credentials&scope={os.environ['RAKUTEN_SID']}".encode(), 'POST'))['access_token']
    out, page = [], 1
    while True:
        r = ET.fromstring(http(f'https://api.linksynergy.com/coupon/1.0?network=1&resultsperpage=500&pagenumber={page}', {'Authorization': 'Bearer ' + tok}))
        for l in r.findall('link'):
            f = lambda k: (l.findtext(k) or '').strip() or None
            oid = hashlib.md5('|'.join(str(x) for x in (f('advertiserid'), f('offerdescription'), f('couponcode'), f('offerstartdate'))).encode()).hexdigest()
            out.append(dict(offer_id=oid, advertiser=f('advertisername'), title=f('offerdescription'), description=f('couponrestriction'),
                code=f('couponcode'), type=', '.join(t.text for t in l.findall('promotiontypes/promotiontype') if t.text),
                start_at=f('offerstartdate'), end_at=f('offerenddate'), regions=None, url=f('clickurl'), terms=f('couponrestriction')))
        if page >= int(r.findtext('TotalPages') or 1): return out
        page += 1

def cj():
    out = []
    for ptype in ('coupon', 'sale/discount', 'free shipping'):
        page = 1
        while True:
            qs = urllib.parse.urlencode({'website-id': os.environ['CJ_WEBSITE_ID'], 'advertiser-ids': 'joined', 'promotion-type': ptype,
                                         'records-per-page': 100, 'page-number': page})
            r = ET.fromstring(http('https://link-search.api.cj.com/v2/link-search?' + qs, {'Authorization': 'Bearer ' + os.environ['CJ_TOKEN']}))
            ls = r.find('links')
            for l in ls.findall('link'):
                f = lambda k: (l.findtext(k) or '').strip() or None
                if not f('promotion-start-date') and not f('coupon-code'): continue   # evergreen text/banner links, not offers
                out.append(dict(offer_id=f('link-id'), advertiser=f('advertiser-name'), title=f('link-name'), description=f('description'),
                    code=f('coupon-code'), type=f('promotion-type'), start_at=f('promotion-start-date'), end_at=f('promotion-end-date'),
                    regions=None, url=f('clickUrl'), terms=None))
            if page * 100 >= int(ls.get('total-matched') or 0): break
            page += 1
    # 24 Nov 2026: some advertisers (Nike US) publish offers as plain text links: no promotion type, no start date,
    # code only in the title. Keep untyped links that have an end date or a "code XXX" in the title; code read from the title.
    seen, page = {o['offer_id'] for o in out}, 1
    while True:
        qs = urllib.parse.urlencode({'website-id': os.environ['CJ_WEBSITE_ID'], 'advertiser-ids': 'joined',
                                     'records-per-page': 100, 'page-number': page})
        r = ET.fromstring(http('https://link-search.api.cj.com/v2/link-search?' + qs, {'Authorization': 'Bearer ' + os.environ['CJ_TOKEN']}))
        ls = r.find('links')
        for l in ls.findall('link'):
            f = lambda k: (l.findtext(k) or '').strip() or None
            if f('link-id') in seen: continue
            m = re.search(r'\bcode[:\s]+([A-Z0-9]{3,20})\b', f('link-name') or '', re.I)
            if not f('promotion-end-date') and not m: continue   # evergreen links, not offers
            seen.add(f('link-id'))
            out.append(dict(offer_id=f('link-id'), advertiser=f('advertiser-name'), title=f('link-name'), description=f('description'),
                code=f('coupon-code') or (m.group(1).upper() if m else None), type=f('promotion-type') or 'text link',
                start_at=f('promotion-start-date'), end_at=f('promotion-end-date'), regions=None, url=f('clickUrl'), terms=None))
        if page * 100 >= int(ls.get('total-matched') or 0): break
        page += 1
    return out

def impact():
    sid, tok = os.environ['IMPACT_ACCOUNT_SID'], os.environ['IMPACT_AUTH_TOKEN']
    hdr = {'Accept': 'application/json', 'Authorization': 'Basic ' + base64.b64encode(f'{sid}:{tok}'.encode()).decode()}
    api = f'https://api.impact.com/Mediapartners/{sid}/'
    out = []
    for c in json.loads(http(api + 'Campaigns?PageSize=200', hdr))['Campaigns']:
        if c.get('ContractStatus') != 'Active': continue
        for d in json.loads(http(api + f"Campaigns/{c['CampaignId']}/Deals?PageSize=500", hdr)).get('Deals', []):
            if d.get('State') != 'ACTIVE': continue
            # Impact deals often have no start date: use the creation date, so old "evergreen" deals age out of the deals view
            out.append(dict(offer_id=str(d['Id']), advertiser=c['CampaignName'], title=d.get('Name'), description=d.get('Description'),
                code=d.get('DefaultPromoCode'), type=d.get('Type'), start_at=d.get('StartDate') or d.get('DateCreated'),
                end_at=d.get('EndDate'), regions=None, url=c.get('TrackingLink'), terms=None))
    return out

def cf():
    """Commission Factory (27 Nov 2026): promotions and coupons of joined merchants. Market = AU (the merchant name has no country)."""
    key = os.environ.get('CF_API_KEY')
    if not key: raise RuntimeError('CF_API_KEY not set')
    api = 'https://api.commissionfactory.com/V1/Affiliate/'
    out = []
    for kind in ('Promotions', 'Coupons'):
        for p in json.loads(http(api + kind + '?apiKey=' + urllib.parse.quote(key), {'Accept': 'application/json'})):
            out.append(dict(offer_id=f"{kind[0].lower()}{p['Id']}", advertiser=(p.get('MerchantName') or '') + ' AU',
                title=p.get('Description') or p.get('Name'), description=p.get('Description'), code=p.get('Code') or p.get('CouponCode'),
                type=kind[:-1].lower(), start_at=p.get('StartDate') or p.get('DateCreated'), end_at=p.get('EndDate'), regions='AU',
                url=p.get('TrackingUrl'), terms=p.get('TermsAndConditions')))
    return out

def q(v):
    return 'NULL' if v in (None, '') else "'" + str(v).replace("'", "''") + "'"

def main():
    sql = ['BEGIN;']
    for name, fn in (('awin', awin), ('rakuten', rakuten), ('cj', cj), ('impact', impact), ('cf', cf)):
        try:
            offers = fn()
        except Exception as ex:
            print(f'{name}: FAILED, skipped ({ex})', file=sys.stderr); continue
        for o in offers:
            cols = ['offer_id', 'advertiser', 'title', 'description', 'code', 'type', 'start_at', 'end_at', 'regions', 'url', 'terms']
            sql.append(f"INSERT INTO rsg.offer (source,{','.join(cols)}) VALUES ('{name}'," + ','.join(q(o[c]) for c in cols) +
                       ') ON CONFLICT (source, offer_id) DO UPDATE SET ' + ','.join(f'{c}=EXCLUDED.{c}' for c in cols[1:]) + ', last_seen=now();')
        sql.append(f"INSERT INTO rsg.revenue_load_log (source, records) VALUES ('offers_{name}', {len(offers)});")
        print(f'{name}: {len(offers)} offers', file=sys.stderr)
    sql.append('COMMIT;'); print('\n'.join(sql))

if __name__ == '__main__':
    main()
