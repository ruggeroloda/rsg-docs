# Wizard capture v2 — IMPLEMENTATION INSTRUCTIONS (for the implementing agent)

_Companion to `WIZARD-CAPTURE-V2-BRIEF.md` (read it first for the why). Written 2026-07-05
against the LIVE files; all line numbers verified that day. Scope: Phases 0+1+2 only.
Phase 3 (real price-drop cron) is a later session — do NOT attempt it here._

## Non-negotiable ground rules
1. **Never edit live directly.** Edit on STAGING (`~/public_html/jfx92wmcr3-staging.onrocket.site/wp-content/themes/rsg_2022/`), verify there, then copy to LIVE (`~/public_html/wp-content/themes/rsg_2022/`).
2. Back up EVERY file before touching it (staging AND live): `cp file file.bak-$(date +%Y%m%d-%H%M)`.
3. Lint every edited PHP file with **both** `ea-php83 -l` and `ea-php74 -l`.
4. Bypass edge cache when verifying: append `?cb=<timestamp>`.
5. `diff` live-vs-new before uploading — the change must be surgical; nothing outside the blocks below may differ.
6. Finish with a deploy log `WIZARD-CAPTURE-V2-DEPLOY-LOG-<date>.md` (backup paths, diffs summary, verification results, rollback commands) and tell Ruggero to **Cloudflare "Purge Everything"** (quiz page is edge-cached ~30d; without purge nothing ships).
7. SSH: key `.ssh-rocket-key` in the project folder; copy to a chmod-able dir first (`chmod` fails in the mount): `cp <folder>/.ssh-rocket-key <outputs>/.rsgkey && chmod 600 ...` then `ssh -i <outputs>/.rsgkey -o StrictHostKeyChecking=no jfx9wij@131.153.143.194`. WP-CLI from `~/public_html`.

## Files touched (only these two)
- `includes/rsg-wizard-capture.php` (164 lines) — endpoint + table.
- `template-wizard-2024.php` (1836 lines) — quiz popup JS/markup + results email card.

## Architecture decision (do not deviate)
The pre-results step CANNOT submit picks — picks are computed server-side only after the form
submits. Flow chosen (single code path, navigation-safe, no email in URL):

1. Quiz popup collects the email → stores it in `sessionStorage['rsg_wiz_email']` → submits
   `#target_wizard` (normal navigation to results).
