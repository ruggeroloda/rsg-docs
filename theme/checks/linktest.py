"""Click one link of each kind on a page (external navigation blocked), report what beacons were sent and their status."""
import asyncio, sys, json
from urllib.parse import urlparse, parse_qs
from playwright.async_api import async_playwright
CASES = [
 # (url, css selector of the link to click, expected box)
 ("/reviews/gps/garmin-forerunner-165/", "a.rsg-gps-amz", "gps"),
 ("/reviews/gps/garmin-forerunner-165/", "a[data-box=bought]", "bought"),
 ("/reviews/road/asics-gel-cumulus-28-review/", "a[data-box=bought]", "bought"),
 ("/reviews/road/asics-gel-cumulus-28-review/", "[data-dfs-shoe] a[data-m]", "BOX (rsg-dfs.js, once)"),
 ("/content/ultimate-guide-polar-gps-watches/", "a[data-amz]:not(.rsg-gps-amz)", "amzk"),
 ("/2019/07/amazon-prime-day-deals-for-runners/", "article a[href*='amazon.com'], .col-lg-8 a[href*='amazon.com']", "text"),
 ("/2023/11/hand-picked-cyber-monday-deals-for-runners-27-nov-2003/", ".col-lg-8 a[href*='awin1'], .col-lg-8 a[href*='prf.hn'], .col-lg-8 a[href*='avantlink'], .col-lg-8 a[href*='runningwarehouse']", "text"),
 ("/2019/01/saucony-cohesion-12-review/", "a[href*='/go/']", "go"),
 ("/reviews/road/asics-gel-cumulus-28-review/", "nav a[href^='/'], .breadcrumb a", "none (internal link)"),
]
async def main(base):
    host = urlparse(base).hostname
    async with async_playwright() as p:
        b = await p.chromium.launch(channel="chrome", args=["--headless=new"])
        for path, sel, exp in CASES:
            ctx = await b.new_context(viewport={"width": 1366, "height": 900})
            beacons = []
            async def gate(route):
                u = route.request.url; h = urlparse(u).hostname or ""
                if h == host:
                    if "/rsg-dfs/v1/click" in u:
                        beacons.append(route.request.post_data)
                        await route.fulfill(status=299)   # dry run on live: the beacon never reaches the server
                        return
                    await route.continue_()
                else:
                    await route.abort()
            await ctx.route("**/*", gate)
            pg = await ctx.new_page()
            st = []
            pg.on("response", lambda r: st.append(r.status) if "/rsg-dfs/v1/click" in r.url else None)
            await pg.goto(base + path, wait_until="domcontentloaded"); await pg.wait_for_timeout(2500)
            loc = pg.locator(sel).first
            n = await pg.locator(sel).count()
            if not n:
                print(f"NO LINK  {exp:24} {path} [{sel}]"); await ctx.close(); continue
            href = await loc.get_attribute("href")
            try:
                await loc.evaluate("e=>{e.removeAttribute('target'); e.addEventListener('click',ev=>ev.preventDefault(),{once:true}); e.scrollIntoView(); e.click();}")
            except Exception as e:
                print("ERR", e)
            await pg.wait_for_timeout(2000)
            got = [{k: v[0] for k, v in parse_qs(x or "").items()} for x in beacons]
            print(f"{exp:24} links={n:<3} sent={len(got)} status={st} {path}\n   href={href[:90]}\n   " + "\n   ".join(json.dumps(g) for g in got))
            await ctx.close()
        await b.close()
asyncio.run(main(sys.argv[1]))
