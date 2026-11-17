#!/usr/bin/env bash
# rsg-dfs monthly health report — /usr/local/bin/dfs-monthly-report.sh (root, /etc/cron.d/dfs-monthly-report,
# 1st of the month 07:30 UTC). Emails Ruggero through the DFS's MailerSend sender (same as the failure emails).
# REPORT_PRINT=1 prints instead of sending.
set -uo pipefail
DB=/var/lib/docker/volumes/dfs_data/_data/dfs.sqlite
q() { sqlite3 -readonly "$DB" "$1" 2>/dev/null; }
attn=()
up=$(uptime -p | sed "s/^up //"); boot=$(uptime -s)
pend=$(apt list --upgradable 2>/dev/null | grep -c upgradable); sec=$(apt list --upgradable 2>/dev/null | grep -c -- "-security")
[ "$sec" -gt 0 ] && attn+=("$sec security update(s) waiting (should install by themselves overnight)")
[ -f /var/run/reboot-required ] && attn+=("a reboot is pending")
applied=$(grep -h "Packages that will be upgraded" /var/log/unattended-upgrades/unattended-upgrades.log* 2>/dev/null | grep -c .)
dock=$(docker --version | cut -d, -f1); restarts=$(docker inspect -f '{{.RestartCount}}' dfs 2>/dev/null); running=$(docker inspect -f '{{.State.Running}}' dfs 2>/dev/null)
[ "$running" = "true" ] || attn+=("DFS container NOT running")
disk=$(df --output=pcent / | tail -1 | tr -dc '0-9'); [ "$disk" -ge 70 ] && attn+=("disk ${disk}% full")
dbsize=$(du -h "$DB" | cut -f1)
local_n=$(ls /var/backups/dfs/dfs_*.age 2>/dev/null | wc -l)
newest=$(ls -1t /var/backups/dfs/dfs_*.age 2>/dev/null | head -1)
drive_n=$(timeout 120 rclone lsf gdrive:Server-Backups/servers/dfs --contimeout 30s --timeout 60s 2>/dev/null | grep -c '\.age$')
match="no"; [ -n "$newest" ] && timeout 300 rclone check /var/backups/dfs gdrive:Server-Backups/servers/dfs --one-way --include "$(basename "$newest")" --contimeout 30s --timeout 60s >/dev/null 2>&1 && match="yes"
[ "$match" = "yes" ] || attn+=("newest backup not confirmed on Google Drive")
[ "${drive_n:-0}" -ge 20 ] || attn+=("only ${drive_n:-0} backups on Google Drive (expect ~30)")
fails=$(q "SELECT (SELECT COUNT(*) FROM feed_runs WHERE status!='ok' AND started>=datetime('now','-31 days'))+(SELECT COUNT(*) FROM price_runs WHERE status!='ok' AND started>=datetime('now','-31 days'));")
oks=$(q "SELECT (SELECT COUNT(*) FROM feed_runs WHERE status='ok' AND started>=datetime('now','-31 days'))+(SELECT COUNT(*) FROM price_runs WHERE status='ok' AND started>=datetime('now','-31 days'));")
sched=$(q "SELECT value FROM meta WHERE key='scheduler';"); push=$(q "SELECT value FROM meta WHERE key='wp_push';")
offers=$(q "SELECT COUNT(*) FROM price_json;")
[ "$sched" = "on" ] || attn+=("DFS scheduler is ${sched:-unknown}")
bans=$(fail2ban-client status sshd 2>/dev/null | awk -F: '/Currently banned/{gsub(/[ \t]/,"",$2);print $2}')
hbfail=$(grep -c "ping failed" /var/log/dfs-heartbeat.log 2>/dev/null || echo 0)
if [ ${#attn[@]} -eq 0 ]; then verdict="All fine - nothing to do."; subj="DFS monthly check: all fine"; else verdict="NEEDS ATTENTION:$(printf '\n - %s' "${attn[@]}")"; subj="DFS monthly check: needs attention"; fi
body="DFS production server (rsg-dfs, Helsinki) - monthly check $(date -u +%F)

$verdict

System:   up $up (last boot $boot UTC); updates waiting: $pend (security: $sec); unattended-upgrade runs logged: $applied
Docker:   $dock; DFS container running: $running, restarts: $restarts
Disk:     ${disk}% used; database $dbsize
Backups:  $local_n on the server, ${drive_n:-?} on Google Drive; newest uploaded and identical on Drive: $match ($(basename "${newest:-none}"))
DFS jobs (31 days): $oks ok, $fails failed (each failure already sent its own email); scheduler: $sched; push to WordPress: $push; shoes with price data: $offers
Security: fail2ban SSH bans currently: ${bans:-0}; heartbeat pings that could not be sent: $hbfail

Not checked automatically: a test decrypt (the server only holds the public key). Do one by hand a few times a year:
dfs/PRODUCTION-SERVER.md -> Restore."
if [ "${REPORT_PRINT:-0}" = "1" ]; then echo "Subject: $subj"; echo "$body"; exit 0; fi
printf '%s' "$body" | docker exec -i dfs python -c "import sys; from dfs import alerts; ok, info = alerts.send(sys.argv[1], sys.stdin.read()); print('sent' if ok else 'NOT sent: ' + str(info))" "$subj"
