select day, to_char(day,'Dy') d, sum(sessions) filter (where channel='Organic Search') org, sum(sessions) filter (where channel='Direct') direct,
 sum(sessions) filter (where channel='Organic Search' and country='United States') us, sum(sessions) filter (where channel='Organic Search' and country='United Kingdom') uk,
 sum(sessions) filter (where channel='Organic Search' and country in ('Australia','Canada')) auca, sum(sessions) filter (where channel='Organic Search' and country not in ('Australia','Canada','United States','United Kingdom')) row,
 sum(sessions) filter (where channel='Organic Search' and device='mobile') mob, sum(sessions) filter (where channel='Organic Search' and device='desktop') desk
from rsg.ga4_site_day where day between '2024-04-29' and '2024-05-19' group by 1 order by 1;
select day, to_char(day,'Dy') d, sum(clicks) c, sum(impressions) i, round((sum(position*impressions)/sum(impressions))::numeric,1) pos from rsg.gsc_page_day where day between '2025-11-17' and '2025-12-20' group by 1 order by 1;
select day, to_char(day,'Dy') d, sum(clicks) c, sum(impressions) i, round((sum(position*impressions)/sum(impressions))::numeric,1) pos from rsg.gsc_page_day where day between '2026-05-11' and '2026-06-07' group by 1 order by 1;
