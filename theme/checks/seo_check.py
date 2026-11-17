"""SEO + structured-data check per URL (edge copy = what crawlers get).
python3 seo_check.py out.json URL...   -> prints a table, writes full detail."""
import sys, json, re, urllib.request, urllib.error, html as H
from html.parser import HTMLParser
UA = {"User-Agent": "Mozilla/5.0 (rsg-seo-check)"}
class NoRedir(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k): return None
OP = urllib.request.build_opener(NoRedir)
def fetch(u):
    try:
        r = OP.open(urllib.request.Request(u, headers=UA), timeout=60)
        return r.status, dict(r.headers), r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, dict(e.headers), e.read().decode("utf-8", "replace")
class P(HTMLParser):
    def __init__(s):
        super().__init__(); s.meta=[]; s.links=[]; s.title=None; s._t=False; s.ld=[]; s._ld=None; s.h1=0
    def handle_starttag(s, t, a):
        a = dict(a)
        if t == "meta": s.meta.append(a)
        elif t == "link": s.links.append(a)
        elif t == "title" and s.title is None: s._t=True; s.title=""
        elif t == "script" and a.get("type") == "application/ld+json": s._ld=""
        elif t == "h1": s.h1 += 1
    def handle_endtag(s, t):
        if t == "title": s._t=False
        if t == "script" and s._ld is not None: s.ld.append(s._ld); s._ld=None
    def handle_data(s, d):
        if s._t: s.title += d
        if s._ld is not None: s._ld += d
def types(o, acc):
    if isinstance(o, dict):
        t = o.get("@type")
        if t: acc.append(t if isinstance(t, str) else "+".join(t))
        for v in o.values(): types(v, acc)
    elif isinstance(o, list):
        for v in o: types(v, acc)
    return acc
def offers(o, acc):
    if isinstance(o, dict):
        if o.get("@type") in ("AggregateOffer", "Offer"): acc.append({k: o.get(k) for k in ("@type","priceCurrency","lowPrice","highPrice","price","offerCount")})
        for v in o.values(): offers(v, acc)
    elif isinstance(o, list):
        for v in o: offers(v, acc)
    return acc
def check(u):
    st, hd, body = fetch(u)
    p = P(); p.feed(body)
    m = {}
    for a in p.meta:
        k = a.get("property") or a.get("name")
        if k: m.setdefault(k, []).append(a.get("content"))
    canon = [l.get("href") for l in p.links if "canonical" in (l.get("rel") or "").split()]
    ld, errs = [], []
    for b in p.ld:
        try: ld.append(json.loads(b))
        except Exception as e: errs.append(str(e))
    return {"url": u, "status": st, "location": hd.get("location") or hd.get("Location"),
        "x_robots": hd.get("x-robots-tag"), "cache": hd.get("cf-cache-status"), "age": hd.get("age"),
        "title": H.unescape((p.title or "").strip()), "description": m.get("description"),
        "robots": m.get("robots"), "canonical": canon, "h1": p.h1,
        "og": {k: v for k, v in m.items() if k.startswith(("og:", "article:"))},
        "twitter": {k: v for k, v in m.items() if k.startswith("twitter:")},
        "ld_blocks": len(p.ld), "ld_errors": errs, "ld_types": types(ld, []), "offers": offers(ld, []), "jsonld": ld}
if __name__ == "__main__":
    res = [check(u) for u in sys.argv[2:]]
    json.dump(res, open(sys.argv[1], "w"), indent=1, sort_keys=True)
    for r in res:
        d = (r["description"] or [""])[0] or ""
        print(f'{r["status"]} {r["cache"]} | canon={"self" if r["canonical"]==[r["url"]] else r["canonical"]} | robots={r["robots"]} | xr={r["x_robots"]} | h1={r["h1"]} | title={len(r["title"])}c desc={len(d)}c | og={len(r["og"])} tw={len(r["twitter"])} | ld={r["ld_blocks"]} err={len(r["ld_errors"])} {sorted(set(r["ld_types"]))} | offers={r["offers"]} | {r["url"]}')
