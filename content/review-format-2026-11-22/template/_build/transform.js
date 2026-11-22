()=>{
const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>[...r.querySelectorAll(s)];
const el=(html)=>{const t=document.createElement('template'); t.innerHTML=html.trim(); return t.content.firstElementChild;};
const rep=[]; const need=(n,x)=>{ if(!x) rep.push('MISSING '+n); return x; };
const L={vom:'https://www.runningshoesguru.com/comparison/nike-pegasus-vs-nike-vomero/', p41:'https://www.runningshoesguru.com/reviews/road/nike-pegasus-41-review/', cum:'https://www.runningshoesguru.com/reviews/road/asics-gel-cumulus-28-review/', best:'https://www.runningshoesguru.com/top/what-are-the-best-daily-trainers-right-now/'};
const note=(t)=>`<p class="rsgx-n">${t}</p>`;
const art=need('article',$('article.rsg-content'));

// ---------- collect existing blocks
const verdictRow=need('verdict',$('.rsg-verdict')).closest('.row');
const prosRow=need('pros',[...art.children].find(c=>c.classList.contains('row')&&/THE PROS/i.test(c.textContent)));
const priceMob=need('priceMob',$('section.rsg-dfs-mob'));
const who=need('who',$('.review_sections-no-toc'));
const assess=need('assess',$('.rsg-assess'));
const factsSec=need('facts',$('section.rsg-facts-sec'));
const take=need('take',$('section.rsg-take'));
const fitBox=need('fit',$('.rsg-fit')), rideBox=need('ride',$('.rsg-ride'));
const text=need('text',$('.review-sections.rsg-text'));
const cmpH=[...document.querySelectorAll('h2')].find(e=>/how does the/i.test(e.textContent));
const cmpTable=need('cmp',$('.rsg-cmp-scroll'));
const trust=need('trust',$('section.rsg-trustbox'));
const priceList=need('priceList',$('#full-price-comparison'));
const reviewer=need('reviewer',$('.rsg-reviewer'));
const comTop=need('comTop',$('.rsg-comments-top')), comRow=need('comRow',$('#comments-row'));
const cw=need('cw',$('#rsg-cw'));
const simH=[...document.querySelectorAll('h2')].find(e=>/similar shoes/i.test(e.textContent));
const simWrap=simH?simH.parentElement:null;
const sims=simWrap?$$('.entries .panel',simWrap).map(p=>{const a=$('h3 a',p),i=$('img',p); return {href:a.href,name:a.textContent.replace(/ review$/i,'').trim(),img:i?(i.currentSrc||i.src):''};}):[];
const figs={}; $$('.wp-caption',text).forEach(f=>figs[f.id.replace('attachment_','')]=f);
const everything=[...art.children];
if(rep.length) return rep.join('; ');

// ---------- 0 hero: testing proof
const proof=$('.rsg-trust-proof'); if(proof){ const how=$('.rsg-how',proof); const s=el('<span class="rsg-ok"><b>82 km in 7 runs</b></span>'); how?how.before(s,' '):proof.append(s); }

// ---------- 1 at a glance
const glance=el(`<section class="rsgx-glance">${note('New, two lines. Best for, fit, weight and drop sit at screens 4.5–5 today, reached by 60 % and 51 % of phone readers.')}<dl class="rsgx-gl"><div><dt>Best for</dt><dd>Daily training</dd></div><div><dt>Fit</dt><dd>Runs small, half size up</dd></div><div><dt>Weight</dt><dd>10.3 oz</dd></div><div><dt>Drop</dt><dd>10 mm</dd></div></dl></section>`);

// ---------- 2 verdict: why line, drop the sentence that repeats the summary
const vp=$('.rsg-verdict p.big'); const sum=($('.rsg-summary')||{}).textContent||'';
if(sum && vp.textContent.trim().startsWith(sum.trim())) vp.textContent=vp.textContent.trim().slice(sum.trim().length).trim();
vp.before(el('<p class="rsgx-why"><b>Why 7/10?</b>The best Pegasus upper to date, on a ride that feels flat and firm. The ride decides it.</p>'));
$('.rsg-verdict').prepend(el(note('“Why 7/10?” replaces the first sentence, which repeated the summary above. The box is shorter than today.')));

// ---------- 3 is it for you
const [buy,skip]=$$('.review-section',who);
const lis=(arr)=>'<ul class="rsgx-ul">'+arr.map(a=>`<li>${a[0]}${a[1]?` <a class="rsgx-go" href="${a[2]}">${a[1]} <span aria-hidden="true">›</span></a>`:''}</li>`).join('')+'</ul>';
$('p',buy).outerHTML=lis([['If you buy the Pegasus every year, the Pegasus 42 is a slight upgrade with a smoother ride.'],['If you want a really durable, well built daily trainer with an outsole that will last a long time, you should get the Pegasus 42.']]);
$('p',skip).outerHTML=lis([['If you want a fun daily trainer with good energy return and bounce, the Pegasus 42 will feel too dull.','See the best daily trainers right now',L.best],['If you want a versatile daily trainer for all paces and distances, the Pegasus 42 won’t feel fast enough or cushioned enough.','Compare it with the Nike Vomero 18',L.vom]]);
who.classList.add('rsgx-who');
const forYou=el(`<section class="rsgx-blk">${note('Moved up from screen 6.7 (41 % of phone readers) to where 73 % still are. Each “should not buy” line now leads to another RSG page: today 75 % of phone review views end here.')}</section>`); forYou.append(who);

// ---------- 4 rating breakdown: four parts
const parts=[['ride','Ride','6.0',6,'Dense, flat and familiar. Fine for easy runs; 23 km was the limit.'],['fit','Fit & comfort','8.5',8.5,'The best Pegasus upper to date. Very narrow toe box: go half a size up.'],['build','Build & durability','8.5',8.5,'Well built, with a hard outsole that lasts.'],['value','Value','6.0',6,'Heavier than the Ride 19, Cumulus 28, Clifton 10 and Ghost 17, and no more cushioned.']];
const rating=el(`<section class="rsgx-blk"><h3 class="rsg-sub-h">Rating breakdown</h3>${note('Four rows instead of ten bars (the four ride scores were one score). Each row jumps to its section: readers tap what they are looking at; the separate jump line on comparisons got 0 taps.')}<div class="rsgx-parts">`+parts.map(p=>`<a class="rsgx-part" href="#${p[0]}"><dl class="rsg-kv rsg-scores"><div class="rsg-rate" style="--v:${p[3]}"><dt>${p[1]}</dt><dd class="rsg-rate-val">${p[2]}</dd></div></dl><p class="rsgx-find">${p[4]} <span class="rsgx-more">Read why ↓</span></p></a>`).join('')+`</div></section>`);

// ---------- 5 head to head
cw.removeAttribute('hidden'); cw.setAttribute('data-cw','on'); cw.classList.add('rsgx-blk');
$('h3',cw).after(el(note('The “head to head” block of the live test, placed where readers decide to stay or leave: the next two sections lose 41 % → 28 % today.')));

// ---------- 6 text
const cap=(id,c)=>{const f=figs[id]; if(!f) return ''; f.querySelectorAll('.wp-caption-text').forEach(x=>x.remove()); f.removeAttribute('style'); return f.outerHTML+(c?`<p class="rsgx-cap">${c}</p>`:'');};
const head=(id,t,sc,lead,n)=>`<div class="rsgx-h" id="${id}"><h2>${t}</h2>${sc?`<span class="rsg-rate-val">${sc}</span>`:''}</div>${n?note(n):''}${lead?`<p class="rsgx-lead">${lead}</p>`:''}`;
const go=(t,h)=>`<p class="rsgx-next"><a class="rsgx-go" href="${h}">${t} <span aria-hidden="true">›</span></a></p>`;
text.innerHTML=`
<div class="review-section">${head('tested','How I tested it','','','New, short, first person. The run log was at the end of the introduction (38 % reach, and that section loses a quarter of its readers). The Pegasus history is cut.')}
<dl class="rsg-kv rsg-facts rsgx-g4"><div><dt>Runs</dt><dd>7</dd></div><div><dt>Distance</dt><dd>81.6 km</dd></div><div><dt>Longest run</dt><dd>23 km</dd></div><div><dt>Paces</dt><dd>6:22–7:13 /km</dd></div></dl>
<p>I’ve owned 10 different versions of the Pegasus over the years. My favourite was version 32 and my least favourite was version 36. I bought this pair at Running Warehouse, a half size bigger than usual: in the store the toe-box felt too tight for me.</p>
<p>All seven runs were easy or recovery runs, plus one 23 km long run. My first run was a 6 kilometre recovery run. The ride felt very similar to the previous version.</p>
<details class="rsgx-log"><summary>All 7 runs</summary><ul><li>10.5 km easy run at 6:22 per km</li><li>12 km recovery run at 7:12 per km</li><li>11 km easy run at 6:22 per km</li><li>8.5 km recovery run at 7:13 per km</li><li>10.6 km recovery run at 7:10 per km</li><li>23 km long run at 6:26 per km</li><li>6 km recovery run at 6:59 per km</li></ul></details></div>

<div class="review-section">${head('ride','Ride','6.0','Dense, flat and familiar. Fine for easy runs; 23 km was the limit.','First text section. Today this is “Sole unit”, the fourth, at screen 14, reached by 22 % of phone readers.')}
${cap('50282')}
<p>The ride of the Pegasus 42 feels familiar rather than exciting and if you’ve run in previous versions of the Pegasus, you’ll be able to run in v42 blindfolded and be able to identify it. It has that distinct dense, rubbery, Pegasus midsole feel.</p>
<p>I only use the Pegasus for easy/recovery runs because it doesn’t enjoy picking up the pace. I don’t feel a lot of responsiveness from the ReactX and the Zoom Air unit also doesn’t provide much bounce.</p>
<div data-slot="ride"></div>
<p>The longest run I did in the Pegasus 42 was 23 kilometres and I wouldn’t do anything longer in it. It has decent stack height but it doesn’t feel like a very cushioned trainer.</p>
<p>There is no forefoot rocker (even though Nike advertises a higher toe spring). This contributes to flat feeling, slow transitions.</p>
${cap('50288','The forefoot: no rocker, and less cushioned than the heel.')}
<p>With a 10 mm drop, the rearfoot feels much more cushioned than the forefoot. This alienates all runners who are forefoot strikers because the forefoot doesn’t feel cushioned enough.</p>
<h3 class="rsgx-vs">Against the Pegasus 41</h3>
<p>Transitions feel smoother thanks to the full-length Zoom Air unit. The previous version had separate air units and the forefoot air unit felt lumpy. The ReactX in the midsole is the same and it doesn’t feel very lively.</p>
${go('Softer, with a forefoot rocker: Pegasus vs Vomero 18',L.vom)}</div>

<div class="review-section">${head('fit','Fit & comfort','8.5','The best Pegasus upper to date. Very narrow toe box: go half a size up.')}
<div data-slot="fit"></div>
<p>The Pegasus 42’s upper is far better than its midsole and it’s the best Pegasus upper to date. It’s padded in all the right places and foot lockdown is superb.</p>
${cap('50273','The toe box is very narrow.')}
<p>It’s a very narrow toe box so you need to go up a half size (or get the wide version) if you prefer a more spacious interior for your feet to swell. If you’re only using it for casual wear, going true to size is fine.</p>
<p>The tongue is semi-gusseted and it doesn’t slide around. The upper is on the warm side because of the padding but breathability is decent- there are plenty of ventilation holes on the toe box and side panels.</p>
${cap('50280')}
<p class="rsgx-next"><a class="rsgx-go" href="#full-price-comparison">Know your size? See today’s prices <span aria-hidden="true">↓</span></a></p></div>

<div class="review-section">${head('build','Build & durability','8.5','Well built, with a hard outsole that lasts.')}
${cap('50283')}
<p>The durable outsole is a strength of the Pegasus 42. It’s not called Waffle traction anymore but there are still protruding rectangles on the forefoot to bite into the ground. The hard rubber on the high wear areas is supremely abrasion resistant and lasts long.</p>
<p>The thing that I respect the most about all the Pegasus versions over the years is how durable and how well-built they feel. There are also big reflector strips on the heel for safety.</p></div>

<div class="review-section">${head('value','Value, and what else to look at','6.0','Heavier than the Ride 19, Cumulus 28, Clifton 10 and Ghost 17, and no more cushioned.','Replaces “Conclusions” (screen 18, 19 % reach). Rivals by name, one short paragraph each, each with a way to the page about it: today 2–3 % of onward clicks from a review go to a comparison.')}
${cap('50272')}
<p>The Pegasus 42 is not the upgrade I was hoping for. It’s still one of the heaviest daily trainers on the market and its midsole doesn’t feel more cushioned. It doesn’t feel as engaging or as versatile as other daily trainers in its price range.</p>
<h3 class="rsgx-vs">Pegasus 42 or Pegasus 41?</h3>
<p>The Pegasus 42 is slightly better because the ride is smoother. Overall, the rides are very similar though and if you just bought the 41, you don’t need to buy the 42. <a class="rsgx-go" href="${L.p41}">Pegasus 41 review <span aria-hidden="true">›</span></a></p>
<h3 class="rsgx-vs">Pegasus 42 or Nike Vomero 18?</h3>
<p>The softer Nike Vomero and Vomero Plus have forefoot rockers and are much better long run trainers. <a class="rsgx-go" href="${L.vom}">Pegasus vs Vomero, side by side <span aria-hidden="true">›</span></a></p>
<h3 class="rsgx-vs">Pegasus 42 or ASICS Cumulus 28?</h3>
<p>The closest midsole foam to ReactX is FF Blast Max that’s in the ASICS Cumulus 28. Both these midsoles feel like they are from 5 years ago. The Cumulus 28 is lighter. <a class="rsgx-go" href="${L.cum}">Cumulus 28 review <span aria-hidden="true">›</span></a></p></div>`;
// sliders into Ride, fit drawing into Fit (each in a copy of the tester's-take card)
const card=(box,title)=>{const s=take.cloneNode(false); s.classList.add('rsgx-take'); s.append(el(`<div class="rsg-take-head"><span class="rsg-take-t">${title}</span><span class="rsg-take-sub">Hands-on judgment, not specs</span></div>`)); s.append(box); return s;};
$('[data-slot="ride"]',text).replaceWith(card(rideBox,'How it rides'));
$('[data-slot="fit"]',text).replaceWith(card(fitBox,'How it fits'));
const tn=el(note('The cushioning sliders and the fit drawing come from “Our tester’s take”. The data zone (ratings, facts, tester’s take) is where a third of readers leave today (73 % → 41 %); each piece now sits next to the text that explains it.')); $('#ride').after(tn);

// ---------- 7 facts, compare, others, prices, trust, reviewer, comments
const facts=el('<section class="rsgx-blk"></section>'); facts.append(factsSec); $('h3',factsSec).after(el(note('The full facts, for those who look for them. The four most asked ones are at the top of the page.')));
const cmp=el('<section class="rsgx-blk"></section>'); if(cmpH){cmpH.className='rsgx-h2'; cmp.append(cmpH);} cmp.append(cmpTable);
const others=el(`<section class="rsg-cw rsgx-blk" data-cw="on"><h3 class="rsg-sub-h">Other shoes to look at</h3>${note('“Similar shoes” sat after the comments, seen on 1–2 % of views. Same three shoes, as a small list in the head-to-head style.')}<ul class="rsg-cw-list n2">`+sims.map(s=>`<li><a href="${s.href}"><img src="${s.img}" alt="" width="64" height="36"><span class="rsg-cw-t"><span class="rsg-cw-n">${s.name}</span></span><span class="rsg-cw-a">Review <span aria-hidden="true">›</span></span></a></li>`).join('')+`<li><a href="${L.best}"><span class="rsg-cw-t"><span class="rsg-cw-n">Best daily trainers right now</span></span><span class="rsg-cw-a">List <span aria-hidden="true">›</span></span></a></li></ul></section>`);
priceList.classList.add('rsgx-blk'); trust.classList.add('rsgx-blk'); reviewer.classList.add('rsgx-blk');
const com=el('<section class="rsgx-blk"></section>'); com.append(comTop,comRow);
prosRow.classList.add('rsgx-blk'); priceMob.classList.add('rsgx-blk'); verdictRow.classList.add('rsgx-blk'); text.classList.add('rsgx-blk');

// ---------- assemble
everything.forEach(c=>{ if(c.parentElement===art) c.remove(); });
art.append(glance,verdictRow,prosRow,priceMob,forYou,rating,cw,text,facts,cmp,others,priceList,trust,reviewer,com);
if(simWrap) simWrap.remove();

// ---------- clean
$$('script,noscript,iframe,link[rel=preload],link[rel=prefetch],link[rel=dns-prefetch],link[rel=preconnect],link[rel=alternate],link[rel=canonical],link[rel=manifest],link[rel="https://api.w.org/"],link[rel=EditURI],link[rel=shortlink],meta[property],meta[name^=twitter],[id^=AdThrive],[class*=adthrive]').forEach(e=>e.remove());
$$('picture source').forEach(e=>e.remove());
$$('img').forEach(i=>{const s=i.currentSrc||i.src||i.getAttribute('data-src')||''; i.setAttribute('data-rsgx',s); i.removeAttribute('srcset'); i.removeAttribute('sizes'); i.removeAttribute('loading'); i.removeAttribute('data-src'); i.removeAttribute('data-srcset');});
const m=document.createElement('meta'); m.name='robots'; m.content='noindex,nofollow'; document.head.prepend(m);
document.title='Template mock-up: Nike Pegasus 42 review';
return rep.join('; ')||'ok';
}
