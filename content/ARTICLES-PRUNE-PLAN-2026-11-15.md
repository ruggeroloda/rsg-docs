# Old articles prune (D5) — plan, on hold until the week of 7 Dec 2026

**20 Nov 2026: moved from ~22 Nov to the week of 7 Dec (Ruggero), after the core update and Black Friday. Read `PRUNE-ANALYSIS-2026-11-20.md` first: it changes the redirect rule (301 only to a same-shoe or same-topic page) and stages the work in two batches.**

_Status 15 Nov 2026: data joined and first-pass proposal made; **nothing changed on live or staging**. Ruggero (15 Nov):
the reskin goes live first (target: end of day 15 Nov); the prune is applied **about a week later (~22 Nov)** so any
Search Console movement can be attributed to one change. Black Friday is 27 Nov: apply by ~23 Nov or wait until after._

## Scope
779 published articles: 588 dated `/YYYY/MM/` posts without review fields + 191 `content` posts. Not in scope: the 937
dated reviews, `/review/`, `/gps/`, new review types, pages (41 pages incl. old giveaways, `/ex/` 2019 lists, test pages:
candidate for a later pass).

## Data (all in `content/_work/articles-prune-20261115/` on the Mac)
- `gsc/pages-1..3.zip`: GSC Performance, Web, last 16 months (14 Jul 2025 – 13 Nov 2026), Pages, filters `/content/`,
  dated 2009–2014, dated 2015–2025. Export 3 hit the 1,000-row cap at 0 clicks / 1 impression, so a missing URL = 0 clicks,
  ≤ 1 impression. `gsc/top-target-pages.csv`: GSC Links → top linked pages (capped at 1,000; tail = 2 links / 1 site; use
  "linking sites", not "incoming links"; GSC links are a sample).
- GA 2026 views + inventory: `../posts-inventory-20261115/posts-content-inventory-2026-11-15.csv`; internal links:
  `../posts-inventory-20261115/links-20261115.jsonl` (content links only; template links follow automatically).
- From live (read-only, 15 Nov): `targets-live.jsonl` (every published URL + listing terms, `targets.php`),
  `excerpts-live.jsonl` (headings + first 80 words of each in-scope article, `excerpts.php`).
- Scripts: `join.py` → `articles-joined.csv` (one row per URL), `shoematch.py` (shoe → same-shoe review / line listing /
  brand listing), `propose.py` + `buckets.py` → `proposal-draft.csv` (**automatic first pass, not checked**: it contains
  wrong targets, e.g. NB 1063 → NB 850 review, Saucony guide → the "Saucony Guide" shoe listing; every line is reviewed
  by hand bucket by bucket).
- Re-run before applying: `targets.php` + `excerpts.php` on live (posts may change), then `join.py`, `propose.py`, `buckets.py`.

## First-pass summary (16-month clicks)
| Bucket | URLs | Clicks | Earns* | Some** | None*** | Draft rule |
|---|---|---|---|---|---|---|
| A. Old reviews in article template | 92 | 9,619 | 25 | 47 | 20 | keep ≥ 10 clicks (as the dated reviews); retire the rest → same-shoe review / line listing |
| B. Best-lists, brand guides, gear | 103 | 4,014 | 5 | 18 | 80 | guides-cleanup rules (brand → `/reviews/<brand>/all/`, GPS → `/reviews/gps/`, trail → `/reviews/trail/`, generic → hub, beginners/heavy → spokes); app/gear lists → 404 |
| C. Shoe & product launches/previews | 114 | 7,975 | 10 | 43 | 61 | 301 → same-shoe review, else line listing, else brand listing (only if it has some value), else 404 |
| D. Other news & site posts | 95 | 1,582 | 3 | 4 | 88 | mostly 404 (giveaways, deals, "Around the Web", Olympics, campaigns, corporate news) |
| E. Training, health, advice | 365 | 16,668 | 38 | 98 | 229 | keep what gets search traffic; retire the rest (301 to a kept same-topic article where one exists, else 404) |
| F. Comparisons | 10 | 20,949 | 5 | 3 | 2 | keep (D4); check the 2 dead ones |

\* ≥ 100 clicks or ≥ 500 views/yr. \** 10–99 clicks or ≥ 2 linking sites. \*** < 10 clicks and ≤ 1 linking site.
Rough outcome: ~300 kept, ~150 redirected, ~330 to draft (404), 0 noindex. The 480 "none" URLs had 804 clicks in
16 months (1.3 % of the articles' clicks).

## Rules agreed (Ruggero's brief, 15 Nov)
- Per URL: GSC clicks + impressions + linking sites + internal links + topic still relevant + a better live page on the
  same intent. Clicks/backlinks → keep/refresh or 301 to the best match; no value + outdated → 301 if a close match exists,
  else unpublish (draft = 404). noindex only where a page must stay for users. Never 301 to an irrelevant page or to home.
- Order: summary (done) → one bucket at a time with Ruggero's OK (planned order D, C, B, A, E, F) → apply.

## Apply checklist (after OK, live + staging)
1. Backups: posts, postmeta, `301_redirects` option (live + staging) to `~/rsg_deploy_backups/articles-prune-<date>/`.
2. **Before drafting any post, search options + postmeta for its ID** (15 Nov incident: brand listings 500 when their
   featured post went to draft); also menus.
3. Redirect rules in the Simple 301 Redirects option, same format; no chains (repoint rules that target a retired page).
4. Status changes via WP-CLI (draft, content kept).
5. Internal links in kept content that point at retired pages: repoint to the 301 target, or unlink for 404s.
6. Verify (≤ 1 req/s): every retired URL → one 301 → 200, or 404 as intended (watch WordPress's 404 slug guessing and
   the theme's `psp_permalink_redirect`: a drafted URL must not get guessed into a random post); kept pages 200 +
   self-canonical; sitemaps no longer list retired URLs; PHP error log quiet.
7. Write `content/ARTICLES-PRUNE-<date>.md` (what, why, undo) + DECISIONS.md line (incl. the 15 Nov "after the reskin" call).