2. The RESULTS page JS, on load, finds the pending email → POSTs it to the endpoint together
   with pick titles+IDs (read from the email card's data attributes) and `source:'pre_results'`
   → clears the key → morphs the email card into a success/confirm state.
3. No pending email (user skipped) → card renders normally with new price-watch copy; a manual
   submit there sends `source:'post_results'`.

Accepted tradeoff: if the results page never loads, the email is lost (negligible — same-click
navigation). Do not "improve" this with a beacon at the popup: it would create pick-less rows
and a second server path.

---

# PART A — `includes/rsg-wizard-capture.php`

## A1. Schema v2 (source + pick IDs)
In `rsg_wizard_capture_install()` (line ~23): bump `RSG_WIZARD_DB_VERSION` from `'1'` to `'2'`
(line ~20) and add to the `CREATE TABLE` statement, after `profile VARCHAR(255) DEFAULT '',`:

```sql
		source VARCHAR(16) DEFAULT '',
		pick_1_id BIGINT UNSIGNED DEFAULT 0,
		pick_2_id BIGINT UNSIGNED DEFAULT 0,
		pick_3_id BIGINT UNSIGNED DEFAULT 0,
```

`dbDelta()` adds the columns on next page load (the version-option guard at the top makes it
run once). Verify after deploy: `wp db query "DESCRIBE newguru_wp_rsg_wizard_signups"`.

## A2. Replace the nonce check with an IP rate limit
Delete lines 63–67 (the `// same-origin nonce` block returning `bad_nonce`). The page is
edge-cached ~30 days; an embedded 24h nonce can NEVER validate reliably — this was a live bug.
Replace with:

```php
	// Public endpoint on an edge-cached page: no nonce (it would go stale in cache).
	// Abuse controls: honeypot (below), per-IP rate limit, is_email(), and DOI at MailerLite.
	$rl_ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	$rl_key = 'rsg_wsub_' . md5( $rl_ip );
	$rl_n   = (int) get_transient( $rl_key );
	if ( $rl_n >= 5 ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'rate_limited' ), 429 );
	}
	set_transient( $rl_key, $rl_n + 1, HOUR_IN_SECONDS );
```

## A3. Accept the new fields + dedupe guard
In the handler, after the existing `$profile = ...` line (~line 100), add:

```php
	$source    = in_array( ( $p['source'] ?? '' ), array( 'pre_results', 'post_results' ), true ) ? $p['source'] : '';
	$pick_1_id = absint( $p['pick_1_id'] ?? 0 );
	$pick_2_id = absint( $p['pick_2_id'] ?? 0 );
	$pick_3_id = absint( $p['pick_3_id'] ?? 0 );
```

Immediately before the `$wpdb->insert(` (~line 106), add a same-email-same-day guard (prevents
double rows if the results JS ever fires twice; ML dedupes by email anyway):

```php
	$dupe = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM $table WHERE email = %s AND created_at > %s LIMIT 1",
		$email, gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS )
	) );
	if ( $dupe ) {
		return new WP_REST_Response( array( 'ok' => true, 'dup' => true ), 200 );
	}
```

(Note: `created_at` uses `current_time('mysql')` = site-local time; use
`date( 'Y-m-d H:i:s', current_time('timestamp') - DAY_IN_SECONDS )` instead of gmdate if the
site TZ is not UTC — check `wp option get timezone_string` and match whichever the column stores.)

Extend the `$wpdb->insert` array with:
```php
		'source'    => $source,
		'pick_1_id' => $pick_1_id,
		'pick_2_id' => $pick_2_id,
		'pick_3_id' => $pick_3_id,
```
and append `'%s','%d','%d','%d'` to the format array **in the same position order**.

Do NOT touch `rsg_ml_subscribe()` or the ML call — unchanged.

# PART B — `template-wizard-2024.php`

## B1. Popup: add the email step (quiz side)
**Current behaviour** (~lines 1053–1064): when the last answer is clicked
(`carbon_plate` / `rockplate` / gated `stability`), the code shows `#wizard-popup`, animates
4 spinner ticks (400ms each), fires `gtag('event','Wizard-Heatmap-2024',{app_name:'Completed',...})`,
and calls `$('#target_wizard').submit()` **immediately**.

**Change:** do NOT submit immediately. After the spinner animation (~1.8s), swap the popup
content to the email step. Implement as a shared function so BOTH completion paths use it —
there is a second direct submit in the `.wizard-submit` click handler (~line 1092) which must
also be routed through it:

```js
function rsgShowEmailStep(){
	$('#wizard-popup').addClass('d-flex');            // ensure visible for the .wizard-submit path
	$('#rsg-wpopup-spin').hide();
	$('#rsg-wpopup-email').show();
	if (typeof gtag === 'function') gtag('event','Wizard-Heatmap-2024',{app_name:'EmailStep-Shown',screen_name:'EmailStep-Shown'});
	setTimeout(function(){ var f=document.getElementById('rsg-wpopup-input'); if(f) f.focus(); }, 50);
}
function rsgFinish(email){
	try { if (email) sessionStorage.setItem('rsg_wiz_email', email); } catch(e){}
	if (typeof gtag === 'function') gtag('event','Wizard-Heatmap-2024',
		{app_name: email ? 'EmailStep-Submitted' : 'EmailStep-Skipped',
		 screen_name: email ? 'EmailStep-Submitted' : 'EmailStep-Skipped'});
	$('#target_wizard').submit();
}
```

- Last-answer handler: keep popup show + spinner animation + the existing `Completed` gtag,
  replace `$('#target_wizard').submit();` with `setTimeout(rsgShowEmailStep, 1800);`.
- `.wizard-submit` handler (~1092): replace `$('#target_wizard').submit();` with
  `rsgShowEmailStep();` (this path skips the spinner — fine).
- Email step buttons: CTA validates (`value.indexOf('@') > 0`, show inline error otherwise)
  then `rsgFinish(email)`; skip link calls `rsgFinish(null)`. Bind Enter key on the input to
  the CTA. Wrap all `sessionStorage` access in try/catch (Safari private mode throws).

## B2. Popup markup (line ~1600)
Inside the existing white inner div of `#wizard-popup`, wrap the current content
(headline + progress bar + spinner rows) in `<div id="rsg-wpopup-spin">…</div>` and add as a
sibling (initially hidden):

```html
<div id="rsg-wpopup-email" style="display:none;text-align:center;">
	<div class="h2">Your 3 matches are ready.</div>
	<p style="color:#4a5560;max-width:34em;margin:0.6em auto 1.2em;">Want them by email?
	We'll also <b>watch prices on all three</b> and let you know when one drops.</p>
	<div style="display:flex;gap:10px;max-width:30em;margin:0 auto 0.8em;">
		<input type="email" id="rsg-wpopup-input" placeholder="you@example.com" autocomplete="email"
			style="flex:1 1 auto;min-width:0;padding:12px 14px;border:1px solid #cdd5dd;border-radius:8px;font-size:1rem;" aria-label="Your email address">
		<button type="button" id="rsg-wpopup-go" class="btn btn-warning" style="white-space:nowrap;font-weight:700;">Email my matches</button>
	</div>
	<div id="rsg-wpopup-err" style="color:#c0392b;font-weight:600;min-height:1.2em;"></div>
	<p style="margin-top:0.4em;"><a href="#" id="rsg-wpopup-skip" style="color:#7a838d;text-decoration:underline;">No thanks — just show my results</a></p>
	<p style="font-size:.75rem;color:#9aa3ac;max-width:32em;margin:0.8em auto 0;">No spam, unsubscribe anytime. <a href="/privacy-policy/" target="_blank" rel="noopener" style="color:#2189E1;">Privacy Policy</a></p>
</div>
```

On mobile (<520px) stack the input/button column-wise — copy the existing
`.rsg-emailcard__row` media-query pattern. **No DOI wording here** (it moved to the success
state). The skip link must be plainly visible — this step is an offer, not a gate.

## B3. Results page: email card upgrade (~lines 1712–1760)
1. In the `$rsg_picks` builder (~1714–1718) also capture IDs:
   `array( 't' => get_the_title($rp->ID), 'u' => get_permalink($rp->ID), 'id' => (int) $rp->ID )`
   and derive `$rsg_pick_1_id` … `$rsg_pick_3_id` (default 0).
2. On the `.rsg-emailcard` div: **delete the `data-nonce` attribute** (line 1737) and add
   `data-pickid1/2/3` with the IDs.
3. Re-copy the card (Phase 2 — for skippers):
   - title → `Want us to watch prices on these 3?`
   - sub → `We'll email you when one of your picks drops in price — plus straight-talking
     shoe advice from our testers, about one email every few days. Unsubscribe anytime.`
   - button → `Watch prices on my picks`
   - fine print → drop the "confirmation link first" sentence; keep only
     `No spam, ever. See our Privacy Policy.`
4. In the card's existing submit JS (~1790s): remove the `X-WP-Nonce` header from the `fetch`,
   add `source:'post_results'` and `pick_1_id/2/3_id` (from dataset) to the payload.
5. **New auto-submit block** — add to the card's IIFE, after element lookups:

```js
var pend = null;
try { pend = sessionStorage.getItem('rsg_wiz_email'); } catch(e){}
if (pend) {
	var payload = { email: pend, website: '', source: 'pre_results',
		quiz_type: box.dataset.type, gender: box.dataset.gender,
		pick_1: box.dataset.pick1, pick_2: box.dataset.pick2, pick_3: box.dataset.pick3,
		pick_1_id: box.dataset.pickid1, pick_2_id: box.dataset.pickid2, pick_3_id: box.dataset.pickid3,
		picks_url: box.dataset.picksurl, profile: box.dataset.profile };
	fetch(box.dataset.rest, { method:'POST', headers:{'Content-Type':'application/json'},
		body: JSON.stringify(payload), keepalive: true })
	.then(function(r){ return r.json(); })
	.then(function(d){
		if (d && d.ok) {
			try { sessionStorage.removeItem('rsg_wiz_email'); } catch(e){}
			box.classList.add('is-done');
			msg.textContent = 'Almost done — check your inbox and click the confirmation link. Then your picks and price watch are set.';
			msg.classList.add('is-ok');
		} // non-ok: leave the card usable as a manual fallback; do not clear the key
	})
	.catch(function(){ /* leave card as manual fallback */ });
}
```

The existing `.is-done` CSS already hides the form and shows only the message — reuse it.
(If `sessionStorage` key survives a failed POST, next results view retries — acceptable.)

# PART C — verification checklist (all on STAGING first, then repeat on LIVE)

Staging is `robots: Disallow` and cache-bypassed with `?cb=`. Test with a REAL external email
you control (NOT `@runningshoesguru.com` — same-domain skips DOI and proves nothing).

1. `ea-php83 -l` and `ea-php74 -l` on both edited files → no errors.
2. Load quiz page → complete road flow → spinner shows ~1.8s → email step appears,
   skip link visible. Submit with email → lands on results → card shows the green
   "check your inbox" state → table row exists with `source='pre_results'`, pick IDs > 0,
   pick titles filled, `ml_status` not 'pending' (check `wp db query`).
3. DOI email actually arrives at the external address.
4. Repeat, click **skip** → results load normally → card shows the new price-watch copy →
   submit there → row with `source='post_results'`.
5. Trail flow once (last question differs — `rockplate` path) → email step still appears.
6. `.wizard-submit` path: if any flow ends with that button, confirm it opens the email step.
7. Rate limit: 6 rapid POSTs via curl → 6th returns 429.
8. Honeypot: POST with `website` filled → `ok:true` but NO table row.
9. Dedupe: same email twice within a day → second returns `ok,dup` and no second row.
10. Reload the results URL after success → card does NOT re-fire (key cleared), no duplicate row.
11. Regression: complete the quiz with devtools network throttled — results page must render
    picks exactly as before; the ONLY body diffs vs pre-deploy are the popup + card blocks.
12. After LIVE deploy: verify with `?cb=`, then tell Ruggero to **purge Cloudflare** and
    re-verify without `?cb=` once purged.

# Rollback
Restore the two `.bak-*` files on live (paths in your deploy log), purge Cloudflare. DB columns
are additive — leave them. Rollback restores the old nonce-checking handler, which matches the
old template markup, so pairs must be rolled back together.

# Manual follow-ups for Ruggero (put these at the top of the deploy log)
- **Cloudflare Purge Everything** after deploy (several earlier deploys also still pending purge).
- MailerLite: welcome automation for `from_wizard` should mention the picks/price-watch framing.
- Phase 3 (real price-drop cron) must ship within ~2–3 weeks — the new copy promises it.
- 7-day check-in: `SELECT source, COUNT(*) FROM newguru_wp_rsg_wizard_signups GROUP BY source;`
  vs results-page loads in `~/access-logs/a5jeufoh...-ssl_log` (unique-param wizard URLs).
