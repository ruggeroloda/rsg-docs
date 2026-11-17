# Comparison redesign — Batch 2 rollout brief

**Date:** 2026-09-10 · **Status:** blocked on verdict/buy-if copy — pick up here next session.
Supersedes the "GSC check-in scheduled 2026-07-22" note in `CLAUDE.md` (that date came and went with
no check-in; this is it, 46 days late).

---

## 1. Pilot measurement (done 2026-09-10, via GSC UI — no API/export available in this session)

Compared **24 Jun–8 Sep 2026** (post-deploy) vs **9 Mar–8 Jun 2026** (pre-deploy, equal-length,
gap around the 6/24 deploy excluded) in Search Console → Performance, filtered per page.

**Site-wide, same window:** clicks 349k→205k (**-41%**), impressions 38.7m→19.1m (**-51%**),
avg. position 7.8→9.1 (**worse by 1.3**). The whole site took a hit in this period — looks
unrelated to the comparison redesign (worth its own investigation: algorithm update, or a side
effect of the 2026-06-23 technical-SEO/schema changes — not diagnosed here).

**All `-vs-` comparison pages (275 URLs) as a category:** clicks -42%, impressions **-60%**,
position worse by 0.2. The category did *worse* than the site average.

**The 3 redesigned pilots**, against that backdrop:

| Page | Clicks | Impressions | Avg. position |
|---|---|---|---|
| hoka-clifton-vs-hoka-bondi | 5.57k→4.29k (-23%) | 352k→227k (-35%) | 4.6→4.6 (flat) |
| asics-nimbus-vs-asics-kayano | 4.51k→2.79k (-38%) | 145k→86.7k (-40%) | 4.4→4.1 (better) |
| brooks-ghost-vs-brooks-adrenaline | 3.58k→2.27k (-37%) | 190k→118k (-38%) | 4.5→5.2 (worse) |

**2 matched non-redesigned controls** (similar traffic tier, same-family pairings, picked from the
same page-1-not-#1 tier as the pilots):

| Page | Clicks | Impressions | Avg. position |
|---|---|---|---|
| asics-nimbus-vs-asics-cumulus | 4.1k→2.25k (-45%) | 140k→69k (-51%) | 4.0→4.6 (worse) |
| nike-vaporfly-next-vs-nike-alphafly-next | 3.91k→1.98k (-49%) | 164k→85.9k (-48%) | 4.1→4.4 (worse) |

**Averages:** pilots -33% clicks / -38% impressions / position ~flat, vs controls -47% / -50% /
position worse by ~0.45. Cleanest single pair: asics-nimbus-vs-asics-kayano (redesigned, position
improved) vs its sibling asics-nimbus-vs-asics-cumulus (not redesigned, position worsened) —
same product family, same site section.

**Read:** not proof on 3 pilots against a noisy, declining backdrop, but a consistent signal
(3/3 beat their controls on clicks/impressions, 2/3 improved or held position) — enough to
justify rolling forward rather than rolling back or waiting for more data.

**Decision (Ruggero, 2026-09-10): roll out to a next batch of ~10-15 pages**, then measure again
before going wider.

---

## 2. Batch 2 candidates

Picked the same way as the original 3 pilots: high-traffic, evergreen franchise-vs-franchise
pairings, currently rendering the OLD comparison table (not yet flagged). Pulled from GSC
Performance → Pages, filtered to `-vs-`, sorted by clicks (24 Jun–8 Sep window).

| # | Slug | Clicks (24 Jun-8 Sep) | Impressions | Notes |
|---|---|---|---|---|
| 1 | `asics-superblast-vs-asics-novablast-3` | 3,287 | 121,839 | biggest of the batch |
| 2 | `asics-nimbus-vs-asics-novablast` | 2,939 | 65,683 | same nimbus family as pilot #2 |
| 3 | `nike-vomero-premium-vs-nike-vomero-plus` | 2,802 | 175,332 | huge impressions, low CTR — redesign's CTR lift could matter most here |
| 4 | `asics-cumulus-vs-asics-novablast` | 1,204 | 23,788 | |
| 5 | `asics-gt-1000-vs-asics-gt-2000` | 1,186 | 45,558 | |
| 6 | `saucony-endorphin-speed-5-vs-saucony-endorphin-speed-4` | 1,134 | 60,481 | |
| 7 | `hoka-clifton-10-vs-hoka-clifton-9` | 906 | 56,123 | |
| 8 | `hoka-one-one-clifton-vs-hoka-one-one-rincon` | 908 | 31,468 | held up best of any page seen tonight (clicks -10% vs everything else -35% to -60%) — worth a look at why before assuming redesign will help further |
| 9 | `nike-zoom-fly-vs-nike-vaporfly-next` | 931 | 30,655 | |

Also on the radar but not confirmed (full slug got cut off mid-pull, re-check in GSC Pages report,
row was ~3,172 clicks / 95,032 impressions): an **asics megablast vs [novablast or superblast] #2**
comparison — likely worth adding as a #10 if it's evergreen.

**Not included:** `asics-nimbus-vs-asics-cumulus` and `nike-vaporfly-next-vs-nike-alphafly-next` —
these are this round's *control* pages (see §1). Recommend leaving them un-redesigned for one more
measurement cycle so there's still a clean non-pilot comparison set; fold them into a later batch
once batch 2 has its own read.

**Not done:** post IDs for these 9 aren't pulled yet (no wp-admin/WP-CLI access this session — see
§4). Look each one up by slug in Comparisons in wp-admin, or `wp post list --post_type=comparison
--name=<slug> --field=ID` once a WP-CLI console is available.

