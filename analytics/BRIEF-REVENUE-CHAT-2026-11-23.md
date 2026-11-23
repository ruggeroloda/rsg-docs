# Brief: revenue data chat (from 23 Nov 2026)

## START HERE: state at end of chat 1 (23 Nov 2026, 19:30 UTC)
Read `analytics/README.md` "Revenue data" first: it is the current state (tables, loaders, limits). Server side: `hosting/hetzner/RUNBOOK.md` "Revenue loader". Code: `hosting/hetzner/revenue/`.
- **In the database** (`rsg` schema, money in US dollars): Amazon US monthly Jan 2025 – Oct 2026 + 12 weeks (files, `analytics/amazon-reader/load_amazon.py`); CJ from 2009 ($182k); Awin account 131905 from 2011 ($83k, ECB rate of the order day); Rakuten from 24 Nov 2022 ($24.6k; older not reachable); ECB rates since 1999; **offers** (`rsg.offer`) from Awin, Rakuten, CJ. View `rsg.revenue_by_page_type`.
- **Daily 06:55 UTC** (`/etc/cron.d/rsg-revenue` → `~/revenue/run_daily.sh`): fx, CJ, Rakuten, Awin, offers. Nothing is written unless every download succeeded; CJ/Awin upsert by id; Rakuten refuses a window with < half the stored rows.
- **Findings:** the Amazon $/click drop started **June 2026** on review links (Jun–Jul click flood: 6.1k and 14.5k review clicks with fewer orders; Aug–Oct orders per click 2–5 % vs 6–9 % in 2025); comparisons held $0.22–0.32/click. October 2026 per 1,000 GA4 visits (129,296): ads (Raptive) $8.80, affiliate $3.37 (Amazon 2.53, Rakuten 0.45, CJ 0.27, Awin 0.11), total $12.16.
- **Offers read (23 Nov):** ~20 of 57 useful (Black Friday codes: Under Armour BLACKFRIDAY/EXTRA10, PUMA CHEER, ASICS CYBER, Finish Line FNL10/15; Zappos shoe deals); Rakuten ASICS lists 21 stale "evergreen" offers (2022–23 models) → filter needed; links can carry our email tag (Rakuten `u1`, CJ `sid`, Awin `clickref`).

## Next, in order (ask Ruggero which first)
1. **Deals-email view + first Black Friday draft** from `rsg.offer`: keep offers started in the last 60 days or with a code ending within 30 days, drop end dates > 1 year ahead, US/all regions, merge identical titles; pair with shoe-level deals from the DFS price data; tag links per email.
2. **Sub-ID → page type**: the new `<post id>-<spot>-<country>` sub-IDs (all networks since 10 Nov) map via the post's type (not built; view group `unmapped`).
3. **Junk-click check**: Jun–Jul 2026 Amazon review clicks vs our own click log.
4. **More networks** (Ruggero prepares, plain text files in `_private/`, also 1Password): **Impact** (Account SID + auth token; Brooks, Mizuno, adidas, New Balance US: actions + deals/promo codes API), **AvantLink** (affiliate ID, API key, website ID; AffiliateReport + AffiliateMerchantDiscounts), **Partnerize** (only if programmes are active; application + user key), **Webgains** (Ruggero 23 Nov: old Runners Need UK partnership, program 5503, looks suspended; API not checked yet).
5. **Raptive** per page (Creator API beta ticket), then the weekly revenue per 1,000 views by page type (strategy step 2).

## Waiting on Ruggero
- **Rotate the CJ token and the Rakuten report link** (both shown in chat 23 Nov), then Claude swaps them in `~/revenue/.env`.
- ~~Name 6 closed Awin programmes~~ dropped 23 Nov (Ruggero: not found in Awin, no business with them for years; they stay `awin <id>`).
- OK to add "revenue loaded in the last 30 h" to the `rsg-umami` health check (failures are silent today).
- Credential files: save as **plain text** (TextEdit Format → Make Plain Text); twice saved as RTF on 23 Nov.

## Session notes (23 Nov)
- GitHub from the Mac's device shell failed (no key access); a cloud clone with the repo attached works: commit on the Mac, `git format-patch`, stage, `git am` in the clone, push. The Mac's local `main` is behind/diverged in hashes (same content): `git pull` there merges cleanly.
- rsgmedia SSH from the device shell works (`_private/claude-hetzner-key`, port 2222). Never end an ssh command with `< /dev/null` when piping data into it; `pkill -f` must not match the ssh command line itself.
- `~/revenue/backfill-cj.sql` and `backfill-awin.sql` (one-off SQL, ~30 MB each) can be deleted.

---

