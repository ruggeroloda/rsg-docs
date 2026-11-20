# Brief: trust details on the About page, reviewer bio and small facts

_Written 20 Nov 2026 by Claude Fable from the crawler-eye check in `analytics/core-updates/REPORT-2026-11-20.md` ("Follow-up 2"). For its own chat. Staging first; live only on Ruggero's "deploy to live"._

## STATUS 20 Nov 2026 19:50 UTC: on staging, waiting for Ruggero ("deploy to live" not given)
Ruggero's choices (20 Nov): visits number out, mailing list 35,000 stays; move the page to `/about/`; **reviewer bio: leave for now** (item 2 open, nothing changed; facts: Brandon's RSG posts run Apr 2020 – Jun 2026, 131 reviews + 161 comparisons, 17 of the reviews are New Balance); Italian line: undecided (he wonders about restarting the .it site with translations; runningshoesguru.it is online, newest review Nimbus 23, 2021), so only the typo is fixed and the link stays.

On staging (theme branch `trust-details` `0d110b4`, pushed; staging runs `47a5bce` = this + the /top/ hub chat's work):
- About page: H1 is the title only, subtitle its own line; AboutPage + Organization schema with founder (Ruggero's Person node) and the 2018/2019 awards; "Some Numbers" = `[rsg_review_count]` (prints "1,300+") + 35,000 list; "Traffic and Advertising Info" → "Advertising Info" without the visits sentence; "also find"; "Blog?". URL `/about/`, 301 from `/about-2/` (Simple 301 Redirects), footer menu link, all theme links.
- One review count: the homepage counted 1,394 (shoe reviews + 29 GPS watch reviews), the hub 1,365 (shoes). `rsg_home_counts()` now counts the hub's set; homepage, menu ("More than 1,300", was typed in) and the About shortcode read it.
- Listing descriptions: Healty → Healthy, Kirachiro → Kihachiro (Asics), Bowermann → Bowerman (Nike), lightweigth → lightweight (adidas); brand terms + their old category twins (6 terms).

To go live (after "deploy to live"), in this order: 1. backup + database part on live: `content/about-2/update-2026-11-20.py` on the live About content, then `wp eval-file content/about-2/apply-2026-11-20.php <new content file>` (slug, 301, menu, typos, count cache); 2. deploy branch `trust-details` alone (staging, then live; old `/about-2/` links redirect in between); 3. purge; 4. `rsg.change_log` rows (deploy.sh writes the theme one; add a `content` row). Staging backups: `~/rsg_deploy_backups/trust-details-20261120/`. Revert: old content file back, slug `about-2`, remove the 301 line, previous `live-*` tag.

Not touched, seen on the way: "All the most important running shoe releases, reviewed every year" and "A team of experienced testers" (Some Numbers) while publishing is paused; the Contact page H1 has the same subtitle-inside-H1 markup (`template-contact.php`); About has no canonical tag (live neither); other dated lines in brand descriptions (Puma "Usain Bolt", Brooks "Pure line").

## Why
Every Google loss on RSG was site-wide, so what Google can check about the site as a whole matters more than any single page. These are facts on the live site that are wrong or out of date (fetched 20 Nov).

## What to fix
1. **About page** (`/about-2/`):
   - "200,000 visitors every month" and "35,000 runners on our mailing list" (in "Some Numbers" and "Traffic and Advertising Info"). GA4 Oct 2026: about 130k sessions, all channels. **Ask Ruggero for the list size he wants stated**, or drop both numbers.
   - "you can now found Running Shoes Guru in Italian": check whether an Italian version still exists; fix or remove.
   - Heading "Why a Running Shoes Guru Blog ?"; H1 runs "About Running Shoes Guru" and "Real Runners, Real Reviews" together.
   - No structured data (the 17 Nov SEO audit proposes AboutPage + Organization with founder and awards: `theme/SEO-AUDIT-2026-11-17.md`).
   - The URL `/about-2/`: propose `/about/` with a redirect, only if Ruggero wants it.
2. **Main reviewer** (`/author/brandonlaw/`, and the "Reviewed by Brandon" block on reviews and comparisons): the bio presents him as RSG's current independent tester. Ruggero said on 20 Nov that he now works for a shoe brand. **Ruggero decides the wording**; options to put to him: past tense with the dates he tested for RSG, plus a line that those reviews were written before he joined the brand. Do not change scores or review text.
3. **Counts that disagree:** homepage description "1,300+ reviews" vs reviews hub "1,365". Use one source for the number.
4. **Typos in listing descriptions:** Asics "Healty Mind in a Healty Body", "Kirachiro Onitsuka" (Kihachiro); descriptions run 350–485 characters.

## Check before changing
Fetch each page again first (facts above are from 20 Nov). `rsg-core` holds some of these texts, WordPress fields the rest: find where each lives before editing.

## Done when
Staging shows the corrected pages on desktop and phone, Ruggero has approved the reviewer wording, and each change has a row in `rsg.change_log` once live.
