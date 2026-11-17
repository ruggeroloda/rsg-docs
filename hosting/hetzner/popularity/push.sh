#!/bin/bash
# Weekly (Mon 07:40 UTC, /etc/cron.d/rsg-popularity): Search Console clicks per review page over the last 90 final days
# -> WordPress POST /wp-json/rsg/v1/popularity (theme includes/reviews/similar.php), which rebuilds the "Similar shoes"
# picks. 16 Nov 2026. Config in .env next to this file: RSG_POP_TOKEN (= WordPress option rsg_popularity_token),
# RSG_POP_URLS (space-separated endpoints, live and/or staging). Log: push.log. Manual run: ~/popularity/push.sh
set -euo pipefail
cd "$(dirname "$0")"
. ./.env
json=$(rsg-sql -qtA <<'SQL'
with last as (select max(day) d from rsg.gsc_page_day),
p as (
  select regexp_replace(regexp_replace(page, '^https?://(www\.)?runningshoesguru\.com', ''), '[?#].*$', '') path, sum(clicks) clicks
  from rsg.gsc_page_day, last
  where day > last.d - 90 and page like '%/reviews/%'
  group by 1 having sum(clicks) > 0
)
select json_build_object('to', (select d from last), 'window', 90, 'clicks', coalesce((select json_object_agg(path, clicks) from p), '{}'::json));
SQL
)
for u in $RSG_POP_URLS; do
  echo "$(date -u +%FT%TZ) $u $(curl -sS -m 600 -H 'Content-Type: application/json' -H "X-RSG-Token: $RSG_POP_TOKEN" --data-binary "$json" "$u" | head -c 300)"
done
