# Brief — harden the Stargrid admin API (for a new chat)

> **DONE 6 Nov (options 1 + 2, plus a password gate instead of a tunnel).** Results, tests and rollbacks: `archive/HANDOVER-2026-11-05.md` → "Done — Stargrid admin API hardening". The text below is the original brief.

_Written 2026-11-06 at the end of the Hetzner security session. Server facts: `../../CONTEXT.md` (Hetzner section) and `HANDOVER-2026-11-05.md` (this folder)._

## Start the new chat with this
> RSG project, workstream Hosting → Hetzner. Read README.md, hosting/hetzner/HANDOVER-2026-11-05.md and hosting/hetzner/STARGRID-ADMIN-API-HARDENING.md. Do the Stargrid admin API hardening described there. One change at a time, backup first (detached), tell me in plain language what you will change and wait for my OK before anything that can break the game or the email approve/veto links. SSH key: `_private/claude-hetzner-key` (port 2222, user ruggero, host 116.203.146.177). Close with the docs updated (handover, CONTEXT.md, DECISIONS.md).

## The problem (plain language)
`admin.playstargrid.com` is on the internet and protected by **one shared token**. The same container has the **Docker control socket** (`/var/run/docker.sock`). Whoever gets the token can make the server start any container, which is the same as owning the server. The token is also accepted in the URL (`?token=…`), so it ends up in logs and browser history.

## What is there (verified 6 Nov)
- Container `stargrid-api` (node:20-alpine), code in `/home/ruggero/stargrid-api` (bind-mounted to `/app`), **not in git** (the nightly backup is the only copy: `git init` first is a good idea). Compose: `/home/ruggero/compose/docker-compose.yml`.
- Public route: Nginx Proxy Manager host 5 `admin.playstargrid.com` → `stargrid-api:3002`.
- `src/routes/admin.js`: middleware `auth` reads `x-admin-token` header **or** `?token=`, compared to `process.env.ADMIN_TOKEN`. All admin routes use it (puzzles, entity candidates/details, regenerate-hint, validate, `/generate`).
- `/generate` (≈ lines 453–481) uses dockerode `docker.run(...)` to start the `stargrid-generator` image. **This is the only reason the socket is mounted.**
- `/api/admin/act` (GET + POST) has no token on purpose: it serves the **email approve/veto links** (signed with `sig` + `exp`, single use via table `action_tokens_used`). It must stay public.
- The admin web UI is served as static files at `/admin`.

## Options (pick with the user; the user is not a security expert, wants low friction)
1. **Minimum (low effort):** accept the token in the header only (drop `?token=`), rotate the token, keep everything else. Reduces leakage, does not remove the root-equivalent risk.
2. **Recommended:** remove the Docker socket from the API container. Move generation out: the API writes a "generate requested" row (job table in Postgres) and a small host-side worker (systemd service or cron on the host) runs `docker run stargrid-generator` and writes the result. Alternative: a Docker socket proxy that only allows creating/starting that one image (weaker).
3. **Reduce exposure at the proxy:** in Nginx Proxy Manager publish only `/api/admin/act` (and the public game API paths) on `admin.playstargrid.com`; keep the other admin routes private (SSH tunnel, like Portainer) or behind an allow-list. Cheap and strong; costs a tunnel step when using the admin UI.
Combine 3 + 2 if the user is willing to use the tunnel for the admin UI.

## Acceptance tests
- Unauthenticated `GET /api/admin/puzzles` → 401; token in query string → rejected (after option 1).
- Email approve and veto links still work end to end (use a real pending puzzle or a test row; links are single-use, so test carefully).
- Puzzle generation still works through the new path; `docker inspect stargrid-api` shows **no** `docker.sock` mount (option 2).
- `playstargrid.com` and the game API unaffected; nightly backup still runs.

## Rules for the work
- Backup first, detached: `sudo systemd-run --unit=rsg-backup-manual-$(date +%s) /usr/local/bin/rsg-backup.sh`. Copy `stargrid-api/` and the compose file before editing (`.bak-<date>`).
- One change at a time; test after each; keep a rollback command in the handover.
- Do not print secret values (token, DB password) into the chat or docs; put new secrets in `/home/ruggero/compose/.env` (mode 600).
- Also decide: rotate `ADMIN_TOKEN` and store the new one in 1Password.
