import json,re,html,csv,collections
rows=json.load(open('reviews.json'))
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
ROAD_MAX=[r'\bbondi\b',r'\bmore\b(?! trail)',r'skyward',r'ghost max',r'glycerin max',r'gaviota',r'paradigm',r'invincible',r'vomero (18|19|plus|premium)',r'magmax',r'hurricane 2[2-9]',r'gliderid?e max',r'cloudsurfer max',r'cloudeclipse',r'neo zen']
ROAD_BORDER=[r'nimbus',r'glycerin',r'triumph',r'1080',r'kayano',r'clifton',r'skyflow',r'adistar',r'cloudmonster',r'wave sky',r'vomero',r'endorphin azura',r'arahi',r'superblast',r'megablast',r'hyperion max']
TRAIL_MAX=[r'mafate',r'stinson',r'olympus',r'enduris',r'caldera',r'trabuco max',r'hierro',r'more trail',r'tecton x 3|ultra glide']
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
    if has(tl,*TRAIL_RACE): cat,why='Race','trail racer line (draft list)'; check='confirm line'
    elif has(tl,*TRAIL_MAX): cat,why='Max cushion','max-cushion trail line (draft list)'; check='confirm line'
    elif 'racing' in ty: cat,why='Race','old tag Racing'
    else: cat,why='Daily trainer','trail default'
  else:
    surf='Road'
    if 'racing' in ty or 'cielo road' in tl: cat,why='Race','old tag Racing'
    elif 'lightweight' in ty and 'cushioning' not in ty: cat,why='Speed trainer','old tag Lightweight'
    elif has(tl,*ROAD_MAX): cat,why='Max cushion','max-cushion line (draft list)'; check='confirm line'
    elif has(tl,*ROAD_BORDER):
      if heel and heel>=39 and camt=='Highly cushioned': cat,why='Max cushion',f'borderline line; heel {heel:g} mm + highly cushioned'
      elif heel and heel<35: cat,why='Daily trainer',f'borderline line; heel {heel:g} mm'
      else: cat,why='Daily trainer','borderline line (premium daily or max?)'
      check='decide line'
    elif heel:
      if heel>=39 and camt=='Highly cushioned': cat,why='Max cushion',f'heel {heel:g} mm + highly cushioned'
      elif heel<35: cat,why='Daily trainer',f'heel {heel:g} mm'
      else: cat,why='Daily trainer',f'heel {heel:g} mm, {camt or "no cushion field"}'
    else: cat,why='Daily trainer','default (no stack data)'
    if 'minimalist' in ty and cat=='Daily trainer': why+='; old Minimalist dropped'
  out.append(dict(id=r['id'],shoe=re.sub(r'\s*Review.*$','',t).strip(),published=r['date'],url=r['link'],
    old_tags=', '.join(sorted(x for x in ty)),heel_mm=r['heel'] or '',cushion_field=camt,
    line=re.sub(r'\s*\d+$','',LINE).title() if check else '',surface=surf,category=cat,stability=stab,reason=why,check=check))
out.sort(key=lambda o:(o['check']=='', o['surface'], o['category'], o['published']), reverse=False)
with open('category-mapping.csv','w',newline='') as f:
  w=csv.DictWriter(f,fieldnames=list(out[0].keys())); w.writeheader(); w.writerows(out)
print(len(out))
print(collections.Counter((o['surface'],o['category']) for o in out).most_common())
print('stability',sum(1 for o in out if o['stability']))
print(collections.Counter(o['check'] for o in out))
for o in out:
  if o['check'] and o['check']!='check: 35-38 mm zone' : pass
import itertools
print([ (o['shoe'],o['category']) for o in out if o['check']=='confirm line'][:80])
