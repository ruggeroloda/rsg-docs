#!/bin/bash
# Umami retention (privacy policy 16 Nov 2026: analytics kept up to 3 years). Monthly, /etc/cron.d/umami-retention.
# Deletes everything older than 3 years from the umami database, child rows first. Source: rsg project hosting/hetzner/umami-retention.sh
set -euo pipefail
docker exec -i postgres psql -v ON_ERROR_STOP=1 -U umami -d umami -q <<'SQL'
BEGIN;
DELETE FROM event_data      WHERE created_at < now() - interval '3 years';
DELETE FROM website_event   WHERE created_at < now() - interval '3 years';
DELETE FROM heatmap_event   WHERE created_at < now() - interval '3 years';
DELETE FROM session_replay  WHERE created_at < now() - interval '3 years';
DELETE FROM error_event     WHERE created_at < now() - interval '3 years';
DELETE FROM session_data    WHERE created_at < now() - interval '3 years';
DELETE FROM session s WHERE s.created_at < now() - interval '3 years'
  AND NOT EXISTS (SELECT 1 FROM website_event e WHERE e.session_id = s.session_id);
COMMIT;
SQL
