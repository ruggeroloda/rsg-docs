# SSH/server-access handover — 2026-09-09

> **RESOLVED 2026-09-10 — see `logs/PHP8-FATALS-DEPLOY-LOG-2026-09-09.md`.**
> Port 22 is blocked by the Cowork session's own egress (it fails to *every*
> host, including github.com and 1.1.1.1, while HTTPS works). The timeouts below
> were that, not a server-side firewall ban — drop the CSF/fail2ban theory. All
> the work was done instead through the Rocket.net panel over HTTPS.
> The underlying bug is fixed and deployed; the diagnosis recorded at the bottom
> of this file was **wrong** (it was not a null-price code path, and it was in a
> different template). Read the deploy log, not the recap below.


Context for whoever picks this up next: we were investigating a Google Search
Console indexing problem on runningshoesguru.com and found a real, live bug
(HTTP 500s on GPS review pages and paginated brand-archive pages). Confirming
the exact cause and fixing it requires server access (PHP error log + theme
file), which we could not get working this session. This file is the
connection/access troubleshooting log only — see the separate GSC diagnosis
recap at the bottom for the underlying bug this was blocking.

## Target server
- Host: `jfx9wij@131.153.143.194`, port 22 (Rocket.net / cPanel box)
- This is the LIVE runningshoesguru.com server referenced throughout the
  project's CLAUDE.md / deploy logs.

## What we tried, in order, with exact results (facts only — no interpretation)

1. **Original key**, `.ssh-rocket-key` in the project folder
   (`rsg-redesign/.ssh-rocket-key`). Fingerprint: `SHA256:b6PXuQkRJi6rVhZUjHFSvlyryFau8OqPfhSkO3uvWgo` (RSA 4096).
   Result: immediate, clean `Permission denied (publickey,gssapi-keyex,gssapi-with-mic,password)`.
   SSH reached the server and offered the key; server actively refused it.

2. Ruggero located `~/.ssh/id_rsa` on his Mac, believing it was a different/newer key.
   Verified via `ssh-keygen -y` that it is **byte-identical** to key #1 above
   (same fingerprint). Tried it two more ways:
   - Via `ssh` from this session's device-bridge shell → same clean `Permission denied`.
   - Via FileZilla (SFTP) directly from his Mac, independent of this session →
     `Server refused our key` / `Access denied` / `Authentication failed`.
   Conclusion (confirmed, not a guess): this specific keypair is not authorized
   on the server right now, regardless of client/path used to offer it.

3. Ruggero found an SSH-key management panel (screenshot showed an entry
   labeled "MacBook Pro" containing that same old public key). This tells us
   the panel's stored record for that key **does not match** what the server's
   sshd is actually honoring — either it never propagated, or something else
   changed server-side. Not resolved/explained, just noted.

4. Generated a **brand new** keypair specifically for this: `ed25519`,
   comment `cowork-fix`, fingerprint `SHA256:xM4EEYev8rGQNmiERiEkr9Ailp3z6441Tb/fvIBePYY`.
   Ruggero uploaded/pasted the public half into the same panel.
   - First connection attempt right after upload: `Permission denied` again
     (clean rejection — reached the auth stage, key not yet accepted).
   - Ruggero said he "had to manually authorise it" in the panel (exact
     mechanism/step not described to me — worth asking him what that
     screen/button was, in case it needs documenting).
   - Retried twice after that: both times got **`Connection timed out`**
     on port 22 — a different failure mode. This did NOT reach the
     authentication stage at all; the TCP handshake itself did not complete.

## Current status: unresolved, do not assume cause

I speculated the timeout could be an automatic firewall ban (e.g. CSF/fail2ban
triggered by the earlier failed attempts) — **this is an unverified guess,
Ruggero correctly pushed back on it, and it should not be treated as
established**. Equally plausible, not ruled out:
- A transient network issue on the device-bridge path at that moment.
- Something about the "manual authorisation" step itself temporarily
  affecting SSH (e.g. a service restart).
- The host/port simply being wrong or changed.
- An actual, still-in-place firewall block — plausible but NOT confirmed.

Before doing more troubleshooting, next session should:
1. Just try the connection again fresh (`ssh -i <new key> jfx9wij@131.153.143.194`)
   — several minutes will have passed, which rules in/out anything time-based.
2. If it still times out, test with a plain TCP check (e.g. `nc -zv -w5
   131.153.143.194 22`) to isolate "can't reach port 22 at all" from
   "SSH-specific" — this distinguishes a firewall/network block from an
   SSH-level problem.
3. Ask Ruggero what the "manual authorise" step actually was (screenshot or
   description) — that panel behaving unexpectedly (see point 3 above) is a
   real, unexplained anomaly worth understanding before trusting it again.
4. If still stuck, the clean path is Rocket.net support — ask them directly
   whether SSH/key auth is currently enabled for this account and whether
   any IP is currently blocked.

## New key material on file (for reference, not secret — public half only)
New keypair comment: `cowork-fix`. Ruggero has both halves; the private key
was pasted into chat once already this session if the transcript is needed.

---

## Underlying bug this access was needed for (recap, see prior session for full detail)

- GSC showed a spike in "not indexed" pages. Two causes identified and NOT
  blocked on server access (already diagnosed with confidence from GSC +
  public HTTP checks alone):
  1. A negative-SEO/spam backlink wave starting ~Aug 5, 2026 (fabricated
     video/streaming URLs referred from spam sites like jigargaglani.com,
     causing a 404 spike; and a scraper site `linnus-auto.fi` mass-linking
     to the site's own `/go/...` affiliate redirect links, causing ~8,100 of
     those correctly-robots.txt-blocked URLs to get indexed anyway as bare
     URLs). Not a site bug — no fix needed beyond optional disavow.
  2. A legacy CDN subdomain (`cdn.runningshoesguru.com`, old bunny.net) with
     no current DNS record — explains a large stale "DNS error" crawl-stat
     bucket. Only one live leftover reference found (a small theme icon).
     Low priority cleanup.

- **The real, still-open bug**: GPS review pages (`/gps/...` and
  `/reviews/gps/...`) and paginated brand-filtered archive pages
  (`/page/N/?brands=X` for N≥2) return HTTP 500, confirmed live with cache
  bypassed. Truncated HTML output (cuts off mid-render, before the product
  card loop) — deterministic, same URL always fails, not load-related.
  Working hypothesis (NOT yet confirmed via error log — that's what SSH
  access is for): a shared price-formatting function fatals (likely a PHP 8
  TypeError) when a product's price meta is null — confirmed via
  unauthenticated REST API that the Garmin VivoActive 4 GPS post has
  `usd`/`aud`/`cad` all null. Page 1 of brand archives works; page 2+
  consistently 500s. GSC's "Server error (5xx)" count shows a staircase
  growth starting right around 2026-06-22 — the day after the project's
  PHP 7.4→8.3 migration ("all fatals fixed", 2026-06-21) — suggesting that
  migration missed this code path (GPS was explicitly left out of the later
  redesign work per CLAUDE.md: "GPS keeps its old panel").

- Next step once server access works: pull the PHP error log for the exact
  fatal, find the shared price-formatting function used by both the GPS
  template and the brand-archive card loop, and add a null-guard before the
  numeric formatting call.
