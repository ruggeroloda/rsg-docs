# DFS-NG — Next Session Kickoff Plan (Phase 0)

> Goal of next session: **stand up the new stack on the existing Hetzner box, load a current copy of
> production data, and get a first version of the read API returning byte-comparable JSON for real
> sampled queries** (shadow-test green on a sample). That proves the riskiest piece — search/output
> parity — on real data before we rebuild anything.
>
> Companion docs: `ARCHITECTURE.md` (current system), `PROPOSAL-dfs-rewrite.md` (target + contract).

---

## Kickoff readiness (verified 2026-06-21)
- ✅ **Hetzner SSH works** via `hetzner-key.pem` (ED25519) → `ruggero@rsgmedia` (116.203.146.177:2222),
  **passwordless sudo**, Docker accessible.
- ✅ **Headroom:** 6.0 GB RAM available (of 7.6 GB; containers use ~770 MB), 125 GB disk free. Swap = 0.
- ⚠️ **Box runs more than `infrastructure.md` lists:** a `stargrid` app (`stargrid-web/api/imggen`)
  plus n8n, Postgres, Adminer, NPM, Portainer. Load is light, but update the infra doc and keep DFS
  resource-capped.

## 0. Confirm at kickoff (≈5 min, before any building)

1. **Hetzner SSH access** — ✅ verified working (`hetzner-key.pem`, `ruggero@116.203.146.177:2222`).
2. **Repo** — create a Git repo for DFS-NG (GitHub?) or build in a local worktree and push later. Pick one.
3. **Two open product decisions** (can defer past Phase 0, but note them):
   - `[dfscat]` raw-SQL category path → migrate to a structured endpoint (needs an RSG theme change).
   - Live-Amazon-blend (`amazon` param) — keep or drop in v1?

## Locked decisions (recap)
Headless API service · **MariaDB/InnoDB** + **PHP 8.3 / Laravel** + **Nginx/PHP-FPM** + **Redis** ·
Docker on the existing Hetzner box · MSRP owned by WordPress · keep the click-redirect endpoint +
the `export.cvs`/`export_brands.cvs` handshake · 14-field JSON contract (`{datafeed:{items:[…]}}`).

---

## 1. Phase 0 task list (ordered)

**A. Prep the box (don't disturb n8n/Postgres)**
- [ ] Confirm SSH; snapshot/note current `docker ps`, free RAM, disk.
- [ ] **Add swap** (e.g. 4 GB) — the box has none; protects n8n during our work.
- [ ] Create an isolated project dir + Docker network (separate from the n8n `compose/`).

**B. Scaffold the stack**
- [ ] `docker-compose.yml`: `mariadb` (InnoDB, utf8mb4), `redis`, `php-fpm` (PHP 8.3 + Laravel),
      `nginx`, `horizon` worker. Memory-cap the worker.
- [ ] New Laravel app skeleton; `.env` with secrets (not committed).
- [ ] Keep it **internal-only first** (no public route); add NPM host `dfs.rsgmedia.info` + TLS later.

**C. Load real production data (reference + test corpus)**
- [ ] On the DFS box: `mysqldump` the live `dfs` (we have the creds) → transfer to Hetzner.
- [ ] Import into the new MariaDB; convert **MyISAM → InnoDB**, **latin1 → utf8mb4**.
- [ ] Sanity-check row counts match live (products ≈ 1.0M, etc.).

**D. First vertical slice — the read API (parity target)**
- [ ] Build `GET /api/v1/products?search=&country_code=&clickref=&redirect_url=…` over the *imported
      current schema* (mirror, not the new model yet) — fastest path to a comparable response.
- [ ] Reproduce: the `{datafeed:{items:[…]}}` shape, the **14 fields**, **price-ascending sort**
      (`[0]` = best), the `Product not found` empty sentinel, deep-link via `redirect_url`.
- [ ] Port the **boolean search** semantics (`makeSqlQueryFromBoolean`/`get_name_clause`) — the
      subtle, must-match part.

**E. Shadow-test harness (the session's exit gate)**
- [ ] Pull a sample of real queries from the live `access.log` (we know 99.5% are `/api/v1/products`).
- [ ] Replay each against **old** (`45.33.39.133`) and **new**, diff the JSON per country.
- [ ] **Exit criteria:** the sampled queries return matching item sets + prices (within agreed
      tolerance) for at least US + one non-US country.

---

## 2. Explicitly OUT of scope for next session (later phases)
Ingestion rewrite (offers model, incremental upsert), the **classifier** (brand allowlist / gender /
kids), the **multi-country** endpoint, **Amazon PA-API 5** + freshness, the WP contract-v2
(post-meta + JS geo), cutover. Phase 0 is infra + data + parity-proof only.

## 3. Guardrails
- **Never touch the live RSG site or the live DFS box's data.** We only *read* prod (mysqldump) and
  build in isolation on Hetzner.
- Don't run `docker compose down -v` near the n8n stack (deletes volumes).
- Keep the legacy DFS fully running — DFS-NG is built alongside, switched only after parity.

## 4. What I need from you at the start
- Working Hetzner SSH (item 0.1) and a repo choice (0.2).
- A yes/no on the two open decisions (0.3) — or "defer", which is fine for Phase 0.

> After Phase 0 proves parity on real data, Phase 1 begins the real rebuild (ingestion + offers +
> classifier) per `PROPOSAL-dfs-rewrite.md`.
