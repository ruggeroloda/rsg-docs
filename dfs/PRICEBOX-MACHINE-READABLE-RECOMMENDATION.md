# Price box machine-readability — recommendation (Phase 1 result)

_8 Nov 2026. Brief: `BRIEF-PRICEBOX-MACHINE-READABLE.md`. Status: **approved and built on staging (review 48721, `?dfs=1`) 8 Nov; Ruggero: "I like it all"**. Read "As built" first: two points changed during the build (rows not a list; Amazon stamp not `<time>`)._

## As built (8 Nov 2026) — the reference for every other template
- **Rows:** plain `<a class="rsg-dfs-row">` links, one per line in the HTML (a `\n` between rows), no `<ul>`/`<li>`, no hidden headings. Reason: trafilatura (a boilerplate remover used in many crawl/AI pipelines) dropped the whole list as navigation when rows were `<li>` under a heading; plain rows survive (tested every variant).
- **Every link reads on its own:** `Men: Nike from $87.97 list price $149.95 41% off See deal` / band: `Men: from $87.97 41% off at Nike`. Gender (`rsg_prices_who()`), shop name (next to the logo, logo `alt=""`) and "list price" (inside `<s>`, so it hides with the struck price) are `<span class="rsg-vh">` text. The "›" after "See deal" is not read out.
- **Dates:** "Prices checked 7–8 Nov" on its own line under "Prices for … (USD)", fixed one-line height, `<time datetime="…Z">` (oldest–newest price time of the shown offers of that country). The Amazon row stamp "as of 8 Nov, 08:05 UTC" stays **plain text** (trafilatura drops `<time>` content; Amazon policy wants a date/time stamp next to the price when prices refresh less than hourly, which this is).
- **"No offers in X right now."** is output only when a list is empty (PHP and JS).
- **Contrast** inside the box: grey `#6c757d`, price green `#1e7e34`, white small text in the blue band. Orange buttons unchanged (Ruggero).
- **Schema** byte-identical to before.
- Function changes: `rsg_prices_row_inner( $o, $blk, $logos, $g )` and `rsg_prices_best_inner( $o, $blk, $g )` take the gender; new `rsg_prices_vh()`, `rsg_prices_who()`, `rsg_prices_time()`, `rsg_prices_checked()`; JS twins `vh()`, `who()`, `time()`, `checked()`.
- **Results (staging 48721):** script repaint = server HTML (23/23 elements); W3C 0 messages; axe 45 → 15 (all orange buttons); CLS load ~0.001, swaps 0, box heights identical across US/DE/men/women; trafilatura and html2text both give one complete line per offer (before: no shop names, glued numbers, 2–6 false "No offers"); HTML +~1 KB per box. Screenshots: `_work/pricebox/compare-*.png`.
- **8 Nov 15:47 UTC addition: shoe name in every link** (hidden text, `data-n`): "Nike Vomero Plus, men: Amazon from $152.97 … See deal", so offer lines stay unambiguous on comparisons and guides (trafilatura drops headings). DEPLOY-LOG "Shoe name in every offer link".
- **Checks to repeat on every new template:** `app/tools/pricebox/val.js` (mirror, axe, names, states, CLS, heights), `shots.js` (before/after), `extract.py` (text extractors). Run from the cloud container (Playwright + `npm i axe-core@4.10.2`; it reaches staging).


