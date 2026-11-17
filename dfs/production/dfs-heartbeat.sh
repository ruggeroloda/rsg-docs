#!/usr/bin/env bash
# rsg-dfs heartbeat — /usr/local/bin/dfs-heartbeat.sh (root, /etc/cron.d/dfs-heartbeat, every 30 min)
# Pings healthchecks.io (URL in /etc/dfs-heartbeat.url) when ALL checks pass; sends a /fail ping with the
# reason when one fails. No ping at all (server or cron down) -> healthchecks.io emails after period+grace (~8 h).
# DRYRUN=1 prints the result without pinging.
set -uo pipefail
URL=$(cat /etc/dfs-heartbeat.url 2>/dev/null)
DB=/var/lib/docker/volumes/dfs_data/_data/dfs.sqlite
MARK=/var/backups/dfs/.last_upload_ok
problems=()
# 1. DFS container running
[ "$(docker inspect -f '{{.State.Running}}' dfs 2>/dev/null)" = "true" ] || problems+=("DFS container not running")
# 2. Disk below 80 %
disk=$(df --output=pcent / | tail -1 | tr -dc '0-9'); [ "${disk:-100}" -lt 80 ] || problems+=("disk ${disk}% full")
# 3. Last backup uploaded to Google Drive less than 36 h ago
if [ -f "$MARK" ]; then age_h=$(( ( $(date -u +%s) - $(cat "$MARK") ) / 3600 )); [ "$age_h" -lt 36 ] || problems+=("last Drive backup ${age_h} h old"); else problems+=("no Drive backup yet"); age_h=-1; fi
# 4. Scheduler switch on, and 5. a DFS job succeeded in the last 8 h
sched=$(sqlite3 -readonly "$DB" "SELECT value FROM meta WHERE key='scheduler';" 2>/dev/null)
[ "$sched" = "on" ] || problems+=("DFS scheduler is ${sched:-unknown}")
last_h=$(sqlite3 -readonly "$DB" "SELECT printf('%.1f',(julianday('now')-julianday(MAX(f)))*24) FROM (SELECT MAX(finished) f FROM feed_runs WHERE status='ok' UNION ALL SELECT MAX(finished) FROM price_runs WHERE status='ok');" 2>/dev/null)
awk -v h="${last_h:-999}" 'BEGIN{exit !(h<8)}' || problems+=("no successful DFS job for ${last_h:-?} h")
summary="disk ${disk}% | backup ${age_h} h | last ok job ${last_h} h ago | scheduler ${sched}"
if [ ${#problems[@]} -eq 0 ]; then msg="OK: $summary"; path=""; else msg="PROBLEM: $(IFS='; '; echo "${problems[*]}") | $summary"; path="/fail"; fi
if [ "${DRYRUN:-0}" = "1" ] || [ -z "$URL" ]; then echo "$msg"; exit 0; fi
curl -fsS -m 10 --retry 3 --data-raw "$msg" "${URL}${path}" >/dev/null || echo "$(date -u +%FT%TZ) ping failed: $msg" >> /var/log/dfs-heartbeat.log
