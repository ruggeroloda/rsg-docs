# DFS Consumer Contract Report — runningshoesguru.com

**Scope:** read-only audit of what the live RSG WordPress install actually consumes from the Datafeed Studio (DFS) backend, to define the contract the rebuilt API must honor.
**Host:** 131.153.143.194 · Webroot `~/public_html` · **Active theme `rsg_2022`** · plugin `dstudio` (active) · WP-CLI 2.9.
**Method:** SSH + grep, no edits, no DB writes.

> **Key facts up front**
> - DFS base URL (`ds_url` option) = `http://45.33.39.133`. Endpoint = `/api/v1/products`. Pricebox = `newrsg-json`.
> - Cache: `dstudio_cache_folder=/dev/shm/`, `dstudio_cache_seconds=21600` (6 h). Geo enabled (`dstudio_geo_enabled=1`).
> - Of the 33 documented fields, **only 14 are actually read** by the active theme. **19 are dead weight.**
> - The new API must emit JSON shaped `{"datafeed":{"items":[ {...} ]}}` — the theme accesses `$json->datafeed->items[N]->FIELD`.
> - **Nothing other than the active theme + the `dstudio` plugin consumes DFS at runtime.** Other matches are inactive themes, backups, the staging copy, and an admin-only test link.

---

## 1) FIELDS ACTUALLY USED

Authoritative method: case-sensitive grep for the field accessed on the decoded item object (`->FIELD`) across the **active** theme `rsg_2022` + active plugins. (A naive case-insensitive grep produces false positives because lowercase WP identifiers — `name`, `description`, `category`, `country_code` — collide with the uppercase DFS field names.)

| FIELD | Used? | file:line example |
|---|---|---|
| MYKEY | **yes** | `include-single-review.php:408` (`->MYKEY`, used as link `target` + GA event id) |
| NAME | **yes** | `includes/widgets/widgets.php:2564` (`<?=$item->NAME?>`) |
| DESCRIPTION | **yes** | `includes/widgets/widgets.php:1771-1772`, `:2566-2568` |
| MERCHANT_NAME | **yes** | `template-parts/guide-boxes.php:166`; GA events theme-wide |
| MERCHANT_LOGO | **yes** | `single-reviews_training.php:843` (`isset(...)` + render) |
| MERCHANT_DESCRIPTION | no | — |
| DISPLAY_PRICE | **yes** | `template-parts/guide-boxes.php:165` (price string) |
| RRP_PRICE | **yes** | `includes/widgets/widgets.php:2448,2451,2497` (struck-through list price) |
| USED_PRICE | no | — |
| DELIVERY_COST | no | — |
| DELIVERY_TIME | no | — |
| DEEP_LINK | **yes** | `template-parts/guide-boxes.php:167` (affiliate `href`; most-referenced field) |
| ORIGINAL_DEEP_LINK | no | — |
| PRODUCT_URL | no | — |
| IMAGE_URL | **yes** | `template-parts/guide-boxes.php:201` (`isset` + `<img>`) |
| IN_STOCK | no | — |
| STOCK_MSG | no | — |
| IS_PREORDER | no | — |
| PREORDER_MSG | no | — |
| IS_CUSTOM | no | — |
| COUNTRY_CODE | no | — (the only `country_code` hits are a WP REST-route param + the outbound SQL, **not** a read of the response field) |
| COUNTRY_NAME | **yes** | `single-reviews_training.php:1377` ("Best offers today in **`<COUNTRY_NAME>`**") |
| CURRENCY_PREFIX | **yes** | `template-parts/guide-boxes.php:165` (price prefix, e.g. `$`/`£`) |
| CURRENCY_CODE | **yes** | `header.php:460` (schema.org `priceCurrency`); `single-reviews_*` "all prices in `<CURRENCY_CODE>`" |
| CATEGORY | **yes** | `includes/widgets/widgets.php:1679` (`if($item->CATEGORY == "Male")` — gender split) |
| SUBCATEGORY | no | — |
| PRODUCT_CATEGORY_ID | no | — |
| DATAFEED_ID | no | — (only appears lowercase `p.datafeed_id` inside an **outbound** SQL string in `includes/shortcodes.php:8`; the returned field is never read) |
| PRICE_COMPARE_SQL | no | — |
| USERFIELD1 | **yes** | `includes/widgets/widgets.php:2443,2492` (`<?=$item->USERFIELD1?>`) |
| USERFIELD2 | no | — |
| USERFIELD3 | no | — |
| USERFIELD4 | no | — |

