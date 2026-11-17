# Newsletter + Shoe-Finder capture — kickoff brief

_Started 2026-06-24. Deliberately left open in places — re-reason it next session, don't treat as settled spec._

Project context lives in `CLAUDE.md`. This brief is the "make RSG profitable again in the
AIO/social era" thread, narrowed to the first concrete lever: **turn the existing quiz into an
email-capture engine, and make the newsletter the durable, Google-independent asset.**

---

## Why this thread (the strategic frame)

The old model (rank in Google → affiliate click) is structurally breaking, not dipping: AI Overviews
answer the question in the SERP, and chatbots do the comparison job so the tracked affiliate link
never fires. Survivors are reducing Google dependence by owning one of: an **audience** (email /
short-form / community), a **product** (paid data / membership, e.g. RTINGS' paywall), or a **B2B
relationship** (brands paying directly). RSG's defensible asset is its **structured shoe database**
(specs, fit, cushioning, tester data, comparison/family hubs) — the RTINGS-style moat.

Monetization paths on the table (rough priority, revisit): (1) own the audience — newsletter first,
short-form later; (2) shift affiliate toward transactional / price-comparison; (3) productize or
license the structured data; (4) brand-direct money (sponsorship, testing, light creative services);
(5) tools / digital products; (6) display as a floor only. **This session = path (1), entry point.**

Short-form video: a templated reviewer pipeline (script + shot list → auto-chop tools like
OpusClip/CapCut → platform shorts) is viable and the right way to standardize remote reviewers.
Shorts are a **discovery/traffic feeder**, not a revenue source (Shorts RPM ~$0.03–0.08 vs $1–10
long-form). Full YouTube is **not** a must — shorts→site/newsletter is a complete funnel. Parked for now.

---

## The asset we're working on

- Page: `/best-running-shoes-wizard/` (page ID 7848), template `template-wizard-2024.php`.
- ~700 users / month, strong engagement (1m04s, ~79% engaged, low bounce). **But €0 revenue and
  thin/dead results.** Key event = affiliate (Shop) click; ~42/mo.
- Finder is only **~1.1% of total site views** → a second lever is routing more internal traffic
  *into* it once results are good.
- Logic: collects ~10 answers as hidden fields, queries the reviews CPT on structured meta
  (`sw_cushioning2`, `sw_strike`, `sw_runneruse`, etc.), returns a 3-shoe podium (1st / also-consider).
- **Captures zero emails today.** Only generic site-wide popups (giveaway, training-PDF) exist.

---

## Decisions leaning-in (this session)

**Capture philosophy — give-first, no gate.** Don't hold results hostage; show all 3 shoes, then
*offer* to save them. Reason: protects trust + deliverability (spam complaints hurt the 45k list),
and the willing opt-ins are worth more than coerced ones. Capture at the results moment (peak
invested effort).

**Placement (mockup done this session).** Podium + orange Shop buttons stay first (they're the
affiliate clicks / key event). Email module sits **immediately after the podium**, above the long
expert-review scroll (which currently buries any bottom-of-page box). A quiet "✉ email me these
picks" link near the header gives top visibility without pushing shoes down. Optional later: a
one-time, dismissible slide-in for scrollers.

**Two consents, GDPR-clean (RSGMedia is an EU/NL entity).** (1) deliver the picks; (2) a *separate,
unchecked* opt-in to the newsletter. Sell the actual asset — the liked **12-part autoresponder
("Shoe Guru series", personal tone, ~1 email / 4 days)** — not a vague "occasional guide". Use
**double opt-in**.

**Defer price-drop / availability alerts.** Good idea, not now (and not worth heavy engineering at
700/mo). Keep the capture to "save my picks" + the series.

**Recency fix — blend, don't gate.** Current query hard-filters to shoes reviewed in the last 12
months (lines ~181 trail / ~272 road), then ranks pure score → starves the pool and surfaces stale
shoes among survivors. Replace with a **decay-weighted score**: `effective = review_score −
age_penalty(months)`, ordered desc, with `post_date` as tiebreak, and a **graceful fallback** that
relaxes attribute filters (water/rock-plate → strike → run-use; never gender/surface/cushioning)
when fewer than 3 match. Penalty curve is a tunable knob — starting proposal ≤12mo:0, 13–24:−0.5,
25–36:−1.0, 37–48:−1.75, 49+:−2.5 (revisit). Principle the user set: "a live 8 beats a stale 9."

**MailerLite wiring (target shape).** Server-side, not an embed: results page → WP REST route
(`/wp-json/rsg/v1/wizard-subscribe`) → MailerLite `POST /api/subscribers`. Token in `wp-config.php`,
never theme/git. Pass quiz answers + the 3 picks as **custom fields** (must be pre-created in the
dashboard) for segmentation + merge-tag welcome email. Two groups (all-signups; coaching-series).
**Use "joins a group" automation triggers, not form triggers** (MailerLite's own DOI caveat). DOI on
→ MailerLite sends confirmation + stores consent automatically.

---

## Open questions / to re-reason tomorrow

- **Age-penalty curve** — accept the starting weights or weight recency harder?
- **Availability signal** — is there a reliable in-stock / active-price field (DFS / ACF) we can use
  instead of age-as-proxy, so we never podium a discontinued shoe? If yes, prefer it.
- **DOI + the picks email** — leaning: single confirm step, picks delivered in/after the confirmation
  ("confirm to lock in your 3 shoes"). Confirm this vs. instant transactional send.
- **Checkbox + welcome-email copy** — first drafts exist; sharpen the value framing of the series.
- **The lifecycle cliff (separate project)** — after the 12-part series, ~99% of sends are the Sunday
  RSS "new reviews" blast. That's where engagement/opens (and any future deal-moment revenue) die.
  Needs its own rethink later; not blocking this build.
- **Visual refresh** — quiz looks dated. Restyle *just the results area + email block* when we're in
  there; don't let a full redesign block the two changes that move the needle.
- **Trail branch** — leave it, but make sure the fallback returns a graceful message instead of empty
  (no recent trail content; trail deprioritized for now).
- **Traffic routing** — once results are solid, add prominent "take the 60-sec finder" entry points
  across reviews/homepage. Cheap, no spam concern.

---

## Suggested build order (when we commit)

1. Recency/decay query + graceful fallback → guarantees 3 strong, buyable shoes every time.
2. Email module (placement per mockup) + MailerLite wiring (WP REST endpoint + JS) + DOI groups/fields.
3. Light restyle of the results area only.

All via the standard flow in `CLAUDE.md`: staging first, back up, lint 8.3 **and** 7.4, verify with
`?cb=`, diff, deploy, user purges Cloudflare, write a deploy log.

---

_Reference: a results-page placement mockup was produced this session (email module after podium)._
