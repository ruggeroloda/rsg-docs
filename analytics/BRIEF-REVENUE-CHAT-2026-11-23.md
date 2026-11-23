# Brief: revenue data chat (from 23 Nov 2026)

For a new chat in the RSG project. Start as usual (`git pull`, `README.md`, `ROADMAP.md`), then read `analytics/REVENUE-DATA-2026-11-23.md` (the plan), `analytics/amazon-tags-2026-11-23/PLAN.md` (Amazon tag history + the new map) and `analytics/README.md`.

## Where things stand (23 Nov, 13:20 UTC)
- **Amazon US tags are one per page type since 23 Nov 13:11 UTC** (live `0ace71d`, change log 139 + 140). The weekly and monthly Amazon reports therefore mix three tag schemes: until 10 Nov (old DFS tags), 16–23 Nov (per spot: revside, revmob, revbar, revdrw, revlow, bestof), from 23 Nov (per page type: 11 IDs). Tag → page type mapping per period: `amazon-tags-2026-11-23/PLAN.md` parts A + B. Compare per tag only from 24 Nov; before that, compare totals or map by the period's table.
- **Amazon files on the Mac** (`data-drops/amazon/`, not in git): monthly Tracking-Id + Category Jan–Dec 2025 and Oct 2026 (+ Linked Product / Top Sellers Oct 2026); weekly Tracking-Id 31 Aug–22 Nov 2026. **Ruggero is downloading the 2026 monthly files now** (Jan–Sep + Nov so far). The Tracking-Id files carry no date range inside: check each new file's name against its totals (Category's Date column = month end; same-month Tracking-Id has the same totals) before using it. November is a partial month.
- **First reads already done** (plan "First reads"): 2025 $15,010 from 93.3k clicks ($0.16/click); Oct 2026 $0.07/click, so the value drop happened in 2026; comparisons earn the most per click; `pimeagi-20` = untagged links.
- **Database:** the `rsg` schema lives in the `umami` database on rsgmedia (`docker exec postgres psql -U rsg_analyst -d umami`; SSH `ruggero@116.203.146.177 -p 2222`, key `_private/claude-hetzner-key`). Change log = `rsg.change_log` (add rows with `rsg-changelog-add`, README "Change log").
- **Waiting on Ruggero:** Raptive Creator API beta ticket (drafted 23 Nov), network reporting tokens (CJ, Awin, Rakuten, AvantLink, Impact) into 1Password.

## Work list, in order (plan "Steps" 3–5)
1. **Read the 2026 months as they arrive** (23 Nov: reader `analytics/amazon-reader/read_amazon.py` built and run on 2025 + Oct 2026 + weeklies, result in the plan's "First reads"; Jan–Sep + Nov 2026 files not in the folder yet: rerun when they land): month by month clicks, orders, earnings, $/click, by tag mapped to page type; find **when in 2026** the value per click dropped and whether it is one page type, untagged (`pimeagi-20`) or everything. Show Ruggero a short table first (his style: summary, then step by step).
2. **Table design** for all sources (grain day × source × account × tag/sub-ID × page; € at the ECB rate of the day, original currency kept; two dates; 90-day re-pull) + the tag/sub-ID → page type mapping table with validity dates (the three Amazon schemes above). Propose before building.
3. **Amazon loader** from the drop folder (files are monthly/weekly summaries, not daily), then the API networks once tokens exist (backfill ≥ 1 Sep), Raptive once beta access exists.
4. **Weekly view:** revenue per 1,000 views by page type (strategy step 2), change dates marked; our click log vs Amazon clicks per week as a bot check.

## Rules for this chat
- Facts only from the files/systems, checked; name price spots as Ruggero sees them.
- Amazon files and anything with money per account stay out of git (`data-drops/` is ignored); summaries in docs are fine.
- Nothing here touches the live site. Anything that changes tracking or the site goes staging first and waits for "deploy to live".
- End with the `rsg-close-out` skill.

## Device-shell notes (23 Nov)
- The session's shell has no `known_hosts`: for GitHub take the keys from `https://api.github.com/meta` (fingerprint ED25519 `SHA256:+DiY3wvvV6TuJJhbpZisF/zLDA0zPMSvHdkr4UvCOqU`) into a session file and set `GIT_SSH_COMMAND="ssh -i _private/github-rsg-docs-deploy-key -o IdentitiesOnly=yes -o UserKnownHostsFile=<file>"`; rsgmedia: `-o StrictHostKeyChecking=accept-new` once. Ask for delete permission on the folder once (git lock files).
- Each shell call ends its processes (an SSH ControlMaster does not survive between calls): batch server work into one call.
