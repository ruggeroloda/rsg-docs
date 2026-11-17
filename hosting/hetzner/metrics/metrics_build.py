"""Nightly metrics layer for the RSG analyst (database umami, schema rsg) — 16 Nov 2026.

1. Bot score for Umami sessions active recently (rsg.session_quality, rules RULES_V below).
2. Page type per URL (rsg.page_type_map).
3. Umami day metrics, human sessions only (rsg.metric_day, source 'umami', UTC days).
4. Search Console day metrics, every loaded day (source 'gsc').
5. Flags: unusual moves vs baselines, good and bad (rsg.flag).
Usage:  metrics_build.py                       -> yesterday + the day before (UTC), all GSC, flags
        metrics_build.py 2026-11-16 2026-11-20 -> rebuild Umami days in that range (inclusive), then the same
Env: DATABASE_URL (owner of schema rsg), UMAMI_WEBSITE_ID (live site).
"""
import datetime as dt, math, os, statistics, sys, time
from collections import defaultdict
import psycopg

DB = os.environ["DATABASE_URL"]
SITE = os.environ.get("UMAMI_WEBSITE_ID", "31037802-afa8-48d7-9ff0-badf153a16d8")
UMAMI_FIRST_FULL_DAY = dt.date(2026, 11, 17)   # live since 16 Nov 09:25 UTC: 16 Nov is partial, never a baseline
RULES_V = 2   # v2 16 Nov 2026 (Ruggero): + direct_offmarket_fhd
# Search Console: the loader takes only Google's "final" data, from today-3 (06:40 UTC) and reloads each day until today-7.
# Flags wait one more reload: a GSC day is used for flags only once it is >= 4 days old (Ruggero 16 Nov: the latest days are iffy).
GSC_SETTLE_DAYS = 4

# ---------------------------------------------------------------- 1. bot score
# Points per rule; quality: >=3 bot, 2 suspect, <2 human. Seen 16 Nov: ~45 % of sessions were automated browsers posing
# as Chrome; they also send spot_seen and Web Vitals, so "no activity" alone doesn't catch them. Change rules -> RULES_V+1
# and rebuild from 2026-11-16 (metrics_build.py 2026-11-16 <yesterday>).
SCORE_SQL = """
WITH act AS (
  SELECT session_id,
         count(*) FILTER (WHERE event_type = 1) pv, count(*) FILTER (WHERE event_type = 5) vit,
         count(*) FILTER (WHERE event_type = 2) ev, extract(epoch FROM max(created_at) - min(created_at)) dur,
         bool_and(coalesce(referrer_domain, '') = '' OR referrer_domain ILIKE '%%runningshoesguru%%')
           FILTER (WHERE event_type = 1) direct_only
  FROM website_event
  WHERE session_id IN (SELECT DISTINCT session_id FROM website_event WHERE created_at >= %(since)s)
  GROUP BY session_id),
r AS (
  SELECT s.session_id, s.website_id, array_remove(ARRAY[
    CASE WHEN s.screen IN ('1280x1200','1600x1600','800x600') THEN 'auto_screen:3' END,
    CASE WHEN s.screen = '375x812' AND s.os IS DISTINCT FROM 'iOS' THEN 'emulated_iphone:3' END,
    CASE WHEN s.city IN ('Ashburn','Boardman','Council Bluffs','The Dalles','Santa Clara') THEN 'datacenter_city:2' END,
    CASE WHEN a.pv = 0 THEN 'no_pageview:3' END,
    CASE WHEN s.os = 'Linux' THEN 'linux_desktop:1' END,
    CASE WHEN a.vit = 0 AND a.ev = 0 AND a.pv <= 1 AND a.dur = 0 THEN 'one_hit_silent:2' END,
    -- v2: direct-only visitor from outside RSG's markets with a US-English browser at 1920x1080 (16 Nov: ~half of the
    -- "human" visits on day one, 40+ countries, median 8 s). Markets = the Search Console loader's list.
    CASE WHEN a.direct_only AND s.screen = '1920x1080' AND s.language = 'en-US'
              AND coalesce(s.country, '') NOT IN ('US','GB','DE','AT','CH','CA','AU','NZ','IE','NL','IN')
         THEN 'direct_offmarket_fhd:2' END], NULL) reasons
  FROM session s JOIN act a USING (session_id))
INSERT INTO rsg.session_quality (session_id, website_id, score, quality, reasons, rules_v, built_at)
SELECT session_id, website_id, sc,
       CASE WHEN sc >= 3 THEN 'bot' WHEN sc = 2 THEN 'suspect' ELSE 'human' END, reasons, %(v)s, now()
FROM (SELECT r.*, (SELECT coalesce(sum(split_part(x, ':', 2)::int), 0) FROM unnest(reasons) x) sc FROM r) t
ON CONFLICT (session_id) DO UPDATE SET score = EXCLUDED.score, quality = EXCLUDED.quality, reasons = EXCLUDED.reasons,
  rules_v = EXCLUDED.rules_v, built_at = EXCLUDED.built_at
"""

