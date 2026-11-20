# Brief: trust details on the About page, reviewer bio and small facts

_Written 20 Nov 2026 by Claude Fable from the crawler-eye check in `analytics/core-updates/REPORT-2026-11-20.md` ("Follow-up 2"). For its own chat. Staging first; live only on Ruggero's "deploy to live"._

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
