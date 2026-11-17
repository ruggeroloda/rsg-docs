# Handover — DFS build step 2 (paused 6 Nov 2026, 22:10; resumed same evening)

## Start here (state 7 Nov, ~04:30)
**Build step 2 is done** (Ruggero accepted it after testing shoes on the screen; DECISIONS 7 Nov). Step 4 (Amazon US) is built too.
**Next: build step 3 — importers, Feeds screen and scheduling** (new chat): feeds as configuration (transport, format, column mapping with 20-row preview, sub-ID), nightly file import + match, nightly Amazon search, 6-hourly Amazon re-price, 70% row-count guard, failure email. Ruggero wants to see how the screens look as they are built. Until this exists nothing runs on its own: the data on the Shoes screen is frozen at 6 Nov 22:56 UTC.
Then: step 5 price data + history, step 6 WordPress on staging, step 7 production Linode + switch-over, step 8 next feeds (plan doc).

**Small items, 7 Nov:** done — sshd `AllowStreamLocalForwarding no`; Amazon client ID/secret redacted in `legacy-scripts/import_amazon_creators_{us,uk}.php` (values are in `_private/CREDENTIALS.md`; US = Hetzner `.env`); launcher handles an already-open tunnel; old preview page marked superseded; stale git files deleted.
**Fixed 7 Nov:** gender detection (plain Men/Women, Male/Female, B (M)/D (M), foreign words; see CONTEXT) and the Shoes list now reads each feed's latest run.
**Left for Ruggero:** rotate the Linode root password (it was printed in a session log on 6 Nov; needs 1Password; or accept until the box is cancelled at cutover).
**Left for later:** DE list prices (no `rrp_de`, no feed list price → DE offers not price-filtered; local-RRP worker in DECISIONS open questions); after ~20 Nov delete rollback copies (`ssh-hardening.conf.bak-20261106-dfs`, `…bak-20261107-streamlocal`, `/data/dfs.sqlite.bak-20261106-pre-metarules`, table `matches_run1`).

Decisions: `../DECISIONS.md` (6 Nov, DFS lines). Facts: `../CONTEXT.md` ("WordPress shoe data", "DFS build/test container").
Preview of where we are (read-only snapshot, rebuilt by hand): https://claude.ai/artifact/JoSr5Rj3ZF1LhaCv7u5nXN

## Done (code in `app/`, pushed to GitHub `ruggeroloda/rsg-dfs`, last commit 622fe77)
| Part | What | Where |
|---|---|---|
| 2.1 Shoe list | 429 published reviews with a `ds_query` (the "1,520" were mostly revisions), all active, gender "both". Read-only WP-CLI export from **staging** | `app/tools/wp_export_shoes.php`, `app/tools/pull_shoes.sh`, `app/dfs/shoes.py`, table `shoes` |
| 2.2 Rules | 429 legacy `ds_query` strings converted to must / any of / must not; 6 broken ones hand-repaired | `app/dfs/rules.py`, table `rules` |
| 2.3 Matcher (file feeds) | Nike US, Zappos, Nike DE (AWIN), RW DE: 132 shoes with offers, 344 offer lines; ~30 s for all four | `app/dfs/feeds.py`, `app/dfs/match.py`, table `matches`, view `offers` |

## Changed direction (6 Nov evening, see DECISIONS)
- Rules come **from WordPress metadata** (brand alias + family + version + per-shoe exceptions); `ds_query` is only a cross-check. Part 2.2 is redone this way.
- Variants (GTX, Trail, Mid, SE…) are judged by the word after the version, not by price (Cascadia 19 GTX on sale slipped through the price filter).
- Shoes screen: Mac only, via SSH tunnel.

## Done in the second session (6 Nov, late)
- **2.2 redone — metadata rules** (`app/dfs/rulebuild.py`, commit 281df7d): table `rules` now = family / version / suffix / aliases / exclude / allow_after per shoe, built from WordPress brand + model. The ds_query rules moved to table `legacy_rules` (cross-check only). DB backup before the change: `/data/dfs.sqlite.bak-20261106-pre-metarules`.
- Cross-check on the 6 Nov feeds (`python -m dfs.compare_rules 2026-11-06`, report `/data/reports/rule-compare-2026-11-06.json`): 367 of 429 agree; the 62 others are the new rule refusing other generations (24 shoes), variants (GTX, SE, Gore-Tex, TR, Waterproof, 9+…; ~30 shoes) or wrong products (4: Cielo Road ← "Cielo Blue" colour, Cloudsurfer 7 ← any Cloudsurfer, Zegama Trail ← Zegama Hike, Roav ← golf), and 4 NB/adidas shoes where it finds glued spellings legacy missed (1080v14, Adizero SL2). Only 2 real gaps, fixed as `EXCEPTIONS` (42898 FuelCell Trainer v2, 49167 Prime X3 Strung).