PAGE_MAP_SQL = """
INSERT INTO rsg.page_type_map (url_path, page_type, last_seen)
SELECT DISTINCT ON (url_path) url_path, tag, created_at FROM website_event
WHERE website_id = %(w)s AND event_type = 1 AND tag IS NOT NULL AND created_at >= %(since)s
ORDER BY url_path, created_at DESC
ON CONFLICT (url_path) DO UPDATE SET page_type = EXCLUDED.page_type, last_seen = EXCLUDED.last_seen
WHERE EXCLUDED.last_seen > rsg.page_type_map.last_seen
"""

# ---------------------------------------------------------------- 3. Umami day metrics
# Base set: one day of live-site events from human sessions. layout for page views/visits/vitals = screen width < 992 px
# (our phone/desktop rule; events carry their own `layout` property).
BASE = """
WITH ev AS (
  SELECT we.event_id, we.visit_id, we.created_at, we.event_type, we.event_name, we.tag, we.url_path,
         we.referrer_domain, we.utm_source, we.utm_medium, we.lcp, we.inp, we.cls, we.ttfb, s.country,
         CASE WHEN coalesce(nullif(split_part(s.screen, 'x', 1), ''), '0')::int BETWEEN 1 AND 991 THEN 'phone'
              WHEN s.screen IS NULL OR s.screen = '' THEN CASE WHEN s.device IN ('mobile') THEN 'phone' ELSE 'desktop' END
              ELSE 'desktop' END AS layout
  FROM website_event we
  JOIN session s ON s.session_id = we.session_id
  JOIN rsg.session_quality q ON q.session_id = we.session_id AND q.quality = 'human'
  WHERE we.website_id = %(w)s AND we.created_at >= %(d)s::date AND we.created_at < %(d)s::date + 1)
"""

D = lambda col, key: f"CASE WHEN GROUPING({col}) = 0 THEN '{key}=' || coalesce({col}::text, 'none') END"

