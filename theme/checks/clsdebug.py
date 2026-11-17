"""Which elements shift, when, by how much. usage: python3 clsdebug.py URL [width]"""
import sys, asyncio, re, json
from playwright.async_api import async_playwright
BLOCK = re.compile(r"adthrive|doubleclick|googlesyndication|google-analytics|googletagmanager|youtube|ytimg|amazon-adsystem")

async def main(url, width):
    async with async_playwright() as p:
        b = await p.chromium.launch(channel="chrome", args=["--headless=new"])
        ctx = await b.new_context(viewport={"width": width, "height": 844})
        await ctx.route("**/*", lambda r: r.fulfill(status=299) if "/wp-json/rsg-dfs/v1/" in r.request.url else (r.abort() if BLOCK.search(r.request.url) else r.continue_()))
        await ctx.add_init_script("""
          window.__shifts = [];
          const desc = n => { if (!n || !n.tagName) return String(n && n.nodeName);
            let s = n.tagName.toLowerCase() + (n.id ? '#' + n.id : '') + (n.className && typeof n.className === 'string' ? '.' + n.className.trim().split(/\\s+/).slice(0,3).join('.') : '');
            const p = n.parentElement; if (p) s = (p.tagName.toLowerCase() + (p.id ? '#' + p.id : '') + (typeof p.className === 'string' && p.className ? '.' + p.className.trim().split(/\\s+/).slice(0,2).join('.') : '')) + ' > ' + s;
            return s; };
          new PerformanceObserver(l => { for (const e of l.getEntries()) if (!e.hadRecentInput)
            window.__shifts.push({t: Math.round(e.startTime), v: +e.value.toFixed(4), src: e.sources.map(s => ({node: desc(s.node), from: [Math.round(s.previousRect.y), Math.round(s.previousRect.height)], to: [Math.round(s.currentRect.y), Math.round(s.currentRect.height)]}))}); })
            .observe({type: 'layout-shift', buffered: true});
        """)
        pg = await ctx.new_page()
        await pg.goto(url, wait_until="load")
        await pg.wait_for_timeout(4000)
        shifts = await pg.evaluate("window.__shifts")
        print("total", round(sum(s['v'] for s in shifts), 4))
        for s in shifts:
            print(s['t'], 'ms', s['v'])
            for x in s['src'][:4]:
                print('    ', x['node'][:110], 'y/h', x['from'], '->', x['to'])
        await b.close()

asyncio.run(main(sys.argv[1], int(sys.argv[2]) if len(sys.argv) > 2 else 390))
