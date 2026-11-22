"""Mock-up: 'Compare it with' block on live reviews, injected in a headless browser (live page only read).
Block sits right before the rating breakdown (after pros/cons; on phones after the phone price block)."""
import asyncio, re, json, sys
from playwright.async_api import async_playwright

BLOCK = re.compile(r"adthrive|doubleclick|googlesyndication|google-analytics|googletagmanager|youtube|ytimg|amazon-adsystem|criteo|pubmatic|rubicon|3lift|openx|adnxs|adsrvr|gumgum|yahoo|smartadserver|adsafeprotected|typekit|t\.runningshoesguru\.com|privacymanager|optable|frvr")
U = "https://www.runningshoesguru.com/wp-content/uploads/"
PAGES = {
  "pegasus-42": ("https://www.runningshoesguru.com/reviews/road/nike-pegasus-42-review/", "Pegasus 42", [
      ("Nike Vomero 18", "/comparison/nike-pegasus-vs-nike-vomero/", U + "2025/04/nike-vomero-18-white-background-300x225.png"),
      ("Nike Pegasus Premium", "/comparison/nike-pegasus-premium-vs-nike-pegasus/", U + "2025/02/nike-pegasus-premium-white-bg-300x225.png"),
      ("Nike Winflo 11", "/comparison/nike-winflo-vs-nike-pegasus/", U + "2024/05/nike-winflo-11-white-background-300x225.png"),
      ("Nike Structure 25", "/comparison/nike-pegasus-vs-nike-structure/", U + "2023/09/nike-structure-25-white-background-300x225.png")]),
  "adrenaline-gts-25": ("https://www.runningshoesguru.com/reviews/road/brooks-adrenaline-gts-25-review/", "Adrenaline GTS 25", [
      ("Brooks Ghost 17", "/comparison/brooks-ghost-vs-brooks-adrenaline/", U + "2025/05/brooks-ghost-17-white-background-300x225.png")]),
}
CSS = """
.rsg-cw{margin:40px 0 8px}
.rsg-cw-list{list-style:none;margin:16px 0 0;padding:0;display:grid;grid-template-columns:1fr;column-gap:24px;border-top:1px solid var(--rsg-line)}
@media (min-width:768px){.rsg-cw-list.n2{grid-template-columns:1fr 1fr}}
.rsg-cw-list li{border-bottom:1px solid var(--rsg-line)}
.rsg-cw-list a{display:flex;align-items:center;gap:14px;min-height:60px;padding:6px 0;color:var(--rsg-ink);text-decoration:none}
.rsg-cw-list a:hover .rsg-cw-n{text-decoration:underline}
.rsg-cw-list img{width:64px;height:48px;object-fit:contain;flex:none}
.rsg-cw-t{flex:1;font-size:16px;line-height:1.3}
.rsg-cw-vs{color:var(--rsg-muted);margin-right:4px}
.rsg-cw-n{font-weight:700;color:var(--rsg-blue)}
.rsg-cw-a{color:var(--rsg-blue);font-size:20px;line-height:1;flex:none}
"""