UMAMI_QUERIES = {
  "pageviews": BASE + f"""
SELECT 'pageviews', concat_ws(';', {D('tag','page_type')}, {D('layout','layout')}), count(*), NULL::int
FROM ev WHERE event_type = 1 GROUP BY GROUPING SETS ((), (tag), (layout), (tag, layout))""",

  "visits": BASE + f""",
v AS (
  SELECT DISTINCT ON (visit_id) visit_id, layout, tag AS landing,
    CASE WHEN utm_medium ILIKE 'email%%' OR utm_source ILIKE '%%mailerlite%%' THEN 'email'
         WHEN referrer_domain ~* '(chatgpt|openai|perplexity|gemini\\.google|copilot|claude\\.ai|you\\.com|phind|meta\\.ai)' THEN 'ai'
         WHEN referrer_domain ~* '(google|bing|duckduckgo|yahoo|baidu|ecosia|yandex|brave|qwant|startpage|naver|seznam)' THEN 'search'
         WHEN referrer_domain ~* '(facebook|instagram|reddit|pinterest|^t\\.co$|x\\.com|twitter|youtube|tiktok|linkedin|strava|threads)' THEN 'social'
         WHEN coalesce(referrer_domain, '') = '' THEN 'direct'
         WHEN referrer_domain ILIKE '%%runningshoesguru%%' THEN 'internal'
         ELSE 'referral' END AS src,
    CASE WHEN country IN ('US','GB','AU','CA','DE','IE','NZ') THEN country WHEN country IS NULL THEN 'unknown' ELSE 'other' END AS geo
  FROM ev WHERE event_type = 1 ORDER BY visit_id, created_at)
SELECT 'visits', concat_ws(';', {D('landing','page_type')}, {D('layout','layout')}, {D('src','source')}, {D('geo','country')}), count(*), NULL::int
FROM v GROUP BY GROUPING SETS ((), (layout), (src), (src, layout), (geo), (landing))""",

  "events": BASE + f""",
p AS (
  SELECT ev.event_name,
    max(d.string_value) FILTER (WHERE d.data_key = 'page_type') pt,
    max(d.string_value) FILTER (WHERE d.data_key = 'component') comp,
    max(d.string_value) FILTER (WHERE d.data_key = 'layout') lay,
    max(d.string_value) FILTER (WHERE d.data_key = 'store') store,
    max(d.string_value) FILTER (WHERE d.data_key = 'opener') opener,
    max(d.string_value) FILTER (WHERE d.data_key = 'step') step
  FROM ev JOIN event_data d ON d.website_event_id = ev.event_id
  WHERE ev.event_type = 2 GROUP BY ev.event_id, ev.event_name)
SELECT CASE event_name WHEN 'spot_seen' THEN 'seen' WHEN 'shop_click' THEN 'clicks' ELSE event_name END,
       concat_ws(';', {D('pt','page_type')}, {D('comp','component')}, {D('lay','layout')}, {D('store','store')},
                 {D('opener','opener')}, {D('step','step')}), count(*), NULL::int
FROM p GROUP BY event_name, GROUPING SETS ((), (lay), (comp), (comp, lay), (pt, comp, lay), (pt), (store), (opener), (step))""",

  "vitals": BASE + f""",
x AS (
  SELECT coalesce(m.page_type, 'unknown') pt, ev.layout, k.name, k.val
  FROM ev LEFT JOIN rsg.page_type_map m ON m.url_path = ev.url_path
  CROSS JOIN LATERAL (VALUES ('lcp', ev.lcp), ('inp', ev.inp), ('cls', ev.cls), ('ttfb', nullif(ev.ttfb, 0))) k(name, val)
  WHERE ev.event_type = 5 AND k.val IS NOT NULL)
SELECT name || '_p75', concat_ws(';', {D('pt','page_type')}, {D('layout','layout')}),
       percentile_cont(0.75) WITHIN GROUP (ORDER BY val), count(*)::int
FROM x GROUP BY name, GROUPING SETS ((layout), (pt, layout))""",
}

OTHER_UMAMI = """
SELECT 'sessions', 'quality=' || q.quality, count(*), NULL::int
FROM session s JOIN rsg.session_quality q USING (session_id)
WHERE s.website_id = %(w)s AND s.created_at >= %(d)s::date AND s.created_at < %(d)s::date + 1 GROUP BY q.quality
UNION ALL
SELECT v.m, '', v.c, NULL::int FROM (
  SELECT count(*) c, count(DISTINCT e.issue_id) i FROM error_event e
  LEFT JOIN rsg.session_quality q ON q.session_id = e.session_id
  WHERE e.website_id = %(w)s AND e.created_at >= %(d)s::date AND e.created_at < %(d)s::date + 1
    AND coalesce(q.quality, 'human') <> 'bot') t
CROSS JOIN LATERAL (VALUES ('js_errors', t.c), ('js_error_issues', t.i)) v(m, c)
"""

def umami_day(cur, day):
    rows = []
    for sql in list(UMAMI_QUERIES.values()) + [OTHER_UMAMI]:
        cur.execute(sql, {"w": SITE, "d": day})
        rows += [r for r in cur.fetchall() if not any(k in r[1] for k in ("store=none", "opener=none", "step=none"))]
    # derived rates: click-through per seen spot (same segment), bot share
    by = {(m, d): v for m, d, v, n in rows}
    for (m, d), v in list(by.items()):
        if m == "seen" and v > 0 and "store=" not in d:
            rows.append(("ctr", d, by.get(("clicks", d), 0) / v, int(v)))
    q = {d.split("=")[1]: v for (m, d), v in by.items() if m == "sessions"}
    if sum(q.values()):
        rows.append(("bot_share", "", q.get("bot", 0) / sum(q.values()), int(sum(q.values()))))
    return rows

