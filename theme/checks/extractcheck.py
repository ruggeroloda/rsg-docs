#!/usr/bin/env python3
"""What text extractors keep of a page (15 Nov 2026).

Usage: python3 extractcheck.py OUTDIR URL_or_FILE...
Needs: pip install trafilatura readability-lxml beautifulsoup4 lxml   (runs on the Mac's device shell or the cloud shell)

For each page: fetches it (Googlebot UA; staging host is fine), runs trafilatura (the extractor behind Common Crawl-style
AI datasets) and readability-lxml (reader-mode style, used by many agent fetch tools), writes OUTDIR/<slug>.traf.md and
.read.txt, and reports which of the page's own items are MISSING from the extracted text:
  - every h2/h3 heading inside <main> (menu/footer headings are ignored),
  - every <dt> label and its first <dd> value inside <main> (facts, ratings, fit, ...),
  - the reviewer name (rel=author link text) and the "purchased ... with our own money" sentence.
Also prints the heading outline of the whole page (headings before the h1 are flagged).
Run on live and on staging for the same URLs and compare: a reskin step must not add trafilatura MISSING items
(readability is informational: it keeps only the review prose on reskinned reviews until the article is one container).
Proxies only: Google renders pages itself and does not depend on these extractors.
"""
import sys, os, re, urllib.request
import trafilatura
from readability import Document
from bs4 import BeautifulSoup

UA = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"

def get(src):
    if os.path.exists(src):
        return open(src, encoding="utf-8").read()
    req = urllib.request.Request(src, headers={"User-Agent": UA})
    return urllib.request.urlopen(req, timeout=60).read().decode("utf-8", "replace")

def norm(t):
    return re.sub(r"\s+", " ", t.replace(" ", " ")).strip().lower()

def items(s):
    main = s.find("main") or s.body
    out = []
    for h in main.find_all(["h2", "h3"]):
        t = h.get_text(" ", strip=True)
        if t and not h.find_parent(id="respond-wrapper"):
            out.append(("heading", t))
    for dt in main.find_all("dt"):
        if dt.find_parent(attrs={"hidden": True}) or dt.find_parent(class_=re.compile("rsg-vh|sr-only")):
            continue
        dd = dt.find_next_sibling("dd")
        out.append(("label", dt.get_text(" ", strip=True)))
        if dd and dd.get_text(strip=True):
            out.append(("value of " + dt.get_text(" ", strip=True)[:20], dd.get_text(" ", strip=True)))
    a = s.find("a", rel="author")
    if a and a.get_text(strip=True):
        out.append(("reviewer", a.get_text(" ", strip=True)))
    m = re.search(r"(?:[Pp]urchased|Bought)[^.<]{0,120}own money", s.get_text(" "))
    if m:
        out.append(("purchase proof", "own money"))
    return out

def outline(s):
    hs = s.find_all(re.compile("^h[1-6]$"))
    seen_h1, lines = False, []
    for h in hs:
        t = h.get_text(" ", strip=True)
        flag = "" if seen_h1 or h.name == "h1" else "  <- before h1"
        if h.name == "h1":
            seen_h1 = True
        lines.append(f"  {h.name} {t[:60] or '(empty)'}{flag}")
    return lines

def main():
    outdir, srcs = sys.argv[1], sys.argv[2:]
    os.makedirs(outdir, exist_ok=True)
    for src in srcs:
        html = get(src)
        slug = re.sub(r"[^a-z0-9]+", "-", src.lower()).strip("-")[-80:]
        s = BeautifulSoup(html, "lxml")
        traf = trafilatura.extract(html, output_format="markdown", include_tables=True) or ""
        read = BeautifulSoup(Document(html).summary(), "lxml").get_text(" ", strip=True)
        open(f"{outdir}/{slug}.traf.md", "w").write(traf)
        open(f"{outdir}/{slug}.read.txt", "w").write(read)
        its = items(s)
        nt, nr = norm(traf), norm(read)
        miss_t = [f"{k}: {v[:50]}" for k, v in its if norm(v) not in nt]
        miss_r = [f"{k}: {v[:50]}" for k, v in its if norm(v) not in nr]
        print(f"== {src}\n  HTML {len(html)/1024:.0f} KB | trafilatura {len(traf)} chars, {len(its)-len(miss_t)}/{len(its)} items | readability {len(read)} chars, {len(its)-len(miss_r)}/{len(its)} items")
        print("  MISSING (trafilatura):", "; ".join(miss_t) or "none")
        print("  MISSING (readability):", "; ".join(miss_r[:12]) + (f" … +{len(miss_r)-12}" if len(miss_r) > 12 else "") or "none")
        print("  outline:"); print("\n".join(outline(s)))

if __name__ == "__main__":
    main()
