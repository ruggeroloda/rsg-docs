import requests, re, html, json, time, sys
UA="Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"
B="https://www.runningshoesguru.com"
pages=sys.argv[1:]
def strip(x): return html.unescape(re.sub(r'<[^>]+>',' ',x))
for p in pages:
    u=p if p.startswith('http') else B+p
    t0=time.time()
    try: r=requests.get(u,headers={"User-Agent":UA},timeout=40,allow_redirects=True)
    except Exception as e: print(p,'ERR',e); continue
    dt=time.time()-t0; t=r.text
    open('gb/'+re.sub(r'[^a-z0-9]+','_',u.split('//')[1])[:80]+'.html','w').write(t)
    hist=[(h.status_code,h.headers.get('location')) for h in r.history]
    body=re.sub(r'<script.*?</script>|<style.*?</style>|<noscript.*?</noscript>','',t,flags=re.S)
    main=re.search(r'<main.*?</main>',body,flags=re.S); mtxt=strip(main.group(0)) if main else ''
    txt=strip(body)
    g=lambda pat: (re.search(pat,t,flags=re.S|re.I) or [None,None])[1]
    title=g(r'<title[^>]*>(.*?)</title>'); desc=g(r'<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']')
    canon=g(r'<link[^>]+rel=["\']canonical["\'][^>]+href=["\'](.*?)["\']'); robots=g(r'<meta[^>]+name=["\']robots["\'][^>]+content=["\'](.*?)["\']')
    h1=[strip(x).strip()[:80] for x in re.findall(r'<h1[^>]*>(.*?)</h1>',t,flags=re.S)]
    h2=[strip(x).strip()[:40] for x in re.findall(r'<h2[^>]*>(.*?)</h2>',t,flags=re.S)]
    lds=[]
    for b in re.findall(r'<script[^>]+application/ld\+json[^>]*>(.*?)</script>',t,flags=re.S):
        try:
            j=json.loads(b); 
            def walk(o):
                if isinstance(o,dict):
                    if '@type' in o: lds.append(o['@type'] if isinstance(o['@type'],str) else '/'.join(o['@type']))
                    for v in o.values(): walk(v)
                elif isinstance(o,list):
                    for v in o: walk(v)
            walk(j)
        except Exception as e: lds.append('INVALID_JSON')
    imgs=re.findall(r'<img [^>]*>',body); noalt=sum(1 for i in imgs if not re.search(r'alt=["\'][^"\']+["\']',i)); lazy=sum(1 for i in imgs if 'loading="lazy"' in i)
    links=re.findall(r'<a [^>]*href=["\'](.*?)["\']',body); internal=[l for l in links if l.startswith('/') or 'runningshoesguru.com' in l]; ext=[l for l in links if l.startswith('http') and 'runningshoesguru.com' not in l]
    aff=sum(1 for l in links if re.search(r'/go/|amzn|amazon\.|awin|rakuten|avantlink|impact|runningwarehouse|tag=',l)); nof=len(re.findall(r'rel=["\'][^"\']*(sponsored|nofollow)',body))
    scripts=re.findall(r'<script[^>]*src=["\'](.*?)["\']',t); third=sorted(set(re.sub(r'^https?://','',s).split('/')[0] for s in scripts if s.startswith('http') and 'runningshoesguru' not in s))
    print(f"\n=== {p}  [{r.status_code}] {dt:.2f}s {len(t)//1024}KB hist={hist}")
    print(' title:',title and html.unescape(title.strip())[:110]); print(' desc:',desc and html.unescape(desc)[:170], f'({len(desc) if desc else 0})')
    print(' canonical:',canon,'| robots:',robots,'| x-robots:',r.headers.get('x-robots-tag'),'| cache:',r.headers.get('cf-cache-status') or r.headers.get('x-cache'),'| lastmod hdr:',r.headers.get('last-modified'))
    print(' h1:',h1,'| h2 n=',len(h2),h2[:14])
    print(' words total',len(txt.split()),'main',len(mtxt.split()),'| imgs',len(imgs),'noalt',noalt,'lazy',lazy,'| links int',len(internal),'ext',len(ext),'aff-like',aff,'rel sponsored/nofollow',nof)
    print(' ld:',sorted(set(lds)))
    print(' dates:',sorted(set(re.findall(r'"date(?:Published|Modified)"\s*:\s*"([^"]{10})',t))),'| og:updated',g(r'article:modified_time["\'][^>]+content=["\'](.*?)["\']'))
    print(' 3rd-party script hosts:',third[:14])
