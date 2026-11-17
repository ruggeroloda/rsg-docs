import asyncio, json, re, sys
from playwright.async_api import async_playwright
t=open("raptive_site.txt").read()
units=[]
for m in re.finditer(r'"pageSelector":"((?:[^"\\]|\\.)*)"', t):
    seg_before=t[max(0,m.start()-1500):m.start()]
    name=re.findall(r'"name":"([A-Za-z_0-9]+)"',seg_before)[-1:] or ["?"]
    dev=re.findall(r'"devices":\[([^\]]*)\]',seg_before)[-1:] or ["?"]
    after=t[m.end():m.end()+1500]
    el=re.search(r'"elementSelector":"((?:[^"\\]|\\.)*)"',after)
    en=re.search(r'"enable":(true|false)',after)
    units.append(dict(name=name[0],dev=dev[0],page=json.loads('"'+m.group(1)+'"'),el=json.loads('"'+el.group(1)+'"') if el else "",en=en.group(1) if en else "?"))
async def main(url):
    async with async_playwright() as p:
        b=await p.chromium.launch(channel="chrome",args=["--headless=new"])
        pg=await b.new_page(viewport={"width":1366,"height":900})
        await pg.route("**/*", lambda r: r.continue_() if "runningshoesguru.com" in r.request.url else r.abort())
        await pg.goto(url, wait_until="domcontentloaded")
        for u in units:
            r=await pg.evaluate("""([ps,es])=>{let pm; try{pm = ps==='' || document.documentElement.matches(ps)}catch(e){pm='ERR:'+e.message.slice(0,40)}; let n; try{n=es?document.querySelectorAll(es).length:'-'}catch(e){n='ERR'}; return [pm,n]}""",[u['page'],u['el']])
            print(f"{str(r[0]):5} els={r[1]!s:<4} {u['name']:12} en={u['en']:5} {u['dev'][:30]:30} page={u['page'][:110]}")
        await b.close()
asyncio.run(main(sys.argv[1]))
