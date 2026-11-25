# List prices (MSRP) per currency — design review and US test run, 19 Nov 2026

**Status:** design reviewed with Ruggero (19 Nov); nothing built. Next: Ruggero's OK on the 10 US proposals below, then build the nightly check. Non-US currencies after Black Friday (non-US is low priority, DECISIONS 18 Nov).
Replaces the approach in `../theme/MSRP-LOCAL-PRICING-BRIEF.md` (24 Jun: per-brand scrapers of brand sites) for the shoes that have offers.

## What was checked (production, 19 Nov)
- WordPress list prices: `rrp_us` on all 429 shoes; `rrp_gb`, `rrp_de`, `rrp_au`, `rrp_ca` empty everywhere.
- A list price is only needed where a shoe has a local offer (shoes without one show "Check price" on Amazon). Shoes with a local offer in the last 7 days: AUD ~100, CAD 110, EUR ~77, GBP 47.
- 5 currencies cover all 11 countries: USD, CAD, GBP, AUD, EUR (RW SE sells in EUR) = the 5 existing fields (`rrp_de` = the EUR price). The DFS looks up `rrp_<country>` (`match._judge`, `prices.py`), so FR/IT/ES/IE/GR/SE would never use `rrp_de`: the lookup must go by currency.
- Nike DE and Nike IT: 589 of 589 shared running items have the same list price (one EUR price is safe at least for Nike).
- List prices in the feeds: Running Room CA "Retail Price" (used), Puma UK (used), **Hoka UK `rrp_price` and Nike UK/DE/IT `product_price_old` on every row but not read** (we read the empty `store_price`; Nike UK Vomero 18 = £144.99), adidas AU none, Running Warehouse (all countries) none: its price is the selling price. Mizuno US: "Original Price" mostly empty.

## Design after review (simplicity, one source of truth)
1. WordPress stays the only store: the 5 existing fields, one per currency.
2. DFS looks up the list price by currency (one small map currency → field); a new currency later = one field + one line.
3. Read the list-price columns the feeds already have (Hoka UK, Nike UK/DE/IT: settings only).
4. Nightly check: evidence only from shops whose feed has a real list-price column (Zappos, Nike, Puma, adidas, Brooks, New Balance, Hoka; not Running Warehouse, not Mizuno). Each shop's most common list price for the shoe; if higher than WordPress, stable 3 days and ≤ 15 % higher → **proposal** on the Shoes screen. Never lower. Nothing is written without approval (list prices inherit matching errors: kids rows, Speedgoat 2).
5. Gaps (RW EU/AU shoes without a brand feed) stay "no list price" until a problem is measured.

Dropped from the first draft: a live computed price chain in DFS (second source of truth), "two shops must agree" (almost never true: one list-price shop per shoe), hand lookups for ~160 RW EU/AU values.

## US test run (read-only, feed files 17–19 Nov, Amazon excluded)
Proposals (raise only):

| Shoe | WordPress | Proposed | Evidence |
|---|---|---|---|
| Hoka Bondi 9 | $170 | $175 | Hoka, Zappos (RW $174.95) |
| Hoka Clifton 10 | $149.95 | $155 | Zappos; Hoka adult rows $155 |
| Nike Vomero 18 | $155 | $165 | Nike (Zappos/RW: some colours $165, most $155) |
| Nike Vaporfly 4 | $260 | $270 | Nike |
| Skechers Aero Burst | $139.95 | $155 | Zappos (RW $154.95) |
| On Cloudsurfer Trail | $159.95 | $169.95 | Zappos |
| On Cloudvista 2 | $149.95 | $160 | Zappos |
| Salomon Speedcross 6 | $139.95 | $150 | Zappos |
| Saucony Omni 22 | $139.95 | $150 | Zappos |
| Saucony Peregrine 15 | $139.95 | $145 | Zappos |

To check by hand: Speedgoat 2 (Hoka sells "Speedgoat 2 Shoes" at $128/$160: re-release or wrong match), Wave Rider 26 (Mizuno $150 is a selling price), Altra Experience Flow and adidas Prime X3 Strung (RW only).
List prices differ by colour (Vomero 18): raising to the higher common price keeps the cheaper colours.

## Found on the way
Hoka US kids shoes were shown as adult offers (Clifton 10, Mach 6, Rincon 4): fixed live 19 Nov (`logs/DEPLOY-LOG.md`).

## Correction, 25 Nov 2026: Running Room CA has no list price
Checked on production (matches of 25 Nov): in all 294 Running Room rows matched to our shoes, "Retail Price" equals "Sale Price" (Clifton 10 men's: both CAD 110, US list $149.95). For 48 of 110 shoes the highest Running Room "retail" price is below the US list price in dollars. So the column is the current selling price, not a list price: it cannot fill `rrp_ca`, and `list_src = feed` on CA offers carries no information (no CA deal signal is possible from it). In AvantLink's programme list Running Room's feed lacks the "Product Sale Prices" flag that MEC, Valhalla Pure Outfitters and Icebug have. CAD list prices need another source: a feed with a real retail column (MEC, if approved; checked only after joining) or the highest price seen over a longer period (history for CA starts 18 Nov; proposals only, never automatic).
Also 25 Nov: Hoka UK and Nike UK still map `list_price` to the empty `store_price` (filled columns per the 19 Nov check: `rrp_price`, `product_price_old`). AvantLink Canada (affiliate 222669, website 279417, key in `_private/avant_link_ca.txt`): 47 programmes, joined only Running Room; no Nike, no adidas.