## Earlier state (morning of 23 Nov, kept for history)

For a new chat in the RSG project. Start as usual (`git pull`, `README.md`, `ROADMAP.md`), then read `analytics/REVENUE-DATA-2026-11-23.md` (the plan), `analytics/amazon-tags-2026-11-23/PLAN.md` (Amazon tag history + the new map) and `analytics/README.md`.

## Where things stand (23 Nov, 13:20 UTC)
- **Amazon US tags are one per page type since 23 Nov 13:11 UTC** (live `0ace71d`, change log 139 + 140). The weekly and monthly Amazon reports therefore mix three tag schemes: until 10 Nov (old DFS tags), 16–23 Nov (per spot: revside, revmob, revbar, revdrw, revlow, bestof), from 23 Nov (per page type: 11 IDs). Tag → page type mapping per period: `amazon-tags-2026-11-23/PLAN.md` parts A + B. Compare per tag only from 24 Nov; before that, compare totals or map by the period's table.
- **Amazon files on the Mac** (`data-drops/amazon/`, not in git): monthly Tracking-Id + Category Jan–Dec 2025 and Oct 2026 (+ Linked Product / Top Sellers Oct 2026); weekly Tracking-Id 31 Aug–22 Nov 2026. **Ruggero is downloading the 2026 monthly files now** (Jan–Sep + Nov so far). The Tracking-Id files carry no date range inside: check each new file's name against its totals (Category's Date column = month end; same-month Tracking-Id has the same totals) before using it. November is a partial month.
- **First reads already done** (plan "First reads"): 2025 $15,010 from 93.3k clicks ($0.16/click); Oct 2026 $0.07/click, so the value drop happened in 2026; comparisons earn the most per click; `pimeagi-20` = untagged links.
- **Database:** the `rsg` schema lives in the `umami` database on rsgmedia (`docker exec postgres psql -U rsg_analyst -d umami`; SSH `ruggero@116.203.146.177 -p 2222`, key `_private/claude-hetzner-key`). Change log = `rsg.change_log` (add rows with `rsg-changelog-add`, README "Change log").
- **Waiting on Ruggero:** Raptive Creator API beta ticket (drafted 23 Nov), network reporting tokens (CJ, Awin, Rakuten, AvantLink, Impact) into 1Password.

## Work list, in order (plan "Steps" 3–5)
1. **Read the 2026 months as they arrive** (23 Nov: reader `analytics/amazon-reader/read_amazon.py` built and run on 2025 + Oct 2026 + weeklies, result in the plan's "First reads"; Jan–Sep + Nov 2026 files not in the folder yet: rerun when they land): month by month clicks, orders, earnings, $/click, by tag mapped to page type; find **when in 2026** the value per click dropped and whether it is one page type, untagged (`pimeagi-20`) or everything. Show Ruggero a short table first (his style: summary, then step by step).
2. **Done 23 Nov** (`analytics/README.md` "Revenue data"). **Table design** for all sources (grain day × source × account × tag/sub-ID × page; € at the ECB rate of the day, original currency kept; two dates; 90-day re-pull) + the tag/sub-ID → page type mapping table with validity dates (the three Amazon schemes above). Propose before building.
3. **Amazon loader** (done 23 Nov, `analytics/amazon-reader/load_amazon.py`) from the drop folder (files are monthly/weekly summaries, not daily), then the API networks once tokens exist (backfill ≥ 1 Sep), Raptive once beta access exists.
4. **Weekly view:** revenue per 1,000 views by page type (strategy step 2), change dates marked; our click log vs Amazon clicks per week as a bot check.

## Rules for this chat
- Facts only from the files/systems, checked; name price spots as Ruggero sees them.
- Amazon files and anything with money per account stay out of git (`data-drops/` is ignored); summaries in docs are fine.
- Nothing here touches the live site. Anything that changes tracking or the site goes staging first and waits for "deploy to live".
- End with the `rsg-close-out` skill.

## Device-shell notes (23 Nov)
- The session's shell has no `known_hosts`: for GitHub take the keys from `https://api.github.com/meta` (fingerprint ED25519 `SHA256:+DiY3wvvV6TuJJhbpZisF/zLDA0zPMSvHdkr4UvCOqU`) into a session file and set `GIT_SSH_COMMAND="ssh -i _private/github-rsg-docs-deploy-key -o IdentitiesOnly=yes -o UserKnownHostsFile=<file>"`; rsgmedia: `-o StrictHostKeyChecking=accept-new` once. Ask for delete permission on the folder once (git lock files).
- Each shell call ends its processes (an SSH ControlMaster does not survive between calls): batch server work into one call.
