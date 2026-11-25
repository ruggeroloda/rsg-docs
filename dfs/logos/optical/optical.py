from PIL import Image, ImageDraw
import numpy as np
NAMES=['nike','zappos','amazon','running-warehouse','hoka','puma','running-warehouse-us','running-warehouse-au','adidas','brooks','mizuno','new-balance','running-room','pace-athletic']
REF=['nike','zappos','amazon','running-warehouse']   # ink reference = the first set (8 Nov), so adding logos never changes the existing ones
CW, CH = 240, 72          # identical canvas for every logo
AREA = 0.40 * CW * CH     # target bounding-box area (fraction of the canvas)
def load(f):
    im = Image.open(f+'.png').convert('RGBA'); a = np.array(im).astype(float)/255
    lum = 0.2126*a[:,:,0]+0.7152*a[:,:,1]+0.0722*a[:,:,2]
    alpha = a[:,:,3] * np.clip((0.97-lum)/0.12, 0, 1)        # white background -> transparent
    out = (np.dstack([a[:,:,:3], alpha])*255).astype('uint8'); img = Image.fromarray(out, 'RGBA')
    ys, xs = np.where(alpha > 0.08); box = (xs.min(), ys.min(), xs.max()+1, ys.max()+1)
    img = img.crop(box); ink = np.array(img)[:,:,3].mean()/255
    return img, ink
data = {f: load(f) for f in NAMES}
ref = np.median([data[f][1] for f in REF])
for f,(img,ink) in data.items():
    w,h = img.size; r = w/h
    s = (AREA/(w*h))**0.5 * (ref/ink)**0.18         # equal area, then lighter logos a bit larger / dense ones smaller
    nw, nh = w*s, h*s
    k = min(1, (CW-4)/nw, (CH-4)/nh); nw, nh = int(round(nw*k)), int(round(nh*k))
    c = Image.new('RGBA', (CW, CH), (0,0,0,0)); c.paste(img.resize((nw,nh), Image.LANCZOS), ((CW-nw)//2, (CH-nh)//2), img.resize((nw,nh), Image.LANCZOS))
    c.save('opt-'+f+'.png'); print(f, 'ink %.2f'%ink, 'ratio %.2f'%r, '->', (nw,nh))
# preview: current (contain in 110x32, left) vs optical (contain in 110x33, centred), as in the box
def row(files, slot=(110,33), left=True):
    W=len(files)*150+20; c=Image.new('RGB',(W,70),'white'); d=ImageDraw.Draw(c)
    for i,f in enumerate(files):
        im=Image.open(f).convert('RGBA'); sw,sh=slot; k=min(sw/im.width, sh/im.height); im=im.resize((int(im.width*k),int(im.height*k)), Image.LANCZOS)
        x=20+i*150; d.rectangle([x,18,x+sw,18+sh],outline=(225,225,225))
        c.paste(im,(x if left else x+(sw-im.width)//2, 18+(sh-im.height)//2), im)
    return c
a=row([f+'.png' for f in NAMES]); b=row(['opt-'+f+'.png' for f in NAMES], left=False)
p=Image.new('RGB',(a.width,150),'white'); p.paste(a,(0,0)); p.paste(b,(0,78)); d=ImageDraw.Draw(p); d.text((4,2),'NOW (110x32 slot, left-aligned)',fill='gray'); d.text((4,80),'OPTICAL (same slot, centred)',fill='gray')
p=p.resize((p.width*2,p.height*2), Image.LANCZOS); p.save('preview.png')
