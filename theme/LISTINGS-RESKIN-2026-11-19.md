# /reviews/ listings reskin — 19 Nov 2026

**DONE — live 19 Nov 2026 15:25 UTC** (theme `04479d8`, tag `live-20261119-152531`; Ruggero: "deploy to live"). Design (approved on the canvas): https://claude.ai/artifact/BqnM8FvA5hEhvTLofUf94s. Code: `rsg_2022/includes/reviews/listing.php`, `reviews_search.php`, CSS 3.8–3.9 in `css/rsg-skin.css`.

## What changed (hub, brand, type, family listings + the search pages)
- **Card** `rsg_review_card()`: test photo (240×180 desktop, full width on phones; cut-out only when no photo), name from the title, one-sentence summary from the Verdict field without "The X is" (`rsg_card_summary()`, 2nd sentence when the 1st is < 70 chars), specs line under the photo (weight · drop · tested by · date), expert score box, price button (box `card`), Read review. Tags: Latest version / Older version (only lines with 2+ reviews, from the search index), Editor's choice / Recommended. Root `div.panel.rsg-card` (not `<article>`: extractors keep only the first), hidden "Expert score: N/10" under the name.
- **Page**: breadcrumb, same H1 and intro texts as before, full-width filter bar of menus only (Brand, Line on brand pages, Type, Find a shoe in this list — Ruggero: no chips), count line, list, 300 px+ right column from 1,140 px (empty: Raptive sticky sidebar), "Browse reviews" link block under the list (the old left column's links: lines, types within the brand, brands within the type; main brands shown, the rest under "More brands").
- Raptive hooks kept: cards = `.col-lg-9 > .panel`; sidebar = exact class `col-lg-3 d-none d-lg-block`.

## Checks (19 Nov)
- **Raptive spots per page view** (Playwright from Ruggero's home connection; staging HTML served under the live URL so Raptive serves): Pegasus desktop 9 → 6, hub desktop 9 → 7, Pegasus phone 7 → 6; confirmed on live after the deploy (6 / 7). Cause: shorter cards → fewer in-list ads (Raptive keeps a minimum distance). Upper bound ~$40/month (16k desktop listing views × ~2.5 fewer ads × ~$1.1 per 1,000). Ruggero chose taller cards over 20 per page.
- **Text extraction** (trafilatura): names 10/10 and scores 10/10 on 7 listings (= live before); prices 0 (were 0–4 of 10); JSON-LD unchanged.
- **Links**: every listing link of the old pages is present (hub 44/44, Nike 48/48; Pegasus 48 vs 40, Saucony Road 31 vs 25).
- HTML: 0 html5 parse errors, no duplicate ids; pages 20 % lighter (hub 206 → 161 KB).

## Baseline for the watch (16–18 Nov, humans, 2,416 listing views)
Desktop 67 %. 31 % landings. After a listing view: open a review 17 %, another listing 41 %, leave 36 %, other 7 %; shop click from the list 1.1 %. Compare the same table ~3 Dec, plus Raptive listing RPM (2 weeks).

## How to re-run the ad check
On the Mac's Linux shell (device_bash): `pip install --user playwright && python3 -m playwright install chromium`; missing lib: `apt-get download libxdamage1` → `dpkg-deb -x` into `~/pwlibs`, `export LD_LIBRARY_PATH=~/pwlibs/usr/lib/aarch64-linux-gnu`. Script: launch with `--disable-blink-features=AutomationControlled`, hide `navigator.webdriver`, scroll the page, count `div[id^="AdThrive"]`. Cloud browsers get no Raptive ads.

## Open
- **"Browse reviews" block inside the list column** (Ruggero, 19 Nov: so Raptive's sidebar column runs down beside it): theme `33e495b` on **staging** 19 Nov 17:13 UTC, waiting for "deploy to live". Checked (Playwright, staging): block inside `.col-lg-9` at 1,440/1,200 (760 px wide, 3 link columns instead of 4, 476–583 px tall), sidebar column now ends with the block (hub: +524 px vs live); 1,024 px and phones unchanged layout (no sidebar), no sideways scroll, no JS errors. Rollback = deploy `live-20261119-152531`.
- Cleanup: `print_review_block_2020()` and the `.rsg-rc` CSS (11 rules) are unused now; template leftovers (`$banner_position`, `$ads_2013_position`, `showposts`).
- Menus navigate on change: keyboard users in some browsers trigger it while arrowing (WCAG 3.2.2); option: a "Show" button for keyboards.
- Tags `live-20261119-152531` + today's staging tags are on the Mac only (cloud push of tags refused by the proxy; `main` pushed).
