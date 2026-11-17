import asyncio, json, sys
from playwright.async_api import async_playwright
async def main():
    async with async_playwright() as p:
        b = await p.chromium.launch(channel="chrome", args=["--headless=new","--disable-blink-features=AutomationControlled"])
        ctx = await b.new_context(viewport={"width":1366,"height":900})
        await ctx.add_init_script("Object.defineProperty(navigator,'webdriver',{get:()=>undefined})")
        urls=[]
        async def gate(route):
            u=route.request.url
            ok = "runningshoesguru.com" in u or ("ads.adthrive.com" in u and ("/sites/" in u or "/builds/core/" in u))
            if "ads.adthrive.com" in u: urls.append(u)
            await (route.continue_() if ok else route.abort())
        await ctx.route("**/*", gate)
        pg = await ctx.new_page()
        bodies={}
        async def resp(r):
            if "ads.adthrive.com/sites/" in r.url:
                try: bodies[r.url]=await r.text()
                except: pass
        pg.on("response", resp)
        await pg.goto("https://www.runningshoesguru.com/", wait_until="domcontentloaded"); await pg.wait_for_timeout(6000)
        print("\n".join(urls[:10]))
        for u,t in bodies.items():
            open("raptive_site.txt","w").write(t); print(u, len(t))
        print(await pg.evaluate("document.body.className"))
        await b.close()
asyncio.run(main())
