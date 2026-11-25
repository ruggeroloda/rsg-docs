"""Second pass over category-mapping.csv after the Fable review (25 Nov 2026).
Rule: one category per line (shoe family), with named version breaks.
Reads category-mapping.csv (unchanged), writes category-mapping-v2.csv.
Run from this folder: python3 -I map2.py
Each rule: (regex on the lower-case shoe name, category, note). First match wins. Road reviews only."""
import csv, re, collections, json, os, html
D='Daily trainer'; P='Performance trainer'; R='Race'; M='Max cushion'
LINES=[
 # racers first (so 'adios pro' is caught before 'adios', 'deviate nitro elite' before 'deviate nitro')
 (r'adios pro|endorphin (pro|elite)|deviate nitro elite|supercomp (elite|pacer)|hyperion elite|rebellion pro|rc elite',R,'race line'),
 (r'\badios\b',R,'Adios: race line, all versions'),
 (r'hyper ?speed',R,'Hyper Speed: race line, all versions'),
 (r'wave (ronin|musha)',R,'racing flat line'),
 (r'fastwitch',R,'racing flat line'),
 (r'brooks hyperion$',R,'the 2016 Hyperion was a racing flat (version break)'),
 # performance trainers
 (r'superblast|megablast|sonicblast',P,'super trainer line'),
 (r'prime x|fuelcell tc\b|supercomp trainer',P,'super trainer line (was Race by old tag)'),
 (r'endorphin (speed|trainer)|kinvara pro',P,'tempo / super trainer line'),
 (r'boston',P,'Boston: all versions'),
 (r'deviate nitro',P,'Deviate Nitro (not Elite): all versions'),
 (r'zoom fly',P,'Zoom Fly: all versions'),
 (r'magic speed',P,'Magic Speed: plated trainer, all versions (judgment)'),
 (r'pegasus (35 )?turbo|pegasus plus',P,'Pegasus Turbo / Plus'),
 (r'noosa',P,'Noosa: tempo line, all versions'),
 (r'hoka.*\bmach\b|\bmach (x|\d)',P,'Mach: all versions'),
 (r'hyperion',P,'Hyperion 2+, Tempo, Max, GTS'),
 (r'cloudmonster hyper',P,"On's own label: super trainer"),
 (r'kinvara',P,'Kinvara: light fast trainer, all versions (judgment)'),
 (r'fuelcell rebel',P,'Rebel: all versions (judgment)'),
 (r'zante',P,'Zante: light fast trainer, all versions (judgment)'),
 (r'cloudflow',P,'Cloudflow: all versions (judgment)'),
 (r'ds[- ]trainer',P,'DS Trainer: light stability tempo shoe, all versions'),
 (r'zoom elite|airzoom elite',P,'Zoom Elite: light tempo trainer, all versions (judgment)'),
 (r'razor',P,'Razor: tempo line, all versions (judgment)'),
 (r'sayonara',P,'Sayonara: all versions'),
 (r'wave shadow',P,'Wave Shadow: all versions (judgment)'),
 (r'cyclone',P,'Cyclone: all versions'),
 (r'newton distance',P,'Newton Distance: all versions'),
 (r'lyte 33|33[- ]dfa',P,'one line, one category'),
 (r'salming speed',P,'one line, one category'),
 (r'clayton|floatride energy x',P,'light / plated trainer, not a racer (judgment)'),
 # max cushion version break
 (r'hurricane 2[4-9]',M,'Hurricane is a max-stack shoe from version 24'),
 (r'hurricane',D,'Hurricane before 24: premium stability daily trainer'),
 # daily trainers
 (r'clifton',D,'Clifton: all versions'),
 (r'wave rider|wave inspire|wave paradox',D,'daily line; old Lightweight tag meant weight'),
 (r'saucony ride\b|ravenna|arahi|vongo|lunarglide|new balance 870|pure ?cadence|vazee|liberty iso',D,'daily / stability line; old Lightweight tag meant weight'),
 (r'rincon',D,'Rincon: light daily trainer, all versions (judgment)'),
 (r'brooks launch',D,'Launch: all versions (judgment)'),
 (r'propel|evoride|gorun ride|go ?run \d|speedform|brooks neuro|freedom iso|enroute|newton gravity|escalante( \d|$)',D,'one line, one category'),
 (r'adidas sl ?20|adizero sl\b',D,'SL line: all versions (judgment)'),
 (r'\bfree\b',D,'Nike Free: old Minimalist, Daily trainer'),
]
STAB_OFF=[r'magic speed']
rows=list(csv.DictReader(open('category-mapping.csv')))
changed=0
for x in rows:
    x['v2_note']=''; n=x['shoe'].lower(); old=(x['surface'],x['category'],x['stability'])
    if x['surface']=='Road':
        hit=False
        for pat,cat,note in LINES:
            if re.search(pat,n):
                x['category']=cat; x['v2_note']=note; hit=True; break
        if not hit and 'minimalist' in x['old_tags'] and x['category']==P:
            x['category']=D; x['v2_note']='old Minimalist: Daily trainer (was Performance by the Lightweight tag)'
        if re.search(r'puregrit',n): x['surface']='Trail'; x['category']=D; x['v2_note']='trail shoe filed under road'
        if any(re.search(p,n) for p in STAB_OFF): x['stability']=''
    elif x['surface']=='Trail':
        if re.search(r'sense pro max',n): x['category']=D; x['v2_note']='cushioned model, not the racer'
        elif re.search(r'sense (\d )?pro|flight vectiv|catamount',n): x['category']=R; x['v2_note']='trail race line (pattern widened; Catamount is judgment)'
        elif re.search(r'wave ascend',n): x['category']=D; x['v2_note']='not a trail racer (old Racing tag)'
    if (x['surface'],x['category'],x['stability'])!=old: changed+=1; x['v2_note']='CHANGED: '+x['v2_note']
# the 6 gym reviews the first pull missed (post type reviews_training)
gp=os.path.expanduser('~/fr/gym.json')
if os.path.exists(gp):
    have={r['url'] for r in rows}
    for g in json.load(open(gp)):
        if g['link'] not in have:
            rows.append(dict(id=g['id'],shoe=re.sub(r'\s*Review.*$','',html.unescape(g['title']['rendered'])).strip(),published=g['date'][:10],url=g['link'],old_tags='training',heel_mm='',cushion_field='',line='',surface='Gym',category='',stability='',reason='training type',check='',v2_note='ADDED: missing from the first pull'))
with open('category-mapping-v2.csv','w',newline='') as f:
    w=csv.DictWriter(f,fieldnames=list(rows[0].keys())); w.writeheader(); w.writerows(rows)
print(len(rows),'rows;',changed,'changed')
print(collections.Counter((r['surface'],r['category']) for r in rows).most_common())
print('stability',sum(1 for r in rows if r['stability']))
