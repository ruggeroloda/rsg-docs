# Next session — theme / rsg-core (updated 12 Nov 2026, ~01:00 CET)

_Read this, then `RUNBOOK.md`. Ruggero's working style here: summary of the list first, then one step at a time, waiting for his input; thorough checks against the real systems._

## Where things stand
- **Live:** theme `5dbb23b` (tag `live-20261111-234900`). Staging = same commit. Nothing open on staging.
- **Done 11–12 Nov** (all re-checked on live): security fixes S1, S2, S3, S5; SEO batch P1–P10 incl. sitemaps by rule and listing descriptions (P5); redirect table cleaned (147 rules, every target one hop to a 200); Garmin FR265 title; empty listings noindex; ad-insertion filter removed; reader-review debug alert removed; `getLatestModelId()` cycle-safe; `/test-cache/` noindex; contact form spam checks without keys (honeypot + JS/time token + Akismet, reCAPTCHA removed); **`affiliate_product` erased** (export kept). Details: `RSG-CORE-AUDIT-2026-11-11.md`, `SEO-SETTINGS-REVIEW-2026-11-11.md`, `../DECISIONS.md` (11–12 Nov).

## Next (Ruggero: separate task, new chat, later)
**`rsg-core`**, in the order of the audit doc. First ask Ruggero two things: (1) plugin code in the same git repo + `deploy.sh` as the theme (proposed) or its own repo; (2) SEO layer = rewrite the engine with byte-identical output (proposed; matches his "keep what Google sees").
1. Plugin skeleton + housekeeping + content model (post types, taxonomies, rewrites, REST everywhere, field groups in code).
2. SEO layer rewrite (engine only, same output; parity = every head tag on all sitemap URLs) + schema (one emitter, `JSON_HEX_TAG`). Carry over: listing descriptions, sitemap rules, canonical rules, robots rules.
3. Comments/reader reviews + search. 4. Quiz ranking + email adapter. 5. Media (Pixian as is). 6. Shortcodes + related posts. Widgets + old DFS helpers after DFS Phase F (~17 Nov).

## Small, waiting
- Site pass done 12 Nov (`RUNBOOK.md` gap 3), incl. photo-gallery and subscribe-to-comments-reloaded removed. `rsg-core` step 3 (comments): no subscription plugin any more.
- Ruggero: resubmit the sitemap in Search Console.
- Most guides are dead and get no traffic (Ruggero): removing/noindexing them is a later content decision, not part of `rsg-core`.
- Dead `is_singular('affiliate_product')` conditions in big templates go with the template rebuild.

## How-to reminders (learned 11–12 Nov)
- Rocket.net SSH from the device shell: `cp <rsg>/theme/.ssh-rocket-key ~/.rsgkey`. GitHub push: copy `_private/github-rsg-theme-deploy-key` to `~/.ssh/github-rsg-theme`; gitleaks for this VM (aarch64) = the linux_arm64 release in `~/bin`.
- `deploy.sh` reads "yes" from stdin: `echo yes | ./deploy.sh staging`. It refuses with any untracked file in the repo (don't save test output there). git needs delete permission on the folder (lock files).
- Edit files with Python keeping line endings (`open(p, newline='')`): some theme files are CRLF (`js/ajax_comment.js`).
- Long WP-CLI jobs: run on the server with `setsid nohup … &` and poll a log; never `pkill -f` a pattern that matches your own ssh command.
- Cloudflare answers 429 above ~6 parallel requests from one IP: crawl with ≤ 5 threads from the cloud shell, or 0.6 s pauses from the device shell.
- Staging serves only 49 own uploads (others 302 to live), so image URLs differ from live; compare head tags / headings / visible text, not raw body HTML.
- Only a full purge exists (`wp eval "rocket_clean_domain();" && wp cdn purge`); purge after adding a redirect for a URL that was cached as a 200.