**14 used:** MYKEY, NAME, DESCRIPTION, MERCHANT_NAME, MERCHANT_LOGO, DISPLAY_PRICE, RRP_PRICE, DEEP_LINK, IMAGE_URL, COUNTRY_NAME, CURRENCY_PREFIX, CURRENCY_CODE, CATEGORY, USERFIELD1.
**19 unused** (safe to drop / deprioritize): MERCHANT_DESCRIPTION, USED_PRICE, DELIVERY_COST, DELIVERY_TIME, ORIGINAL_DEEP_LINK, PRODUCT_URL, IN_STOCK, STOCK_MSG, IS_PREORDER, PREORDER_MSG, IS_CUSTOM, COUNTRY_CODE, SUBCATEGORY, PRODUCT_CATEGORY_ID, DATAFEED_ID, PRICE_COMPARE_SQL, USERFIELD2, USERFIELD3, USERFIELD4.

> **Contract minimum:** the new `/products` response must reliably populate the 14 used fields. `CATEGORY` must use the exact string values `"Male"` / `"Female"` (the theme branches on them literally). `CURRENCY_PREFIX` is the symbol; `DISPLAY_PRICE`/`RRP_PRICE` are the bare numbers (theme concatenates `CURRENCY_PREFIX . PRICE`).

---

## 2) HOW THE API IS CALLED

**Entry point:** plugin `wp-content/plugins/dstudio/dstudio.php`. The theme calls the `dstudio()` wrapper, which buffers `dstudio_pricebox()`. URL built in `dstudio_pricebox()`:

```php
$url = "$ds_url/api/v1/products?search=$query&clickref=$clickref&override_tag=$override_tag"
     . "&referer=$referer&amazon=$title&pricebox=$pricebox"
     . "&redirect_url=$ds_redirect_url$ignorezero";
```