# ---------------------------------------------------------------- 4. Search Console
GSC_SQL = """
WITH c AS (
  SELECT day, lower(device) dev, clicks, impressions, position,
         CASE WHEN country IN ('usa','gbr','aus','can','deu','irl','nzl')
              THEN upper(CASE country WHEN 'usa' THEN 'us' WHEN 'gbr' THEN 'gb' WHEN 'aus' THEN 'au' WHEN 'can' THEN 'ca'
                                      WHEN 'deu' THEN 'de' WHEN 'irl' THEN 'ie' ELSE 'nz' END) ELSE 'other' END geo
  FROM rsg.gsc_country_day),
a AS (
  SELECT day, concat_ws(';', CASE WHEN GROUPING(dev) = 0 THEN 'device=' || dev END,
                             CASE WHEN GROUPING(geo) = 0 THEN 'country=' || geo END) dims,
         sum(clicks) cl, sum(impressions) im, sum(position * impressions) / nullif(sum(impressions), 0) pos
  FROM c GROUP BY day, GROUPING SETS ((), (dev), (geo))),
p AS (
  SELECT g.day, 'page_type=' || coalesce(m.page_type, rsg.guess_page_type(regexp_replace(g.page, '^https?://[^/]+', ''))) dims,
         sum(clicks) cl, sum(impressions) im,
         sum(position * impressions) / nullif(sum(impressions), 0) pos
  FROM rsg.gsc_page_day g LEFT JOIN rsg.page_type_map m ON m.url_path = regexp_replace(g.page, '^https?://[^/]+', '')
  GROUP BY 1, 2)
SELECT day, metric, dims, val, n FROM (SELECT * FROM a UNION ALL SELECT * FROM p) u,
  LATERAL (VALUES ('clicks', cl::numeric, NULL::int), ('impressions', im::numeric, NULL::int),
                  ('position', pos::numeric, im::int), ('ctr', cl::numeric / nullif(im, 0), im::int)) v(metric, val, n)
WHERE val IS NOT NULL
"""

# ---------------------------------------------------------------- 5. flags
LOWER_IS_BETTER = {"lcp_p75", "inp_p75", "cls_p75", "ttfb_p75", "js_errors", "js_error_issues", "position", "bot_share"}
RATES = {"ctr", "bot_share"}
LEVELS = {"lcp_p75", "inp_p75", "cls_p75", "ttfb_p75", "position"}
CWV_LIMITS = {"lcp_p75": (2500, 4000), "inp_p75": (200, 500), "cls_p75": (0.1, 0.25)}

def judge(metric, win, x, refs, nx, nrefs):
    """refs: list of reference values (counts/levels) or (num, den) for rates. Returns (expected, change, z, sev, basis) or None."""
    if metric in RATES:
        num = sum(a for a, b in refs); den = sum(b for a, b in refs)
        if den < (300 if win == "1d" else 600) or nx < (100 if win == "1d" else 300):
            return None
        p0 = num / den
        if p0 <= 0 or p0 >= 1:
            return None
        se = math.sqrt(p0 * (1 - p0) * (1 / nx + 1 / den))
        z = (x - p0) / se; ch = x / p0 - 1
        if abs(z) >= 3 and abs(ch) >= 0.25:
            return p0, ch, z, "strong" if abs(z) >= 6 and abs(ch) >= 0.5 else "notable"
        return None
    if metric in LEVELS:
        e = statistics.median(refs)
        if metric == "position":
            ch = x - e
            if (nx or 0) >= 100 and abs(ch) >= (2.0 if win == "1d" else 1.0):
                return e, ch, None, "notable"
            return None
        if (nx or 0) < 30 or e <= 0:
            return None
        ch = x / e - 1
        lim = CWV_LIMITS.get(metric)
        crossed = lim and any((e <= l) != (x <= l) for l in lim)
        if abs(ch) >= 0.25 and (crossed or abs(ch) >= 0.5):
            return e, ch, None, "strong" if crossed else "notable"
        return None
    # counts: Poisson noise plus the spread seen in the reference periods
    e = statistics.mean(refs)
    var = e + (statistics.variance(refs) if len(refs) >= 3 else 0)
    z = (x - e) / max(math.sqrt(var), 1)
    ch = x / e - 1 if e > 0 else None
    if win == "1d" and e >= 10 and x == 0:
        return e, -1.0, z, "strong"          # expected traffic and nothing: usually a breakage
    if max(x, e) >= (20 if win == "1d" else 50) and abs(z) >= 3 and (ch is None or abs(ch) >= 0.25):
        return e, ch, z, "strong" if abs(z) >= 6 else "notable"
    return None

