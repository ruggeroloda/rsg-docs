# Wayback CDX index for RSG pages: one row per page per month with a 200 snapshot (2023-01 .. 2026-11). 20 Nov 2026.
import csv, json, sys, time, requests
rows = list(csv.DictReader(open(sys.argv[1])))
out = csv.writer(open(sys.argv[2], "w", newline=""))
out.writerow(["page", "type", "breaks", "month", "timestamp", "wayback_url"])
S = requests.Session(); S.headers["User-Agent"] = "rsg-research/1.0 (site owner)"
for i, r in enumerate(rows):
    url = "www.runningshoesguru.com" + r["page"]
    for attempt in range(4):
        try:
            j = S.get("https://web.archive.org/cdx/search/cdx", params={"url": url, "output": "json", "from": "202301", "to": "202611",
                      "filter": "statuscode:200", "collapse": "timestamp:6", "fl": "timestamp,original"}, timeout=60)
            if j.status_code == 429: time.sleep(30); continue
            data = j.json(); break
        except Exception as e:
            time.sleep(10); data = []
    for ts, orig in data[1:]:
        out.writerow([r["page"], r["type"], r["breaks"], ts[:4] + "-" + ts[4:6], ts, f"https://web.archive.org/web/{ts}/{orig}"])
    print(i, r["page"], len(data) - 1 if data else 0, flush=True)
    time.sleep(1.5)
