import asyncio, re, os, hashlib, time, io, sys, urllib.request
from playwright.async_api import async_playwright
from PIL import Image
BLOCK = re.compile(r"adthrive|doubleclick|googlesyndication|google-analytics|googletagmanager|youtube|ytimg|amazon-adsystem|criteo|pubmatic|rubicon|3lift|openx|adnxs|adsrvr|gumgum|yahoo|smartadserver|adsafeprotected|typekit|t\.runningshoesguru")
URL="https://www.runningshoesguru.com/reviews/road/nike-pegasus-42-review/"
OUT=sys.argv[1]; VAR=sys.argv[2]; os.makedirs(OUT+'/assets',exist_ok=True)
def fetch(u):
    for k in range(3):
        try:
            r=urllib.request.urlopen(urllib.request.Request(u,headers={'User-Agent':'Mozilla/5.0'}),timeout=40); return r.read()
        except Exception as e: err=e; time.sleep(3)
    print('FAIL',u,err); return None
cache={}
def local(u, img=False):
    base=u.split('#')[0]
    if base in cache: return cache[base]
    import glob
    hit=glob.glob(f"{OUT}/assets/{hashlib.md5(base.encode()).hexdigest()[:10]}.*")
    if hit: cache[base]='assets/'+os.path.basename(hit[0]); return cache[base]
    if os.environ.get('OFFLINE'): print('MISS',base); cache[base]=None; return None
    data=fetch(base); time.sleep(0.4)
    if data is None: cache[base]=None; return None
    ext=os.path.splitext(base.split('?')[0])[1].lower() or '.bin'
    name=hashlib.md5(base.encode()).hexdigest()[:10]
    if img and ext in ('.jpg','.jpeg','.png','.webp'):
        try:
            im=Image.open(io.BytesIO(data))
            if im.width>900: im=im.resize((900,int(im.height*900/im.width)))
            b=io.BytesIO()
            if im.mode in ('RGBA','LA','P') and ext=='.png': im.save(b,'PNG',optimize=True); ext='.png'
            else: im.convert('RGB').save(b,'JPEG',quality=78); ext='.jpg'
            data=b.getvalue()
        except Exception as e: print('img keep',base,e)
    open(f'{OUT}/assets/{name}{ext}','wb').write(data); cache[base]=f'assets/{name}{ext}'; return cache[base]
async def main():
    js=open('transform.js').read()
    async with async_playwright() as p:
        b=await p.chromium.launch()
        SNAP=open(os.path.join(os.path.dirname(os.path.abspath(__file__)),'snapshot-live-page-2026-11-22.html')).read(); aside=open('aside.html').read()
        aside=aside[aside.index('>')+1:aside.rindex('</aside>')]
        ctx=await b.new_context(viewport={"width":390,"height":844},java_script_enabled=False)
        async def rt(r):
            if r.request.url==URL: await r.fulfill(status=200,content_type='text/html; charset=utf-8',body=SNAP)
            else: await r.abort()
        await ctx.route("**/*", rt)
        pg=await ctx.new_page(); await pg.goto(URL, wait_until="domcontentloaded", timeout=30000); await pg.wait_for_timeout(500)
        await pg.evaluate("(h)=>{document.querySelector('aside.rsg-sidebar').innerHTML=h}",aside)
        h=await pg.evaluate("document.body.scrollHeight"); y=0
        while y<h:
            y+=500; await pg.evaluate(f"window.scrollTo(0,{y})"); await pg.wait_for_timeout(120); h=await pg.evaluate("document.body.scrollHeight")
        await pg.wait_for_timeout(1500); await pg.evaluate('window.scrollTo(0,0)'); await pg.wait_for_timeout(1200)
        await pg.evaluate(f"window.__V='{VAR}'")
        print('transform:',await pg.evaluate(js))
        html=await pg.content(); await b.close()
    # images
    def img_sub(m):
        tag=m.group(0); s=re.search(r'data-rsgx="([^"]*)"',tag); src=s.group(1) if s else ''
        tag=re.sub(r'\sdata-rsgx="[^"]*"','',tag)
        if src.startswith('http'):
            l=local(src.replace('&amp;','&'),img=not src.split('?')[0].endswith('.svg'))
            if l: tag=re.sub(r'\ssrc="[^"]*"','',tag,count=1).replace('<img',f'<img src="{l}"',1)
        return tag
    html=re.sub(r'<img\b[^>]*>',img_sub,html)
    # css urls (fonts, icons)
    def css_sub(m):
        u=m.group(2); frag=('#'+u.split('#',1)[1]) if '#' in u else ''
        l=local(u); return f'url({m.group(1)}{l}{frag}{m.group(1)})' if l else m.group(0)
    html=re.sub(r'url\((["\']?)(https://www\.runningshoesguru\.com/[^)"\']+)\1\)',css_sub,html)
    # icon sprite: inline it, point every <use> at the inline copy
    spr=open(OUT+'/'+local('https://www.runningshoesguru.com/wp-content/themes/rsg_2022/images/icons.svg')).read()
    spr=re.sub(r'<\?xml[^>]*\?>','',spr); spr=spr.replace('<svg','<svg style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true"',1)
    html=re.sub(r'(href=")(?:https?:)?(?://www\.runningshoesguru\.com)?/wp-content/themes/rsg_2022/images/icons\.svg(#[^"]*")',r'\1\2',html)
    html=re.sub(r'<body([^>]*)>',lambda m:'<body'+m.group(1)+'>'+spr,html,count=1)
    html=re.sub(r'(href|action)="/(?!/)',r'\1="https://www.runningshoesguru.com/',html)
    extra=open('extra.css').read()
    bar='''<div class="rsgx-bar"><span>Mock-up, not live. Texts: Brandon’s sentences from the live review, rearranged; “Why 7/10?”, finding lines and link labels are examples. Buttons that need the site’s scripts do nothing.</span><button type="button" id="rsgx-t">Show notes</button><a href="both.html">Phone + desktop</a></div>
<script>(function(){var b=document.getElementById('rsgx-t');b.addEventListener('click',function(){var on=document.body.classList.toggle('rsgx-notes');b.textContent=on?'Hide notes':'Show notes';});if(window.self!==window.top){var x=document.querySelector('.rsgx-bar');if(x&&/embed/.test(location.hash))x.style.display='none';}})();</script>'''
    html=html.replace('</head>',f'<style id="rsgx-css">{extra}</style></head>',1).replace('</body>',bar+'</body>',1)
    if not html.lstrip().lower().startswith('<!doctype'): html='<!DOCTYPE html>'+html
    html=html.replace('Mock-up, not live.',f'Version {VAR.upper()}. Mock-up, not live.').replace('<a href="both.html">Phone + desktop</a>','<a href="index.html">A and B side by side</a>')
    open(f'{OUT}/{VAR}.html','w').write(html)
    print('html bytes',len(html),'assets',len([c for c in cache.values() if c]),'failed',[k for k,v in cache.items() if not v])
asyncio.run(main())
