#!/bin/bash
# Refresh Rocket.net STAGING from LIVE (11 Nov 2026), instead of Rocket.net's "re-sync" (which copies all 25 GB of uploads).
# Run from the Mac:  ssh -i ~/.rsgkey jfx9wij@194.39.148.145 'bash -s' < hosting/rocket/refresh-staging.sh
# What it does (staging only; live is only read):
#   1. backs up staging's database, plugins folder and .htaccess to ~/rsg_deploy_backups/staging-refresh-<stamp>/
#   2. copies live's plugins folder over staging's (code; drop-ins and wp-rocket-config stay staging's own)
#   3. replaces staging's database with live's, then rewrites live URLs to the staging URL
#   4. restores staging-only values: the DFS push user's application passwords (test bench login)
#   5. makes sure staging serves uploads it doesn't have from live (.htaccess rule), flushes staging caches
# Never copies uploads. The theme is not touched (deploy.sh keeps staging and live on the same commit).
set -euo pipefail
LIVE="$HOME/public_html"; STG="$HOME/public_html/jfx92wmcr3-staging.onrocket.site"
LIVE_URL="https://www.runningshoesguru.com"; STG_URL="https://jfx92wmcr3-staging.onrocket.site"
STAMP=$(date -u +%Y%m%d-%H%M); BK="$HOME/rsg_deploy_backups/staging-refresh-$STAMP"; mkdir -p "$BK"
W="wp --skip-themes --skip-plugins"
say() { echo "$(date -u +%T) $*"; }

say "1. backups -> $BK"
(cd "$STG" && $W db export - --single-transaction --quick 2>/dev/null | gzip > "$BK/staging-db.sql.gz")
tar -C "$STG/wp-content" -czf "$BK/staging-plugins.tar.gz" plugins
cp -p "$STG/.htaccess" "$BK/staging.htaccess"
(cd "$STG" && $W user meta get 115 _application_passwords --format=json 2>/dev/null) > "$BK/dfs-push-app-passwords.json"
[ -s "$BK/staging-db.sql.gz" ] && [ -s "$BK/dfs-push-app-passwords.json" ] || { say "backup incomplete, stopping"; exit 1; }

say "2. plugins live -> staging"
rsync -a --delete "$LIVE/wp-content/plugins/" "$STG/wp-content/plugins/"

say "3. database live -> staging"
(cd "$LIVE" && $W db export - --single-transaction --quick 2>/dev/null) | (cd "$STG" && $W db import - 2>/dev/null)
cd "$STG"
for from in "$LIVE_URL" "http://www.runningshoesguru.com" "https://runningshoesguru.com" "http://runningshoesguru.com"; do
  n=$($W search-replace "$from" "$STG_URL" --all-tables-with-prefix --precise --skip-columns=guid --format=count 2>/dev/null)
  say "   $from -> staging: $n replacements"
done
wp cache flush >/dev/null 2>&1 || true   # Redis still holds staging's old values; without this the restore below is silently skipped

say "4. staging-only values"
$W user meta update 115 _application_passwords "$(cat "$BK/dfs-push-app-passwords.json")" --format=json 2>/dev/null >/dev/null
got=$($W user meta get 115 _application_passwords --format=json 2>/dev/null)
[ "$got" = "$(cat "$BK/dfs-push-app-passwords.json")" ] || { say "   dfs-push app passwords NOT restored - fix by hand from $BK"; exit 1; }
say "   home=$($W option get home 2>/dev/null) dfs-push app passwords restored"

say "5. uploads fallback + caches"
if ! grep -q "RSG lean staging" .htaccess; then
  { printf '# BEGIN RSG lean staging: uploads missing on staging come from live (11 Nov 2026)\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteCond %%{REQUEST_FILENAME} !-f\nRewriteRule ^wp-content/uploads/(.*)$ %s/wp-content/uploads/$1 [R=302,L]\n</IfModule>\n# END RSG lean staging\n\n' "$LIVE_URL"; cat .htaccess; } > .htaccess.new && mv .htaccess.new .htaccess
fi
wp cache flush >/dev/null 2>&1 || true
wp eval 'rocket_clean_domain();' >/dev/null 2>&1 || true
say "done. Check staging pages, then: wp cdn purge (from the staging webroot) if needed."
