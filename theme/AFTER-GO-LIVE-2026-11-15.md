# Reskin — after go-live (15 Nov 2026)

_Reskin live since 15 Nov 2026 14:30 UTC (`4ac5ef4`, tag `live-20261115-143049`). Rollback point `live-20261115-103600`: `GO-LIVE-PLAN-2026-11-15.md`. The old-article prune (D5) waits until ~22 Nov so this week's numbers can be read on their own._

## Watch over the next 7 days (to ~22 Nov)
**Search Console**
- Pages (indexing): no jump in "Duplicate / Google chose a different canonical", "Crawled – not indexed" or "Soft 404" for reviews, comparisons and old dated reviews.
- Enhancements: Review snippets / Product snippets / Breadcrumbs / Profile page. New valid items expected (937 old reviews, 62 legacy `/review/`, articles, author pages); errors should stay 0.
- Performance, compare with the 7 days before: clicks/impressions/CTR for `/reviews/`, `/comparison/`, dated `/20xx/` reviews, `/content/`. A dip of a few days after a big template change is normal; a slide that keeps going past ~10 days is not.
- Sitemaps: resubmit `https://www.runningshoesguru.com/wp-sitemap.xml` (now listed in robots.txt).
- Core Web Vitals (field data lags ~28 days): lab CLS is 0–0.009 on every template.

**GA4 + our click log**
- Affiliate clicks per 1,000 views by template (before: comparisons ~38, road reviews ~23, old dated posts ~1).
- Click log `newguru_wp_rsg_clicks` by box: new codes `rail` (sidebar card), `drw` (drawer), `mob` (phone block), `stick` (phone bar), `newer` (newer-version card on old reviews), `art`/`artm`/`hub`/`hubm` (article box). Main question: does the phone bar get clicks now that it sits at the top?
- Article pages now log price-box views, so site-wide clicks per 1,000 views drop for that reason alone.

**Raptive**
- Daily revenue / RPM and viewability vs the week before (ad slots: reviews +1–2, comparisons phone −2 to +1, desktop +3 to +5).
- Send the mail now with the live link (Raptive asked to hear when it's ready, 13 Nov), including the mobile video player position.

**Ops**
- `rsg-php` / `rsg-site` healthchecks; PHP weekly digest (Monday ~05:20 UTC); WP Rocket used-CSS failures (2 of ~500 on 15 Nov, pages then get full CSS).

## Work after go-live (Ruggero's go/no-go, 15 Nov)
1. Click spots + Amazon tracking IDs (`CLICK-SPOTS-BRIEF-2026-11-15.md`), own deploy. Goes before the drawer part of the front-end modernization (`MODERN-WEB-GUIDANCE-2026-11-15.md` item 4): same files.
2. GPS listing at `/reviews/gps/`: today it shows all shoe reviews with canonical `/reviews/`; the header "GPS" link and 7 GPS redirects land there. Also the "All running shoes Reviews" back link on GPS reviews.
3. Raptive mail + re-map to the new markers; then C1/C2 (`HTML-AUDIT-RESKIN-2026-11-15.md`; C2 brings the comparison subtitle back for text extractors).
4. Mega-menu editorial keep/cut (46 links newly visible to Google; all are clean 200 pages).
5. Price card 288 px (last pass).
6. ~~`rsg-php`: ignore fatals from WP-CLI scripts (12:10 UTC false alarm today).~~ **DONE — live 18 Nov** (WP-CLI errors go to their own `-cli.log`).
7. Related-review cards use full-size cut-out PNGs (400–580 KB each, lazy-loaded): serve a small size.
8. Guides/top-list template (extracts 2/17 and 2/19 items, same as before).
9. Rich Results test (live URL) on one review, one comparison and one author page.
10. D5 old-article prune ~22 Nov (`content/ARTICLES-PRUNE-PLAN-2026-11-15.md`).
11. Front-end modernization with Modern Web Guidance (`MODERN-WEB-GUIDANCE-2026-11-15.md`, prompt inside): items that don't touch the price-box files first; the drawer only after click spots is live.
