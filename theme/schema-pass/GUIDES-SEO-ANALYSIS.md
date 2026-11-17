# Guides SEO Analysis — RunningShoesGuru Buying Guides
*Deep-dive: what HTML / schema / page-structure changes maximise SERP ranking + LLM pickup, given the post-HCU collapse. Grounded in a live audit of the 15 `guide` posts (rendered via `single-guide.php`).*

---

## TL;DR — the honest diagnosis

The guides went invisible because the **Helpful Content system + subsequent core updates** (and now AI Overviews siphoning clicks) reclassified them as low-priority "best-of" affiliate roundups, then **staleness** (updates stopped after the 2023 hit) compounded it. That is a **content-quality / E-E-A-T classification problem**, not a markup problem.

Therefore, be clear-eyed about leverage:

- **Schema/JSON-LD is NOT a ranking factor.** It earns rich results and makes the page machine-extractable (Google AI Overviews, ChatGPT, Perplexity, Gemini). Do it — it's cheap, additive, and increasingly important for LLM citation — but it will **not** reverse an HCU suppression on its own.
- **HTML/structure changes** (heading semantics, extractable answers, tables, entity clarity) help crawlers *and* LLMs parse and quote you. Meaningful, moderate effort, low risk.
- **Content + E-E-A-T + freshness** is the actual recovery lever. Highest effort, highest impact, the only thing that addresses *why* Google demoted them.

The good news from the audit: **the content is not thin.** You have real testing structure, pros/cons, who-should-buy, spec tables, rating breakdowns, a named expert author, strong internal linking, and minimal affiliate density. That's exactly the raw material an HCU recovery can be built on. The problem is the *signals* are buried and the *freshness* lapsed.

---

## What the pages look like today (live audit — Best Hoka guide as representative)

| Signal | State | Verdict |
|---|---|---|
| Rendering | Full server-side HTML (~244 KB) | ✅ Good — crawlable, no JS dependency |
| Word count | ~7,800 words | ✅ Substantial, not thin |
| H1 | Exactly 1 | ✅ Correct |
| Heading hierarchy | 16×H2, 13×H3, 14×H4, 6×H5, **49×H6** | ❌ Broken — see below |
| Product heading | `<h4>The winner</h4>` (generic, repeated) | ❌ Product name not in a heading |
| JSON-LD | **0 blocks. None.** | ❌ Biggest fixable gap |
| Canonical | Present | ✅ |
| robots | `index,follow … max-image-preview:large` | ✅ |
| Spec tables | 7 `<table>` | ✅ Good for extraction |
| Internal links | 116 | ✅ Strong |
| Affiliate links | 4 | ✅ Low density (not spammy) |
| Author | Named, "222 other reviews" | ✅ Exists, but buried |
| Experience language | "we tested"=0, "our team"=0, "tested"=0 in body | ❌ Experience told once in a headline, never shown |
| Freshness | Most modified Aug 2024; titles say "(May 2025)" | ❌ Stalled + date mismatch reads as stale/automated |

### The heading problem in detail
Each product pick currently renders as:
```
<h4>The winner</h4>          ← should be the product name, e.g. "Hoka Skyflow — Best Overall"
   <h6>In short</h6>
   <h6>Pros</h6>
   <h6>Cons</h6>
   <h6>Who should buy it</h6>
   <h6>Who should not buy it</h6>
   <h6>Main Specs</h6>
   <h6>Rating breakdown</h6>
   <h6>Team review</h6>
```
Two failures: (1) the single most important heading per section says "The winner" instead of naming the entity (shoe), so neither Google nor an LLM can cheaply associate the section with "Hoka Skyflow"; (2) jumping H4 → H6 skips H5 and dead-ends at H6 for content that is structurally H3/H4. The result is a flat, semantically meaningless outline on a 7,800-word page — the opposite of what LLM extractors and Google's passage ranking want.

---

## The four-layer plan

### Layer 0 — Decisions / triage (do first)
1. **Consolidate or commit.** 15 guides, only ~6 with broad demand (beginners, women, heavy runners, trail, GPS watches) vs brand roundups (Nike/Adidas/Hoka/etc.). Decide which deserve genuine refresh investment. HCU recovery rewards *concentrated* quality, not 15 half-updated pages. Candidates: merge thin/overlapping brand guides, or noindex the weakest until refreshed (a small number of strong pages beats many mediocre ones under the quality classifier).
2. **Fix the title-date mismatch.** Either genuinely update the page (then the date is honest) or drop the month-year from the title. A "(May 2025)" stamp on content last edited Aug 2024 is a trust/staleness liability.
3. **Use `single-gift-guide.php` finding:** it's dormant (0 published `gift-guide` posts). Ignore it for this work — all effort is on `single-guide.php`.

### Layer 1 — HTML / structure (helps crawlers AND LLMs; moderate effort, low risk)
1. **Fix heading semantics** (template change in `single-guide.php`):
   - Promote each pick to a descriptive heading: `<h2>1. Hoka Skyflow — Best Overall</h2>` (rank number + entity + category). This puts the *shoe name* and the *use-case* into the heading — directly answers "best Hoka for X".
   - Demote the sub-blocks to consistent `<h3>` (Pros, Cons, Who should buy, Specs, etc.), no H6 dead-ends, no skipped levels.
