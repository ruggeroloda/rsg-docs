#!/bin/sh
# Daily revenue loads on rsgmedia (cron: /etc/cron.d/rsg-revenue). SQL is printed only after
# every API call succeeded, so a failed pull changes nothing in the database.
cd /home/ruggero/revenue || exit 1
set -a; . ./.env; set +a
echo "== $(date -u +%FT%TZ) fx" >> daily.log
python3 fx_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) cj" >> daily.log
python3 cj_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) rakuten" >> daily.log
python3 rakuten_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) awin" >> daily.log
python3 awin_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) impact" >> daily.log
python3 impact_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) avantlink" >> daily.log
python3 avantlink_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) commission_factory" >> daily.log
python3 cf_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) raptive" >> daily.log
python3 raptive_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) offers" >> daily.log
python3 offers_load.py 2>> daily.log | docker exec -i postgres psql -U umami -d umami -v ON_ERROR_STOP=1 -q >> daily.log 2>&1
echo "== $(date -u +%FT%TZ) dashboard" >> daily.log
# Earnings dashboard (25 Nov 2026): rebuild the page; written next to the served folder, then moved in whole
python3 dashboard/build_dashboard.py dashboard/template.html /home/ruggero/earnings-dash/index.tmp >> daily.log 2>&1 && mv /home/ruggero/earnings-dash/index.tmp /home/ruggero/earnings-dash/public/index.html
