# Homepage — final design (17 Nov 2026)

Source of truth for building the new homepage on staging. Canvas: https://claude.ai/artifact/EdFLv1U8gCUpRsaiJe4tdX → page **"Final design"**: board `Final.dc.html` (desktop 1,440, full page) and `FinalPhone.dc.html` (390). The markup/CSS of those boards is the reference (reskin tokens from `rsg_2022/css/rsg-skin.css`, fonts Big Shoulders Display + Public Sans). Other canvas pages (Homepage, Hero experiments, Poster hero, Dark hero) are history, not spec. Data behind the choices: `HOMEPAGE-DATA-2026-11-16.md`.

## Page order (both layouts)
1. Header 2. Hero 3. Our picks right now (overlaps the hero) 4. Comparisons 5. Latest reviews 6. Reviews by brand + Reviews by type (with GPS links) 7. How we test 8. Free training plans 9. Footer.
No Raptive display ads on the homepage (only the existing video unit, position to confirm with Raptive). No publish dates anywhere on the page.

## 1. Header
- Navy `rsg-ink`, 64 px, 3 px `rsg-blue` bottom line, sticky. Logo (`logo2020-navy.svg`, 140×30) with **"EST. 2009"** under it (11 px, 700, letter-spacing .32em, `rsg-on-ink-muted`).
- Nav: Reviews ▾, Comparisons ▾, Best ▾, Shoe Finder, GPS, Training; search icon button. Below 992 px: search + menu buttons (44 px).

## 2. Hero — dark, photo of the latest review
**Photo = the latest published review's first gallery photo (own test photo), automatic.** Mock-up uses ASICS Gel Cumulus 28.
### Desktop (≥ 992 px)
- Full-bleed photo. Left part: same photo blurred (≈30 px), darkened, under a navy veil (gradient `rsg-ink` .86 → 0 at ~70 %). Right ~70 %: sharp photo, slightly dimmed (brightness .82), mask fades it in from the left.
- Text column (max 580 px, left aligned, white):
  - Small line: "430+ reviews · 173 comparisons · every shoe bought, never sent" (label style, `rsg-on-ink-muted`).
  - H1 as a justified two-line block: **"INDEPENDENT RUNNING / SHOE REVIEWS SINCE 2009"** — both lines set to the same width with container units (`.lock`: l1 = 100cqi/8.72, l2 = 100cqi/10.24), Big Shoulders 900, uppercase.
  - Lead: "Every shoe bought with our own money and run 50+ miles before we score it." (20 px).
  - Search bar: one white 60 px bar, magnifier icon inside left, blue "Search" button inside right; placeholder "Search a shoe, e.g. Pegasus 42"; visually hidden label.
  - "Most read" + pills (see-through white): Pegasus 42 (review), Megablast vs Superblast 2 (comparison), Vomero Plus (review), Clifton vs Bondi (comparison) — from Search Console, to be refreshed.
  - Thin divider, label "Or start browsing", then two **underlined text links** (not buttons): "All 430+ reviews" → /reviews/, "or try the Shoe Finder" → wizard. Shoe Finder deliberately secondary.
- Bottom right on the photo: tags "LATEST REVIEW" (blue) / shoe name (navy) / "Tested by {tester}, bought at {shop}" (white) → link to the review.
- **No score bib in the hero** (removed 17 Nov).
- Scroll effect: photo drifts down + zooms slightly as the hero leaves the screen (CSS scroll-driven animation, `animation-timeline: view()`), tags slide out; off for `prefers-reduced-motion`; browsers without support show it still.
### Phone/tablet (< 992 px) — "poster"
- Photo fills the hero (min-height = screen − header, capped at 760 px): whole photo lightly blurred (3 px), darkened (.55), navy tint + vignette. No directional fade.
- Centered column: H1 in three lines **"INDEPENDENT RUNNING / SHOE REVIEWS / SINCE 2009"** (capitals, Big Shoulders 800, letter-spacing .02em, ≈42 px at 375); lead (17 px); search bar; **"Most read" pills in one swipeable row** (44 px); then, with ~30 px extra space above, two underlined white links "Browse all 430+ reviews" / "Try the Shoe Finder".
- Bottom center: "Photo: {shoe}, our latest review" → link to the review.
- (Sentence-case variant was shown; capitals chosen to match desktop.)
- Build note: the mock-up carries two hero blocks toggled by CSS. On the site build **one** hero (one H1, one search input) whose layout changes by breakpoint.

