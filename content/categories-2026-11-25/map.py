import json,re,html,csv,collections
rows=json.load(open(__import__('os').path.expanduser('~/catmap/reviews.json')))
def num(x):
  try: return float(x)
  except: return None
def has(t,*p):
  global LINE
  for x in p:
    m=re.search(x,t)
    if m: LINE=m.group(0); return True
  return False
LINE=''
# draft line lists (Ruggero confirms)
ROAD_MAX=[r'\bbondi\b',r'\bmore\b(?! trail)',r'skyward',r'ghost max',r'glycerin max',r'gaviota',r'paradigm',r'invincible',r'vomero (1[89]|2[0-9]|plus|premium)',r'magmax',r'hurricane 2[2-9]',r'gliderid?e max',r'cloudsurfer max',r'cloudeclipse',r'neo zen',r'max ?road',r'balos']
RACE_NAMES=[r'adios pro',r'endorphin pro',r'carbon x']
PERF=[r'superblast',r'megablast',r'hyperion max',r'hyperion tempo',r'mach x',r'\bmach 4\b',r'boston',r'supercomp trainer',r'deviate nitro',r'tempo next',r'evo sl',r'endorphin trainer',r'neo vista',r'switch fwd',r'sonicblast',r'aero burst',r'fresh foam x tempo']
ROAD_BORDER=[r'nimbus',r'glycerin',r'triumph',r'1080',r'kayano',r'clifton',r'skyflow',r'adistar',r'cloudmonster',r'wave sky',r'vomero',r'endorphin azura',r'arahi']
TRAIL_MAX=[r'mafate',r'stinson',r'olympus',r'enduris',r'trabuco max',r'more trail',r'zzzz']
TRAIL_RACE=[r's[-/ ]?lab',r'tecton',r'vectiv pro',r'\bzinal\b',r'pulsar',r'ultrafly',r'sense pro',r'vaporfly trail|ultrafly']
out=[]
for r in rows:
  t=html.unescape(r['title']); tl=t.lower(); ty=set(r['types']); heel=num(r['heel']); camt=r['camt'] or ''
  stab='Stability' if ('stability' in ty or 'motion-control' in ty) else ''
  check=''; LINE=''
  global_line=None
  
  if 'training' in ty or r['kind']=='reviews_training':
    surf,cat,why='Gym','','training type'
  elif 'trail' in ty or r['kind']=='reviews_trail':
    surf='Trail'
    if has(tl,*TRAIL_RACE): cat,why='Race','trail racer line (draft list)'; check=''
    elif has(tl,*TRAIL_MAX): cat,why='Max cushion','max-cushion trail line (draft list)'; check=''
    elif 'racing' in ty: cat,why='Race','old tag Racing'
    else: cat,why='Daily trainer','trail default'
  else:
    surf='Road'
    if 'racing' in ty or 'cielo road' in tl: cat,why='Race','old tag Racing'
    elif has(tl,*RACE_NAMES): cat,why='Race','race shoe with a wrong old tag (Ruggero 25 Nov)'
    elif has(tl,*PERF): cat,why='Performance trainer','tempo/super trainer by name (Ruggero 25 Nov)'
    elif 'lightweight' in ty and 'cushioning' not in ty: cat,why='Performance trainer','old tag Lightweight'
    elif has(tl,*ROAD_MAX): cat,why='Max cushion','max-cushion line (draft list)'; check=''
    elif has(tl,*PERF): cat,why='Performance trainer','super trainer line (Ruggero 25 Nov)'
    elif has(tl,*ROAD_BORDER): cat,why='Daily trainer','premium daily line, narrow max-cushion rule (Ruggero 25 Nov)'
    else: cat,why='Daily trainer','default'
    if 'minimalist' in ty and cat=='Daily trainer': why+='; old Minimalist dropped'
  out.append(dict(id=r['id'],shoe=re.sub(r'\s*Review.*$','',t).strip(),published=r['date'],url=r['link'],
    old_tags=', '.join(sorted(x for x in ty)),heel_mm=r['heel'] or '',cushion_field=camt,
    line=re.sub(r'\s*\d+$','',LINE).title() if check else '',surface=surf,category=cat,stability=stab,reason=why,check=check))
out.sort(key=lambda o:(o['check']=='', o['surface'], o['category'], o['published']), reverse=False)
with open(__import__('os').path.expanduser('~/mnt/rsg/content/categories-2026-11-25/category-mapping.csv'),'w',newline='') as f:
  w=csv.DictWriter(f,fieldnames=list(out[0].keys())); w.writeheader(); w.writerows(out)
print(len(out))
print(collections.Counter((o['surface'],o['category']) for o in out).most_common())
print('stability',sum(1 for o in out if o['stability']))
print(collections.Counter(o['check'] for o in out))
for o in out:
  if o['check'] and o['check']!='check: 35-38 mm zone' : pass
import itertools
print([ (o['shoe'],o['category']) for o in out if o['check']=='confirm line'][:80])
