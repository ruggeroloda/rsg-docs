# Content & distribution

_Last updated 2026-11-26. Current state; the day-by-day history is in `../DECISIONS.md`._

## Where each piece of content work stands
| Piece | State (26 Nov) | Doc |
|---|---|---|
| Shoe categories (new scheme, URLs, 1,359 reviews mapped) | Built on staging; n8n step after 1 Dec; live 8–10 Dec | `categories-2026-11-25/` (`PLAN.md`, `BRIEF-BUILD.md`, `REVIEW-FABLE.md`) |
| Brand names ASICS + HOKA | Plan agreed; staging after 1 Dec | `brand-names-2026-11-25/PLAN.md` |
| Review process (intake → publish) | Direction agreed: Airtable stays, one form, one n8n flow; switch from Make Tue 1 Dec | `REVIEW-PROCESS-2026-11-25.md`, `BRIEF-REVIEW-FLOW-N8N-2026-11-25.md`, `../automations/review-flow/README.md` |
| Review format (what a review is) | Independent view written; **six decisions open (Ruggero)**; nothing live on reviews before the 8 Dec read | `REVIEW-FORMAT-2026-11-22.md` |
| Shoes to test | 14 to assign to testers; Running Shoes table cleaned | `SHOE-LIST-2026-11-20.md`, `RUNNING-SHOES-CLEANUP-2026-11-25.csv`, `../ROADMAP.md` |
| Page proposals (engagement) | Proposals 1–2 shown, none decided; judged against `../STRATEGY.md`; "Compare it with" runs as the "head to head" test, read 8 Dec | `ENGAGEMENT-READ-2026-11-21.md`, `proposals-2026-11-21/`, `BRIEF-PROPOSAL-CHAT-2026-11-21.md` |
| Comparison refreshes | 12 of 26 linked from the test refreshed; 11 of 39 on the SEO list; resume after the freeze (from 1 Dec) | `compare-test-2026-11-22/COMPARISONS-TO-REFRESH.md`, `../theme/SEO-AUDIT-2026-11-17.md` "2.1" |
| Content review work list (Fable, 20 Nov) | Continue there | `CONTENT-REVIEW-2026-11-20.md` |
| Old-article prune | Starts the week of 7 Dec, reading first | `PRUNE-ANALYSIS-2026-11-20.md`, apply checklist `ARTICLES-PRUNE-PLAN-2026-11-15.md`, `GUIDES-CLEANUP-2026-11-15.md` |
| Video and social | Live ideas, not now | `VIDEO-BROLL-SHOT-LIST-2026-11-19.md`, `pegasus-42-social-scripts.md` |

## Direction so far (Ruggero, Nov 2026)
- "Nimble" restart: budget **one shoe per month** (~$100 with the Running Warehouse discount).
- Cover only the most popular main-brand models (Pegasus, Novablast, Supernova…).
- Squeeze several formats from each shoe: review, photos, b-roll, running footage, Shorts, video and web comparisons.
- Reviewer keeps the shoe as before; add ~$30 per shoe for video work.
- Use AI for low-value, time-consuming tasks.
- Accept samples for secondary coverage; focus on high-commercial-intent flagship shoes.
- RSG also anchors the VVV consulting business; entity disambiguation work is pending.

## To define
- Shoe list for the next 6 months.
- Distribution per format (site, YouTube/Shorts, social, email).
- Production workflow and tools (remove.bg → Pixian: live 5 Nov, see below).
- How the shoe list drives DFS scope (which shoes need prices).

