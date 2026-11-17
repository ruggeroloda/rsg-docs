SERVER-BACKUPS — README
=======================
Owner: Ruggero (ruggeroloda@gmail.com). Created 2026-11-05.

DON'T create or rename folders in here by hand. The upload tool (rclone, Google Drive
scope "drive.file") can only see files and folders it created itself. A hand-made folder with
the same name ends up as a duplicate.

LOGIN (since 2026-11-11): own Google OAuth client "rclone-backups" in Cloud project
quickstart-1593179204049 (credentials: rsg/_private/google-oauth-rsg-backups.txt). Before that
the servers used rclone's shared client (rate-limited) and wrote to the folder "Backups"; that
client can't see this folder and vice versa. "Backups" now only holds the NAS copy (nas/) and the
servers' last old backups (servers/, sites/: delete those after 2026-12-11).

STRUCTURE:  Server-Backups/<type>/<source>/<source>_<YYYY-MM-DD_HHMM>.<ext>  (sites/rsg uses
            sub-folders db/, files/, uploads/, archive/ with rsg-db_..., rsg-files_..., rsg-uploads-<year>_...)
  servers/   whole-server backups (rsgmedia, optiplex, ...)
  sites/     website databases + uploads (rsg = runningshoesguru.com)
  data/      app data exports (airtable-accounting, ...)
  devices/   laptops / desktops

ENCRYPTION: all files end in .age (https://age-encryption.org). Every source uses the same
key pair. The private key is NOT on any server. Keep it in at least two places:
  - Mac: Documents/claude/projects/rsg/_private/backup-age-key.txt
  - 1Password (done 6 Nov)
Public key (safe to share; used by servers to encrypt):
  age1sq06vgedr64ej0fw5j72qjmlmlkdmjzc0kg5wmtcgy73e79n8czs8nv8j9

RESTORE (any machine with age):
  servers/*:      age -d -i backup-age-key.txt FILE.tar.zst.age | zstd -d | tar -x
  sites/rsg db:   age -d -i backup-age-key.txt rsg-db_<stamp>.sql.gz.age | gunzip > rsg.sql
  sites/rsg tars: age -d -i backup-age-key.txt rsg-files_<stamp>.tar.gz.age | tar -xz   (uploads: .tar.age | tar -x)
  Full WordPress restore steps: projects/rsg/hosting/RUNBOOK.md

SOURCES
-------
servers/rsgmedia
  What:   Hetzner CPX32 116.203.146.177 — all Postgres DBs (n8n, stargrid), Docker volumes
          (incl. n8n encryption key), /home/ruggero (Stargrid code, compose, NPM certs),
          system config (cron, ssh, ufw, fail2ban).
  When:   nightly 03:30 UTC (/etc/cron.d/rsg-backup -> /usr/local/bin/rsg-backup.sh)
  Keep:   30 days here; 7 days on the server (/var/backups/rsgmedia)
  Log:    /var/log/rsg-backup.log on the server
  Source: projects/rsg/hosting/hetzner/rsg-backup.sh
  Restore tested: 2026-11-05 (36/36 n8n workflows, 18 credentials)

servers/dfs
  What:   Hetzner CAX11 135.181.110.157 (rsg-dfs, DFS production) — dfs.sqlite (online backup +
          integrity check), the server .env, system config.
  When:   nightly 03:15 UTC (/etc/cron.d/dfs-backup -> /usr/local/bin/dfs-backup.sh)
  Keep:   30 days here; 7 days on the server (/var/backups/dfs)
  Source: projects/rsg/dfs/production/dfs-backup.sh
  Restore tested: 2026-11-09 (dfs/PRODUCTION-SERVER.md)

sites/rsg
  What:   runningshoesguru.com WordPress (Rocket.net, live): db/ = database dump (.sql.gz.age),
          files/ = wp-config.php + .htaccess + wp-content without uploads/cache (.tar.gz.age),
          uploads/ = wp-content/uploads, one .tar.age per year folder + "other" (loose files).
  When:   Sundays 04:10 UTC (db + files), 04:40 UTC (uploads), crontab of the Rocket.net user
          -> ~/rsg-backup/rsg-wp-backup.sh (rclone + age in ~/bin there)
  Keep:   db + files 60 days; uploads: past years once, current + previous year + other: 2 newest
  Source: projects/rsg/hosting/rocket/rsg-wp-backup.sh
  Watch:  healthchecks.io "rsg-site" fails if the newest db backup is 8+ days old
  Restore tested: 2026-11-11 (db: 103 tables, complete; files: 5,613 entries)
  Note:   Rocket.net also keeps 30 daily backups of its own (panel -> Backups): first choice for a restore.
