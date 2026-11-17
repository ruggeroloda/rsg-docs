#!/usr/bin/env bash
# rsg-dfs nightly backup — /usr/local/bin/dfs-backup.sh (root, /etc/cron.d/dfs-backup, 03:15 UTC)
# One age-encrypted archive: the DFS database (consistent sqlite .backup), the server .env, and the
# system config. The code is on GitHub and is not backed up here. Keeps 7 locally; uploads to
# Google Drive Backups/servers/dfs (keep 30 days). Hetzner's own server backups are a second copy.
# Decrypt: age -d -i backup-age-key.txt dfs_<stamp>.tar.zst.age | zstd -d | tar -x
set -euo pipefail
STAMP=$(date -u +%Y-%m-%d_%H%M)
DEST=/var/backups/dfs
WORK=$(mktemp -d /var/tmp/dfs-backup.XXXX)
RECIPIENT=/etc/rsg-backup/age-recipient.txt
DB=/var/lib/docker/volumes/dfs_data/_data/dfs.sqlite
OUT="$DEST/dfs_${STAMP}.tar.zst.age"
REMOTE="gdrive:Server-Backups/servers/dfs"
LOG=/var/log/dfs-backup.log
trap 'rm -rf "$WORK"' EXIT
mkdir -p "$DEST"; chmod 700 "$DEST"
exec >>"$LOG" 2>&1
echo "=== $(date -u +%FT%TZ) start"
# 1. Database: sqlite online backup (safe while the DFS runs), then an integrity check of the copy
sqlite3 "$DB" ".backup '$WORK/dfs.sqlite'"
[ "$(sqlite3 "$WORK/dfs.sqlite" 'PRAGMA integrity_check;')" = "ok" ] || { echo "ERROR integrity check failed"; exit 1; }
# 2. Secrets + config (code is in git)
cp /home/ruggero/dfs/.env "$WORK/dfs.env"
( cd /home/ruggero/dfs/app && git -c safe.directory=/home/ruggero/dfs/app log -1 --format='%H %cI %s' ) > "$WORK/git_commit.txt" 2>/dev/null || true
docker ps -a --format '{{.Names}}\t{{.Image}}\t{{.Status}}' > "$WORK/docker_ps.txt"
ufw status verbose > "$WORK/ufw.txt" 2>/dev/null || true
tar -cf "$WORK/etc.tar" /etc/cron.d /etc/ssh/sshd_config.d /etc/ufw /etc/fail2ban/jail.local /etc/rsg-backup \
  /etc/apt/apt.conf.d/52unattended-upgrades-local /etc/systemd/system/dfs-firewall.service /usr/local/sbin/dfs-firewall.sh \
  /usr/local/bin/dfs-backup.sh /usr/local/bin/dfs-heartbeat.sh /etc/sudoers.d/90-ruggero 2>/dev/null || true
# 3. One encrypted archive
tar -C "$WORK" -cf - . | zstd -q -6 | age -R "$RECIPIENT" > "$OUT.part"
mv "$OUT.part" "$OUT"
echo "archive $(du -h "$OUT" | cut -f1) $OUT"
# 4. Local retention: newest 7
ls -1t "$DEST"/dfs_*.tar.zst.age | tail -n +8 | xargs -r rm -f
# 5. Off-server copy (Google Drive, scope drive.file); the heartbeat reads the marker below
timeout 900 rclone copy "$OUT" "$REMOTE" --quiet --contimeout 30s --timeout 120s --retries 5 --low-level-retries 10
timeout 300 rclone delete "$REMOTE" --min-age 30d --quiet --contimeout 30s --timeout 120s || true
date -u +%s > /var/backups/dfs/.last_upload_ok
echo "uploaded to $REMOTE"
echo "=== $(date -u +%FT%TZ) done"
