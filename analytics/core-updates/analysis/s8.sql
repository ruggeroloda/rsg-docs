with a as (select page, sum(clicks)/7.0 c, sum(impressions)/7.0 i, sum(position*impressions)/nullif(sum(impressions),0) p from rsg.gsc_page_day where day between '2025-11-17' and '2025-11-23' group by 1),
b as (select page, sum(clicks)/7.0 c, sum(impressions)/7.0 i, sum(position*impressions)/nullif(sum(impressions),0) p from rsg.gsc_page_day where day between '2025-12-03' and '2025-12-09' group by 1)
select replace(coalesce(a.page,b.page),'https://www.runningshoesguru.com','') page, round(a.c) c0, round(b.c) c1, round(a.i) i0, round(b.i) i1, round(a.p::numeric,1) p0, round(b.p::numeric,1) p1
from a full join b using(page) order by coalesce(a.i,0)-coalesce(b.i,0) desc limit 25;
with a as (select page, sum(clicks)/7.0 c, sum(impressions)/7.0 i from rsg.gsc_page_day where day between '2025-11-17' and '2025-11-23' group by 1),
b as (select page, sum(clicks)/7.0 c, sum(impressions)/7.0 i from rsg.gsc_page_day where day between '2025-12-03' and '2025-12-09' group by 1)
select count(*) filter (where a.i>=50) pages_50imp, count(*) filter (where a.i>=50 and coalesce(b.i,0)<a.i*0.5) lost_half_imp, count(*) filter (where a.i>=50 and coalesce(b.i,0)<a.i*0.1) lost_90, count(*) filter (where a.i>=50 and coalesce(b.i,0)>a.i) gained,
 round(sum(a.i)) i0, round(sum(b.i)) i1, round(sum(a.c)) c0, round(sum(b.c)) c1 from a full join b using(page);
with a as (select query, sum(clicks)/7.0 c, sum(impressions)/7.0 i, sum(position*impressions)/nullif(sum(impressions),0) p from rsg.gsc_query_day where day between '2025-11-17' and '2025-11-23' group by 1),
b as (select query, sum(clicks)/7.0 c, sum(impressions)/7.0 i, sum(position*impressions)/nullif(sum(impressions),0) p from rsg.gsc_query_day where day between '2025-12-03' and '2025-12-09' group by 1)
select coalesce(a.query,b.query) q, round(a.c,1) c0, round(b.c,1) c1, round(a.i) i0, round(b.i) i1, round(a.p::numeric,1) p0, round(b.p::numeric,1) p1 from a full join b using(query) order by coalesce(a.i,0)-coalesce(b.i,0) desc limit 30;
