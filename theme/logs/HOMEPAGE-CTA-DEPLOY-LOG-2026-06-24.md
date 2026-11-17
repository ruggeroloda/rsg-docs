# Homepage hero CTA → quiz — deploy log (2026-06-24)

Single surgical, theme-file-only change. Standard flow: staging → lint 8.3+7.4 → render-check →
diff vs live → back up live → copy to live → verify live → (user) purge Cloudflare.

## Change
File `template-home-2020.php`, **line 108** (first hero button). Repointed from the stale, non-ranking
"Best running shoes" guide to the shoe-finder wizard (page 7848).

FROM:
```php
<a href="<?=$guide->url?>" class="btn btn-warning btn-block" rel="nofollow">Best running shoes&nbsp;&nbsp;<span class="right-arrow"></span></a>
```
TO:
```php
<a href="<?=get_page_link(7848)?>" class="btn btn-warning btn-block" rel="nofollow">Take the quiz&nbsp;&nbsp;<span class="right-arrow"></span></a>
```
`get_page_link(7848)` = the wizard, same pattern already used at line ~166. Classes + `rel="nofollow"`
preserved. Intentional side effects: homepage hero now has two finder entry points (line 166 also links
it) and no longer links the dead guide.

## Verification
- Lint `ea-php83 -l` + `ea-php74 -l`: pass (staging and live).
- Diff live-vs-staging: single line (108) only — surgical.
- Render (origin, cache-bypassed) on staging and live: hero button →
  `https://www.runningshoesguru.com/best-running-shoes-wizard/`, text "Take the quiz".

## Backups / rollback
- Live backup: `~/public_html/wp-content/themes/rsg_2022/template-home-2020.php.bak-202606242111`
- Staging backup: `template-home-2020.php.bak-<ts>` next to the staging file.
- Rollback: restore the `.bak-*`, or revert line 108 to `$guide->url` + "Best running shoes".

## ⚠️ Pending USER action
- **Cloudflare "Purge Everything"** (Rocket.net panel) — edge cache is `s-maxage` ~30d, so the old hero
  HTML keeps serving until purged. (Joins the other deploys already awaiting one purge.)

## NOT done tonight (deferred per user)
The quiz-results work from `NEXT-SESSION-CTA-AND-RECENCY.md` / `NEWSLETTER-QUIZ-BRIEF.md` was scoped out.
Note for whoever picks it up: the documented recency-fix line numbers (~181/~272, the `INTERVAL 1 YEAR`
date gate + ORDER BY) point at a **commented-out `/* ... */` SQL block — dead code**. The active results
query is the `$args_2024` **WP_Query** (~line 300+, `meta_key='vote'`, ORDER BY `vote` DESC, date DESC;
verified road reviews store score in `vote`, not `stars`). Real findings from this session's investigation:
(1) the quiz **does** return 3 shoes for valid combos (not empty); (2) **live bug:** `var_dump($_REQUEST['rockplate']);`
at line ~302 leaks debug text onto every trail results page; (3) `$the_count` is never assigned →
"We compared __ possible running shoes" renders blank; (4) no recency weighting (the brief's "blend,
don't gate" intent should be ADDED to the active WP_Query via a `posts_clauses`/`posts_orderby` filter —
there is no gate to remove); (5) the first questions (`sw_cushioning2/sw_strike/sw_runneruse`) are
collected but ignored by the active query.
