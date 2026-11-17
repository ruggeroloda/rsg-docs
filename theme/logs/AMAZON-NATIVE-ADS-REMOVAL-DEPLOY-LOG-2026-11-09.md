# Deploy log — Amazon Native Shopping Ads removed (2026-11-09)

**Why:** Rocket.net logs showed 404s like `/z-na.amazon-adsystem.com/widgets/onejs?...adInstanceId=39e23af4-...`. Cause: the theme still printed Amazon's old Native Shopping Ads widget with a protocol-relative URL (`//z-na.amazon-adsystem.com/...`); some crawlers resolve that as a path on our domain. Amazon retired the product: `z-na.amazon-adsystem.com` no longer resolves (checked from the Rocket.net server), so the widget showed nothing to visitors anyway.

**Changed (staging first, then live):**
- `include-amazon-native.php` → emptied (comment only). Kept the file because 12 templates call `get_template_part('include','amazon-native')`.
- `footer.php` → removed the `is_singular('post'|'content') && !is_review()` block with widget `9beebdd8-...`.
- Not touched: `header.php` preconnects to `c./s.amazon-adsystem.com` (part of the pub.network ad stack; those hosts still resolve).
- Content DB: `wp db search amazon-adsystem` → no matches in post content (only WP Rocket / Fast Velocity option lists).

**Backups:** `*.bak-amzn-20261109` next to both files (live + staging).

**Checks:** `php -l` OK. Staging + live: home, a post, a review, search → 200, full HTML, 0 widget references. Caches purged (`rocket_clean_domain()` + `wp cdn purge`).

**Rollback:** `cp -p include-amazon-native.php.bak-amzn-20261109 include-amazon-native.php && cp -p footer.php.bak-amzn-20261109 footer.php`, then purge.

**Note:** crawlers that already stored the bad URL may keep requesting it for a while; it 404s harmlessly.
