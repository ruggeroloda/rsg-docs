import asyncio, sys, json
from playwright.async_api import async_playwright
OUT=sys.argv[1]
SEL=['.rsgx-glance','.rsg-verdict','section.rsg-dfs-mob','.rsgx-who','.rsgx-parts','#rsg-cw','#tested','#ride','#fit','#build','#value','section.rsg-facts-sec','.rsg-cmp-scroll','#full-price-comparison','.rsg-reviewer','#comments-row']
async def main():
    async with async_playwright() as p:
        b=await p.chromium.launch()
        for w in (390,1366):
            ctx=await b.new_context(viewport={"width":w,"height":844 if w==390 else 900}); pg=await ctx.new_page()
            errs=[]; pg.on('requestfailed',lambda r: errs.append(r.url[-70:]))
            await pg.goto('file://'+OUT+'/index.html'); await pg.wait_for_timeout(1200)
            hh=await pg.evaluate("document.body.scrollHeight"); y=0
            while y<hh:
                y+=600; await pg.evaluate(f"window.scrollTo(0,{y})"); await pg.wait_for_timeout(80)
            await pg.evaluate("window.scrollTo(0,0)"); await pg.wait_for_timeout(800)
            pos=await pg.evaluate("(S)=>Object.fromEntries(S.map(s=>{const e=document.querySelector(s);return [s,e?Math.round(e.getBoundingClientRect().top+scrollY):null]}))",SEL)
            h=await pg.evaluate("document.body.scrollHeight"); ext=await pg.evaluate("[...document.querySelectorAll('img')].filter(i=>!i.complete||i.naturalWidth==0).map(i=>i.getAttribute('src')).slice(0,8)")
            print(w,h,json.dumps(pos)); print(' broken',ext,' failed',errs[:6])
            await pg.screenshot(path=f'{OUT}/_shot_{w}.png',full_page=True); await ctx.close()
        await b.close()
asyncio.run(main())