def sev_name(sev, good):
    """strong + bad = alert (look today), strong + good = win, otherwise notable."""
    return ("win" if good else "alert") if sev == "strong" else "notable"

def flags_for(cur, source, as_of, valid_days):
    """valid_days: days whose data exists (a missing count row on such a day = 0)."""
    since = as_of - dt.timedelta(days=36)
    cur.execute("SELECT day, metric, dims, value, n FROM rsg.metric_day WHERE source = %s AND day BETWEEN %s AND %s",
                (source, since, as_of))
    series = defaultdict(dict)
    for day, m, d, v, n in cur.fetchall():
        series[(m, d)][day] = (float(v), n)
    out = []
    for (m, d), pts in series.items():
        def val(day):
            if day in pts:
                v, n = pts[day]
                return (v * n, n) if m in RATES else (v, n)
            if day in valid_days and m not in RATES and m not in LEVELS:
                return (0.0, None)
            return None
        for win, span in (("1d", 1), ("7d", 7)):
            cur_days = [as_of - dt.timedelta(days=i) for i in range(span)]
            cur_vals = [val(x) for x in cur_days]
            if any(c is None for c in cur_vals):
                continue
            # reference blocks: same weekday (1d) / the 4 previous weeks (7d); 1d falls back to the previous 7 days
            blocks = [[as_of - dt.timedelta(days=7 * k + i) for i in range(span)] for k in range(1, 5)]
            basis = "same weekday, 4 weeks" if win == "1d" else "previous 4 weeks"
            refs = [[val(x) for x in b] for b in blocks]
            refs = [r for r in refs if all(c is not None for c in r)]
            if win == "1d" and len(refs) < 3:
                refs = [[val(as_of - dt.timedelta(days=i))] for i in range(1, 8)]
                refs = [r for r in refs if r[0] is not None]
                basis = "previous 7 days"
                if len(refs) < 5:
                    continue
            elif len(refs) < (3 if win == "1d" else 2):
                continue
            if m in RATES:
                x_num = sum(c[0] for c in cur_vals); x_den = sum(c[1] for c in cur_vals)
                if not x_den:
                    continue
                x, nx = x_num / x_den, x_den
                rv = [(sum(c[0] for c in r), sum(c[1] for c in r)) for r in refs]
            elif m in LEVELS:
                nx = sum(c[1] or 0 for c in cur_vals)
                x = sum(c[0] * (c[1] or 1) for c in cur_vals) / max(sum((c[1] or 1) for c in cur_vals), 1)
                rv = [sum(c[0] * (c[1] or 1) for c in r) / max(sum((c[1] or 1) for c in r), 1) for r in refs]
            else:
                x, nx = sum(c[0] for c in cur_vals), None
                rv = [sum(c[0] for c in r) for r in refs]
            res = judge(m, win, x, rv, nx, None)
            if res:
                e, ch, z, sev = res
                good = (x < e) if m in LOWER_IS_BETTER else (x > e)
                out.append((as_of, source, win, m, d, x, e, ch, z, nx, good, sev_name(sev, good), basis))
    return out

GSC_PAGE_FLAGS = """
-- page-level Search Console moves: last 7 days vs the 4 weeks before (weekly average), pages with real traffic
WITH w AS (
  SELECT regexp_replace(page, '^https?://[^/]+', '') path,
         sum(clicks) FILTER (WHERE day > %(d)s::date - 7) x,
         sum(clicks) FILTER (WHERE day <= %(d)s::date - 7) / 4.0 e,
         sum(impressions) FILTER (WHERE day > %(d)s::date - 7) ix
  FROM rsg.gsc_page_day WHERE day > %(d)s::date - 35 AND day <= %(d)s::date GROUP BY 1)
SELECT path, coalesce(x, 0), coalesce(e, 0), coalesce(ix, 0) FROM w WHERE greatest(coalesce(x, 0), coalesce(e, 0)) >= 15
"""

