# RSG — rules for every session

Start: `git pull`, then read `README.md` (primer) and `ROADMAP.md`, then the
`RUNBOOK.md` of the workstream you touch. Folders have their own CLAUDE.md
(e.g. `theme/`).

## Ruggero's rules
- Never deploy to live without his explicit "deploy to live". "OK", "go",
  "continue" are not that. Staging first, always.
- Change only what he asks ("change the font" = only the font); keep every
  change revertable.
- Simple, maintainable solutions over workarounds; re-check a design
  critically before building it.
- Verify against the real systems; don't write a fact you haven't checked.
- Name price spots as he sees them ("sidebar price card"), never by code alone.
- Page or hero designs: always show mobile next to desktop.
- Claude handles all git (commits, scans, pushes, tags); he doesn't use git.
- Server security: plain language, design the safe setup yourself, no heavy
  login friction, one standing Claude SSH key (no new key per session).

## Docs
This repo is the only place docs change. The claude.ai project holds copies
of README.md and ROADMAP.md only. No secrets in git (`_private/` stays out).
End every working session with the `rsg-close-out` skill.
