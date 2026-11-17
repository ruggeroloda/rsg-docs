"""Full-page screenshots (390/820/1366) after scrolling, ads/YouTube blocked, + CLS/LCP at 390 and image facts.
usage: python3 shots.py OUTDIR URL...   -> OUTDIR/<slug>_<w>.png and OUTDIR/metrics.json"""
import sys, os, json, re, asyncio
from playwright.async_api import async_playwright

BLOCK = re.compile(r"adthrive|doubleclick|googlesyndication|google-analytics|googletagmanager|youtube|ytimg|amazon-adsystem|criteo|pubmatic|rubicon|3lift|openx|adnxs|adsrvr|gumgum|yahoo|smartadserver|adsafeprotected|typekit")

async def shoot(browser, url, width, outdir, metrics):
    ctx = await browser.new_context(viewport={"width": width, "height": 900}, device_scale_factor=1)
    await ctx.route("**/*", lambda r: r.fulfill(status=299) if "/wp-json/rsg-dfs/v1/" in r.request.url else (r.abort() if BLOCK.search(r.request.url) else r.continue_()))
    await ctx.add_init_script("""
      window.__cls = 0; window.__lcp = 0;
      new PerformanceObserver(l => { for (const e of l.getEntries()) if (!e.hadRecentInput) window.__cls += e.value; }).observe({type: 'layout-shift', buffered: true});
      new PerformanceObserver(l => { for (const e of l.getEntries()) window.__lcp = e.startTime; }).observe({type: 'largest-contentful-paint', buffered: true});
    """)
    page = await ctx.new_page()
    await page.goto(url, wait_until="load", timeout=90000)
    await page.wait_for_timeout(1500)
    m = {}
    if width == 390:
        m["cls_load"] = round(await page.evaluate("window.__cls"), 4)
        m["lcp_ms"] = round(await page.evaluate("window.__lcp"))
    h = await page.evaluate("document.body.scrollHeight")
    y = 0
    while y < h:
        y += 500
        await page.evaluate(f"window.scrollTo(0,{y})")
        await page.wait_for_timeout(150)
        h = await page.evaluate("document.body.scrollHeight")
    await page.wait_for_timeout(2500)
    await page.evaluate("window.scrollTo(0,0)")
    await page.wait_for_timeout(800)
    info = await page.evaluate("""() => { const imgs=[...document.images].filter(i=>i.offsetParent!==null);
       return {imgs: imgs.length, broken: imgs.filter(i=>i.complete && i.naturalWidth===0).map(i=>i.currentSrc||i.src).slice(0,5),
               blank_iframes: [...document.querySelectorAll('iframe')].filter(f=>f.src==='about:blank').length,
               height: document.body.scrollHeight}; }""")
    if width == 390:
        m["cls_total"] = round(await page.evaluate("window.__cls"), 4)
    m.update(info)
    slug = re.sub(r"[^a-z0-9]+", "-", url.split("://")[1].split("/", 1)[1].lower()).strip("-") or "home"
    await page.screenshot(path=f"{outdir}/{slug}_{width}.png", full_page=True)
    metrics.setdefault(slug, {})[str(width)] = m
    await ctx.close()

async def main():
    outdir = sys.argv[1]; os.makedirs(outdir, exist_ok=True)
    metrics = {}
    async with async_playwright() as p:
        b = await p.chromium.launch(channel="chrome", args=["--headless=new"])
        for u in sys.argv[2:]:
            for w in (390, 820, 1366):
                try:
                    await shoot(b, u, w, outdir, metrics)
                except Exception as e:
                    print("ERR", u, w, e)
            print("done", u, flush=True)
        await b.close()
    json.dump(metrics, open(f"{outdir}/metrics.json", "w"), indent=1)

asyncio.run(main())
