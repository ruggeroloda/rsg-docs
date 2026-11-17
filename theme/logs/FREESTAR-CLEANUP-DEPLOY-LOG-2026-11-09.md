# Deploy log — Freestar / Quantcast leftovers removed (2026-11-09)

**Why:** Raptive is the only ad network, but the theme still carried code from the old Freestar setup (and Quantcast/btloader/Confiant hints): 13 dead `preconnect` hints in every page head (+ 2 unused `typekit.com` hints), a commented Freestar config, and ~70 Freestar ad-slot blocks across 25 templates. Most slots sat inside HTML comments (dead bytes); a few were live and threw `freestar is not defined` JS errors (checked live before the change: `/gps/polar-m600/`, `/gps/garmin-forerunner-935-review/`, `/2014/12/top-5-gps-running-watches-for-2015/`).

**What changed (script `theme/_work/freestar-cleanup-20261109.py`, same script on staging then live; 25 files each):**
- `header.php`: removed the "recommended list of pre-connections" note + 13 preconnects (a–d.pub.network, c./s.amazon-adsystem.com, quantserve ×2, quantcount, quantcast, btloader ×2, confiant) + `typekit.com` dns-prefetch/preconnect (fonts come from use.typekit.net / p.typekit.net, which stay). Removed the commented Freestar config/preload block and 4 commented leaderboard slots.
- All templates: every Freestar slot block (`<div data-freestar-ad…><script>freestar.config.enabled_slots.push(…)</script></div>`) removed, live or commented. HTML comments left empty by that (only spacer/hr/empty wrapper divs) removed; comments containing PHP kept (only the slot inside removed). Live wrapper divs/spacers kept so layout is unchanged.
- `functions.php`: `/* … */` PHP comments holding only Freestar `$ads_text` strings removed; commented `rsg_freestar_4.js` enqueue line removed.
- Not touched: `page-39842.php` ("Ads test page", published, Freestar-only template) — **decision for Ruggero: unpublish/delete the page**. Unreferenced files `js/rsg_freestar_4.js`, `js/deployads.js` left (never loaded). Old Sortable lazy-ad branch in footer lazy-loader left (tangled with image lazy-load; theme rebuild).

**Checks:**
- `php -l` on all 25 files (staging + live): OK (the `functions.php` "Optional parameter $after" deprecation is pre-existing).
- Staging, 60 URLs covering every template: server HTML before/after compared after normalising (HTML comments, removed slots/links, Akismet/nonce/WP Rocket random IDs). 48 identical; the other 12 differ only by noise (random related review, random "N people viewed these deals", WP Rocket cached-vs-fresh optimisation state). Freestar references 24–38 → 0 per page (ads test page excepted).
- Live dry run removed exactly the same 641 lines as staging.
- Browser (Playwright), live after deploy: the 3 pages above → 0 Freestar errors, 0 slot divs; 6 key templates mobile+desktop → no own JS errors (remaining errors are ad-network ones). Remaining head hints: use.typekit.net, p.typekit.net, ajax.googleapis.com (all used); `fonts.gstatic.com` preconnect is injected client-side by a third-party script, not by the theme.
- HTML per page ~2–4 KB smaller. Caches purged (`rocket_clean_domain()` live + staging, `wp cdn purge`).

**Backups:** `*.bak-freestar-20261109` next to each changed file (live + staging). Full diffs on the server: `~/deploy-notes/freestar-removal-diff-{live,staging}-20261109.txt`.

**Rollback:** `cd <theme> && for b in $(find . -name "*.bak-freestar-20261109"); do cp -p "$b" "${b%.bak-freestar-20261109}"; done`, then `wp eval 'rocket_clean_domain();'` + `wp cdn purge`.
