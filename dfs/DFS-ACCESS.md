# DFS — Access & Box Topology (read this first)

**Last verified:** 2026-06-21

There are **two different servers**. Don't confuse them — this cost a wrong turn before.

## 1. LEGACY DFS (production — where prices are actually pulled)
This is the live price-comparison engine RSG consumes. **Go here for anything about real/current DFS data, imports, or prices.**

- **Host:** `45.33.39.133` (`dfs.runningshoesguru.com`), web root `/var/www/html`
- **SSH/SFTP:** port **2200**, user **root**, password **`[see _private/CREDENTIALS.md]`**
  (Source: `dfs-filezilla-sites.xml` now in `../_private/` — FileZilla export, password is base64 `[see _private/CREDENTIALS.md]`.)
  ⚠️ Does **not** accept the Hetzner key — password auth only.
- **DB:** MySQL on `127.0.0.1`, db `dfs`, user `root`, pass `[see _private/CREDENTIALS.md]`
  (in `/var/www/html/lib/database.inc`).
- **Connect from the sandbox:** `sshpass` isn't installable; use Python `paramiko`
  (`pip install paramiko --break-system-packages`), `paramiko.SSHClient().connect('45.33.39.133', port=2200, username='root', password='[see _private/CREDENTIALS.md]')`.
- **The HTTP API (`/api/v1/products`) is IP-allow-listed** to the WP boxes only
  (`131.153.143.194`, `35.214.170.233`) + referer-gated to `runningshoesguru.com`.
  You **cannot** probe it from the sandbox or even box localhost (both 403). To verify the API
  end-to-end, query the DB directly or run from an allow-listed host.

## 2. HETZNER (rebuild target — NOT production DFS)
Where the new DFS will be built. **Nothing about current Amazon prices lives here.** Runs Stargrid +
n8n + Postgres only. Don't go here to debug live DFS.

- **Host:** `116.203.146.177` (`rsgmedia`), SSH port **2222**, user **ruggero**
- **Key:** `claude-hetzner-key` (ED25519, in `_private/`; your own key is in 1Password), passwordless sudo, Docker.
- n8n has an "RSG — Full Import" workflow but it's **finance/invoices** (ABNAMRO/PayPal/ICS),
  **not** product/Amazon import. n8n is irrelevant to DFS pricing.

---

## Amazon US + UK — FIXED & LIVE (US 2026-06-21, UK 2026-06-22)
US (feed 40) and **UK (feed 41)** now pull live prices via **Creators API**, autonomously.
- **UK:** `import_amazon_creators_uk.php` (+ `.sh`), cron `30 3 * * *` (staggered after US 03:00),
  marketplace `www.amazon.co.uk`, tag `runshogur-21`, writes `59.csv`. First run 2026-06-22:
  6,650 rows, 0 errors; `import.php 41` loaded 6,610 products (Pegasus 42 ~£154+), sentinel gone.
- ⚠️ **WP exclude_merchants:** after enabling each market, check the WordPress price-box config —
  US (40) was hidden in `exclude_merchants` from when it was broken; **UK is merchant 41**, likely
  hidden the same way. Remove 41 from the WP excluded-merchants list (and add to `only_merchants`
  allow-lists) or UK offers won't show even though the data is live. (Same for DE 49 / IT 58 / CA 70.)

US (feed 40) details:
- **Fetcher:** `/var/www/html/admin/bin/import_amazon_creators_us.php` (+ wrapper `import_amazon_creators_us.sh`).
  Reads `export.cvs` → OAuth token → `searchItems` + OffersV2 → writes `datafiles_amazon/54.csv`
  (same 5-col format parser 32 expects). Log: `admin/logs/import_amazon_creators_us.txt`.
- **Cron:** `0 3 * * * import_amazon_creators_us.sh` (before the 06:00 `import.php all`). Crontab backed
  up at `/root/dfs-amazon-backup-20260621/crontab.bak`.