## 3. Our picks right now (brought up over the hero)
- Section pulled up over the hero: desktop −80 px, phone −56 px; content sits in a white card (radius 6, shadow), grey `rsg-paper` background starts below the overlap.
- H2 "Our picks right now" (not "Best running shoes right now": /top/ must own that query). Link "The full list, with alternatives" → /top/best-running-shoes-right-now/. Intro links "best running shoes right now" to the same page.
- Table, one shoe per row (navy header row: #, Pick, Our score, Price today): rank · own test photo · category label (blue) + name + "weight · drop (· carbon plate)" · score box · small grey link "from $X at {shop}" (rel sponsored nofollow) · quiet button **"Read review"** (no yellow buy buttons on the homepage).
- Rows (from the /top/ page): Daily training — ASICS Novablast 5 (9/10); Beginners — Saucony Ride 18 (9/10); On a budget — Nike Winflo 11 (10/10); Long runs — Hoka Bondi 9 (8/10); Fast training — adidas adizero Evo SL (9/10); Marathon racing — Brooks Hyperion Elite 4 PB (8/10).
- Phone: rows become swipeable cards (82 % width, snap), full-width 4:3 photo with rank badge, name + score, price link, full-width "Read review".
- Under the table: "US prices, men's sizes, checked {date}. We earn a commission if you buy through these links. It never changes a score." (Amazon requires the price timestamp.)
- Data: live DFS prices per visitor country (same source as the article "Shoes in this article" box), with the usual fallback; own click-spot code + Amazon US tracking ID group `rsg-bestof-20` (`home-*`), "seen" logging.

## 4. Comparisons
- Left: H2 "Can't choose between two shoes?" + text "We've run both. Each comparison puts two shoes side by side: how they ride, how they fit, weight, drop, our scores and today's prices. Then we tell you which one to buy, and for what kind of running." + link "All 173 comparisons". **No search box** (removed).
- Right: "Most read" — 6 cards (share image 1200×630 + title): Megablast vs Superblast 2, Clifton vs Bondi, Vomero Premium vs Vomero Plus, Nimbus vs Novablast, Nimbus vs Kayano, Ghost vs Adrenaline (Search Console, 90 days).

## 5. Latest reviews
- H2 "Latest reviews", link "All 430+ reviews". 6 cards: own test photo 4:3, name, score box, one-line verdict, "Tested by {name}". **No dates.**
- **The 6 newest reviews, including the one in the hero photo** (added back 17 Nov): Cumulus 28, Pegasus 42, Glycerin 23, Ride 19, Structure Plus, Nimbus 28.

## 6. Reviews by brand / by type
- "Reviews by brand": 8 logo tiles (navy, single colour) → /reviews/{brand}/all/: ASICS, Nike, Brooks, Saucony, Hoka, New Balance, adidas, Mizuno; link "All brands". **Mock-up uses the site's old GIF logos (old Brooks/Hoka One One/Nike marks): replace with current logos before go-live.**
- "Reviews by type": photo tiles (own test photos, navy overlay, display label) → Cushioned, Stability, Lightweight, Racing, Trail (/reviews/all/{type}/), plus a blue **GPS watches** tile with direct links to Garmin Forerunner 265, Coros Pace 3, Garmin Forerunner 245, Garmin Forerunner 55 (keeps homepage links to individual GPS reviews; Black Friday). No "All GPS" link until /reviews/gps/ lists watches (today it lists shoe reviews).

## 7. How we test
- Light card (white, blue top border) on grey — not dark (dark read as the footer).
- H2 "How we test", text "No free pairs, no paid scores. Each shoe is bought from a normal shop, and every review links to where we bought it. Then one of our testers runs it on their usual routes until they know it."
- Facts: Run in every shoe 50+ miles · Comparisons 173 · Shoe reviews 430+ · Specs: weight and drop from the brand (testers wear different sizes). Button "How we review" → /about-2/ (How we review section).
- "The testers": Brandon Law, Kristin Lassen, Jim Coulson, Henry Howard, TJ Jones, Michael Mason-D'Croz (photo, author link, credential from the byline).

## 8. Free training plans
- H2 "Free training plans", "5K, 10K, half and full marathon, as PDFs. More than 60,000 runners have trained with them."; email field + "Send me the plans"; "We'll also email you deals on shoes we've tested. Unsubscribe any time. Privacy policy". Wire to MailerLite (reuse the Shoe Finder connection).

## 9. Footer
- Navy; logo + EST. 2009; "Independent running shoe reviews and comparisons. Made in Amsterdam, tested on roads and trails everywhere."; columns Reviews (All reviews, Comparisons, Best running shoes, Best daily trainers, GPS watches), Tools (Shoe Finder, Training plans, Running shoes FAQ), About (About us, How we review, Contact); bottom line © 2026 RSGMedia · KvK, Legal disclaimer, Privacy policy, "We earn commissions from shop links. Scores are never for sale."

## SEO / build checklist
- Title starts with "Running shoe reviews" (+ comparisons); one H1; Organization + WebSite schema (sameAs for entity disambiguation).
- Keep internal links listed above (GPS reviews, daily trainers, training plans).
- Hero photo: serve WP sizes/srcset, preload; fonts self-hosted (already).
- Machine-readability check on the new markup (going-forward rule).
- Counts (430+ reviews, 173 comparisons) from real totals, not the old "1,239".
- Timing: ship a few days before or a week after the article pruning (~22 Nov).
- Before go-live: current brand logos; /top/best-running-shoes-right-now/ title still says "(April 2025)"; click-spot codes + "seen" for the homepage spots; Raptive video unit position.
- Separate, urgent: public REST data on reviews exposes a commenter email (old subscribe-to-comments meta) — strip it.
