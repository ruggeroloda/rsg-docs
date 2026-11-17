# Next session — execute: homepage CTA swap + quiz recency fix

_Created 2026-06-24. Two surgical, theme-file-only changes. Staging → lint → verify → diff → back up
→ live → purge Cloudflare → deploy log. Full context in `NEWSLETTER-QUIZ-BRIEF.md` + `CLAUDE.md`._

**Connect:** copy `.ssh-rocket-key` to outputs, `chmod 600`, then
`ssh -i <key> jfx9wij@131.153.143.194`.
- Live theme: `~/public_html/wp-content/themes/rsg_2022`
- Staging theme: `~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022`
- Lint BOTH: `ea-php83 -l <file>` **and** `ea-php74 -l <file>`. Cache-bust render with `?cb=<ts>`.
- DB/uploads NOT deployed — staging DB has drifted, so staging may surface different shoes than live;
  validate the *logic* on staging, confirm *render* on live after deploy.

## Confirm at start (≈2 min, then proceed)
1. CTA wording — default **"Take the quiz"**; alts "Take the 60-second quiz" / "Find your perfect shoe".
2. Recency penalty curve — default weights below; weight harder if desired.
3. Availability signal — is there a reliable in-stock / active-price field (DFS/ACF)? If yes, prefer
   it (only podium buyable shoes). If unknown, ship age-decay alone.

---

## Change 1 — homepage hero CTA → the quiz

File `template-home-2020.php` (front page = page 4714), **line ~108**. The first hero button currently
points to the (stale, non-ranking) guide.

FROM:
```php
<a href="<?=$guide->url?>" class="btn btn-warning btn-block" rel="nofollow">Best running shoes&nbsp;&nbsp;<span class="right-arrow"></span></a>
```
TO:
```php
<a href="<?=get_page_link(7848)?>" class="btn btn-warning btn-block" rel="nofollow">Take the quiz&nbsp;&nbsp;<span class="right-arrow"></span></a>
```
Notes: `get_page_link(7848)` = the wizard, same pattern already used at line ~166. Keep `rel="nofollow"`
and classes. Intentional side effects: homepage hero will have two finder entry points (line 166 also
links it) and no longer links the guide — fine, guide is dead.

## Change 2 — quiz recency: blend, don't gate

File `template-wizard-2024.php`. The hard 1-year date gate starves results; replace with an age-decay
ranking. **Two spots:** line ~272 (road, `meta_key='stars'`) and line ~181 (trail, `meta_key='vote'`).

In each, **delete** the clause:
```sql
AND ( {posts}.post_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR) )
```
and **replace the ORDER BY** with a decayed score (road uses `stars`, trail uses `vote` — keep each
file's existing meta_key in `rsg_order_join`):
```sql
ORDER BY (
  (rsg_order_join.meta_value + 0)
  - CASE
      WHEN TIMESTAMPDIFF(MONTH, {posts}.post_date, NOW()) <= 12 THEN 0
      WHEN TIMESTAMPDIFF(MONTH, {posts}.post_date, NOW()) <= 24 THEN 0.5
      WHEN TIMESTAMPDIFF(MONTH, {posts}.post_date, NOW()) <= 36 THEN 1.0
      WHEN TIMESTAMPDIFF(MONTH, {posts}.post_date, NOW()) <= 48 THEN 1.75
      ELSE 2.5 END
) DESC, {posts}.post_date DESC
LIMIT 3
```
(`{posts}` = `$wpdb->posts`.) Principle: a live 8 beats a stale 9. Removing the date gate alone fixes
most starvation; the decay keeps recency as a tiebreak without excluding good older shoes.

**Phase 2 (only if common answer-combos still return <3):** graceful attribute fallback — if the query
yields <3 rows, re-run relaxing filters in order water/rock-plate → strike → run-use; never relax
gender / surface / cushioning. Trail branch should show a graceful "no strong trail match" message
rather than empty (trail content paused).

**Verify:** submit the quiz on staging for a few combos (esp. a stability/plush combo that was empty
before) and confirm 3 shoes return, with a strong older shoe correctly out-ranked by a fresh one.

---

## Deploy procedure (each file)
1. Edit on **staging**. Lint `ea-php83 -l` AND `ea-php74 -l` (must pass both).
2. Render-check via `?cb=<ts>` (homepage; submit wizard for the quiz).
3. Diff staging-vs-live to confirm a surgical change only.
4. Back up live: `cp <file> <file>.bak-$(date +%Y%m%d%H%M)`.
5. Copy staging file → live. Verify live render.
6. **Tell user to run Cloudflare "Purge Everything"** (Rocket.net panel) — required or old HTML serves.
7. Append a deploy log (rollback = restore the `.bak-*`).

## Rollback
Per file: restore its `.bak-*`. CTA: revert line 108 to `$guide->url` + "Best running shoes". Recency:
restore the two original ORDER BY clauses + the date gate.
