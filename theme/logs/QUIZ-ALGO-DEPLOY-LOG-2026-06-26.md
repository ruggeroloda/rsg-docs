# Quiz algo (recency-decay + relax-fallback) — deploy log 2026-06-26

**File:** `template-wizard-2024.php` (wizard page ID 7848, `/best-running-shoes-wizard/`).
**Status:** LIVE 2026-06-26. Lint 8.3 + 7.4 clean. Validated on live data + render-checked live (HTTP 200).
**Awaiting:** user Cloudflare "Purge Everything" (edge cache still serves old HTML to normal visitors).

## Problem (what was actually live)
The brief (`NEWSLETTER-QUIZ-BRIEF.md` / `NEXT-SESSION-CTA-AND-RECENCY.md`) assumed a hard 1-year
date gate — but that gate only existed in a **commented-out legacy `$wpdb` block** (lines ~79–284).
The live path is a `WP_Query` via `$args_2024`. Its real flaws:
1. **No recency weighting** — ordered by `vote` DESC, `date` only a tiebreak → a stale top-`vote`
   shoe could win the podium.
2. **No fallback** — if the answer combo matched <3 reviews, results were thin/empty (the "dead
   results" complaint). No relaxation.
Also: a stray `var_dump($_REQUEST['rockplate']);` (line 302) was printing on the trail branch, and
`$the_count` ("We compared N shoes") was never set → rendered blank.

Confirmed during build: both road **and** trail rank on `vote` (0–10); all 303 road + 58 trail
reviews have `vote>0`; **none** have `stars` (the CLAUDE.md `stars` note is legacy `post`-type only).
Gender is **not** a result filter — it only flips `$women` at render (lines 1700–1702) to show the
**women's version/CTA** of each picked shoe. Same 3 shoes regardless of gender. Preserved unchanged.

## Fix (live WP_Query block only)
Replaced the single `$reviews_the_query = new WP_Query($args_2024);` line with, guarded by
`if ($wizard_form_submitted == 1)`:
- **Recency decay** on the 0–10 `vote` scale: `effective = vote − age_penalty(months)`, where
  penalty = ≤12mo:0 · 13–24:0.5 · 25–36:1.0 · 37–48:1.75 · 49mo+:2.5. Candidates pulled (pool of 50,
  same filters), scored + `usort`'d in PHP, top 3 taken; tiebreak = newer `post_date`.
  Principle: a live 8 beats a stale 9.
- **Relax-until-3 fallback** (never empty): if the filtered pool has <3, drop the least-critical
  filter and re-query, in order —
  - Road: `cushioning_type` → `carbon_plate` → `stability`; **`usage` always protected**.
  - Trail: `rockplate` → `water_resistance` → `usage_these_shoes_for`; surface pinned by post_type.
  - True-empty (mainly trail) → `post__in => [0]` so the template renders nothing rather than fatal.
- Final podium built as `WP_Query(post__in => top3, orderby => post__in)` → downstream render
  (`$wp_query = $reviews_the_query; while(have_posts())`) untouched.
- Removed the stray `var_dump` (line 302); set `$the_count = found_posts` of the full-filter pool
  (the blank "We compared __" now shows a real number, e.g. 66).

## Validation (against live DB, via `wp eval-file`)
- Loose combo (balanced/some-stability/no-plate/daily_training): 66 matches; fresh 2025-09 vote-10
  ranks above older vote-10s (eff 9.5). Decay tiebreak works.
- Strict combo (plush/very-stable/full-plate/long_distance_racing): exactly 3; a 2025 nine (eff 9.0)
  beats a 2024 nine (eff 8.5).
- Forced 0-match first pass (bogus cushioning value): relaxes `cushioning_type` → 50 → returns top 3.
  Fallback path proven.
- Live render: `…?cb=…&wizard_form_submitted=1&sw_trail=0&…` → HTTP 200, no PHP errors, 3 shoes,
  "We compared 66".

## Backups / rollback
- Live backup: `~/public_html/wp-content/themes/rsg_2022/template-wizard-2024.php.bak-202606261736`
- Staging backup: `…/jfx92wmcr3-staging.onrocket.site/…/template-wizard-2024.php.bak-202606261731`
- **Rollback:** restore the live `.bak-202606261736`.

## Known limits / follow-ups
- Trail `usage_these_shoes_for==2` builds a nested OR clause (no top-level `key`) → not relaxable by
  key match. Low impact (rockplate/water_resistance relax first; trail deprioritized).
- Penalty curve is a tunable knob; revisit if podiums skew too fresh/stale.
- Still TODO this thread: MailerLite capture (with country via CF-IPCountry) + results-page email module.

## ⚠️ USER ACTION
Run **Cloudflare "Purge Everything"** (Rocket.net panel) so visitors get the new ranking.
