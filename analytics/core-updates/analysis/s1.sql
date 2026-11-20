\d rsg.ga4_landing_day
\d rsg.ga4_site_day
\d rsg.gsc_page_day
\d rsg.gsc_query_day
\d rsg.change_log
select min(day),max(day) from rsg.ga4_landing_day;
select * from rsg.change_log order by 1 limit 5;