## Background removal: replace remove.bg (done 5 Nov 2026: Pixian live; current state in `../theme/RUNBOOK.md` "Background removal")
- **Checked live 5 Nov** (live = staging): `rsg_2022/includes/remove_bg.php`, loaded from `functions.php:1528`. Only **remove.bg** is active (lines 7–101, `acf/save_post` on `professional_image` / `field_5d9574dae3414`). The ClippingMagic block (105–209) is **commented out**, dead code.
- Behaviour: runs when a review's professional image changes; **skips if the upload is already a PNG**. Writes the cut-out PNG next to the original and repoints the same attachment to it.
- Keys: the remove.bg key is a **literal in the theme file** (line 30), not `wp-config.php`; ClippingMagic credentials sit literally in the commented block. Remove both; cancel/rotate.
- **Risk:** no error handling. If remove.bg fails (no credits, account closed) the error response is saved as the `.png` and the attachment points to it → broken image. Disable the hook before closing remove.bg.
- Dependency: the active comparison featured-image generator (same file, lines 363–508) composites both shoes' **transparent PNGs** → any replacement must output transparent PNG.
- Volume: professional images set on 34 posts in 2026, 132 in 2025, 748 in 2023. Restart plan ≈ 1–2 per month.
- **Crop:** the 2025 images are 1280×960 (4:3) with the shoe fit to width and 1% padding. That's the old ClippingMagic setting. The current remove.bg call doesn't crop (Nimbus 28 has uneven margins), and neither does the theme.
- **Pick (5 Nov): Pixian.AI** (Cedar Lake Ventures, the ClippingMagic company). $5 = 250 credits, which never expire as long as you buy once every 2 years. RSG photos cost 0.07–0.19 credits (~$0.001–0.004). Free test on 8 RSG photos: quality on par with remove.bg; built-in crop `result.crop_to_foreground=true`, `result.margin=1%`, `result.target_size=1280 960`, `result.vertical_alignment=middle` gives the same output as ClippingMagic. Credentials: `../_private/pixian.txt`. Test files: `_work/bg-test/`.
- **Deadline:** the remove.bg API stops on 1 Dec 2026, 09:00 CET.
- Goal (superseded by the Pixian pick): free in-house replacement (e.g. open-source `rembg` / BiRefNet model as a small service on Hetzner, called by the theme instead of the paid APIs). (History; n8n holds no bank logins.)
- **Status 5 Nov: LIVE.** Pixian hook live on staging + production; $5 pack valid until 2028-11-05. Log + go-live steps: `../theme/logs/PIXIAN-BG-REMOVAL-DEPLOY-LOG-2026-11-05.md`.
- Later: when the Make insert flows move to n8n, add the same Pixian call there (and set `professional_image` automatically).

## Thinking agreed 8 Nov 2026 (with Claude)
- Why the foundation work first (Ruggero): savings fund shoes; a decade of technical debt; dated design needs solid foundations before a redesign; processes (social, email, cadence) were built for high volume/traffic and must be rebuilt for the new reality; "what is a good review in 2026" is not yet answered, and copying who ranks is not enough: RSG must be better and different.
- **Review-format research comes before the theme rebuild's review template** (otherwise the template is built twice). Mostly thinking and reading, run in parallel with the end of DFS: what still earns traffic/clicks (GSC + affiliate data); what AI answers already give for shoe queries, so what a review must add (first-hand testing, measurements, long-term wear, fit across feet, honest "don't buy if"); what the best reviewers do differently; how one review feeds social and email. Own chat when Ruggero wants it.
- **Redesign order: layout first (wireframes, judged on "does it work"), UI last** (small design system: type, colour, spacing, components, applied at once). Layout follows the review format. A light visual tidy-up during the rebuild is fine; the real visual redesign waits for the layout.
- **Homepage**: a front door for people who already know RSG plus a trust signal for Google/AI engines, not a blog feed (newest review is from 10 May 2026: a feed would show the site as inactive) and not an SEO page. Sketch: "find your next shoe" (quiz + entry by need: road, trail, stability, carbon, budget, wide), best picks per category (guides), **deals today from DFS** (price drops, 90-day lows: fresh with no writing), how we test / who we are, latest reviews (small, dated), newsletter. First step: GA4 share of sessions starting on the homepage + what is clicked there.