VAR = {}
VAR["A"] = """
.rsg-cw{margin:40px 0 8px}
.rsg-cw-list{list-style:none;margin:14px 0 0;padding:0;display:grid;grid-template-columns:1fr;gap:8px 16px}
@media (min-width:768px){.rsg-cw-list.n2{grid-template-columns:1fr 1fr}}
.rsg-cw-list a{display:flex;align-items:center;gap:12px;min-height:64px;padding:6px 14px 6px 8px;background:var(--rsg-paper);border-radius:var(--rsg-radius);color:var(--rsg-ink);text-decoration:none}
.rsg-cw-list a:hover{background:var(--rsg-blue-wash)}
.rsg-cw-list img{width:64px;height:48px;object-fit:contain;flex:none;mix-blend-mode:multiply}
.rsg-cw-t{flex:1;font-size:16px;line-height:1.25}
.rsg-cw-vs{color:var(--rsg-muted);margin-right:4px}
.rsg-cw-n{font-weight:700}
.rsg-cw-a{color:var(--rsg-blue);font-weight:700;font-size:15px;white-space:nowrap;flex:none}
"""
VAR["B"] = """
.rsg-cw{margin:40px 0 8px}
.rsg-cw-list{list-style:none;margin:14px 0 0;padding:0;display:grid;grid-template-columns:1fr;column-gap:24px;border-top:1px solid var(--rsg-line)}
@media (min-width:768px){.rsg-cw-list.n2{grid-template-columns:1fr 1fr}}
.rsg-cw-list li{border-bottom:1px solid var(--rsg-line)}
.rsg-cw-list a{display:flex;align-items:center;gap:12px;min-height:64px;padding:6px 0;color:var(--rsg-ink);text-decoration:none}
.rsg-cw-list img{width:64px;height:48px;object-fit:contain;flex:none}
.rsg-cw-t{flex:1;font-size:16px;line-height:1.25}
.rsg-cw-vs{color:var(--rsg-muted);margin-right:4px}
.rsg-cw-n{font-weight:700;color:var(--rsg-blue);text-decoration:underline;text-underline-offset:3px}
.rsg-cw-a{flex:none;width:32px;height:32px;border-radius:50%;background:var(--rsg-blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;line-height:1}
"""
def block_html(shoe, rows, v="A"):
    lis = "".join(f'<li><a href="{href}"><img src="{img}" alt="" width="64" height="48" loading="lazy"><span class="rsg-cw-t"><span class="rsg-cw-vs">vs</span> <span class="rsg-cw-n">{name}</span></span>{('<span class="rsg-cw-a">Read &rsaquo;</span>' if v=="A" else '<span class="rsg-cw-a" aria-hidden="true">&rsaquo;</span>')}</a></li>' for name, href, img in rows)
    n2 = " n2" if len(rows) > 1 else ""
    return f'<section class="rsg-cw" aria-label="{shoe} head to head"><h3 class="rsg-sub-h">{shoe} head to head</h3><ul class="rsg-cw-list{n2}">{lis}</ul></section>'

INJECT = """([html, css]) => {
  const st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);
  const h = [...document.querySelectorAll('h3.rsg-sub-h')].find(e => /Rating breakdown/i.test(e.textContent));
  let anchor = h; const p = h.previousElementSibling; if (p && p.tagName === 'HR') anchor = p;
  const box = document.createElement('div'); box.innerHTML = html; anchor.parentNode.insertBefore(box.firstChild, anchor);
  return true;
}"""

async def shot(b, key, w, mode, v="A"):
    url, shoe, rows = PAGES[key]
    ctx = await b.new_context(viewport={"width": w, "height": 900}, device_scale_factor=2 if w == 390 else 1)
    await ctx.route("**/*", lambda r: r.fulfill(status=299) if "/wp-json/rsg-dfs/v1/" in r.request.url else (r.abort() if BLOCK.search(r.request.url) else r.continue_()))
    pg = await ctx.new_page()
    await pg.goto(url, wait_until="load", timeout=90000)
    await pg.wait_for_timeout(1200)
    if mode == "after":
        await pg.evaluate(INJECT, [block_html(shoe, rows, v), VAR[v]])
    # load lazy images around the area
    for y in range(0, 7000, 600):
        await pg.evaluate(f"window.scrollTo(0,{y})"); await pg.wait_for_timeout(120)
    await pg.wait_for_timeout(1500)
    box = await pg.evaluate("""() => {
      const pros = [...document.querySelectorAll('h3.rsg-sub-h')].find(e => /pros/i.test(e.textContent));
      const rat = [...document.querySelectorAll('h3.rsg-sub-h')].find(e => /Rating breakdown/i.test(e.textContent));
      const a = pros.getBoundingClientRect().top + scrollY - 40, z = rat.getBoundingClientRect().top + scrollY + 260;
      return {y: a, h: z - a};
    }""")
    out = f"/root/mk/cw2-{key}-{mode}{v}-{w}.png"
    await pg.screenshot(path=out, full_page=True, clip={"x": 0, "y": box["y"], "width": w, "height": box["h"]})
    await ctx.close()
    return out

async def main():
    async with async_playwright() as p:
        b = await p.chromium.launch(channel="chrome")
        for v in ("A", "B"):
            for w in (390, 1366):
                print(await shot(b, "pegasus-42", w, "after", v), flush=True)
        await b.close()
asyncio.run(main())
