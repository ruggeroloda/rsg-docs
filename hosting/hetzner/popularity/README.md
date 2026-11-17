# Search Console popularity push (rsgmedia → WordPress)
- `push.sh` on rsgmedia `~/popularity/`, weekly Mon 07:40 UTC (`/etc/cron.d/rsg-popularity`, log `~/popularity/push.log`), after the daily Search Console load (06:40) and metrics build (07:10).
- Sends clicks per `/reviews/…` page over the last 90 final Search Console days to `POST /wp-json/rsg/v1/popularity` (header `X-RSG-Token` = WordPress option `rsg_popularity_token`, autoload off). WordPress stores clicks per day per review (`rsg_gsc_cpd`) and rebuilds the "Similar shoes" picks (`rsg_similar_ids`); state in option `rsg_popularity_meta`.
- `.env` (not in git): `RSG_POP_TOKEN=…`, `RSG_POP_URLS="https://www.runningshoesguru.com/wp-json/rsg/v1/popularity https://jfx92wmcr3-staging.onrocket.site/wp-json/rsg/v1/popularity"`.
