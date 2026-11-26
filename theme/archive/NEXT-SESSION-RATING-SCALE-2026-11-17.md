# User ratings in schema → always /10 (17 Nov 2026)

**DONE — live since 17 Nov 2026 21:23 UTC** (`27cada1`, tag `live-20261117-212319`, together with the homepage hero rotation). Branch `fix/user-rating-scale-10` merged and deleted. The brief below is kept as written.

_Ruggero: "Everything goes to 10." Code done, not deployed at the time: the Mac wasn't connected (SSH to Rocket.net only from the Mac)._

## Why
GSC "Rating value is out of range" (7 comparison items, crawled 15–16 Nov, before the 16 Nov user-rating fix). Live check 17 Nov ~19:30 CET: all 173 comparisons + 455 review pages have valid ratings now (Ruggero can "Validate fix" regardless of this deploy). Remaining inconsistency: comparisons and old reviews sent aggregateRating /5, review pages /10 (Pegasus Plus 3.6/5 vs 7.3/10).

## Change
GitHub `ruggeroloda/rsg-theme`, branch **`fix/user-rating-scale-10`**, commit `27cada1` (on top of `main` `11d3061`). Stored ratings unchanged.
- `includes/rsg-schema.php`: `rsg_comparison_user_rating()` returns /10 (pct/10); comparison Product nodes + old-review schema `bestRating` 10; review pass scales old-type ratings (stored /5) to /10.
- `include-single-gps.php`, `includes/widgets/widgets.php`: orphan microdata (no `itemscope`, ignored by Google) /100 → /10.
- Visible scores were already /10 everywhere (no template change).

## To deploy (Mac session)
1. In `theme/rsg_2022/`: `git fetch origin && git checkout main && git merge --ff-only origin/main && git merge --ff-only origin/fix/user-rating-scale-10 && git push origin main` (gitleaks first).
2. `./deploy.sh staging` → check a comparison (e.g. Pegasus Plus vs Pegasus 41: Pegasus Plus aggregateRating ≈7.3/10) and an old review with ≥3 ratings.
3. Live only after Ruggero's "deploy to live"; then delete the branch.
