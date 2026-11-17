#!/bin/bash
# Off-site backup of the LIVE WordPress site (Rocket.net) to Google Drive, outside Rocket.net (11 Nov 2026).
# Source: rsg project hosting/rocket/rsg-wp-backup.sh  ->  server ~/rsg-backup/rsg-wp-backup.sh (crontab of jfx9wij).
#   weekly : database (wp db export) + wp-config.php + .htaccess + wp-content without uploads/cache,
#            age-encrypted (public key only on the server) -> gdrive:Server-Backups/sites/rsg/{db,files}/, kept 60 days.
#   uploads: wp-content/uploads as one age-encrypted tar per year -> gdrive:Server-Backups/sites/rsg/uploads/ (past years once,
#            current + previous year + loose files every run, 2 copies kept; caches left out).
# Rocket.net's own daily backups (30 kept) stay the first restore option; this is the copy outside Rocket.net.
# Restore: see hosting/RUNBOOK.md. Freshness is checked by the rsgmedia site check (healthchecks.io "rsg-site").
set -euo pipefail
export PATH="$HOME/bin:$PATH"
# rclone uses its shared public Google client, which hits "rateLimitExceeded" now and then: retry chunks more often.
export RCLONE_LOW_LEVEL_RETRIES=20 RCLONE_DRIVE_CHUNK_SIZE=64M
MODE="${1:-weekly}"
WEB="$HOME/public_html"; DEST="gdrive:Server-Backups/sites/rsg"; KEEP_DAYS=60
REC="$HOME/rsg-backup/age-recipient.txt"; LOG="$HOME/rsg-backup/backup.log"
STAMP=$(date -u +%Y-%m-%d_%H%M)
log() { echo "$(date -u +%FT%TZ) $*" >> "$LOG"; }
trap 'log "FAILED $MODE (line $LINENO)"' ERR
cd "$WEB"
case "$MODE" in
  weekly)
    wp db export - --single-transaction --quick --skip-themes --skip-plugins 2>/dev/null \
      | gzip -6 | age -R "$REC" | rclone rcat "$DEST/db/rsg-db_$STAMP.sql.gz.age"
    tar -C "$WEB" -czf - --exclude=wp-content/uploads --exclude=wp-content/cache \
        --exclude=wp-content/upgrade --exclude=wp-content/upgrade-temp-backup wp-config.php .htaccess wp-content \
      | age -R "$REC" | rclone rcat "$DEST/files/rsg-files_$STAMP.tar.gz.age"
    rclone delete --min-age "${KEEP_DAYS}d" "$DEST/db"
    rclone delete --min-age "${KEEP_DAYS}d" "$DEST/files"
    log "ok weekly $(rclone size "$DEST/db/rsg-db_$STAMP.sql.gz.age" | tr '\n' ' ')" ;;
  uploads)
    # One encrypted tar per year folder (Drive creates files slowly: ~0.7 files/s, so 275k single files would take days).
    # Past years: uploaded once (a missing year is uploaded again). Current + previous year and the loose files: every run,
    # keeping the 2 newest copies. Regenerable caches are left out.
    exec 9>"$HOME/rsg-backup/uploads.lock"
    flock -n 9 || { log "skip uploads: previous run still running"; exit 0; }
    fails=0; U="$WEB/wp-content/uploads"; Y=$(date -u +%Y); have=$(rclone lsf "$DEST/uploads" 2>/dev/null || true)
    for d in "$U"/20[0-9][0-9]; do
      y=$(basename "$d")
      if [ "$y" -lt $((Y-1)) ] && grep -q "^rsg-uploads-${y}_" <<<"$have"; then continue; fi
      if ! { tar -C "$U" -cf - "$y" | age -R "$REC" | rclone rcat "$DEST/uploads/rsg-uploads-${y}_$STAMP.tar.age"; }; then
        log "warn: uploads $y failed (retried next run)"; fails=$((fails+1)); continue
      fi
      rclone lsf "$DEST/uploads" --include "rsg-uploads-${y}_*" | sort | head -n -2 | while read -r f; do rclone deletefile "$DEST/uploads/$f"; done || log "warn: could not prune old copies"
    done
    tar -C "$U" -cf - --exclude='./20[0-9][0-9]' --exclude=./cache --exclude=./dfs-images-cache --exclude=./amazon-api-cache \
        --exclude=./minit --exclude=./fvm . | age -R "$REC" | rclone rcat "$DEST/uploads/rsg-uploads-other_$STAMP.tar.age"
    rclone lsf "$DEST/uploads" --include "rsg-uploads-other_*" | sort | head -n -2 | while read -r f; do rclone deletefile "$DEST/uploads/$f"; done || log "warn: could not prune old copies"
    [ "$fails" -eq 0 ] || { log "FAILED uploads: $fails year(s), see warnings"; exit 1; }
    log "ok uploads $(rclone size "$DEST/uploads" | tr '\n' ' ')" ;;
  *) echo "usage: $0 weekly|uploads"; exit 2 ;;
esac
