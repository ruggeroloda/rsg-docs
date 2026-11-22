# Comparisons linked from the "head to head" test (22 Nov 2026)

_The 10 reviews that carry the "head to head" block (test live 22 Nov 2026 09:19 UTC, theme `f999afc`, runs to 7 Dec, read 8 Dec; `theme/RUNBOOK.md`). Each line = one tile, in the order shown (comparison views 15–21 Nov). "Refreshed" = rewritten in the SEO checklist 2.1 batches (18 + 20 Nov, `rsg.change_log`); every other one has not been checked. Source of the list: `RSG_CW_TEST` in `theme/rsg_2022/includes/reviews/compare-with.php` (changing a tile = change that list, staging → "deploy to live"; changing it during the test changes what the "on" group sees, so log it)._

| Review | Tile | Comparison | Status |
|---|---|---|---|
| Nike Pegasus 42 | vs Nike Vomero 18 | /comparison/nike-pegasus-vs-nike-vomero/ | refreshed 18 Nov |
| | vs Nike Pegasus Premium | /comparison/nike-pegasus-premium-vs-nike-pegasus/ | to check |
| | vs Nike Winflo 11 | /comparison/nike-winflo-vs-nike-pegasus/ | refreshed 18 Nov |
| | vs Nike Structure 25 | /comparison/nike-pegasus-vs-nike-structure/ | **refreshed 22 Nov** (live, change log 135; Structure 25 → 26 swapped, text rewritten from the Pegasus 42 + Structure 26 reviews; meta description rewritten: it called the Structure neutral) |
| Nike Vomero Plus | vs Nike Vomero Premium | /comparison/nike-vomero-premium-vs-nike-vomero-plus/ | to check |
| | vs Nike Pegasus Plus | /comparison/nike-vomero-plus-vs-nike-pegasus-plus/ | to check |
| ASICS Gel-Nimbus 28 | vs Hoka Bondi 9 | /comparison/asics-nimbus-28-vs-hoka-bondi-9/ | **refreshed 22 Nov** (live ~11:05 UTC, change log 131; review specs fixed live, change log 132: Bondi 9 weight 9.3 → 10.5 oz, Nimbus 28 toe box normal → tight; backups Rocket `~/rsg_deploy_backups/comparisons-2026-11-22/`). Was: "Which one to buy" contradicted the table and review |
| | vs ASICS Gel-Nimbus 27 | /comparison/asics-nimbus-28-vs-asics-nimbus-27/ | to check |
| | vs ASICS Gel-Cumulus 28 | /comparison/asics-nimbus-vs-asics-cumulus/ | refreshed 18 Nov |
| | vs Brooks Glycerin 23 | /comparison/brooks-glycerin-23-vs-asics-nimbus-28/ | to check |
| adidas adizero Evo SL | vs adidas Adios Pro Evo 1 | /comparison/adidas-evo-sl-vs-adidas-adios-pro-evo-1/ | to check |
| ASICS Novablast 5 | vs ASICS Superblast 2 | /comparison/asics-superblast-vs-asics-novablast-3/ | **refreshed 22 Nov** (live, change log 133; review specs change log 134) (already Superblast 2 vs Novablast 5; text was wrong on stability and the Novablast; URL kept: slug change = redirect + tile change during the test; specs fixed on staging: Superblast 2 weight 9.0 → 8.8 oz + toe box normal → tight, Novablast 5 weight 8.7 → 8.8 oz + toe box normal → wide/normal, as in their texts) |
| | vs ASICS Gel-Nimbus 28 | /comparison/asics-nimbus-vs-asics-novablast/ | to check |
| | vs ASICS Megablast | /comparison/asics-megablast-vs-asics-novablast/ | to check |
| | vs ASICS Gel-Cumulus 28 | /comparison/asics-cumulus-vs-asics-novablast/ | refreshed 20 Nov |
| New Balance FuelCell Rebel v5 | vs Rebel v4 | /comparison/new-balance-rebel-v5-vs-new-balance-rebel-v4/ | to check |
| Nike Zoom Fly 6 | vs Nike ZoomX Vaporfly 4 | /comparison/nike-zoom-fly-vs-nike-vaporfly-next/ | to check (URL says Vaporfly Next%) |
| | vs Nike Zoom Fly 5 | /comparison/nike-zoom-fly-6-vs-nike-zoom-fly-5/ | to check |
| | vs Nike Pegasus 41 | /comparison/nike-pegasus-vs-nike-zoom-fly/ | **on staging 22 Nov** (Pegasus 41 → 42 swapped, text + meta description rewritten from the Pegasus 42 + Zoom Fly 6 reviews; spec fix on staging: Zoom Fly 6 toe box normal → tight, as in its text) |
| | vs Nike Air Zoom Tempo Next% | /comparison/nike-zoom-fly-vs-nike-tempo-next/ | to check (old model) |
| Brooks Adrenaline GTS 25 | vs Brooks Ghost 17 | /comparison/brooks-ghost-vs-brooks-adrenaline/ | refreshed 18 Nov |
| ASICS Gel-Cumulus 28 | vs ASICS Gel-Nimbus 28 | /comparison/asics-nimbus-vs-asics-cumulus/ | refreshed 18 Nov |
| | vs ASICS Novablast 5 | /comparison/asics-cumulus-vs-asics-novablast/ | refreshed 20 Nov |
| | vs ASICS Gel-Kayano 32 | /comparison/asics-cumulus-vs-asics-kayano/ | refreshed 20 Nov |
| Saucony Ride 19 | vs Saucony Ride 18 | /comparison/saucony-ride-19-vs-saucony-ride-18/ | to check |
| | vs Saucony Triumph 23 | /comparison/saucony-ride-vs-saucony-triumph/ | refreshed 20 Nov |
| | vs Saucony Kinvara 14 | /comparison/saucony-ride-vs-saucony-kinvara/ | **refreshed 22 Nov** (live, change log 137; Kinvara 14 → 16 swapped, text + meta description rewritten) |
| | vs ASICS Gel-Cumulus 26 | /comparison/asics-cumulus-vs-saucony-ride/ | **refreshed 22 Nov** (live, change log 136; Cumulus 26 → 28 swapped, text + meta description rewritten; Ride 19 weight 9.0 → 9.2 oz as in its text, change log 138) |