def gsc_page_flags(cur, as_of, valid_days):
    need = {as_of - dt.timedelta(days=i) for i in range(35)}
    if not need <= valid_days:
        return []
    cur.execute(GSC_PAGE_FLAGS, {"d": as_of})
    out = []
    for path, x, e, ix in cur.fetchall():
        x, e = float(x), float(e)
        z = (x - e) / max(math.sqrt(max(e, 1) * 1.5), 1)   # x1.5: day-to-day spread above Poisson
        ch = x / e - 1 if e > 0 else None
        if abs(z) >= 3 and (ch is None or abs(ch) >= 0.3):
            out.append((as_of, "gsc", "7d", "page_clicks", "page=" + path, x, e, ch, z, int(ix), x > e,
                        sev_name("strong" if abs(z) >= 6 else "notable", x > e), "previous 4 weeks, weekly average"))
    out.sort(key=lambda r: -abs(r[8]))
    return out[:30]

# ---------------------------------------------------------------- main
def main():
    t0 = time.time()
    today = dt.datetime.now(dt.timezone.utc).date()
    if len(sys.argv) == 3:
        d_from, d_to = dt.date.fromisoformat(sys.argv[1]), dt.date.fromisoformat(sys.argv[2])
    else:
        d_from, d_to = today - dt.timedelta(days=2), today - dt.timedelta(days=1)
    d_from = max(d_from, dt.date(2026, 11, 16))
    with psycopg.connect(DB) as conn, conn.cursor() as cur:
        cur.execute(SCORE_SQL, {"since": d_from - dt.timedelta(days=1), "v": RULES_V}); scored = cur.rowcount
        cur.execute(PAGE_MAP_SQL, {"w": SITE, "since": d_from - dt.timedelta(days=1)})
        conn.commit()
        n_rows = 0
        day = d_from
        while day <= d_to:
            rows = umami_day(cur, day)
            cur.execute("DELETE FROM rsg.metric_day WHERE source = 'umami' AND day = %s", (day,))
            cur.executemany("INSERT INTO rsg.metric_day VALUES (%s, 'umami', %s, %s, %s, %s)", [(day, *r) for r in rows])
            conn.commit(); n_rows += len(rows); day += dt.timedelta(days=1)
        cur.execute(GSC_SQL); g = cur.fetchall()
        cur.execute("DELETE FROM rsg.metric_day WHERE source = 'gsc'")
        cur.executemany("INSERT INTO rsg.metric_day VALUES (%s, 'gsc', %s, %s, %s, %s)", g)
        conn.commit(); n_rows += len(g)
        # flags: Umami as of the last built day; GSC as of the last loaded day
        cur.execute("SELECT DISTINCT day FROM rsg.metric_day WHERE source = 'umami' AND day >= %s", (UMAMI_FIRST_FULL_DAY,))
        u_days = {r[0] for r in cur.fetchall()}
        cur.execute("SELECT day FROM rsg.gsc_load_log"); g_days = {r[0] for r in cur.fetchall()}
        g_days = {x for x in g_days if x <= today - dt.timedelta(days=GSC_SETTLE_DAYS)}
        gsc_last = max(g_days) if g_days else None
        fl = []
        if d_to >= UMAMI_FIRST_FULL_DAY:
            fl += flags_for(cur, "umami", d_to, u_days)
        if gsc_last:
            fl += flags_for(cur, "gsc", gsc_last, g_days) + gsc_page_flags(cur, gsc_last, g_days)
        cur.execute("DELETE FROM rsg.flag WHERE (source = 'umami' AND as_of = %s) OR (source = 'gsc' AND as_of = %s)", (d_to, gsc_last))
        cur.executemany("INSERT INTO rsg.flag (as_of, source, win, metric, dims, value, expected, change, z, n, good, severity, basis)"
                        " VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s) ON CONFLICT DO NOTHING", fl)
        secs = round(time.time() - t0, 1)
        cur.execute("INSERT INTO rsg.metric_run (umami_from, umami_to, gsc_last_day, sessions_scored, metric_rows, flags, seconds)"
                    " VALUES (%s,%s,%s,%s,%s,%s,%s)", (d_from, d_to, gsc_last, scored, n_rows, len(fl), secs))
        conn.commit()
    print(dt.datetime.now().isoformat(timespec="seconds"), f"umami {d_from}..{d_to} gsc_last {gsc_last} scored {scored}"
          f" rows {n_rows} flags {len(fl)} {secs}s", flush=True)

if __name__ == "__main__":
    main()
