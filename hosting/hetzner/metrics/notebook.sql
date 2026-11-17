-- Analyst notebook (schema rsg, database umami) — 16 Nov 2026. Idempotent. Shared by the analyst (rsgmedia), Claude in the
-- RSG project and Ruggero (through Claude). Data tables stay read-only for rsg_analyst; these tables it may insert/update.

-- What changed, when: deploys (written by deploy.sh), site/SEO/ads/hosting/DFS/tracking changes, Google updates, seasons.
-- planned = announced but not confirmed done (the analyst/Claude sets planned = false + the real time when it happens).
CREATE TABLE IF NOT EXISTS rsg.change_log (
  id         serial PRIMARY KEY,
  at         timestamptz NOT NULL,          -- when it took effect (UTC)
  until_at   timestamptz,                   -- end, for periods (Black Friday, a Google update rollout)
  kind       text NOT NULL CHECK (kind IN ('deploy','content','seo','ads','hosting','dfs','tracking','email','external','season')),
  title      text NOT NULL,
  details    text,
  ref        text,                          -- git tag/commit, ticket, doc
  expect     text,                          -- what it should move (helps the analyst attribute)
  planned    boolean NOT NULL DEFAULT false,
  source     text NOT NULL DEFAULT 'claude', -- deploy.sh | claude | analyst | ruggero
  created_at timestamptz NOT NULL DEFAULT now());
CREATE INDEX IF NOT EXISTS change_log_at ON rsg.change_log (at);

-- Things worth attention, and what happened to them. key = what makes it the same finding again (e.g. 'clicks|component=sticky_bar;layout=phone'),
-- so the analyst updates last_seen/seen_count instead of raising it again; snooze_until silences it.
CREATE TABLE IF NOT EXISTS rsg.finding (
  id           serial PRIMARY KEY,
  created_at   timestamptz NOT NULL DEFAULT now(),
  updated_at   timestamptz NOT NULL DEFAULT now(),
  created_by   text NOT NULL DEFAULT 'analyst',
  status       text NOT NULL DEFAULT 'open'
               CHECK (status IN ('open','investigating','proposed','accepted','rejected','monitoring','done','dismissed')),
  kind         text NOT NULL CHECK (kind IN ('alert','win','trend','question','idea')),
  priority     smallint NOT NULL DEFAULT 2 CHECK (priority BETWEEN 1 AND 3),   -- 1 = act now
  title        text NOT NULL,
  summary      text,                 -- what is happening, in plain words
  evidence     text,                 -- numbers + the queries behind them
  causes       text,                 -- likely causes, ranked, with the change_log ids checked
  proposal     text,                 -- what to do / which experiment
  key          text,
  first_seen   date,
  last_seen    date,
  seen_count   int NOT NULL DEFAULT 1,
  snooze_until date,
  change_ids   int[]);
CREATE UNIQUE INDEX IF NOT EXISTS finding_open_key ON rsg.finding (key)
  WHERE key IS NOT NULL AND status NOT IN ('done','dismissed','rejected');

-- Discussion trail on a finding (who said what, when): Ruggero's answers go here through Claude.
CREATE TABLE IF NOT EXISTS rsg.finding_note (
  id         serial PRIMARY KEY,
  finding_id int NOT NULL REFERENCES rsg.finding(id),
  at         timestamptz NOT NULL DEFAULT now(),
  author     text NOT NULL CHECK (author IN ('analyst','claude','ruggero')),
  body       text NOT NULL);

-- Experiments: designed to fit RSG's traffic (how long it must run, what counts as a win).
CREATE TABLE IF NOT EXISTS rsg.experiment (
  id           serial PRIMARY KEY,
  finding_id   int REFERENCES rsg.finding(id),
  created_at   timestamptz NOT NULL DEFAULT now(),
  updated_at   timestamptz NOT NULL DEFAULT now(),
  status       text NOT NULL DEFAULT 'proposed' CHECK (status IN ('proposed','approved','running','done','cancelled')),
  title        text NOT NULL,
  hypothesis   text NOT NULL,
  change       text NOT NULL,        -- what we change (and where: staging first)
  metric       text NOT NULL,        -- primary metric, as metric_day metric + dims or a query
  success_rule text NOT NULL,        -- e.g. '+20 % ctr, z >= 2, after >= 300 seen per arm'
  min_days     int,
  started      date,
  ended        date,
  result       text,
  decision     text);

-- Every report the analyst sends (body in Markdown), so Claude can read and discuss them.
CREATE TABLE IF NOT EXISTS rsg.report (
  id           serial PRIMARY KEY,
  kind         text NOT NULL CHECK (kind IN ('daily','weekly','monthly','adhoc')),
  period_start date NOT NULL,
  period_end   date NOT NULL,
  created_at   timestamptz NOT NULL DEFAULT now(),
  subject      text NOT NULL,
  body         text NOT NULL,
  finding_ids  int[],
  emailed_at   timestamptz);

CREATE OR REPLACE FUNCTION rsg.touch_updated_at() RETURNS trigger LANGUAGE plpgsql AS
$$ BEGIN NEW.updated_at = now(); RETURN NEW; END $$;
DROP TRIGGER IF EXISTS finding_touch ON rsg.finding;
CREATE TRIGGER finding_touch BEFORE UPDATE ON rsg.finding FOR EACH ROW EXECUTE FUNCTION rsg.touch_updated_at();
DROP TRIGGER IF EXISTS experiment_touch ON rsg.experiment;
CREATE TRIGGER experiment_touch BEFORE UPDATE ON rsg.experiment FOR EACH ROW EXECUTE FUNCTION rsg.touch_updated_at();

-- Access: rsg_analyst reads everything (as before) and writes only the notebook (no DELETE anywhere).
GRANT SELECT ON ALL TABLES IN SCHEMA rsg TO rsg_analyst;
GRANT INSERT, UPDATE ON rsg.change_log, rsg.finding, rsg.finding_note, rsg.experiment, rsg.report TO rsg_analyst;
GRANT USAGE ON ALL SEQUENCES IN SCHEMA rsg TO rsg_analyst;
