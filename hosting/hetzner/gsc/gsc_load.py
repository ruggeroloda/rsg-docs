"""Load Google Search Console search analytics into Postgres (schema rsg, database umami) — 16 Nov 2026.

Per day: rsg.gsc_page_day (page, complete totals = the Pages report; Google drops ~40 % of clicks once page is combined
with another dimension, so the split tables below are partial), rsg.gsc_page_country_day (page x country, COUNTRIES only),
rsg.gsc_country_day (country x device, whole site), rsg.gsc_query_day (page x query; Google drops rare/anonymised queries).
Usage:  gsc_load.py                 -> the last 5 final days (re-loads them: Google revises recent days)
        gsc_load.py 2025-07-01 2026-11-10   -> backfill a range (GSC keeps 16 months)
Env: GSC_SITE (e.g. sc-domain:runningshoesguru.com), GSC_KEY_FILE (service account JSON), DATABASE_URL.
"""
import datetime as dt, os, sys, time
import psycopg, psycopg.types.json, requests
from google.oauth2 import service_account
from google.auth.transport.requests import Request

SITE = os.environ["GSC_SITE"]
KEY = os.environ.get("GSC_KEY_FILE", "/secrets/gsc-service-account.json")
DB = os.environ["DATABASE_URL"]
API = "https://searchconsole.googleapis.com/webmasters/v3/sites/{}/searchAnalytics/query"

creds = service_account.Credentials.from_service_account_file(KEY, scopes=["https://www.googleapis.com/auth/webmasters.readonly"])

def token():
    if not creds.valid:
        creds.refresh(Request())
    return creds.token

def query(day, dims):
    rows, start = [], 0
    while True:
        body = {"startDate": day, "endDate": day, "dimensions": dims, "rowLimit": 25000, "startRow": start, "type": "web"}
        for attempt in range(5):
            r = requests.post(API.format(requests.utils.quote(SITE, safe="")), json=body,
                              headers={"Authorization": "Bearer " + token()}, timeout=120)
            if r.status_code in (429, 500, 503):
                time.sleep(2 ** attempt * 5); continue
            r.raise_for_status(); break
        else:
            r.raise_for_status()
        got = r.json().get("rows", [])
        rows += got
        if len(got) < 25000:
            return rows
        start += 25000

COUNTRIES = {"usa", "gbr", "deu", "aut", "che", "can", "aus", "nzl", "irl", "nld", "ind"}  # price-box markets + big audiences

def first_incomplete(a, b):
    """Google's own marker: the first day whose data is not final yet (None = all final)."""
    body = {"startDate": a, "endDate": b, "dimensions": ["date"], "type": "web", "dataState": "all"}
    r = requests.post(API.format(requests.utils.quote(SITE, safe="")), json=body,
                      headers={"Authorization": "Bearer " + token()}, timeout=120)
    r.raise_for_status()
    return (r.json().get("metadata") or {}).get("firstIncompleteDate")

def load(conn, day, fi=None):
    pd = query(day, ["page"])
    pc = [r for r in query(day, ["page", "country"]) if r["keys"][1] in COUNTRIES]
    cd = query(day, ["country", "device"])
    qd = query(day, ["page", "query"])
    v = lambda r: (int(r["clicks"]), int(r["impressions"]), r["position"])
    with conn.transaction(), conn.cursor() as cur:
        for t in ("gsc_page_day", "gsc_page_country_day", "gsc_country_day", "gsc_query_day"):
            cur.execute(f"DELETE FROM rsg.{t} WHERE day = %s", (day,))
        cur.executemany("INSERT INTO rsg.gsc_page_day VALUES (%s,%s,%s,%s,%s)", [(day, *r["keys"], *v(r)) for r in pd])
        cur.executemany("INSERT INTO rsg.gsc_page_country_day VALUES (%s,%s,%s,%s,%s,%s)", [(day, *r["keys"], *v(r)) for r in pc])
        cur.executemany("INSERT INTO rsg.gsc_country_day VALUES (%s,%s,%s,%s,%s,%s)", [(day, *r["keys"], *v(r)) for r in cd])
        cur.executemany("INSERT INTO rsg.gsc_query_day VALUES (%s,%s,%s,%s,%s,%s)", [(day, *r["keys"], *v(r)) for r in qd])
        n = {"page": len(pd), "page_country": len(pc), "country_device": len(cd), "page_query": len(qd)}
        cur.execute("INSERT INTO rsg.gsc_load_log (day, rows) VALUES (%s, %s) ON CONFLICT (day) DO UPDATE SET loaded_at = now(), rows = EXCLUDED.rows",
                    (day, psycopg.types.json.Jsonb(n)))
        # every load of a day, with its totals: shows whether Google still revises "final" days (16 Nov 2026)
        cur.execute("INSERT INTO rsg.gsc_day_revision (day, clicks, impressions, first_incomplete) VALUES (%s, %s, %s, %s)",
                    (day, sum(int(r["clicks"]) for r in cd), sum(int(r["impressions"]) for r in cd), fi))
    print(day, n, flush=True)

def main():
    today = dt.date.today()
    if len(sys.argv) == 3:
        a, b = dt.date.fromisoformat(sys.argv[1]), dt.date.fromisoformat(sys.argv[2])
    else:
        a, b = today - dt.timedelta(days=7), today - dt.timedelta(days=3)
    fi = first_incomplete(a.isoformat(), today.isoformat())
    with psycopg.connect(DB) as conn:
        d = a
        while d <= b:
            load(conn, d.isoformat(), fi)
            d += dt.timedelta(days=1)

if __name__ == "__main__":
    main()
