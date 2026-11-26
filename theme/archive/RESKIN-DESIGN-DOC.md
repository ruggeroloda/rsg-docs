# Reskin design document + design system — pointers (15 Nov 2026)

- **Design document** (decisions, rules, components, status): https://claude.ai/artifact/U6C4DFLkLeJq1hyUA44V79
- **Design system / visual reference**: https://claude.ai/artifact/1BPZk5NNAqZjqWgVbW3dZD — tokens (colour roles, type, spacing, radius), brand-book README, and 29 components rebuilt from staging markup with the real theme CSS (bootstrap, base, rsg-skin, rsg-dfs + template inline CSS), each shown at 1,366 / 820 / 390 px. The theme's width media queries are rewritten as container queries (and vw/vh as cqw/cqh) so each frame lays out like that screen width. No Raptive ads; links inert; prices = 15 Nov snapshot.

Build scripts (cloud container, not kept): extraction from staging with Playwright (rendered DOM, prices painted, ancestors kept as a skeleton), CSS conversion, images downscaled into `project/media/`. To refresh after more reskin changes: re-extract the changed components and republish their `preview.html` + `components/bundle.css`.

Sources for both: `RESKIN-A-R2-2026-11-14.md` (status table), `COMPARISON-RESKIN-2026-11-15.md`, `HTML-AUDIT-RESKIN-2026-11-15.md`, `NEXT-SESSION-RESKIN-STEP5.md`, `RESKIN-GO-LIVE-CHECKLIST.md`, `../claude/DECISIONS.md`. Tokens: `rsg_2022/css/rsg-skin.css`.

Keep current: when a reskin decision changes, update the design document's decision log and the affected section; when markup/CSS changes, refresh the affected component previews.
