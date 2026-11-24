#!/bin/bash
# Site checks for www.runningshoesguru.com, run every 5 min on rsgmedia by /etc/cron.d/rsg-site-check.
# Source: rsg project hosting/hetzner/rsg-site-check.sh -> /usr/local/bin/rsg-site-check.sh
# Two healthchecks.io checks, each pinged with success, or /fail with the reason:
#  rsg-site (URL in /etc/rsg-site-check.url) = is the site up:
#   (1) the page visitors get through the edge cache; (2) a fresh render at the origin (a query string the edge has
#   never cached; the edge keeps HTML 30 days, so it can hide an origin outage); (3) a review page renders its price
#   box; (4) the newest off-site WordPress backup on Google Drive is < 8 days old (weekly job on Rocket.net);
#   (5) this server's own nightly backup (rsgmedia) is < 36 h old.
#  rsg-php (URL in /etc/rsg-php-check.url; split off 14 Nov 2026 so one PHP fatal no longer reads as "site down") =
#   PHP logging is on and there was no PHP fatal error on the site in the last 60 min (endpoint from the mu-plugin
#   rsg-php-errors.php, token in /etc/rsg-site-check.token = WP option rsg_php_errors_token).
#  rsg-umami (URL in /etc/rsg-umami-check.url; 16 Nov 2026; skipped while the file is missing) = Umami analytics:
#   app answers its heartbeat (127.0.0.1:3010), the public tracker script answers through Nginx Proxy Manager, and the
#   dashboard is NOT reachable from outside (/login must be 404), Search Console data loaded within 6 days (rsg-gsc cron), analyst metrics built within 30 h (rsg-metrics cron). A failure here is an analytics gap, never a site problem.
# Each check gets one retry after 30 s before reporting a failure. DRYRUN=1 prints instead of pinging.
set -uo pipefail
SITE="https://www.runningshoesguru.com"; REVIEW="/reviews/road/nike-vomero-18-review/"
SITE_URL=$(cat /etc/rsg-site-check.url 2>/dev/null || true)
PHP_URL=$(cat /etc/rsg-php-check.url 2>/dev/null || true)
UMAMI_URL=$(cat /etc/rsg-umami-check.url 2>/dev/null || true)
site_check() {
  local t body code
  t=$(date +%s)
  body=$(curl -s -m 20 -A "rsg-site-check" -w '\n%{http_code}' "$SITE/") || return 1
  code=${body##*$'\n'}; [ "$code" = 200 ] && grep -q '</html>' <<<"$body" || { echo "edge: HTTP $code"; return 1; }
  body=$(curl -s -m 30 -A "rsg-site-check" -w '\n%{http_code} %{time_total}' "$SITE/?rsgcheck=$t") || { echo "origin: no answer in 30 s"; return 1; }
  code=${body##*$'\n'}; [ "${code%% *}" = 200 ] && grep -q '</html>' <<<"$body" || { echo "origin: HTTP ${code%% *}"; return 1; }
  body=$(curl -s -m 30 -A "rsg-site-check" "$SITE$REVIEW?rsgcheck=$t") || { echo "review: no answer"; return 1; }
  grep -q 'class="rsg-dfs' <<<"$body" || { echo "review: no price box on $REVIEW"; return 1; }
  local newest age state=/var/lib/rsg-site-check/newest-backup
  mkdir -p "${state%/*}"
  if [ -z "$(find "$state" -mmin -360 2>/dev/null)" ]; then     # Drive listing takes ~50 s: refresh at most every 6 h
    newest=$(rclone --config /root/.config/rclone/rclone.conf lsf --files-only --format ts --separator ';' gdrive:Server-Backups/sites/rsg/db 2>/dev/null | sort | tail -1)
    [ -n "$newest" ] && echo "$newest" > "$state"
  fi
  newest=$(cat "$state" 2>/dev/null)
  [ -n "$newest" ] || { echo "backup: none found on Drive"; return 1; }
  # Size too, not only the date: a failed export still uploads a ~220-byte empty file (15 + 22 Nov 2026).
  [ "${newest#*;}" -ge 1000000 ] 2>/dev/null || { echo "backup: newest WordPress db backup is only ${newest#*;} bytes"; return 1; }
  newest=${newest%%;*}
  age=$(( ( $(date +%s) - $(date -d "$newest" +%s) ) / 86400 ))
  [ "$age" -lt 8 ] || { echo "backup: newest WordPress backup is $age days old"; return 1; }
  local own; own=$(find /var/backups/rsgmedia -name '*.age' -mmin -2160 2>/dev/null | head -1)
  [ -n "$own" ] || { echo "rsgmedia backup: none in the last 36 h"; return 1; }
  echo "ok: edge + origin (${code#* }s) + price box + WP backup ${age} d + rsgmedia backup"
}
php_check() {
  local tok pe; tok=$(cat /etc/rsg-site-check.token 2>/dev/null)
  pe=$(curl -s -m 20 -A "rsg-site-check" -H "X-RSG-Check: $tok" "$SITE/wp-json/rsg/v1/php-errors?rsgcheck=$(date +%s)") || { echo "php: no answer from the error endpoint"; return 1; }
  pe=$(python3 -c 'import json,sys
d=json.loads(sys.stdin.read())
if "code" in d: print("FAIL php: error endpoint refused (%s)" % d.get("code")); sys.exit()
if not d.get("logging"): print("FAIL php error logging is off"); sys.exit()
if d.get("fatal_60m"): print("FAIL php: %d fatal error(s) in the last hour, last: %s" % (d["fatal_60m"], (d.get("last_fatal") or "")[:200])); sys.exit()
print("OK %d" % d.get("warnings_24h", 0))' <<<"$pe" 2>/dev/null) || pe="FAIL php: bad answer from the error endpoint"
  case "$pe" in OK*) ;; *) echo "${pe#FAIL }"; return 1 ;; esac
  echo "ok: PHP logging on, 0 fatal in 60 min (${pe#OK } warnings/24h)"
}
umami_check() {
  local c
  c=$(curl -s -o /dev/null -m 10 -w '%{http_code}' http://127.0.0.1:3010/api/heartbeat); [ "$c" = 200 ] || { echo "umami app: heartbeat $c"; return 1; }
  c=$(curl -s -o /dev/null -m 15 -w '%{http_code}' https://t.runningshoesguru.com/js/main.js); [ "$c" = 200 ] || { echo "tracker script from outside: $c"; return 1; }
  c=$(curl -s -o /dev/null -m 15 -w '%{http_code}' https://t.runningshoesguru.com/login); [ "$c" = 404 ] || { echo "dashboard reachable from outside: /login $c"; return 1; }
  local g; g=$(docker exec postgres psql -U umami -d umami -tAc "select current_date - max(day) from rsg.gsc_load_log" 2>/dev/null | tr -d ' ')
  [ -n "$g" ] && [ "$g" -le 6 ] || { echo "Search Console data stale: newest day ${g:-?} days old (cron /etc/cron.d/rsg-gsc)"; return 1; }
  local h; h=$(docker exec postgres psql -U umami -d umami -tAc "select floor(extract(epoch from now() - max(run_at)) / 3600) from rsg.metric_run" 2>/dev/null | tr -d ' ')
  [ -n "$h" ] && [ "$h" -le 30 ] || { echo "analyst metrics stale: last build ${h:-?} h ago (cron /etc/cron.d/rsg-metrics)"; return 1; }
  echo "ok: app up, tracker public, dashboard private, Search Console data ${g} days old, metrics built ${h} h ago"
}
run() {  # run check $1 (one retry after 30 s), ping URL $2
  local msg
  msg=$($1) || { sleep 30; msg=$($1) || msg="FAIL ${msg:-unknown}"; }
  if [ "${DRYRUN:-}" = 1 ] || [ -z "$2" ]; then echo "$1: $msg"; return; fi
  case "$msg" in FAIL*) curl -fsS -m 10 --retry 3 --data-raw "$msg" "$2/fail" >/dev/null ;; *) curl -fsS -m 10 --retry 3 --data-raw "$msg" "$2" >/dev/null ;; esac
}
run site_check "$SITE_URL" &
run php_check "$PHP_URL" &
[ -n "$UMAMI_URL" ] && run umami_check "$UMAMI_URL" &
wait
