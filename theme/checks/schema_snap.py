"""Snapshot decoded JSON-LD + og/twitter meta of URLs: python3 schema_snap.py out.json URL..."""
import sys, json, re, urllib.request, hashlib
out = {}
for u in sys.argv[2:]:
    html = urllib.request.urlopen(urllib.request.Request(u, headers={"User-Agent": "Mozilla/5.0 rsg-check"}), timeout=60).read().decode("utf-8", "replace")
    blocks = re.findall(r'<script type="application/ld\+json"[^>]*>(.*?)</script>', html, re.S)
    dec = []
    for b in blocks:
        try: dec.append(json.loads(b))
        except Exception as e: dec.append({"PARSE_ERROR": str(e)})
    meta = sorted(re.findall(r'<meta (?:property|name)="(?:og|twitter|article):[^>]*>', html))
    out[u] = {"jsonld": dec, "meta": meta, "nblocks": len(blocks)}
    print(len(blocks), hashlib.md5(json.dumps(dec, sort_keys=True).encode()).hexdigest()[:10], u)
json.dump(out, open(sys.argv[1], "w"), indent=1, sort_keys=True)
