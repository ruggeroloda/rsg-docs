"""Load GA4 history into Postgres (schema rsg, database umami) — 20 Nov 2026. Weekly reload of the last 2 months; any range by hand.

Per month (one API call per table and month, paged at 250k rows): rsg.ga4_page_day, rsg.ga4_landing_day,
rsg.ga4_site_day, rsg.ga4_event_day (see schema.sql). Each month is replaced whole in one transaction.
Usage:  ga4_load.py                      -> last month + this month (weekly cron, Mondays 06:50 UTC)
        ga4_load.py 2023-02 2026-11      -> months from .. to (inclusive); the current month loads up to yesterday
Env: GA4_PROPERTY (250633127), GA4_KEY_FILE (service account JSON, same as Search Console), DATABASE_URL.
"""
import datetime as dt, os, sys, time
import psycopg, psycopg.types.json, requests
from google.oauth2 import service_account
from google.auth.transport.requests import Request

PROP = os.environ["GA4_PROPERTY"]
KEY = os.environ.get("GA4_KEY_FILE", "/secrets/gsc-service-account.json")
DB = os.environ["DATABASE_URL"]
API = f"https://analyticsdata.googleapis.com/v1beta/properties/{PROP}:runReport"
HOSTS = ["www.runningshoesguru.com", "runningshoesguru.com"]
AUTO = ["page_view", "user_engagement", "session_start", "first_visit", "scroll"]  # GA4's own events: not loaded

creds = service_account.Credentials.from_service_account_file(KEY, scopes=["https://www.googleapis.com/auth/analytics.readonly"])

def token():
    if not creds.valid:
        creds.refresh(Request())
    return creds.token

def host_filter(extra=None):
    f = {"filter": {"fieldName": "hostName", "inListFilter": {"values": HOSTS}}}
    return {"andGroup": {"expressions": [f, extra]}} if extra else f

def report(a, b, dims, mets, flt):
    """All rows for dims x metrics between a and b (dates as YYYYMMDD keys)."""
    rows, off = [], 0
    while True:
        body = {"dateRanges": [{"startDate": a, "endDate": b}], "dimensions": [{"name": d} for d in dims],
                "metrics": [{"name": m} for m in mets], "dimensionFilter": flt, "limit": 250000, "offset": off,
                "keepEmptyRows": False}
        for attempt in range(5):
            r = requests.post(API, json=body, headers={"Authorization": "Bearer " + token()}, timeout=300)
            if r.status_code in (429, 500, 503):
                time.sleep(2 ** attempt * 10); continue
            r.raise_for_status(); break
        else:
            r.raise_for_status()
        j = r.json()
        got = j.get("rows", [])
        rows += [([d["value"] for d in x["dimensionValues"]], [m["value"] for m in x["metricValues"]]) for x in got]
        off += len(got)
        if not got or off >= j.get("rowCount", 0):
            return rows

def day(s):
    return dt.date(int(s[:4]), int(s[4:6]), int(s[6:]))

def load(conn, m0):
    m1 = (m0.replace(day=28) + dt.timedelta(days=4)).replace(day=1)
    end = min(m1 - dt.timedelta(days=1), dt.date.today() - dt.timedelta(days=1))
    a, b = m0.isoformat(), end.isoformat()
    not_auto = {"notExpression": {"filter": {"fieldName": "eventName", "inListFilter": {"values": AUTO}}}}
    pg = report(a, b, ["date", "pagePath"], ["screenPageViews", "keyEvents", "userEngagementDuration"], host_filter())
    ld = report(a, b, ["date", "landingPage", "sessionDefaultChannelGroup"],
                ["sessions", "engagedSessions", "screenPageViews", "keyEvents"], host_filter())
    st = report(a, b, ["date", "sessionDefaultChannelGroup", "deviceCategory", "country"],
                ["sessions", "engagedSessions", "screenPageViews", "keyEvents"], host_filter())
    ev = report(a, b, ["date", "pagePath", "eventName"], ["eventCount", "keyEvents"], host_filter(not_auto))
    other = {k: sum(1 for d, _ in t if "(other)" in d[1:]) for k, t in (("page", pg), ("landing", ld), ("site", st), ("event", ev))}
    i = lambda v: int(float(v))
    with conn.transaction(), conn.cursor() as cur:
        for t in ("ga4_page_day", "ga4_landing_day", "ga4_site_day", "ga4_event_day"):
            cur.execute(f"DELETE FROM rsg.{t} WHERE day BETWEEN %s AND %s", (a, b))
        cur.executemany("INSERT INTO rsg.ga4_page_day VALUES (%s,%s,%s,%s,%s)",
                        [(day(d[0]), d[1], i(v[0]), i(v[1]), i(v[2])) for d, v in pg])
        cur.executemany("INSERT INTO rsg.ga4_landing_day VALUES (%s,%s,%s,%s,%s,%s,%s)",
                        [(day(d[0]), d[1], d[2], *map(i, v)) for d, v in ld])
        cur.executemany("INSERT INTO rsg.ga4_site_day VALUES (%s,%s,%s,%s,%s,%s,%s,%s)",
                        [(day(d[0]), d[1], d[2], d[3], *map(i, v)) for d, v in st])
        cur.executemany("INSERT INTO rsg.ga4_event_day VALUES (%s,%s,%s,%s,%s)",
                        [(day(d[0]), d[1], d[2], *map(i, v)) for d, v in ev])
        n = {"page": len(pg), "landing": len(ld), "site": len(st), "event": len(ev)}
        cur.execute("INSERT INTO rsg.ga4_load_log (month, rows, other_rows) VALUES (%s,%s,%s) ON CONFLICT (month) DO UPDATE "
                    "SET loaded_at = now(), rows = EXCLUDED.rows, other_rows = EXCLUDED.other_rows",
                    (m0, psycopg.types.json.Jsonb(n), psycopg.types.json.Jsonb(other)))
    print(m0.strftime("%Y-%m"), b, n, "other:", other, flush=True)

def main():
    if len(sys.argv) == 3:
        m = dt.date.fromisoformat(sys.argv[1] + "-01")
        last = dt.date.fromisoformat(sys.argv[2] + "-01")
    else:  # weekly job: last month + this month (GA4 settles within ~3 days, so the whole previous month is reloaded once more)
        last = dt.date.today().replace(day=1)
        m = (last - dt.timedelta(days=1)).replace(day=1)
    with psycopg.connect(DB) as conn:
        while m <= last:
            load(conn, m)
            m = (m.replace(day=28) + dt.timedelta(days=4)).replace(day=1)

if __name__ == "__main__":
    main()