## Findings in one paragraph
Search engines are fine: RSG can only get **product snippets** (merchant listings need the shopper to buy on our page), Google explicitly allows one `AggregateOffer` for multiple sellers, and the staging JSON-LD passes the Rich Results Test with **0 errors, 0 warnings**. No peer (RunRepeat, RTINGS, Tom's Guide, OutdoorGearLab, Doctors of Running, BITR) marks up offers at all; most don't even have prices in their HTML. The weak point is the **visible text**, which is what AI engines (ChatGPT/Perplexity appear to treat even JSON-LD as plain text) and screen readers use. Extracted text today has **no seller names** (logos are images), a **false "No offers in United States right now."** after every list, **no men/women labels**, glued numbers (`$87.97$149.95 -41%See deal`), and a vague "updated 7 Nov" next to rows dated 8 Nov. axe: only colour contrast fails (45 nodes).

## Decisions (Ruggero, 8 Nov)
- "41% off" visible instead of "-41%": **yes**.
- Orange buttons (white on `#fb8832`, 2.42:1): **keep as is for now** (theme-wide; theme rebuild).

## Recommendation (what Phase 2 builds)
All changes in the one component: `rsg_prices_price_html()` / `rsg_prices_row_inner()` / `rsg_prices_best_inner()` / `rsg_prices_box()` and their JS twins. Same look except "41% off" and slightly darker grey/green.

| # | Change | Why |
|---|---|---|
| 1 | Rows become a list: `<ul class="rsg-dfs-ul"><li><a class="rsg-dfs-row">…</a></li></ul>` (whole row stays one link; reserved empty rows stay `aria-hidden`) | One offer per line for extractors; "list, 3 items" for screen readers (WCAG H77). No visual change. |
| 2 | Seller as text: `<span class="rsg-vh">Nike</span>` beside the logo; logo `alt=""` (no double reading). No logo = visible name, as today | Seller survives text extraction |
| 3 | Hidden heading per list: `<h3 class="rsg-vh">Men's prices in United States</h3>` (JS updates country/gender); "Men:" / "Women:" hidden prefix on the best-price links (head band, sticky bar, comparison buttons) | Gender is explicit |
| 4 | "No offers in X right now." output **only when the list is empty** (PHP + JS); overlay as today, so no layout shift | Removes the false statement |
| 5 | Price text: `from <b>$87.97</b>` · `<span class="rsg-vh">list price</span> <s>$149.95</s>` · tag **"41% off"** · real spaces between parts; "›" hidden from screen readers (`content:" \203A" / ""`) | Reads as "Nike from $87.97, list price $149.95, 41% off, See deal" |
| 6 | Dates in `<time datetime="…Z">`: Amazon "as of 8 Nov, 08:05 UTC"; meta "updated 7 Nov" → **"prices checked 7–8 Nov"** (oldest–newest shown offer of that country; one date if equal; computed in PHP/JS, contract unchanged) | Unambiguous and honest freshness |
| 7 | Contrast inside the box only: grey `#868e96` → `#6c757d` (4.7:1), green `#28a745` → `#1e7e34` (5:1), header-band small text full white instead of 85% (≈5:1 on `#0f70c4`) | Fixes 30 of 45 axe failures; the 15 left are the orange buttons (deferred) |
| 8 | Schema: **unchanged** (one USD `AggregateOffer`, men + women, US only). No nested `Offer`s, no `priceValidUntil`/`availability` | Already valid and ahead of peers; no Google gain; avoids a second copy of prices to keep in sync. Revisit nested `Offer`s (seller + price only) if Bing/Copilot data later shows value. |
| — | Not doing: `<data value>` on prices (no consumer), table layout (breaks whole-row link), changes to the 3 duplicate box copies | Simplicity |

Size: about +0.5 KB HTML per box. Zero layout shift (fixed row heights; hidden text takes no space). "41% off" is ~2 characters wider: check the sidebar.

## Test plan (before = 8 Nov baseline above)
1. **Lint**: `ea-php83 -l` and `ea-php74 -l` on the PHP; `node --check` on the JS.
2. **Mirror**: on US, server HTML == JS repaint (innerHTML compare per box) → no text change on repaint.
3. **W3C** (boxes): valid, as before.
4. **Rich Results Test** (code mode) + **Schema Markup Validator** (code mode): Product snippet valid, 0 errors, 0 warnings; offers identical to before.
5. **axe** (WCAG 2.2 AA) on all boxes: only the 15 known orange-button contrast nodes remain; 0 other violations.
6. **Text extraction** (trafilatura + html2text), before vs after: every offer line has seller + price + "list price" + "% off"; gender headings present; 0 "No offers" when offers exist.
7. **Accessible names** (Chrome a11y tree): row = "Nike from $87.97 list price $149.95 41% off See deal".
8. **States**: country switch to DE (headings and dates update), Men/Women switch, a shoe with an empty list ("No offers" shown), a stale Amazon offer ("Check price").
9. **CLS**: layout-shift observer during load + country/gender swaps = 0; mobile + desktop.
10. **Visual**: before/after screenshots (comparison box, sidebar, mobile, sticky bar, comparison table); only diffs = "41% off", darker grey/green.

Then: deploy log, DECISIONS line, commit to `ruggeroloda/rsg-dfs`, `theme/README` note; box rolls onto the other templates (DFS 6.3 rest) in its final form.

## Logged separately (not this brief)
- DFS matcher: women's Nike US link for 48721 points to a **wide** version (`…womens-road-running-shoes-wide…`).

Sources: Google product snippets (updated 8 Sep 2026), merchant listings, structured data guidelines, AI features + AI optimization guide (10 Jul 2026); schema.org AggregateOffer; W3C Understanding SC 2.4.4; P. J. Adam strikethrough demo; SER/Pulse report on ChatGPT/Perplexity and JSON-LD (Feb 2026); Search Engine Land on Bing/Copilot using schema.
