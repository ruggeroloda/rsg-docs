select distinct channel from rsg.ga4_landing_day;
select day, sum(sessions) filter (where country='United States') us, sum(sessions) filter (where country='United Kingdom') uk, sum(sessions) filter (where country in ('Germany','France','Italy','Spain','Netherlands','Ireland','Sweden','Belgium')) eea, sum(sessions) filter (where country in ('Australia','Canada')) auca, sum(sessions) filter (where country in ('India','Indonesia','Malaysia','Singapore','Philippines')) asia,
 sum(sessions) filter (where device='mobile') mob, sum(sessions) filter (where device='desktop') desk
from rsg.ga4_site_day where channel='Organic Search' and day between '2024-01-29' and '2024-02-15' group by 1 order by 1;
select day, sum(sessions) filter (where country='United States') us, sum(sessions) filter (where country='United Kingdom') uk, sum(sessions) filter (where device='mobile') mob, sum(sessions) filter (where device='desktop') desk
from rsg.ga4_site_day where channel='Direct' and day between '2024-01-29' and '2024-02-15' group by 1 order by 1;
