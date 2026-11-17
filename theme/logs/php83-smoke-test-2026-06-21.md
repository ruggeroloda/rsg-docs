# PHP 8.3 Smoke Test — Staging (`rsg_2022`)

**Date:** 2026-06-21
**Target:** `https://jfx92wmcr3-staging.onrocket.site` (web PHP **8.3.22**, LiteSpeed)
**Method:** Enabled `WP_DEBUG` + `WP_DEBUG_LOG` (display off) on staging, crawled one cache-busted URL per template type (forcing fresh PHP render, not LiteSpeed cache), captured per-page `debug.log` delta, then restored config. All test artifacts removed; `WP_DEBUG` back to `false`.

> Key point: the earlier "homepage = 200, clean" result was **LiteSpeed serving a cached copy**. With a cache-buster forcing fresh execution, real PHP 8.3 errors surface. Public visitors hitting cached URLs currently get 200s, but any cache purge / new URL / logged-in view will hit these.

## Result by template

| Template | HTTP | Verdict |
|---|---|---|
| home | **500** | ❌ Fatal |
| post | 200 (via 301) | ✅ |
| page | 200 | ✅ |
| guide | 200 | ✅ |
| review | 200 | ✅ |
| reviews_road | 200 | ✅ |
| reviews_trail | **500** | ❌ Fatal |
| reviews_gps | 200 | ✅ |
| reviews_training | **500** | ❌ Fatal |
| gps | 200 (via 301) | ✅ |
| top | 200 | ✅ |
| best | 404 | ⚪ by design (not publicly_queryable) |
| comparison | 200 | ✅ |
| content | 200 | ✅ |
| noindex | 200 | ✅ |
| external_product | 404 | ⚪ by design (not publicly_queryable) |
| tax: category | 200 | ✅ |
| tax: brands | 200 | ✅ |
| tax: shoes_type | 200 | ✅ |
| tax: shoes_family | 200 | ✅ |
| tax: content_category | 200 | ✅ |
| tax: gps_brands | 200 | ✅ |
| wizard | 200 | ✅ |
| tools_page | 200 | ✅ |
| search | 200 (via 301) | ✅ |
| 404 | 404 | ✅ correct |

Aggregate log volume across the crawl: **3 fatal errors, ~1,686 deprecations, ~1,373 warnings, ~29 notices.**

## The 3 blockers (must fix before go-live)

### 1. Home — `includes/widgets/widgets.php:2930`
`Uncaught Error: Attempt to modify property "items" on null`
```php
$price_json->datafeed->items[0] = $amazon_item_obj;
```
`$price_json->datafeed` is null. PHP 7 auto-created the object; PHP 8 fatals.
**Fix:** initialize the object graph before assignment, e.g.
```php
if (!isset($price_json->datafeed) || !is_object($price_json->datafeed)) {
    $price_json->datafeed = new stdClass();
}
if (!isset($price_json->datafeed->items) || !is_array($price_json->datafeed->items)) {
    $price_json->datafeed->items = [];
}
$price_json->datafeed->items[0] = $amazon_item_obj;
```

### 2. reviews_trail — `single-reviews_trail.php:2175`
`Uncaught TypeError: implode(): Argument #2 must be ?array, string given`
```php
echo ucfirst(implode($good_for_running_arr1, ', '));   // legacy arg order
```
**Root cause:** legacy `implode($array, $glue)` order — deprecated in 7.4, **removed in 8.0**.
**Fix:** swap args (and guard empties):
```php
$good_for_running_arr1 = [];
$gfr = get_field('good_for_running', $first_shoe_ID);
if (is_array($gfr)) {
    foreach ($gfr as $g) { $good_for_running_arr1[] = $g['value']; }
}
echo ucfirst(implode(', ', $good_for_running_arr1));
```

### 3. reviews_training — `single-reviews_training.php:2155`
`Uncaught TypeError: implode(): Argument #2 must be ?array, string given`
```php
<?= ucfirst(implode(', ', get_field('usage', $first_shoe_ID))); ?>
```
**Root cause:** here arg order is correct, but `get_field('usage', …)` returned a **string** (PHP 7 tolerated; 8 fatals). Three copies on this line (first/second/third shoe).
**Fix:** cast to array:
```php
<?= ucfirst(implode(', ', (array) get_field('usage', $first_shoe_ID))); ?>
```

## Non-fatal but should be cleaned (deprecation/warning hotspots)

Top deprecations:
- **`utf8_encode() is deprecated`** ×181 — `includes/reviews/functions.php` (`rsg_datafeed_json`). Deprecated in PHP 8.2. Replace with `mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1')`.
- **`str_replace(): passing null`** ×81 — guard args with `?? ''`.
- **AWeber bundled library** ×~29 each across many lines — `includes/plugins/rsg_aweber/...` old `Iterator`/`ArrayAccess` return-type deprecations. Needs `#[\ReturnTypeWillChange]` or library replacement. (Bundled-plugin debt, same category as the ACF bundle.)

Top warnings (PHP 8 strictness — mostly null/undefined access):
- `Trying to access array offset on null` ×173
- `Undefined variable $price_event` ×151
- `Undefined array key "shoes_type"` ×149
- `Undefined variable $override_tag` ×98
- `Attempt to read property "ID" on null` ×79
- biggest single source: `includes/reviews/functions.php:991` (×181) and `:960` (×128); also `includes/widgets/widgets.php:2895` (×108), `reviews_search.php:443/531`.

## Notes
- `best` and `external_product` 404s are **expected** — both CPTs have `publicly_queryable = NULL`, so no public single view. Not a regression.
- The 301s (post, gps, search) are canonical/slug redirects that all resolve to 200.
- Roadmap-wise: the widget Amazon path (fatal #1) and `rsg_datafeed_json`/`utf8_encode` churn sit in the pricing/DFS code that the caching rework will rewrite anyway — but they must be patched now so PHP 8.3 is shippable before that larger project.
