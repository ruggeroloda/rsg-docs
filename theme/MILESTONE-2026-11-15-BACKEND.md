# Milestone 15 Nov 2026 — backend rebuild + reskin live

**Version:** theme + `rsg-core` 0.5.1 = commit `4ac5ef4` · live tag `live-20261115-143049` · milestone tag `milestone-2026-11-15` (GitHub `ruggeroloda/rsg-theme`) · live since 15 Nov 2026 14:30 UTC.
**Period:** 8–15 Nov 2026. What changed under the hood (backend only; frontend effects are being measured separately: `GO-LIVE-PLAN-2026-11-15.md`, `AFTER-GO-LIVE-2026-11-15.md`).

## Engine — theme code
| | 11 Nov (first git commit `85a088b`) | 15 Nov (`4ac5ef4`) |
|---|---|---|
| Theme size | 20.4 MB | 9.8 MB |
| Files | 722 | 321 |
| PHP files / lines | 282 / 114,000 | 98 / 45,000 |
| JavaScript | 2.5 MB | 0.43 MB |
| CSS | 545 KB | 202 KB |

- 423 files deleted, 118 commits. Removed: a bundled 2014-era copy of ACF (→ maintained Secure Custom Fields plugin), Attention Grabber, Sortable/adtech scripts, Zarget, Facebook pixel, geni.us loader, the retired Amazon Product Advertising API code (with AWS keys), the fake "N people viewed these deals" counter, `/feed/tca`, 11 never-called functions; Typekit → 6 self-hosted font files.
- **Chassis:** content model (post types, taxonomies, `/reviews/` URLs) and the Raptive ad module moved out of the theme into our own plugin `rsg-core` (0.5.1): a theme change can no longer 404 every review.
- **Workshop:** theme under git (private GitHub), deploy script with PHP 8.3 + 8.4 lint, backup of every replaced file, tags, staging first, one-command rollback.
- Code before 11 Nov was never in git: the 8 Nov starting point was slightly bigger than the first column.

## Fuel system — prices
- Old PHP "Datafeed Studio" on Linode → **new Python price engine** (one Docker container) on its own Hetzner server (CAX11, Helsinki); feeds Amazon, Nike, Zappos, Running Warehouse, Hoka, Puma, adidas for US, DE, GB, AU; pushes prices into WordPress after the Amazon updates (max 4 cache purges a day, was up to 9).
- Shop thumbnails: 2.7–3.6 MB Nike PNGs → ~2 KB WebP copies on our own server.
- Matcher: wide/narrow versions no longer matched (28 wrong offers fixed); trademark signs ignored (Nimbus 28, Cumulus 28, Kayano 32 gained Zappos offers).
- Every affiliate click in our own log (page, price box, shop, country, gender).

## Fuel tank — database
| | Before | 15 Nov (measured on live) |
|---|---|---|
| Database size | ~2 GB (11 Nov) | 451 MB |
| Tables | ~102 | 29 |
| Settings loaded on every request (autoload) | 660 KB | 84 KB |
| Options | ~970 | 351 |
| Redirect rules | 147, with chains and dead targets | 182, no chains |

- 73 tables dropped: 58 left by removed plugins + 15 old sign-up tables (encrypted archive kept on Drive).
- Also removed: 619 dead options, 18,000 failed background-job rows, 573 orphan term links, old comment-subscription data (2,567 subscribers, 4,696 meta rows), 48 dead posts + 87 images, the old `affiliate_product` posts.

## Bodywork — files on the server
- ~500 MB of removed-plugin leftovers deleted from live (285 MB + 208 MB).
- **Two public 2013 database dumps removed** (52 MB, full user + newsletter data, downloadable by anyone).
- Staging slimmed by 25 GB.

## Accessories — plugins
- 2 uninstalled (import-users-from-csv, subscribe-to-comments) + the theme's ACF copy → SCF plugin. 11 active: akismet, dstudio (old DFS, fallback), disable-gutenberg, integromat-connector, object-cache-pro, rsg-core, secure-custom-fields, simple-301-redirects, wp-crontrol, wp-rocket, wp-rocket-no-cache.

## Locks — security
- SQL-injectable 404 redirect query fixed; comment/review handlers no longer echo raw input.
- Contact form: honeypot + Akismet (reCAPTCHA removed; its key was never on the server).
- Keys out of git, scanned with gitleaks before every push. Price server: SSH key only, firewall.

## Server load
- Per-country cache rule off: one cached copy per page instead of one per country → fewer full page builds.
- Mega-menu printed in the page instead of a server call on every hover (~8 % of origin requests on 13 Nov).

## Dashboard + insurance
- PHP error logging on (nothing logged since 26 Aug: LiteSpeed ignored the setting) → ~100 old warning types now visible; weekly digest.
- Health checks every 5 min: `rsg-site` (edge + fresh render + price box + backup age), `rsg-php` (fatals).
- Off-site encrypted backups to Google Drive (database + files weekly, uploads per year); price server + rsgmedia nightly.
- Staging refresh script (no 25 GB re-copy). Every custom field readable/writable through the REST API (Make, n8n).

## Not done yet
- Retire the old price system: `dstudio` plugin, Linode server ($29/mo), `[ds]` shortcode in 76 old posts (DFS Phase F).
- Rest of the `rsg-core` audit (`RSG-CORE-AUDIT-2026-11-11.md`); ~100 old PHP warning types.

Sources: git (`85a088b` vs `4ac5ef4`), live database queries 15 Nov 17:35 CET, `DECISIONS.md` 8–15 Nov, theme `RUNBOOK.md` (site pass 12 Nov).