28 tiles, 26 different comparisons (Nimbus vs Cumulus and Cumulus vs Novablast are on two reviews each); 7 refreshed, 19 to check. Tile names on the site come from the review's brand + model fields (e.g. "Asics Gel Nimbus 27", "Hoka One One Bondi 9").

**Wrong shoe in the comparison (checked 22 Nov, `first_shoe`/`second_shoe` vs newer reviews; Ruggero: fix these first):** Pegasus vs Structure (Structure 25 → 26), Pegasus vs Zoom Fly (Pegasus 41 → 42), Ride vs Kinvara (Kinvara 14 → 16), Cumulus vs Ride (Cumulus 26 → 28). Zoom Fly vs Tempo Next%: no newer Tempo review (old model, tile choice for Ruggero). Version pairs (Nimbus 28 vs 27, Rebel v5 vs v4, Zoom Fly 6 vs 5, Ride 19 vs 18) are meant that way; Zoom Fly vs Vaporfly already uses the Vaporfly 4 (only the address says Next%).

**State end of 22 Nov:** 12 of 26 refreshed (7 before, 5 on 22 Nov: Nimbus 28 vs Bondi 9, Superblast 2 vs Novablast 5, Pegasus 42 vs Structure 26, Cumulus 28 vs Ride 19, Ride 19 vs Kinvara 16; change log 131–138), 1 on staging (Pegasus vs Zoom Fly), 13 to check. All 4 wrong-shoe comparisons fixed (3 live, 1 staging). Ruggero 22 Nov: Zoom Fly vs Tempo Next% stays as it is. Review spec fields that contradicted their own text were fixed in the same pass (weights, toe box; old values in Rocket `~/rsg_deploy_backups/comparisons-2026-11-22/reviews-live-before.txt`).

**Next (23 Nov, max 5 live), by comparison views 15–21 Nov:** (1) Pegasus vs Zoom Fly: Ruggero reads staging → live; (2) Vomero Premium vs Vomero Plus (176 views); (3) Nimbus 28 vs Nimbus 27 (141; score box from the two reviews' ratings gives the 27 the win while the text calls the 28 better: text must follow the reviews, ratings are the testers'); (4) Glycerin 23 vs Nimbus 28 (116; calls the Nimbus "super soft"); (5) Pegasus Premium vs Pegasus (95). Then: Zoom Fly vs Vaporfly 94, Nimbus vs Novablast 80, Ride 19 vs 18 69, Megablast vs Novablast 66, Rebel v5 vs v4 53, Vomero Plus vs Pegasus Plus 44, Evo SL vs Adios Pro Evo 1 24, Zoom Fly 6 vs 5 20. Work files: Mac `content/_work/compare-refresh-2026-11-22/` (not in git).