2. **Lead with an extractable answer.** Add a short "Quick picks" summary block at the very top: a bulleted/`<dl>` list of "Best Overall → Hoka Skyflow", "Most cushioning → Bondi 9", each linking to its section. This is the single highest-value block for AI Overviews and "people also ask" — LLMs lift exactly this format.
3. **One comparison table near the top** spanning all picks (model, category, price, weight, drop, rating). You already have per-product spec tables; a single roundup table is the canonical LLM/SERP extraction target for "best X" queries.
4. **Semantic markup of the trust block.** "Why you can trust us" → real `<section>` with author byline, credentials, testing methodology, and visible "last updated" date. Make E-E-A-T *visible*, not just present.
5. **Descriptive anchor text** on the 116 internal links (avoid "read more"); strengthens topical entity graph.

### Layer 2 — Schema / JSON-LD (rich results + LLM facts; cheap, additive, already-specced)
Per `GUIDES-SCHEMA-BRIEF.md`, reuse `rsg_build_product_node()` from `rsg-schema.php`:
- **`ItemList`** of the picks (`itemListOrder` descending, each `ListItem` → `Product` with brand/image/specs/offers/aggregateRating).
- **`BreadcrumbList`** Home → Guides → this guide.
- **Strongly consider adding `FAQPage`** (the machinery already exists for reviews — `rsg_build_review_faq()`) wired to a real on-page FAQ. FAQ rich results are reduced in Google SERPs now, but FAQ schema remains a high-value, easily-quoted block for LLMs.
- Consider an `Article`/`mainEntity` wrapper carrying `author` (Person w/ credentials), `datePublished`/`dateModified` — this is where the E-E-A-T author + freshness signal gets machine-stated. Keep scope tight but the author/date is worth it.
- All additive, frontend-invisible, behind `is_singular('guide')` — same verified-safe pattern as the review/comparison passes.

### Layer 3 — Content + E-E-A-T (the real HCU recovery lever; high effort, high impact)
This is what actually addresses *why* Google demoted them.
1. **Show the experience, don't assert it.** Body text says "we tested" 0 times. Add per-shoe first-hand detail: who on the team ran in it, mileage, surfaces, what surprised them, measured weights, original photos of the actual shoes (not brand renders). Google's reviews system explicitly rewards "evidence of first-hand experience" (own photos, measurements, A/B with prior models).
2. **Original data > aggregation.** A roundup that restates specs anyone can find is exactly the HCU target. Add something only you have: your own durometer/weight measurements, ranking rationale, "we changed our #1 pick because…", longitudinal durability notes.
3. **Author authority.** Surface the named author's credentials prominently (running background, # shoes tested), link to an author bio page with `Person`/`sameAs` schema. E-E-A-T is person-level now.
4. **Freshness cadence.** Pick the ~6 high-demand guides and commit to a real quarterly refresh (new releases in/out, re-test, update "last updated"). Honest recency is both a ranking and an LLM-trust signal.
5. **Intent match / prune.** Cut or merge guides that cannibalise each other or have no search demand. A leaner set of demonstrably-helpful pages lifts the site-wide quality signal that HCU evaluates.

### Layer 4 — LLM / AI-Overview specific
LLMs and AI Overviews now intercept a large share of "best running shoe for X" queries. To be *cited* (the new visibility):
- **Extractable, self-contained answers** — the Quick-picks block + comparison table (Layer 1) are the primary citation targets.
- **Clear entity disambiguation** — full model names in headings, consistent naming, brand in schema.
- **Stated freshness** — `dateModified` in schema + visible "last updated"; LLMs prefer recent sources.
- **Authoritativeness signals** — author credentials, methodology, original data; LLMs weight source trust.
- Consider an **`/llms.txt`** and clean `sitemap` exposure; ensure no aggressive bot-blocking of GPTBot/PerplexityBot/Google-Extended *if* citation visibility is the goal (a business decision — citation vs. content-licensing stance).

---

## Recommended sequencing

1. **Layer 2 (schema)** — already specced and safe; ship it now as the planned pass. Earns rich results + immediate machine-readability. *(Low effort, this week.)*
2. **Layer 1 (heading/structure/quick-picks/comparison table)** — template work in `single-guide.php`; biggest parsing/LLM win per hour. *(Moderate, next.)*
3. **Layer 0 triage** — decide the keep/merge/noindex set before pouring content effort in. *(Decision, parallel.)*
4. **Layer 3 (content/E-E-A-T refresh)** on the chosen high-demand guides — the actual ranking recovery. *(High effort, ongoing.)*

> Reality check: Layers 1–2 make the pages maximally *eligible* and *quotable*. Only Layer 3 changes the quality classification that took the rankings. Do all of it, but don't expect schema alone to move SERP positions.
