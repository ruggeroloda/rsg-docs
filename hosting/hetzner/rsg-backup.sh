#!/usr/bin/env bash
# rsgmedia nightly backup — /usr/local/bin/rsg-backup.sh (runs as root from /etc/cron.d/rsg-backup)
# Creates one age-encrypted archive: all Postgres DBs, Docker volumes (incl. n8n encryption key),
# /home/ruggero (code, compose, NPM certs), system config. Keeps 7 locally; uploads to Google Drive if
# the rclone remote "gdrive" exists, keeping 30 days there.
# Decrypt: age -d -i backup-age-key.txt rsgmedia_<stamp>.tar.zst.age | zstd -d | tar -x
set -euo pipefail

STAMP=$(date -u +%Y-%m-%d_%H%M)
DEST=/var/backups/rsgmedia
WORK=$(mktemp -d /var/tmp/rsg-backup.XXXX)
RECIPIENT=/etc/rsg-backup/age-recipient.txt
OUT="$DEST/rsgmedia_${STAMP}.tar.zst.age"
REMOTE="gdrive:Server-Backups/servers/rsgmedia"
LOG=/var/log/rsg-backup.log
trap 'rm -rf "$WORK"' EXIT
exec >>"$LOG" 2>&1
echo "=== $(date -u +%FT%TZ) start"

# 1. Postgres: every database + roles, consistent logical dump
docker exec postgres pg_dumpall -U admin | zstd -q -6 > "$WORK/postgres_all.sql.zst"

# 2. Docker volumes (n8n_data holds the n8n encryption key in "config")
for v in n8n_data portainer_data compose_imggen_images; do
  tar -C /var/lib/docker/volumes/$v -cf "$WORK/vol_$v.tar" _data
done

# 3. Home: code, compose files, NPM data + certs, scripts (exclude rebuildable stuff)
# tar exits 1 ("file changed as we read it", e.g. live NPM access logs): archive is still complete, accept it; exit 2+ = real failure
tar -C /home -cf "$WORK/home_ruggero.tar" \
  --exclude='ruggero/.nvm' --exclude='ruggero/.cache' --exclude='ruggero/.npm' \
  --exclude='*/node_modules' --exclude='ruggero/backups' --exclude='*.mp4' --exclude='*.mov' \
  --exclude='*/venv' --exclude='*/.venv' --exclude='*/__pycache__' ruggero || { rc=$?; [ "$rc" -eq 1 ] || exit "$rc"; echo "WARN home tar: files changed while reading (exit 1), continuing"; }

# 4. System config
crontab -u ruggero -l > "$WORK/crontab_ruggero.txt" 2>/dev/null || true
ufw status verbose > "$WORK/ufw.txt" 2>/dev/null || true
docker ps -a --format '{{.Names}}\t{{.Image}}\t{{.Status}}' > "$WORK/docker_ps.txt"
tar -cf "$WORK/etc.tar" /etc/cron.d /etc/ssh/sshd_config /etc/ufw /etc/fail2ban /etc/rsg-backup 2>/dev/null || true

# 5. One encrypted archive
tar -C "$WORK" -cf - . | zstd -q -3 | age -R "$RECIPIENT" > "$OUT.part"
mv "$OUT.part" "$OUT"
echo "archive $(du -h "$OUT" | cut -f1) $OUT"

# 6. Local retention: newest 7
ls -1t "$DEST"/rsgmedia_*.tar.zst.age | tail -n +8 | xargs -r rm -f

# 7. Off-server copy to Google Drive, scope drive.file (skipped until the rclone remote is configured)
if rclone listremotes 2>/dev/null | grep -q '^gdrive:$'; then
  rclone copy "$OUT" "$REMOTE" --quiet
  rclone delete "$REMOTE" --min-age 30d --quiet || true
  echo "uploaded to $REMOTE"
else
  echo "WARN no rclone remote 'gdrive' — local copy only"
fi
echo "=== $(date -u +%FT%TZ) done"
