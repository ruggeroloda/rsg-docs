# Conversion tracking — Phase 1 brief (cleanup + enrich, comparison pages)

**Created:** 2026-06-29 · **Theme:** `rsg_2022` · **Status:** PLAN (not yet built).
**Goal of the larger thread:** improve conversion (affiliate click-through / RPM) on comparison
pages. Phase 1 is the *measurement foundation* — you can't optimize what you can't segment.

---

## Scope (decided 2026-06-29)
- **Enrich: comparison pages only.** Wire the new event on `single-comparison.php` (+ the redesign
  block, which reuses the same link string). Reviews / guides / top deferred to a later phase.
- **Cleanup: only where we re-wire.** Remove the dead Universal Analytics `onClick="ga('send'…)"`
  from the comparison link strings we touch. **Leave** the global `analytics.js` loader in
  `header.php` for a later phase (it's shared, not a comparison re-wire).

## Why (from the 2026-06-28 audit)
- Affiliate clicks are tracked by **one global listener in `footer.php`** that binds a GA4
  `affiliate_click` to any link whose href contains `…/go/index.php`. Comparison "See deal" buttons
  DO route through `/go/index.php` (DFS `DEEP_LINK`), so they ARE caught.
- **But the event is analytically useless:** it sends hardcoded `{app_name:'myAppName',
  screen_name:'Home'}` — no merchant, shoe, position, or price. Can't tell comparison vs review
  clicks apart, can't attribute to a product/retailer, can't measure revenue/RPM.
- Each button also carries an inline `onClick="ga('send','event','Aff_Link','Comparison-Page',…)"`
  — **dead** (Universal Analytics stopped processing July 2023); the footer's `a.onclick=` assignment
  overrides it anyway. Pure clutter.
- `gtag` is **delay-loaded by WP Rocket**, so a click that is the user's first interaction can race
  the loader and drop the event.

---

## Best-practice synthesis (web research, 2026-06-29)
Cross-checked our plan against current GA4 affiliate-tracking guidance (Analytics Mania, Goho Money,
Commission Factory, MeasureSchool). What it **confirms**, and **three upgrades** we're adopting:

**Confirmed (our plan already matches):** one dedicated `affiliate_click` custom event (NOT GA4's
generic Enhanced-Measurement outbound click — that can't carry merchant/offer/position); register each
param as a custom dimension or it won't report; mark the event a Key event; `link_position` is an
industry-standard recommended param (their examples: `button_top`, `table_row_1`); lowercase_snake
controlled naming to avoid "40 spellings of the same partner"; GA4 under-counts (ad-blockers,
attribution) so use **relative trends**, not absolute totals.

**Upgrade 1 — keep the `/go/` href as the identifier; add `data-*` for enrichment. (Revised
2026-06-29.)** The guides recommend a CSS class instead of href-matching — but that advice targets
sites that link **direct to many merchant domains** (no clean marker, brittle domain regex). We're the
opposite: **every affiliate link routes through one gateway, `…/go/index.php`** — a deterministic marker
with **no false positives and no misses** (it even catches links we never touch). A class would be
*less* complete (must be added to every link; one forgotten = untracked) and no more precise. So we
**keep matching the `/go/` href** as the universal identifier and **layer `data-*` attributes**
(merchant, shoe, position, value) onto the comparison links — the listener reads them when present,
sends the basic event when not. **No class.** (The only thing a class would buy is surviving a future
rename of `/go/` — speculative, not worth the completeness risk.)

**Upgrade 2 — position-aware network sub-IDs: PARKED (decided 2026-06-29).** In theory the `/go/`
sub-ID (`override_tag=rsg-comp-20`) is how a network attributes real commissions, so making it
position-aware would give true **revenue/EPC by position**. **But** of our networks only **Amazon**
supports sub-tracking IDs, and its reports can't be pulled automatically — so the payoff isn't there
yet. **Decision: leave `override_tag` exactly as-is.** Consequence/limitation to accept: GA4 gives us
**clicks/CTR by position**, but **not revenue by position** — so Phase 1 optimization runs on
click-through, under the assumption that more clicks ≈ more revenue (caveat: positions/merchants can
have different EPC). Revisit if/when networks expose automatable sub-ID reporting.

**Upgrade 3 — make the send GTM-ready.** Industry default is to manage this in Google Tag Manager
(no 50-event cap, marketer-editable, "Just Links" trigger handles click-before-navigation). This site
has **no GTM today** (hardcoded gtag). We won't introduce GTM in Phase 1, but we'll route the event
through one thin wrapper (`rsgTrackAffiliate(data)`) so a later GTM migration is a one-function swap
rather than a hunt-and-replace.

## Event schema (the enrichment)
Keep the event name **`affiliate_click`** (continuity). Send these params; orthogonal by design so
the `cta_position` vocabulary stays small and reusable:

| Param | Source | Example | Notes |
|---|---|---|---|
| `page_type` | `get_post_type()` (already the `content_group`) | `comparison` | distinguishes templates |
| `link_position` | **stamped at each render site** (controlled vocab) | `card`, `best_price` | industry-standard name; see vocab below |
| `item_index` | which shoe column | `1`, `2` | multi-shoe pages |
| `shoe` | brand + model | `Brooks Glycerin 23` | the "offer" |
| `merchant` | DFS `MERCHANT_NAME` | `Amazon.com` | the "partner" |
| `destination_url` | the `/go/` deeplink | `…/go/index.php?…` | recommended; what was clicked |
| `value` | DFS display price (number) | `149.95` | **click-intent proxy**, not booked commission — see note |
| `currency` | DFS currency (ISO) | `USD` | enables GA4 revenue/RPM math |
| `cta_element` *(optional)* | logo / price / button | `button` | the price & logo are also affiliate links |

> **`value` is the displayed product price**, used as a proxy for click value — NOT actual affiliate
> revenue. Document so GA4 "revenue" is read as click-intent, not income. *Refinement (later):* set
> `value` to your known average **EPC** (earnings-per-click) instead, so GA4 "revenue" approximates real
> money. Either way, the **affiliate network stays the source of truth for actual commissions** (at the
> aggregate level — per-position revenue isn't available, see Upgrade 2 parked) — GA4 is for on-site
> behavior.

## CTA position naming convention (`link_position`)
**Rule:** lowercase `snake_case`, a **small controlled vocabulary**, **page-type-agnostic**
(page type is a separate param). Position describes *where on the page*, nothing else. New templates
reuse the same words rather than inventing variants. *(Sub-ID/`override_tag` stays unchanged in Phase 1
— see Upgrade 2 parked — so position lives only in the GA4 event for now.)*

Vocabulary (reserve now, use as needed):
`card` · `best_price` · `price_table` · `sticky_bar` · `inline` · `hero`

### Comparison CTA map (single-comparison.php)
| Render lines | Position value | What it is |
|---|---|---|
| 376 / 403 | `card` | "See deal" in each shoe's module/card (also feeds the redesign block, L221) |
| 524 / 536 | `card_alt` *(confirm at build)* | second card block — verify what layout this is before naming |
| 644 / 689 | `best_price` | the "Best price online" section |
| (price/logo anchors 369/396/520/532/639/684) | same position + `cta_element` | clicking price or logo also converts |

Link string is assembled at L165, L197, L628, L673 — all four get replaced by the helper.

---

## Implementation design
1. **New PHP helper** (e.g. `rsg_affiliate_link_attrs($item, $index, $shoe_name, $position)`) returns
   the anchor attributes: `href` (the existing deeplink, **`override_tag` unchanged** — this `/go/` URL
   is also the identifier), `rel="nofollow sponsored"`, `target="_blank"`, and `data-merchant`,
   `data-shoe`, `data-item-index`, `data-value`, `data-currency`, `data-page-type`, `data-link-position`.
   **No class, no `ga('send')`** (dead). Replaces the four `$link_html_1/2 = …` assemblies → single
   source of truth (classic table + redesign block inherit).
2. **Pass position per render site.** Each call names its `link_position` (`card`, `best_price`, …),
   which the helper writes into both `data-link-position` AND the sub-ID. Optional `data-cta-element`.
3. **Rewrite the footer listener** as a **single delegated listener** (lighter than today's
   per-element loop, and catches future JS-injected CTAs like a sticky bar):
   - on click of an affiliate link (`a[href*="/go/index.php"]`), read its `data-*` and call **one
     wrapper** `rsgTrackAffiliate(data)` (so a later GTM swap is one function) which sends
     `affiliate_click` with the full params;
   - **degrade gracefully** — a `/go/` link without `data-*` (every non-comparison link site-wide) still
     fires a basic `affiliate_click` exactly as today (no regression);
   - **harden timing** — guard `typeof gtag`, use `event_callback`/beacon so the event isn't lost
     (mitigates WP-Rocket's delayed gtag; `target="_blank"` also keeps the page alive).
4. **Cleanup (scoped):** the dead inline `ga('send',…)` is gone by virtue of the helper replacing
   the four link strings. Nothing else removed this phase.

## GA4 setup checklist (user-side, lines up 1:1 with params)
1. Admin → Custom definitions → **register event-scoped custom dimensions**: `cta_position`,
   `merchant`, `shoe`, `item_index`, `page_type`, (`cta_element`). *Without this GA4 collects but
   won't report them.*
2. Admin → Events → mark **`affiliate_click` as a Key event**.
3. `value`/`currency` need no registration (GA4 built-ins) — they power revenue/RPM.
4. Validate with **DebugView / Realtime** while clicking on staging.
Reports this unlocks: clicks by page/shoe/merchant/position, CTR (clicks÷views) per comparison,
mobile-vs-desktop, and value/RPM — built in Explorations (Free-form, Funnel, Path) + Looker Studio.

---

## Verification plan
- Lint `ea-php83 -l` + `ea-php74 -l` on every touched file.
- Staging render (cache-bust `?cb=`): all comparison CTAs present, hrefs unchanged (`/go/…`),
  data-attrs populated, classic + redesign both correct.
- **GA4 DebugView**: click each position → one `affiliate_click` with correct params + value.
- **No-regression check**: a non-comparison `/go/` link (e.g. a review page) still fires the basic event.
- Diff live-vs-new to confirm surgical change.

## Files (expected)
- `single-comparison.php` — 4 link-string assemblies → helper; per-site `data-cta-position` stamps.
- `includes/comparison-redesign-block.php` — inherits the helper via `$link_html_1/2`; add position
  stamp on its `card` deal (L221) if not inherited.
- `footer.php` — listener rewrite.
- New: small helper (own include or in an existing comparison include) + this brief's deploy log.

## Rollback
Per-file `*.bak-*` before each edit; restore the backups to revert. Tracking change is JS/PHP only —
no DB, no schema. Reverting the footer listener restores today's basic event.

## Deferred to later phases
- Extend enrichment to review / guide / top templates (same helper).
- Remove the global `analytics.js` UA loader in `header.php`.
- Confirm/rename the `card_alt` block (L524/536).
- **GTM migration** (strategic) — move tag management to Google Tag Manager; the `rsgTrackAffiliate`
  wrapper makes this a one-function swap. Adds a container script but removes future code deploys for
  tracking changes.
- **EPC-based `value`** — replace product-price `value` with measured earnings-per-click once network
  revenue data is workable, so GA4 "revenue" approximates real money.
- **Position-aware sub-IDs (Upgrade 2)** — revisit if/when networks beyond Amazon expose automatable
  sub-ID reporting; would unlock true revenue/EPC-by-position.
- Then: the actual conversion *features* (sticky mobile buy-bar, price+retailer in card, freshness line).

## References (best-practice sources, 2026-06-29)
- Analytics Mania — *Track Affiliate Link Clicks with GA4 (2 methods)*:
  https://www.analyticsmania.com/post/track-affiliate-link-clicks-with-google-analytics-4/
- Goho Money — *GA4 affiliate tracking: UTMs, events, naming* (event/param spec, `link_position`):
  https://gohomoney.com/ga4-affiliate-tracking/
- Commission Factory — *Affiliate attribution in GA4* (GA4 vs network attribution):
  https://blog.commissionfactory.com/affiliate-marketing/conversion-tracking-with-ga4-how-to-successfully-navigate-affiliate-channel-attribution
- MeasureSchool — *How to Track Affiliate Links with GA4 (2025)*:
  https://measureschool.com/track-affiliate-links-with-ga4/