- **First run:** 1,448 shoes → 7,451 rows, 0 errors. `import.php 40` loaded 7,390 US products, live
  prices (e.g. Pegasus 42 ~$160 W / ~$175 M), all `updated_at` today. Old sentinel `99999999` gone.
- Backups of pre-change files in `/root/dfs-amazon-backup-20260621/`.
- ~60 rows/run fail to insert on special-char escaping (~0.8%) — minor, can refine later.
- **Note:** DFS API has a cache (cleared daily 05:00); site-facing prices fully refresh within the
  cache TTL / next clear.

**STILL TODO (other marketplaces, still on dead/frozen data):** DE (49), IT (58), CA (70)
need their own Creators API credentials (separate Associates Central each) + the same script
templated per locale (`x-marketplace` + that market's tag). US ✅ + UK ✅ done. See `CREDENTIALS.md`.

## Amazon pricing — verified state (2026-06-21)

**Headline: DFS is NOT pulling new Amazon prices, and it CANNOT be restored as-is.** Two reasons:
(1) the fetch job stopped Jan 27 + keys are invalid (below), and (2) **PA-API itself is retired** —
see the migration note immediately below. The daily import just re-imports frozen Jan-27 CSVs.

> 🚨 **PA-API IS DEAD — Creators API migration required (confirmed 2026-06-21).**
> Amazon **deprecated PA-API v5 on Apr 30, 2026 and retired the endpoint May 15, 2026**. The register
> page now says *"PA-API is no longer accepting new customers. Please onboard to Creators API."*
> **You cannot get new PA-API keys.** Replacement = **Amazon Creators API**, which uses **OAuth 2.0**
> (Client ID + Client Secret), NOT AWS access-key/secret SigV4 — so DFS's `amazon.inc` SigV4 path is
> obsolete and must be **rewritten**, not re-keyed.
> - **RSG marketplaces (5):** US (`rsg-single-20`), CA — region **NA**; UK (`runshogur-21`),
>   DE (`runshogur01-21`), IT — region **EU**. Creators API endpoint is unified; locale set via
>   `x-marketplace` header; credentials created **per locale** in Associates Central.
> - **Get credentials:** Associates Central → **Tools → Creators API** → apply (per marketplace) →
>   Add new credential → **Credential ID** (`[see _private/CREDENTIALS.md]`) + **Secret**
>   (`[see _private/CREDENTIALS.md]`). Token: POST `https://api.amazon.com/auth/o2/token`,
>   `[see _private/CREDENTIALS.md]`, `scope=creatorsapi::default`. Use **OffersV2** (Offers V1 retired).
> - **Eligibility catch (likely why keys lapsed):** final-accepted Associate + **≥10 qualifying sales
>   in last 30 days PER marketplace** + primary account owner only. Low-volume locales (e.g. IT) may
>   not qualify.
> - Docs: https://affiliate-program.amazon.com/creatorsapi/docs/en-us/introduction ·
>   https://affiliate-program.amazon.com/creatorsapi/docs/en-us/onboarding/register-for-creators-api ·
>   https://webservices.amazon.com/paapi5/documentation/register-for-pa-api.html (retirement notice)
>
> Implication for the rebuild: the Amazon path is a **Creators-API integration project** (OAuth2 +
> new request/response shape + OffersV2), feeding the same Stage-A→CSV→Stage-B flow (or replacing it).

**Legacy detail (pre-retirement findings) below — the "adjacent broken paths" framing is superseded
by the retirement above, but the pipeline mechanics still hold.**

> ⚠️ **CORRECTION (the trap):** an earlier pass concluded "Amazon is working, fresh today." **Wrong.**
> The `last_import = today` and varied prices are a **stale-timestamp illusion** — the daily import
> re-ingests a CSV that was last *written by Amazon* on **Jan 27, 2026**. Always check the **fetch**
> stage, not just the import stage. Details below.

**The pipeline is two stages (Ruggero's model is correct):**
1. **FETCH (Stage A)** — `import_api_cvs.php` (wrapper `import_api_csv.sh`): reads RSG's shoe list
   `export.cvs`, queries **Amazon PA-API** per shoe, and **writes** result CSVs to
   `/var/www/html/admin/datafiles_amazon/{n}.csv`. Logs to `admin/logs/import_api_csv.txt`.
2. **IMPORT (Stage B)** — `import.php` (cron `0 6 * * *`): each Amazon `datafeeds` row is
   `feed_type=automatic` with `url = http://ruggero:…@45.33.39.133/admin/datafiles_amazon/{n}.csv`
   — i.e. it just **curl-downloads the local Stage-A CSV** and imports it, stamping `updated_at=now`.

Feed → file map (all frozen): 40 US→`54.csv` (Jan 27), 41 UK→`59.csv` (Jan 26),
70 Canada→`62.csv` (Jan 26), 49 DE→`60.csv` (**0 bytes**), 58 IT→`61.csv` (**0 bytes**).

**ACTUAL STATE: Amazon price fetching is DEAD since 2026-01-27.**
- `import_api_csv.txt` ends **mid-run at Jan 27 18:47** (still printing "written to CVS product
  name: …", then cuts off) — **no API error, no exception**: the process was killed mid-stream
  (consistent with the ~Jan 28–29 reboot; uptime 144 days as of Jun 21) and **never restarted**.
- **`import_api_csv.sh` is NOT in crontab.** Cron only has `import_api.sh` (`import_api.php all` — a
  temp-table shuffle that does **no Amazon querying**; ran today 09:30, logs `import_api.txt`) and
  `import.sh` (Stage B importer). So nothing has fetched Amazon since Jan 27.
- Net effect: every morning Stage B re-imports the Jan-27 CSVs → site shows **~5-month-old Amazon
  prices** that look freshly updated. `products`: 19,418 Amazon rows all `updated_at` today, but the
  values are Jan 27. DE & IT = 0 because their Stage-A CSVs are 0-byte.

**TWO independent breakages (both confirmed):**
1. **Fetch job not scheduled** — `import_api_cvs.php` stopped Jan 27 and isn't in cron.
2. **PA-API keys are invalid** — live-tested 2026-06-21 with a single `SearchItems` call using DFS's
   own `AwsV4` signing + feed-40 keys (access key `AKIAIVPE…`, tag `rsg-single-20`, us-east-1):
   → **HTTP 401 `UnrecognizedClientException` — "The Access Key ID … is invalid."**
   Correctly signed and hitting **PA-API 5** (a signing bug would be `InvalidSignature`), so this is a
   **dead/revoked credential**, not a v4-vs-v5 code issue. Likely Associates/PA-API access deactivated
   (no API-driven sales / account status). Both US (`rsg-single-20`) and UK (`runshogur-21`) use the
   same `AKIAIV…` key family → both fail identically.

**Root cause vs. the v4 theory:** the May-1 PA-API v4 deprecation is **not** the cause — the code is
already on v5; the keys are simply invalid now, and the fetch job stopped (Jan 27) before that anyway.

**To restore Amazon:** (a) get **fresh PA-API 5 credentials** from an active Associates account with
API access, write them into the `datafeeds` rows (`amazon_access_key`/`amazon_secret_key`, plus the
correct `amazon_associates_tag` per marketplace); (b) re-enable `import_api_csv.sh` in cron (Stage A)
so it re-writes the `datafiles_amazon/*.csv`; Stage B (`import.php`) then picks them up. Fix DE/IT
(0-byte CSVs) separately. Test harness used: single signed `SearchItems` POST — read-only, no DFS writes.

**Separately dead (unrelated to the above):** theme-side `rsg_2022/includes/amazon-api.php` (GPS
watches + gift guides) — dead hardcoded keys, stopgapped to blank (see `AMAZON-PATH-HANDOFF.md`);
and `amazonproducts` table = 0 rows (the on-the-fly `amazon=` live-blend cache). Also several
non-Amazon feeds fail daily (Under Armour, SportsShoes, Sportitude, SportChek = `NO FILE FOUND`).
