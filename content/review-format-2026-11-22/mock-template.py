import asyncio, re, sys
from playwright.async_api import async_playwright
BLOCK = re.compile(r"adthrive|doubleclick|googlesyndication|google-analytics|googletagmanager|youtube|ytimg|amazon-adsystem|criteo|pubmatic|rubicon|3lift|openx|adnxs|adsrvr|gumgum|yahoo|smartadserver|adsafeprotected|typekit|t\.runningshoesguru")
URL="https://www.runningshoesguru.com/reviews/road/nike-pegasus-42-review/"
JS = r"""()=>{
const $=(s,r=document)=>r.querySelector(s);
const css=document.createElement('style'); css.textContent=`
.rsgx-why{margin:0 0 14px;padding:0 0 14px;border-bottom:1px solid #dfe5ee;font-size:1.05em;line-height:1.5}
.rsgx-why b{display:block;font-size:.72em;letter-spacing:.08em;text-transform:uppercase;color:#5b6b82;font-weight:600;margin-bottom:4px}
.rsgx-parts{margin:0;padding:0}
.rsgx-part{display:block;padding:14px 0;border-top:1px solid #dfe5ee;color:inherit;text-decoration:none}
.rsgx-part:first-child{border-top:0;padding-top:0}
.rsgx-part .rsg-rate{margin:0}
.rsgx-part .rsg-rate dt{font-weight:600;font-size:1.05em}
.rsgx-find{margin:8px 0 0;line-height:1.45}
.rsgx-more{color:#0b62c4;font-weight:600;white-space:nowrap}
.rsgx-h{display:flex;align-items:baseline;justify-content:space-between;gap:12px;border-top:3px solid #0f2340;padding-top:14px;margin-top:8px}
.rsgx-h h2{margin:0}
.rsgx-lead{font-weight:600;font-size:1.15em;line-height:1.4;margin:6px 0 18px}
.rsgx-test{margin:14px 0 20px!important}.rsgx-h{margin-top:28px!important}
.rsgx-vs{font-weight:700;margin:22px 0 6px;font-size:1.05em}
.rsgx-note{position:sticky;top:0;z-index:99999;background:#fff4c2;color:#3b2f00;font:600 12px/1.3 system-ui;padding:6px 10px;text-align:center}
@media(min-width:992px){.rsgx-parts{display:grid;grid-template-columns:1fr 1fr;column-gap:40px}.rsgx-part:nth-child(2){border-top:0;padding-top:0}}
`; document.head.appendChild(css);
const note=document.createElement('div'); note.className='rsgx-note'; note.textContent='Mock-up, not live. Example texts: Brandon’s own sentences from the live review, rearranged.'; document.body.prepend(note);

// 1 why line in the verdict box
const v=$('.rsg-verdict p.big'); const why=document.createElement('p'); why.className='rsgx-why';
why.innerHTML='<b>Why 7/10?</b>The best Pegasus upper to date on a ride that feels flat and firm. The ride is what you buy a daily trainer for, so it decides the score.';
v.before(why);

// 2 rating breakdown: four parts
const h=[...document.querySelectorAll('h3.rsg-sub-h')].find(e=>/rating breakdown/i.test(e.textContent));
let row=h.nextElementSibling; while(row && !row.classList.contains('row')) row=row.nextElementSibling;
const parts=[['ride','Ride','6.0',6,'Dense, flat and familiar. Fine for easy runs; 23 km was the limit.'],
['fit','Fit & comfort','8.5',8.5,'The best Pegasus upper to date. Very narrow toe box: go half a size up.'],
['build','Build & durability','8.5',8.5,'Well built, with a hard outsole that lasts.'],
['value','Value','6.0',6,'Heavier than the Ride 19, Cumulus 28, Clifton 10 and Ghost 17, and no more cushioned.']];
row.outerHTML='<div class="rsgx-parts">'+parts.map(p=>`<a class="rsgx-part" href="#${p[0]}"><dl class="rsg-kv rsg-scores" style="margin:0"><div class="rsg-rate" style="--v:${p[3]}"><dt>${p[1]}</dt><dd class="rsg-rate-val">${p[2]}</dd></div></dl><p class="rsgx-find">${p[4]} <span class="rsgx-more">Read why ↓</span></p></a>`).join('')+'</div>';

// 3 who should / should not buy up, right under the breakdown
const who=$('.review_sections-no-toc'); const assess=$('.rsg-assess');
const sp=document.createElement('div'); sp.className='spacer-l'; assess.before(who); who.after(sp);

// 4 the text, rebuilt around the four parts
const fig=id=>{const e=document.getElementById('attachment_'+id); return e?e.outerHTML:'';};
const head=(id,t,sc,lead)=>`<div class="rsgx-h" id="${id}"><h2>${t}</h2>${sc?`<span class="rsg-rate-val">${sc}</span>`:''}</div>${lead?`<p class="rsgx-lead">${lead}</p>`:'<div class="spacer"></div>'}`;
const T=$('.review-sections.rsg-text');
T.innerHTML=`
<div class="review-section">${head('tested','How I tested it','', '')}
<dl class="rsg-kv rsg-facts rsgx-test"><div><dt>Runs</dt><dd>7</dd></div><div><dt>Distance</dt><dd>81.6 km</dd></div><div><dt>Longest run</dt><dd>23 km</dd></div><div><dt>Paces</dt><dd>6:22–7:13 /km</dd></div></dl>
<p>I’ve owned 10 different versions of the Pegasus over the years. My favourite was version 32 and my least favourite was version 36. I bought this pair at Running Warehouse, a half size bigger than usual: in the store the toe-box felt too tight for me.</p>
<p>All seven runs were easy or recovery runs, plus one 23 km long run at 6:26 per km. My first run was a 6 kilometre recovery run. The ride felt very similar to the previous version.</p></div>

<div class="review-section">${head('ride','Ride','6.0','Dense, flat and familiar. Fine for easy runs; 23 km was the limit.')}
${fig(50282)}
<p>The ride of the Pegasus 42 feels familiar rather than exciting and if you’ve run in previous versions of the Pegasus, you’ll be able to run in v42 blindfolded and be able to identify it. It has that distinct dense, rubbery, Pegasus midsole feel.</p>
<p>I only use the Pegasus for easy/recovery runs because it doesn’t enjoy picking up the pace. I don’t feel a lot of responsiveness from the ReactX and the Zoom Air unit also doesn’t provide much bounce.</p>
<p>The longest run I did in the Pegasus 42 was 23 kilometres and I wouldn’t do anything longer in it. It has decent stack height but it doesn’t feel like a very cushioned trainer.</p>
<p>There is no forefoot rocker (even though Nike advertises a higher toe spring). This contributes to flat feeling, slow transitions.</p>
${fig(50288)}
<p>With a 10 mm drop, the rearfoot feels much more cushioned than the forefoot. This alienates all runners who are forefoot strikers because the forefoot doesn’t feel cushioned enough.</p>
<p class="rsgx-vs">Against the Pegasus 41</p>
<p>Transitions feel smoother thanks to the full-length Zoom Air unit. The previous version had separate air units and the forefoot air unit felt lumpy. The ReactX in the midsole is the same and it doesn’t feel very lively.</p></div>

<div class="review-section">${head('fit','Fit & comfort','8.5','The best Pegasus upper to date. Very narrow toe box: go half a size up.')}
${fig(50280)}
<p>The Pegasus 42’s upper is far better than its midsole and it’s the best Pegasus upper to date. It’s padded in all the right places and foot lockdown is superb.</p>
<p>It’s a very narrow toe box so you need to go up a half size (or get the wide version) if you prefer a more spacious interior for your feet to swell. If you’re only using it for casual wear, going true to size is fine.</p>
${fig(50273)}
<p>The tongue is semi-gusseted and it doesn’t slide around. The upper is on the warm side because of the padding but breathability is decent- there are plenty of ventilation holes on the toe box and side panels.</p></div>

<div class="review-section">${head('build','Build & durability','8.5','Well built, with a hard outsole that lasts.')}
${fig(50283)}
<p>The durable outsole is a strength of the Pegasus 42. It’s not called Waffle traction anymore but there are still protruding rectangles on the forefoot to bite into the ground. The hard rubber on the high wear areas is supremely abrasion resistant and lasts long.</p>
<p>The thing that I respect the most about all the Pegasus versions over the years is how durable and how well-built they feel. There are also big reflector strips on the heel for safety.</p></div>

<div class="review-section">${head('value','Value, and what else to look at','6.0','Heavier than the Ride 19, Cumulus 28, Clifton 10 and Ghost 17, and no more cushioned.')}
${fig(50272)}
<p>The Pegasus 42 is not the upgrade I was hoping for. It’s still one of the heaviest daily trainers on the market and its midsole doesn’t feel more cushioned. It doesn’t feel as engaging or as versatile as other daily trainers in its price range.</p>
<p class="rsgx-vs">Pegasus 42 or Pegasus 41?</p>
<p>The Pegasus 42 is slightly better because the ride is smoother. Overall, the rides are very similar though and if you just bought the 41, you don’t need to buy the 42.</p>
<p class="rsgx-vs">Pegasus 42 or Nike Vomero?</p>
<p>The softer Nike Vomero and Vomero Plus have forefoot rockers and are much better long run trainers. <a href="#">Pegasus vs Vomero: the full comparison</a></p>
<p class="rsgx-vs">Pegasus 42 or ASICS Cumulus 28?</p>
<p>The closest midsole foam to ReactX is FF Blast Max that’s in the ASICS Cumulus 28. Both these midsoles feel like they are from 5 years ago. The Cumulus 28 is lighter.</p></div>`;
}"""
async def shot(b,w,out):
    ctx=await b.new_context(viewport={"width":w,"height":900},device_scale_factor=1)
    await ctx.route("**/*", lambda r: r.fulfill(status=299) if "/wp-json/rsg-dfs/v1/" in r.request.url else (r.abort() if BLOCK.search(r.request.url) else r.continue_()))
    pg=await ctx.new_page(); await pg.goto(URL, wait_until="load", timeout=90000); await pg.wait_for_timeout(1200)
    await pg.evaluate(JS)
    h=await pg.evaluate("document.body.scrollHeight"); y=0
    while y<h:
        y+=500; await pg.evaluate(f"window.scrollTo(0,{y})"); await pg.wait_for_timeout(120); h=await pg.evaluate("document.body.scrollHeight")
    await pg.wait_for_timeout(2000); await pg.evaluate("window.scrollTo(0,0)"); await pg.wait_for_timeout(600)
    pos=await pg.evaluate("""()=>Object.fromEntries(['.rsg-verdict','.rsgx-parts','#who_should_buy_it','.rsg-assess','#tested','#ride','#fit','#build','#value','.rsg-cmp-scroll'].map(s=>[s,Math.round(document.querySelector(s).getBoundingClientRect().top+scrollY)]))""")
    print(w,h,pos)
    await pg.screenshot(path=out,full_page=True); await ctx.close()
async def main():
    async with async_playwright() as p:
        b=await p.chromium.launch()
        await shot(b,390,'mock_390.png'); await shot(b,1366,'mock_1366.png'); await b.close()
asyncio.run(main())