Parameter sources:
- `$ds_url` = option **`ds_url` = `http://45.33.39.133`**
- `search` = urlencoded `ds_query` (the post's `ds_query` meta or the `query` option), with sort appended (`order_field=p.display_price order_direction=asc`, since `ds_sort=plth`) and, when geo is enabled, ` | country_code=<CC>`
- `clickref` = option `ds_clickref` (empty) + per-call `clickref`
- `override_tag` = per-call (e.g. `rsg-road-bottom-20`), else empty
- `referer` = urlencoded current page URL
- `amazon` = urlencoded `ds_title`
- `pricebox` = **`newrsg-json`** (passed explicitly by every theme call)
- `redirect_url` = option **`ds_redirect_url` = `https://www.runningshoesguru.com/go`** (so DFS builds deep links pointing back at the `/go/` hop)
- `ignorezero` = `&ignorezero=1` only if `'ignorezero'` is in the options array

**Live option values (WP-CLI):**

| option | value |
|---|---|
| `ds_url` | `http://45.33.39.133` |
| `ds_redirect_url` | `https://www.runningshoesguru.com/go` |
| `ds_clickref` | *(empty)* |
| `ds_heading` | *(empty)* |
| `ds_pricebox` | `default` |
| `ds_sort` | `plth` (price low→high) |
| `dstudio_cache_folder` | `/dev/shm/` |
| `dstudio_cache_seconds` | `21600` (6 hours) |
| `dstudio_geo_enabled` | `1` |

**Caching:** `dstudio_check_cache()` keys on `md5($url)` → `SERVER_NAME_<md5>.dscache` in `/dev/shm/`, valid for 6 h. Transport is `dstudio_http_get_request()` (cURL, 60 s connect timeout, sends a `Referer` header).

**Per-geo behavior — yes, it is called once per country.** Every theme call passes `'countrycode' => RSG_get_country_code()` (the visitor's geo, defaulted to `US`). Representative call (`include-single-review.php:106`):

```php
$price_json = dstudio(array('query' => $pricebox, 'pricebox' => 'newrsg-json',
                            'countrycode' => RSG_get_country_code()), null);
```

Many templates fire **multiple calls per render** — a men's box and a women's box (`$pricebox` / `$pricebox_women`), and in some files explicit per-country calls (`include-single-review.php:112-131` has GB/US/AU/CA variants — currently commented out, but the men+women double-call is live). `single-reviews_training.php` issues 3+ separate price-box calls per page (lines 830/853/871). **Net: N requests per page = (men + women) × (number of price boxes on the page), each a separate HTTP GET to `/api/v1/products`.**

`countrycode` flows into the query as ` | country_code=<CC>` (appended in `dstudio_pricebox()` when `dstudio_geo_enabled`). The DFS side filters the feed by that country.

There is a **second, lower-traffic call site** in the active theme: `includes/shortcodes.php` → `dfscat_func()` (shortcode `[dfscat]`) builds a **raw SQL** `search` and calls `pricebox=newrsg-json-cat` (note the `-cat` variant), country forced to one of `USA/GBR/DEU`:

```php
$url = $ds_url."/api/v1/products?search=$query&pricebox=newrsg-json-cat&redirect_url=".$ds_redirect_url."&test=".time();
```

The rebuilt API must therefore also accept a raw `SELECT ... FROM products/datafeeds/category_product ...` style `search` payload and a `newrsg-json-cat` pricebox, not just keyword search.

---

## 3) RESPONSE PARSING

`dstudio_pricebox()` simply `echo`s the raw DFS response. The theme captures it via the `dstudio()` output buffer, then normalizes + decodes with **`rsg_datafeed_json()`** (`includes/reviews/functions.php:983`):

```php
function rsg_datafeed_json($price_json) {
    if(strpos($price_json,'Product not found') > 0) return '';      // sentinel string
    $price_json = str_replace("<style type='text/css'></style>", "", $price_json);
    $price_json = str_replace("<!-- DS:No cache folder-->", "", $price_json);
    $price_json = str_replace("<!-- DS:No cache seconds -->", "", $price_json);
    $price_json = utf8_encode(str_replace(',]}}', '] }}', $price_json)); // trailing-comma fix
    if(strpos($price_json,'<<<') > 0) {                              // optional <<<...>>> escaping
        $price_json = explode('<<<', $price_json);
        $price_json = $price_json[0].json_encode($price_json[1]).$price_json[2];
    }
    $price_json = json_decode($price_json);                          // -> stdClass
    return $price_json;
}
```

**Assumptions the new API must not break:**
- Response is JSON decoded to objects; items read as `$json->datafeed->items[0]`, `[1]`, … (numeric, ordered; item `[0]` = "best price" because `ds_sort=plth`).
- A "no results" response must contain the literal string **`Product not found`** (the theme uses `strpos(...,'Product not found')` as the empty sentinel — note the `> 0` quirk: a match at position 0 is missed).
- The decoder tolerates/strips DFS HTML cruft (`<style>` tag, `DS:` cache comments) and a trailing comma before `]}}`. Cleaner output is fine; these are defensive.
- `dstudio_pricebox()` also does a global `str_replace("'", "", $response)` — **single quotes are stripped from the response**, so field values must not depend on apostrophes.

**Rendering** (after decode) maps items straight into markup, e.g. `template-parts/guide-boxes.php:165-167`:

```php
$price        = $price_json->datafeed->items[0]->CURRENCY_PREFIX . $price_json->datafeed->items[0]->DISPLAY_PRICE;
$merchant_name= $price_json->datafeed->items[0]->MERCHANT_NAME;
$link         = $price_json->datafeed->items[0]->DEEP_LINK;     // affiliate href
```

`widgets.php` iterates `$price_json->datafeed->items` (count-bounded), assigns `$item = ...->items[N]`, and emits NAME/DESCRIPTION/CATEGORY/MERCHANT_LOGO/RRP_PRICE/USERFIELD1. `header.php:460` injects `CURRENCY_CODE` into schema.org `Offer` JSON-LD. No numeric reformatting — prices are printed as received (occasionally `round()`-ed).

---

## 4) /go/ CLICK REDIRECT

Standalone PHP (not WordPress), `~/public_html/go/index.php` (active). It does **not** build the affiliate link itself — DFS already embeds the full deep link via `redirect_url=https://www.runningshoesguru.com/go`. `/go/` is a hardened single-hop bouncer to the DFS click handler:

```php
$ds_url = 'http://45.33.39.133';                 // hardcoded (matches ds_url option)
// gatekeeper: HTTP_REFERER host must be runningshoesguru.com / www. — else 301 to homepage
// require id matching ^[A-Za-z0-9_-]{1,64}$  — else 301 home
$allowed_keys = ['id','src','clickref','override_tag','site_id'];   // whitelist; values urlencoded
header("Location: {$ds_url}/index.php?{$final_qs}", true, 302);     // 302 so affiliate cookies refresh
```

**What it expects from DFS:** an `id` token (the click key — corresponds to `MYKEY`) plus optional `src`, `clickref`, `override_tag`, `site_id`. It forwards them to `http://45.33.39.133/index.php`, which is the DFS click/redirect endpoint that resolves to the real merchant URL and records the click.
- **Click tracking is recorded on the DFS side** (`45.33.39.133/index.php`), not in WordPress. `/go/` only filters + forwards; it sets `noindex` + no-store headers and bot/referer gating (added to kill ~153k Search-Console 403s).
- Variants present but **not** the live router: `go/index_medium.php` (adds canonicalization), `go/index_old.php` (legacy 403-on-bad-referer, lists historical DFS IPs `204.197.246.27`, `162.211.87.169`, `45.79.73.89`, `45.33.63.168`).

> **Rebuild implication:** the new DFS must keep an `/index.php?id=<MYKEY>&clickref=&override_tag=&site_id=&src=` click endpoint at the same base, OR `/go/`'s hardcoded `$ds_url` must be repointed. Deep links inside the feed are minted by DFS using the `redirect_url` param, so the contract is: **feed `DEEP_LINK` → `https://www.runningshoesguru.com/go/?id=…` → `<DFS>/index.php?id=…`**.

---

## 5) MSRP TODAY (`get_geo_rrp()`)

MSRP / list price shown next to the live price is **not** sourced from DFS `RRP_PRICE` in the main review templates — it comes from **ACF fields on the WordPress post**, via `get_geo_rrp()` (`functions.php:1867`):

```php
function get_geo_rrp($ID = null) {
    global $post; if(!$ID) $ID = $post->ID;
    $rrp = NULL;
    if(RSG_get_country_code() != "GB" && RSG_get_country_code() != "CA" && RSG_get_country_code() != "AU")
        $rrp = get_field('rrp_us', $ID);              // default
    else
        $rrp = get_field('rrp_'.strtolower(RSG_get_country_code()), $ID);   // rrp_gb / rrp_ca / rrp_au
    return $rrp;
}
```

- ACF fields: **`rrp_us`** (default) and country variants **`rrp_gb`, `rrp_ca`, `rrp_au`** only.
- **Non-US fallback confirmed:** any country that is not GB/CA/AU resolves to `rrp_us`. And `RSG_get_country_code()` itself collapses any country outside a 17-country allowlist to `"US"`, so e.g. DE/FR/IT visitors get `rrp_us`. Only GB, CA, AU have their own RRP field.
- Callers: `single-top.php:287/702`, `single-best.php:585`, `single-guide.php:289/704`, `single-reviews_{training,trail,road,gps}.php`, `include-single-{gps,review-trail}.php` (all `$rrp = get_geo_rrp(...)`). Comparison tables hardcode `get_field('rrp_us', …)` with a literal `US$` prefix.
- The DFS `RRP_PRICE` field **is** still consumed, but only in the widget price-list (`widgets.php:2448/2451/2497`, struck-through). So MSRP has two independent sources: ACF (`get_geo_rrp`) for the main review UI, DFS `RRP_PRICE` for the widget list. The new API still needs to emit `RRP_PRICE` for the widgets.

---

## 6) OTHER DFS COUPLING (whole webroot)

**Active runtime consumers of DFS = exactly two:** the active theme `rsg_2022` and the `dstudio` plugin. Everything else found is inactive, backup, staging, or non-runtime:

| Match | Location | Status |
|---|---|---|
| `45.33.39.133` runtime | `go/index.php`, `go/index_medium.php`, `go/index_old.php`, `ds_url` option | **active** (`go/index.php` is the live router) |
| `45.33.39.133` admin link | `rsg_2022/includes/reviews/functions.php:232` — "Amazon.com TEST" button to `…/admin/views/apisearch.php` | active theme but **editor-only**, not a feed consumer |
| Legacy DFS IPs `204.197.246.27 / 162.211.87.169 / 45.79.73.89 / 45.33.63.168` | `go/index_old.php`, `rsg_2022/includes/widgets/widgets.php`, `_backupthemes/*` | historical/commented; `index_old.php` not wired up |
| `/api/v1/products` callers | plugin `dstudio.php`; `rsg_2022/includes/shortcodes.php` (`dfscat`) | **active** |
| `/api/v1/images` | `rsg_2022/includes/remove_bg.php:125` → `clippingmagic.com` | **not DFS** (background-removal SaaS) |
| `datafeed` / `dstudio` strings | `themes/rsg_2018`, `themes/rsg_2019`, `_backupthemes/*`, `jfx92wmcr3-staging.onrocket.site/*` | **inactive** themes / staging mirror |
| `wflogs/config-synced.php` | Wordfence | incidental string match, no DFS dependency |

**Cron / scripts that touch DFS** (all in `dstudio.php`, and they push *to* DFS, not read from it):
- `dfs_cron_export_products` (hourly) + on every `save_post` → `do_dfs_export_products()` writes `wp-content/plugins/dstudio/export.cvs` (product keyword list).
- `dfs_cron_export_brands` (hourly) + `save_post` → `do_dfs_export_brands()` writes `export_brands.cvs`.
- A third scheduled event `dfs_export_products` exists in WP-Cron but **no matching `add_action` hook is in the current code** — orphaned/legacy; it fires nothing. Worth cleaning up.
- These CSVs are the **reverse-direction contract**: RSG exports its catalog so DFS knows what to fetch. The rebuild must keep ingesting `export.cvs` / `export_brands.cvs` (or replace that handshake).

**Confirmation:** no second live API base, and **nothing outside the active theme + `dstudio` plugin consumes the DFS product feed at runtime.** The `rsg_2018`/`rsg_2019` hits and the `jfx92wmcr3-staging.onrocket.site` copy are dormant duplicates (active theme = `rsg_2022`, confirmed via `wp option get template/stylesheet`).

---

## What the new DFS API must emit (summary contract)

1. `GET /api/v1/products?search=<keyword|raw SQL>&pricebox=newrsg-json[-cat]&clickref=&override_tag=&referer=&amazon=&redirect_url=https://www.runningshoesguru.com/go&country_code=<CC>[&ignorezero=1]`
2. Body: `{"datafeed":{"items":[ … ]}}`, items **sorted price-ascending**, `[0]` = best price.
3. Each item must populate the **14 used fields**: `MYKEY, NAME, DESCRIPTION, MERCHANT_NAME, MERCHANT_LOGO, DISPLAY_PRICE, RRP_PRICE, DEEP_LINK, IMAGE_URL, COUNTRY_NAME, CURRENCY_PREFIX, CURRENCY_CODE, CATEGORY, USERFIELD1`. (`CATEGORY ∈ {"Male","Female"}`; `DEEP_LINK` minted through `redirect_url` → `/go/`.)
4. Empty result = response containing the string `Product not found`.
5. Keep the DFS click endpoint at `<base>/index.php?id=<MYKEY>&clickref=&override_tag=&site_id=&src=` (where `/go/` forwards) and keep ingesting `export.cvs` / `export_brands.cvs`.
6. The other 19 fields can be dropped without touching the active theme.
