-- articles published 2022-2024: channel mix in first 14 days
select page, channel, min(day) d0, sum(sessions) s from rsg.ga4_landing_day where page ~ '^/(2023|2024)/[0-9]{2}/' and page !~ 'review' group by 1,2 having sum(sessions)>300 order by 1, s desc limit 80;
