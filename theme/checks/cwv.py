"""CLS/LCP lab check on live, phone 390 + desktop 1366, fonts real (Typekit allowed) and Typekit blocked.
Ads, analytics, YouTube blocked (no impressions, no GA hits); the price-box view/click beacons are answered
locally with 299, so nothing is written to the click/view log. One page load at a time, 2 s pause (edge 429 above ~1.5 req/s).
usage: python3 cwv.py OUT.json URL...   (lab numbers, no throttling: compare templates/runs, not against field CWV)"""
import sys, json, re, asyncio, time
from urllib.parse import urlparse
from playwright.async_api import async_playwright

ADS = re.compile(r"adthrive|doubleclick|googlesyndication|google-analytics|googletagmanager|youtube|ytimg|amazon-adsystem|criteo|pubmatic|rubicon|3lift|openx|adnxs|adsrvr|gumgum|yahoo|smartadserver|adsafeprotected|facebook|hotjar|clarity")
INIT = """
window.__cls = 0; window.__lcp = null; window.__shifts = [];
const desc = n => { if (!n || !n.tagName) return ''; let s = n.tagName.toLowerCase() + (n.id ? '#' + n.id : '')
  + (typeof n.className === 'string' && n.className.trim() ? '.' + n.className.trim().split(/\\s+/).slice(0,2).join('.') : ''); return s; };
new PerformanceObserver(l => { for (const e of l.getEntries()) if (!e.hadRecentInput) { window.__cls += e.value;
  window.__shifts.push({t: Math.round(e.startTime), v: +e.value.toFixed(4), n: e.sources.map(s => desc(s.node)).slice(0,3)}); } })
  .observe({type: 'layout-shift', buffered: true});
new PerformanceObserver(l => { for (const e of l.getEntries()) window.__lcp = {t: Math.round(e.startTime), el: desc(e.element),
  url: (e.url || '').split('?')[0].slice(-80), size: e.size}; }).observe({type: 'largest-contentful-paint', buffered: true});
"""
VIEWPORTS = {390: dict(viewport={"width": 390, "height": 844}, device_scale_factor=2, is_mobile=True, has_touch=True),
             1366: dict(viewport={"width": 1366, "height": 768}, device_scale_factor=1)}

async def measure(browser, url, width, fonts):
    site = urlparse(url).hostname
    stat = {"typekit_req": 0, "beacons_stubbed": 0, "site_errors": []}
    ctx = await browser.new_context(**VIEWPORTS[width])
    async def gate(route):
        u = route.request.url; h = urlparse(u).hostname or ""
        if h == site and "/wp-json/rsg-dfs/v1/" in u and route.request.method == "POST":
            stat["beacons_stubbed"] += 1; return await route.fulfill(status=299)
        if "typekit" in h:
            stat["typekit_req"] += 1
            return await (route.continue_() if fonts == "real" else route.abort())
        if ADS.search(u):
            return await route.abort()
        await route.continue_()
    await ctx.route("**/*", gate)
    await ctx.add_init_script(INIT)
    pg = await ctx.new_page()
    pg.on("response", lambda r: stat["site_errors"].append([r.status, r.url[-80:]])
          if (urlparse(r.url).hostname == site and r.status >= 400) else None)
    resp = await pg.goto(url, wait_until="load", timeout=90000)
    await pg.wait_for_timeout(2500)
    m = {"status": resp.status if resp else None, "cache": (resp.headers.get("cf-cache-status") if resp else None)}
    m.update(await pg.evaluate("""() => { const n = performance.getEntriesByType('navigation')[0] || {};
        const fcp = performance.getEntriesByName('first-contentful-paint')[0];
        return {ttfb: Math.round(n.responseStart || 0), fcp: fcp ? Math.round(fcp.startTime) : null,
                load: Math.round(n.loadEventEnd || 0), lcp: window.__lcp, cls_load: +window.__cls.toFixed(4),
                fonts_loaded: [...document.fonts].filter(f => f.status === 'loaded').map(f => f.family).filter((v,i,a)=>a.indexOf(v)===i).slice(0,6)}; }"""))
    h = await pg.evaluate("document.body.scrollHeight"); y = 0
    while y < h and y < 30000:
        y += 600; await pg.evaluate(f"window.scrollTo(0,{y})"); await pg.wait_for_timeout(120)
        h = await pg.evaluate("document.body.scrollHeight")
    await pg.wait_for_timeout(1500)
    m["cls_scroll"] = round(await pg.evaluate("window.__cls"), 4)
    m["top_shifts"] = sorted(await pg.evaluate("window.__shifts"), key=lambda s: -s["v"])[:3]
    m.update(stat)
    await ctx.close()
    return m

async def main():
    out, urls = sys.argv[1], sys.argv[2:]
    res = {"run_utc": time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime()), "note": __doc__.strip(), "pages": {}}
    async with async_playwright() as p:
        b = await p.chromium.launch(channel="chrome", args=["--headless=new"])
        for u in urls:
            for w in (390, 1366):
                for fonts in ("real", "blocked"):
                    try:
                        r = await measure(b, u, w, fonts)
                    except Exception as e:
                        r = {"error": str(e)[:200]}
                    res["pages"].setdefault(u, {})[f"{w}_{fonts}"] = r
                    lcp = (r.get("lcp") or {}).get("t")
                    print(u[-60:], w, fonts, "LCP", lcp, "CLS", r.get("cls_load"), r.get("cls_scroll"), "err", len(r.get("site_errors", [])), flush=True)
                    json.dump(res, open(out, "w"), indent=1)
                    await asyncio.sleep(2)
        await b.close()

asyncio.run(main())
