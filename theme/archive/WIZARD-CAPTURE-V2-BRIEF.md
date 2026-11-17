# Wizard capture v2 — pre-results email step + price-drop hook

_Plan brief, 2026-07-05. Status: PLAN — awaiting approval, nothing deployed._

## Why (diagnosis, 2026-07-05)
The 2026-06-26 give-first module technically works but converted **0 / ~800-900 results views**
(~100 quiz completions/day at origin; **zero POSTs ever** to `rsg/v1/wizard-subscribe` in access
logs — nobody even attempted). Causes: no value exchange (picks already on screen), placement
below 3 shoe cards full of competing review/buy CTAs at peak shopping intent, DOI friction
announced before typing. Plus a **latent bug**: results pages are edge-cached ~30d but embed a
24h `wp_rest` nonce (`template-wizard-2024.php:1737`) → once anyone does submit from a cached
page, it fails `bad_nonce`.

## Concept
Capture **before** the picks satisfy curiosity, with a hook the page can't give:
> **"Where should we send your 3 matches? We'll also alert you if they drop in price."**
> [email] [See my matches →]   ·   _Skip — just show my results_

Skippable, so completion rate is protected. Post-results card stays as a second catch for
skippers, re-copied around the price-drop hook.

## Mechanics found in code (grounding)
- Quiz is a client-side slider. Last answer (`carbon_plate` / `rockplate` / gated `stability`)
  triggers `#wizard-popup` (spinner, ~1.6s), fires gtag `Completed`, then
  `$('#target_wizard').submit()` (GET → server-rendered results). ~lines 1053–1064.
- Capture endpoint: `includes/rsg-wizard-capture.php`, REST `rsg/v1/wizard-subscribe`,
  logs to `newguru_wp_rsg_wizard_signups` + MailerLite group `from_wizard` (DOI).
- Signups table has `consent_picks/consent_series`, `pick_1..3` (titles), `profile`, `ml_status`.

---

## Phase 0 — foundations (ship with Phase 1)
1. **Drop the nonce** on `rsg/v1/wizard-subscribe` (public endpoint on cached pages — a nonce
   can never be reliable here). Keep honeypot + email validation; **add per-IP rate limit**
   (transient, e.g. 5/hr) and reject disposable-domain obvious junk if trivial.
2. **`source` column** on `rsg_wizard_signups` (`pre_results` | `post_results`) + store the
   3 pick **post IDs** (needed later for price alerts). Additive `ALTER TABLE`, no risk.
3. gtag funnel events: `EmailStep-Shown`, `EmailStep-Submitted`, `EmailStep-Skipped`.

## Phase 1 — pre-results email step (the bet)
Replace the auto-submit-after-spinner with a 2-state popup:
1. Spinner state (unchanged, keeps the "we're computing" payoff feel).
2. Email state: headline "We found your 3 matches", email input, CTA
   **"Email my matches + watch prices"**, and a clearly visible skip link
   ("No thanks — show my results"). Both paths end in `$('#target_wizard').submit()`.
   - Email path: `navigator.sendBeacon` (fallback `fetch keepalive`) to the REST endpoint
     **before** submitting the form — non-blocking, navigation-safe.
   - DOI mention moved to the *success* flash ("Check your inbox to confirm"), not before.
   - `sessionStorage` flag set on submit → Phase 2 card hides for that visitor.
Files: `template-wizard-2024.php` (popup markup + JS), `includes/rsg-wizard-capture.php`
(nonce removal, rate limit, `source`, pick IDs).

## Phase 2 — post-results card rework (catch the skippers)
- Re-copy around the same hook: "Want us to watch prices on these 3? We'll email you when
  one drops." (concrete, references what's on screen, not "newsletter").
- Hide if `sessionStorage` says already subscribed/asked.
- `source = post_results`. Placement stays after podium (acceptable as secondary).

## Phase 3 — make the price-drop promise TRUE (within ~2-3 weeks of Phase 1)
Promise discipline: we say "we watch prices", so we must. Spec when Phases 1-2 are live:
- At signup store baseline best price per pick (theme's DFS consumption, per country).
- Weekly WP-Cron: re-pull best price per subscriber pick; drop ≥ threshold (e.g. 10% or $15)
  → notify via ML (automation trigger or campaign to a segment). Details TBD.
- Interim honesty: welcome automation includes current best prices for their 3 picks.

## Measurement
- Funnel: gtag events + `source` split in the signups table; results-loads from origin
  access logs (unique-param URLs bypass edge cache, so origin ≈ true completions).
- Check-in **7 days after deploy**: target ≥2% of completions submitting (≥~15/wk);
  anything >0 already beats v1. No A/B infra — before/after at ~100 completions/day is fine.

## Deploy rules (as always)
Staging first + backup → lint `ea-php83` **and** `ea-php74` → verify with `?cb=` → diff →
backup live → upload → verify → **Cloudflare Purge Everything** (critical: the quiz page
itself is cached; note several earlier deploys still await a purge) → deploy log
`WIZARD-CAPTURE-V2-DEPLOY-LOG-<date>.md`.

## Risks / watchpoints
- Extra step could dent results-page reach → skip link prominent; watch `Completed` vs
  `EmailStep-Skipped`+`Submitted` ratio; instant rollback = restore auto-submit (one JS block).
- sendBeacon fire-and-forget: we won't show per-user errors pre-navigation — acceptable
  (log server-side; table is source of truth).
- ML DOI still gates list growth (~40-60% confirm typical). Account-wide setting, unchanged.
- MailerLite API key rotation still pending (see CLAUDE.md manual actions).
