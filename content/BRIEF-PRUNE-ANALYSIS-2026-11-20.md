# Brief: should RSG prune, what, and how far? (independent analysis)

**DONE — analysis delivered 20 Nov 2026: `PRUNE-ANALYSIS-2026-11-20.md` (nothing changed on the site).**

_Written 20 Nov 2026 by Claude Fable for a separate Fable chat. This file gives the question, the material and the risks. It holds numbers, not a recommendation: reach your own._

## The question (Ruggero, 20 Nov)
A comprehensive analysis of a possible prune. An old-article prune is already planned (`ARTICLES-PRUNE-PLAN-2026-11-15.md`, ~22 Nov): test that plan rather than take it as given, and decide whether pruning should go further (old reviews, old `post` pages) or not happen at all.

## What is known (Search Console, Oct 2026, against `wp_pages` of 20 Nov)
- 2,391 published, indexable pages. 1,264 (53 %) had under 5 clicks in the month; 681 (28 %) had none.
- By year published: 2019 or earlier 1,380 pages / 15 % of clicks (1,008 under 5 clicks); 2020–22 511 / 28 %; 2023–24 366 / 24 %; 2025–26 134 / 32 %.
- By type: `post` 1,523 pages, 522 with no clicks; `content` 191, 117 with none; reviews and comparisons almost all get clicks.
- 378 URLs with impressions are not in the WordPress list (old redirects, archives, feeds): find out what they are.
- Every Google loss since 2023 was site-wide (`../analytics/core-updates/REPORT-2026-11-20.md`). Google says its reviews system can judge a whole site when much of it is reviews, and that deleting content is a last resort after a core update. Removing the thin `/overview/` pages in Jul 2024 was followed by no lift.

## What to work out
1. For each candidate group: clicks, impressions, links from other sites, internal links in and out, ad and affiliate income (`../ads/BASELINE-2026-11.md`; key events in GA4), email or direct visits (pages can matter without Google).
2. Per group, the options: keep, improve, merge into a newer page, redirect, remove (410), or noindex. Old reviews of superseded models are a special case: a "newer version" card is already on the content work list.
3. What a prune can and cannot be expected to do, with evidence: cases where it helped or did nothing, what Google has said, RSG's own Jul 2024 removal.
4. Risk and order: the November 2026 core update is rolling out (from 10 Nov), the reskin went live 15 Nov, Black Friday is 27–30 Nov. Say whether to wait, and how to stage a prune so its effect can be read (batches, a holdout group).
5. How we would know it worked: which numbers, by when.

## Material
- Data: `../analytics/core-updates/data/` (GA4 landing pages by month from Feb 2023, Search Console by page from Jul 2025, `wp_pages`), database and access in `../analytics/core-updates/README.md`. Scripts from the core-update analysis: `../analytics/core-updates/analysis/`.
- Earlier work, to test: `ARTICLES-PRUNE-PLAN-2026-11-15.md`, `CONTENT-REVIEW-2026-11-20.md`, `../theme/SEO-AUDIT-2026-11-17.md`.
- Rules: nothing changes on the live site without Ruggero's "deploy to live". This chat delivers an analysis and a staged plan, not deletions.
