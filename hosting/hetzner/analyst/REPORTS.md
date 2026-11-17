# Reports: when, what, how long

All reports are Markdown (the runner turns them into an email). Store each in `rsg.report` (kind, period, subject,
body, finding ids) and print the same Markdown to stdout as the last thing in the run. **Empty stdout = no email.**
Plain names for spots, numbers with their baseline, short sentences. Tables: small, aligned, no more than ~8 rows.

## Daily — alarm only (07:40 UTC, about yesterday)
- **Send only if** the pipeline is broken, or after triage a `1d` flag with severity `alert` is still real (or a spot
  dropped to zero). Otherwise store a report with subject `quiet` and print nothing.
- Budget: triage + steps 3–4 of METHOD for each alert; max 3 alerts in detail, the rest in one line. ~300 words.
- Subject: `RSG alert — <what, ≤ 8 words> (<date>)`
- Body, per alert:
  - **What:** the number vs its baseline (and the date/period)
  - **Where:** the segment (page type / spot / layout / country / page)
  - **Since:** when it started
  - **Likely cause:** with the change-log entry or "unknown yet" — and confidence
  - **Do:** one concrete action (or "watch until <date>")
  - A last line `Also moving: …` for wins worth a mention (one line, no detail).

## Weekly — the real report (Mondays 07:50 UTC)
- Period: Umami = last Monday–Sunday (UTC); Search Console = the 7 days ending today − 4 (say the dates).
- Budget: 1–3 deep dives done end to end (METHOD 1–8); everything else short. ~600–900 words.
- Subject: `RSG week <ISO week>: <headline, ≤ 8 words>`
- Sections:
  1. **In short** — 3–5 bullets, good and bad, each with number + baseline + what it means.
  2. **Scorecard** — this week vs the 4-week average (arrow + %), and a one-word note when it moved beyond noise:
     human visits · from search · page views · price spots seen · shop clicks · clicks per seen spot · Search Console
     clicks · impressions · average position · phone LCP / INP (p75) · bot share.
  3. **Going well** — what, why (evidence), how to get more of it.
  4. **Needs attention** — what, why, what to do.
  5. **Deep dives** — question → answer → evidence (compact table) → cause + confidence → proposal or experiment
     (duration and success rule from the sample-size rule).
  6. **Experiments** — running (progress vs needed sample, expected end), results, proposals waiting for an OK.
  7. **Open findings** — one line each: #id, title, status, next check.
  8. **Data notes** — warm-ups, gaps, bot patterns, anything that limits the conclusions.

## Monthly — strategy (5th of the month, 08:00 UTC, so Search Console covers the whole month)
- Period: the previous calendar month; compare with the month before and, for Search Console, the same month last year.
- Budget: ~1,000–1,500 words.
- Subject: `RSG <Month YYYY>: <headline>`
- Sections: **The month in one paragraph** · **Search** (by page type; top gaining/losing pages; Google updates in the
  month) · **Money proxies** (page views for ads; seen → click funnel per page type and spot; Amazon share) · **Speed**
  (Core Web Vitals by layout/page type) · **Experiments ledger** (what ran, result, decision, what we learned) ·
  **Recommendations** (top 3–5, ranked by impact × confidence ÷ effort, each with the first step) · **Questions for
  Ruggero** (decisions only he can make).