---

## 3. What's blocking deploy: verdict/buy-if copy

Each page needs the same 3 fields the 3 pilots have (see
`comparison-redesign-20260624/COMPARISON-ACF-FIELDS-DEPLOY-LOG-2026-06-28.md` for the full field
spec) — **Ruggero is writing these, not today:**

- **`quick_verdict`** — short-form author verdict/TL;DR (pilots ran 191-266 characters)
- **`buy_if_1`** — "Buy the FIRST shoe if…" (max 160 chars)
- **`buy_if_2`** — "Buy the SECOND shoe if…" (max 160 chars)

(`comparison_redesign` itself is just the ON toggle — no copy needed for that one.)

---

## 4. Deploy steps once copy is ready

The ACF fields are already registered and live-editable (done 2026-06-28) — **this batch needs NO
code deploy**, just content + a cache purge, unlike the original pilot rollout which set meta via
raw WP-CLI.

1. wp-admin → Comparisons → open each of the 9 (10?) pages above.
2. Toggle **"Use new comparison design"** ON.
3. Fill **The verdict, in short** + **Buy the FIRST/SECOND shoe if…** with Ruggero's copy.
4. Publish.
5. Purge the **Rocket.net** cache ("Purge Everything") — required before the new layout serves to
   real visitors (edge cache is `s-maxage` ~30d).
6. Spot-check 2-3 live URLs render the new layout correctly (photos should auto-pull from each
   linked review's `sole_unit`/`upper`/featured image — no extra work needed there).
7. Append a deploy log entry (new file or append to the 2026-06-24 one) — page list, date, who
   wrote the copy, cache-purge confirmation.
8. Update `CLAUDE.md`: move batch 2 from "candidates" to "live", set a **real** next check-in date
   this time (the 2026-07-22 date was set and then never followed up — GSC took 46 days to
   actually get checked). Suggest ~6-8 weeks out, logged as an actual reminder/scheduled task,
   not just a note in a markdown file.

⚠️ **Access note (2026-09-10):** SSH from a Cowork session does not work — port 22 is blocked by
the session's egress (confirmed 2026-09-09/10, see `RSG-SESSION-LOG-2026-09-09-10.md`). Everything
above goes through the **Rocket.net panel over HTTPS**: wp-admin normally, or the panel's WP-CLI
console if doing this in bulk with `wp post meta update` (works — only `wp eval` is blocked there).
No login session for the panel exists in this browser tab yet; that'll need signing in first.

---

## 5. Open question for Ruggero

`hoka-one-one-clifton-vs-hoka-one-one-rincon` (candidate #8) barely declined at all this period
(-10% clicks vs. -35% to -60% for everything else pulled tonight). Worth a quick look at why
before redesigning it — if something else is already working there, the redesign's contribution
would be hard to isolate, and pushing a self-declared "winner" page for other diagnosis might be
more useful than redesigning it.
