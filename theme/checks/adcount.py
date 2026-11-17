"""Count Raptive ad slots on pages (phone 390 px + desktop 1366 px).
usage: python3 adcount.py URL [URL ...]   (env COOKIE='name=value' to send a cookie)
Prints: url, viewport, slots (.adthrive-ad), filled (slot with iframe), body classes with 'adthrive', ads script loaded?"""
import os, sys, asyncio
from playwright.async_api import async_playwright

VIEWPORTS = {"phone": (390, 844), "desktop": (1366, 900)}

async def one(browser, url, name, size):
    ctx = await browser.new_context(viewport={"width": size[0], "height": size[1]},
                                    user_agent=("Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1"
                                                if name == "phone" else
                                                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"))
    ck = os.environ.get("COOKIE")
    if ck:
        n, v = ck.split("=", 1)
        from urllib.parse import urlparse
        await ctx.add_cookies([{"name": n, "value": v, "domain": urlparse(url).hostname, "path": "/"}])
    await ctx.add_init_script("Object.defineProperty(navigator,'webdriver',{get:()=>undefined})")
    from urllib.parse import urlparse as _u
    site = _u(url).hostname
    # only our site, Raptive's own layout files and fonts load: no auctions, no impressions (no invalid traffic)
    async def gate(route):
        h = _u(route.request.url).hostname or ""
        if h == site and "/wp-json/rsg-dfs/v1/" in route.request.url:   # view/click log beacons: answered locally, never stored
            return await route.fulfill(status=299)
        ok = h == site or (h == "ads.adthrive.com" and ("/sites/" in route.request.url or "/builds/core/" in route.request.url and "/js/" in route.request.url)) or "typekit" in h
        await (route.continue_() if ok else route.abort())
    await ctx.route("**/*", gate)
    page = await ctx.new_page()
    ads_js = []
    page.on("request", lambda r: ads_js.append(r.url) if "ads.adthrive.com/sites/" in r.url else None)
    try:
        await page.goto(url, wait_until="domcontentloaded", timeout=60000)
        await page.wait_for_timeout(4000)
        h = await page.evaluate("document.body.scrollHeight")
        y = 0
        while y < h:
            y += 600
            await page.evaluate(f"window.scrollTo(0,{y})")
            await page.wait_for_timeout(250)
            h = await page.evaluate("document.body.scrollHeight")
        await page.wait_for_timeout(3000)
        res = await page.evaluate("""() => ({
            slots: document.querySelectorAll('.adthrive-ad').length,
            filled: [...document.querySelectorAll('.adthrive-ad')].filter(e => e.querySelector('iframe')).length,
            body: [...document.body.classList].filter(c => c.startsWith('adthrive')).join(' '),
            nbody: document.body.classList.length, list: [...document.querySelectorAll('.adthrive-ad')].map(e=>e.className.replace('adthrive-ad','').trim().slice(0,60)+' <'+(e.parentElement.className||e.parentElement.tagName).slice(0,30)+'>') })""")
        status = "ok"
    except Exception as e:
        res, status = {}, f"ERR {e.__class__.__name__}"
    await ctx.close()
    return f"{name:8} slots={res.get('slots')} filled={res.get('filled')} adsjs={'yes' if ads_js else 'no'} bodyclasses={res.get('nbody')} adthrive=[{res.get('body','')}] {status}  {url}\n      " + "\n      ".join(res.get('list',[]))

async def main(urls):
    async with async_playwright() as p:
        browser = await p.chromium.launch(channel="chrome", args=["--headless=new","--disable-blink-features=AutomationControlled"])
        for u in urls:
            for name, size in VIEWPORTS.items():
                print(await one(browser, u, name, size), flush=True)
        await browser.close()

asyncio.run(main(sys.argv[1:]))