## Next, in order
1. ~~Metadata rules:~~ done (above); variant list confirmed and logged in DECISIONS.
3. ~~Minimal Shoes screen~~ done (6 Nov, late): http://localhost:18090 via `Open RSG server tools.command`. List + flags, per-shoe rule edit with instant preview, block/unblock, active/gender. sshd `PermitOpen` + tunnel script updated (approved). Tested end to end (block, save, reset, guards, isolation). Not seen rendered by Claude (no browser available); page script tested in jsdom on real data. 7 Nov: site-style price box added on the shoe page (Ruggero's request), same jsdom test.
2. ~~Re-run the matcher~~ done (commit ace0b9d): 114 shoes / 280 offer lines (was 132 / 344); every removal checked as a wrong match; details in CONTEXT ("Second DFS match run").
   Original brief: Metadata rules: clean fields (brand aliases, strip a brand repeated in `model`, e.g. "PUMA MagMax Nitro 2"), split family/version, default rule per shoe, variant check after the version; compare with the legacy rules and list only the disagreements.
2. Re-run the matcher; show what changed vs the 6 Nov run.
3. ~~Minimal Shoes screen (Flask):~~ done, see above. Original brief: list + flags; per shoe editable rule fields with instant preview of today's rows (kept/dropped + why); block a product. Tunnel design first (sshd `PermitOpen`, ask Ruggero).
4. ~~Amazon US test searches~~ done 6 Nov late (commit 113b721; results in CONTEXT, "Amazon Creators API"). Quota turned out ~190k calls/day (Ruggero's Associates stats), so no rationing. **Decided and built** (commit 48d97b0, `dfs/app/dfs/amazon.py`): 3 pages per search, ASIN pool (table `amazon_pool`) re-priced every 6 h (`python -m dfs.amazon search` nightly / `refresh` every 6 h; not scheduled yet: scheduling comes with build step 3, importers). First full run 6 Nov 22:01–22:56 UTC: 1,539 searches + 385 refresh calls, 0 errors; pool 3,844 ASINs; 307 shoes with Amazon offers; vs live DFS: equal cheapest 297, new lower 118, live lower 77 shoe × genders (details in CONTEXT). The 77 "live lower" cases are good candidates for the hand check.
   Amazon is one merchant "Amazon" in offers; the seller is kept in `amazon_pool.seller`.
   Original brief: Amazon US: a handful of test searches only (quota shared with the live DFS, ~1,450 calls/night).
5. Hand check of ~50 shoes on the screen.

## Known gaps
- Nike DE and RW DE have no list price, and almost no review has `rrp_de`: DE offers are not price-filtered. Later: local-RRP worker (DECISIONS, open questions).
- Brand-level merchant excludes from WordPress were dropped on purpose (redesign later).

## Security notes (6 Nov late)
- The Linode root password and the DFS MySQL password were printed into the session output by a failed masking command (Claude). Rotate the Linode root password (or accept it until the box is cancelled at cutover).
- `dfs/legacy-scripts/import_amazon_creators_us.php` (and probably `_uk.php`) holds the Amazon client secret in plain text; redact the copies (the live Linode file is read-only for us).

## Practical notes for the session
- Network: SSH from the Mac's device shell needs the code-execution network setting on **All domains** (it was off at the start of the 6 Nov chat).
- Keys (copy to `$HOME/k/`, `chmod 600` there; chmod fails inside the mounted folder): Hetzner `_private/claude-hetzner-key` (port 2222, user ruggero, 116.203.146.177); Rocket.net `theme/.ssh-rocket-key` (jfx9wij@65.181.124.237); GitHub deploy key `_private/github-rsg-dfs-deploy-key` (push with `GIT_SSH_COMMAND="ssh -i … -o IdentitiesOnly=yes"`).
- Deploy: `rsync -a --exclude .git --exclude __pycache__ app/ ruggero@…:/home/ruggero/dfs/app/` then `cd /home/ruggero/dfs/app/docker && docker compose up -d --build`. Run jobs with `docker exec dfs python -m dfs.<module>`; use `docker exec -i` when piping a script in.
- Git in the Mac folder can't delete its own lock files (deletion not allowed for the session): after a commit, move `.git/HEAD.lock` / `.git/objects/maintenance.lock` aside to `.git/stale-*` (several are there now and can be deleted by hand).
- The preview page is built in the cloud workspace, which can't reach Hetzner: data is exported from the container as gzip+base64 and copied over.
