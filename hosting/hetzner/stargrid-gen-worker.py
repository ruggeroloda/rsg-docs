#!/usr/bin/env python3
"""Stargrid generation worker (host side). Replaces the Docker socket in stargrid-api.
The API inserts rows into generate_jobs; this service runs the whitelisted generator
container and writes progress/output back. Nothing from the row is ever executed as-is:
mode is looked up in SCRIPTS, date must match YYYY-MM-DD."""
import subprocess, threading, time, re, fcntl, sys

SCRIPTS = {
    'crossword_sparse': ['sparse_crossword.py'],
    'crossword_dense': ['dense_crossword.py'],
    'connection_grid': ['autopilot.py', 'one'],
    'connection_grid_legacy': ['connection_grid.py'],
}
AUTOPILOT_LOCK = '/home/ruggero/autopilot/lock'
JOB_TIMEOUT = 2 * 3600


def psql(sql, **v):
    cmd = ['docker', 'exec', '-i', 'postgres', 'psql', '-U', 'admin', '-d', 'stargrid',
           '-At', '-q', '-X', '-v', 'ON_ERROR_STOP=1']
    for k, val in v.items():
        cmd += ['-v', f'{k}={val}']
    r = subprocess.run(cmd, input=sql, capture_output=True, text=True, timeout=60)
    if r.returncode != 0:
        raise RuntimeError(r.stderr.strip()[:300])
    return r.stdout


def append(jid, text):
    if text:
        psql("UPDATE generate_jobs SET output = output || :'c' WHERE id = :id;", c=text, id=jid)


def finish(jid, status, code, tail=''):
    psql("UPDATE generate_jobs SET status = :'s', exit_code = NULLIF(:'x','')::int, "
         "finished_at = now(), output = output || :'t' WHERE id = :id;",
         s=status, x='' if code is None else str(code), t=tail, id=jid)


def run_job(jid, mode, date, force):
    spec = SCRIPTS.get(mode)
    if not spec or not re.fullmatch(r'\d{4}-\d{2}-\d{2}', date):
        return finish(jid, 'error', None, 'ERROR: invalid job\n')
    lockf = None
    if spec[0] == 'autopilot.py':
        lockf = open(AUTOPILOT_LOCK, 'a')
        try:
            fcntl.flock(lockf, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except OSError:
            return finish(jid, 'error', None, 'ERROR: autopilot is running right now, try again later\n')
    extra = ['--force'] if (mode == 'connection_grid' and force) else []
    name = f'sg-gen-{jid}'
    cmd = ['docker', 'run', '--rm', '--name', name, '--network', 'internal',
           '-v', '/home/ruggero/stargrid-generators:/gen', '-v', '/home/ruggero/crossword:/crossword:ro',
           '-e', 'ENABLE_PATH=/crossword/enable.txt', '-e', 'FREQ_PATH=/crossword/enable_freq.txt',
           'stargrid-generator', 'python3', '/gen/' + spec[0], *spec[1:], '--date', date, *extra]
    buf, lock = [], threading.Lock()
    p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, text=True, bufsize=1)

    def reader():
        for line in p.stdout:
            with lock:
                buf.append(line)
    t = threading.Thread(target=reader, daemon=True)
    t.start()
    start = time.time()
    while p.poll() is None:
        time.sleep(2)
        with lock:
            chunk, buf[:] = ''.join(buf), []
        append(jid, chunk[:20000])
        if time.time() - start > JOB_TIMEOUT:
            subprocess.run(['docker', 'kill', name], capture_output=True)
            t.join(5)
            return finish(jid, 'error', None, '\nERROR: timed out\n')
    t.join(5)
    with lock:
        chunk = ''.join(buf)
    code = p.returncode
    if code != 0:
        chunk += f'\nERROR: Container exited with code {code}\n'
    finish(jid, 'done' if code == 0 else 'error', code, chunk[:20000])
    if lockf:
        lockf.close()


def main():
    psql("UPDATE generate_jobs SET status='error', finished_at=now(), "
         "output = output || E'\\nERROR: worker restarted\\n' WHERE status='running';")
    print('worker ready', flush=True)
    while True:
        try:
            out = psql("UPDATE generate_jobs SET status='running', started_at=now() WHERE id = "
                       "(SELECT id FROM generate_jobs WHERE status='pending' ORDER BY id LIMIT 1 "
                       "FOR UPDATE SKIP LOCKED) RETURNING id||'|'||mode||'|'||puzzle_date||'|'||force;")
            row = next((l for l in out.splitlines() if '|' in l), None)
            if row:
                jid, mode, date, force = row.split('|')
                print(f'job {jid} {mode} {date}', flush=True)
                run_job(int(jid), mode, date, force == 't')
                continue
        except Exception as e:
            print('error:', e, file=sys.stderr, flush=True)
        time.sleep(2)


if __name__ == '__main__':
    main()
