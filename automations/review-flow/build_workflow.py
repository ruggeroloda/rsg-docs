#!/usr/bin/env python3
"""Build the n8n workflow "RSG — Review Insert (Airtable to WordPress)" from fields.json.

    python3 build_workflow.py staging   -> workflow-staging.json (drafts on staging, picks status "test")
    python3 build_workflow.py live      -> workflow-live.json    (drafts on live, picks "Ready to Publish")
    python3 build_workflow.py staging recXXXXXXXXXXXXXX[,recYYYYYYYYYYYYYY]
        -> test run: reads that one Airtable record (even if it already has a WordPress ID), makes the
           draft on staging and writes NOTHING to Airtable (the PATCH calls send no fields).
           Why: a new record in "Shoe Reviews" fires the Airtable automation "When Review received
           change Status" (shoe -> Received, review -> Pictures Need Saving -> Make's Dropbox scenario).

Import on rsgmedia: docker exec n8n n8n import:workflow --input=<file>
The workflow id stays the same (WORKFLOW_ID), so an import replaces the previous version.
Change the field list in fields.json, never in n8n.
"""
import json
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
WORKFLOW_ID = "rsgReviewInsert1"
ERROR_WORKFLOW = "3HbWqPZ49CYRfOHw"  # RSG — Error Notifier
AIRTABLE_CRED = {"airtableTokenApi": {"id": "4oQJXi2Yfbu7VWsD", "name": "Airtable Personal Access Token account"}}
WP_CRED = {"httpBasicAuth": {"id": "2rc23hiXxgr0E9qT", "name": "RSG WordPress (ruggero)"}}
# Make connector plugin key (header iwc-api-key), same on live and staging. Only for fields.json -> make_extras meta.
MAKE_KEY_CRED = {"httpHeaderAuth": {"id": "rsgWpMakeKey0001", "name": "RSG WordPress Make connector key"}}

TARGETS = {
    "staging": {"wp": "https://jfx92wmcr3-staging.onrocket.site", "pick": "test"},
    "live": {"wp": "https://www.runningshoesguru.com", "pick": "Ready to Publish"},
}

BUILD_JS = r"""
// Generated from automations/review-flow/fields.json by build_workflow.py. Do not edit in n8n.
const F = __FIELDS__;
const S = $('Settings').first().json;
const A = F.airtable;
const users = $('Get WordPress users').all().map(i => i.json);
const recs = $('Get ready reviews').first().json.records || [];

const first = v => Array.isArray(v) ? v[0] : v;
const txt = v => { v = first(v); return (v === undefined || v === null) ? '' : String(v).trim(); };
const blank = v => v === undefined || v === null || v === '' || (Array.isArray(v) && v.length === 0);

let ctx = {};
function value(f, fl) {
  switch (f.how) {
    case 'const': return f.value;
    case 'text': { const t = txt(fl[f.from]); return t === '' ? undefined : t; }
    case 'number': { const v = first(fl[f.from]); return blank(v) ? undefined : Number(v); }
    case 'spec': { const n = Number(first(fl[f.from])); return (!isFinite(n) || n === 0 || n === 999) ? undefined : n; }
    case 'select': { const t = txt(fl[f.from]); return t === '' ? undefined : t; }
    case 'select_code': { const t = txt(fl[f.from]); return t === '' ? undefined : t.split(':')[0].trim(); }
    case 'multi': { const v = fl[f.from]; return blank(v) ? undefined : [].concat(v).map(x => String(x).trim()); }
    case 'lines': { const rows = f.from.map(id => txt(fl[id])).filter(t => t !== '').map(t => ({ [f.row]: t })); return rows.length ? rows : undefined; }
    case 'model': { const shoe = txt(fl[f.from[0]]), brand = txt(fl[f.from[1]]); return (brand ? shoe.split(brand).join('') : shoe).trim() || undefined; }
    case 'term': { let v = fl[f.from]; if (v && v.valuesByLinkedRecordId) v = Object.values(v.valuesByLinkedRecordId).flat(); const n = Number(first(v)); return isFinite(n) && n > 0 ? n : undefined; }
    case 'rw_url': {
      const t = txt(fl[f.from]);
      if (t === '') return undefined;
      if (!/runningwarehouse\.com/i.test(t) || /[?&]from=guru/.test(t)) return t;  // only Running Warehouse links get the tag
      return t + (t.includes('?') ? '&' : '?') + 'from=guru';
    }
    case 'template': return f.template.replace(/\{(\w+)\}/g, (m, k) => ctx[k] ?? '');
    default: throw new Error('fields.json: unknown how "' + f.how + '" for ' + f.acf);
  }
}

function shoeType(fl) {
  const cats = [].concat(fl[A.category] || []).flatMap(c => String(c).split(';')).map(c => c.trim());
  if (cats.includes('Trail')) return 'trail';
  if (cats.includes('Training')) return 'training';
  return 'road';
}

function author(name) {
  if (!name) return undefined;
  const n = name.toLowerCase();
  const hit = users.find(u => (u.name || '').toLowerCase() === n) || users.find(u => (u.name || '').toLowerCase().includes(n));
  return hit ? hit.id : undefined;
}

const out = [];
for (const r of recs) {
  const fl = r.fields || {};
  const title = txt(fl[A.title]);
  if (!blank(fl[A.wordpress_id]) && !S.test_record) { out.push({ json: { skip: 'already has WordPress ID', record_id: r.id, title } }); continue; }
  const images = [].concat(fl[A.edited_images] || []).map(a => ({ url: a.url, filename: a.filename }));
  if (!images.length) { out.push({ json: { skip: 'no Edited Images', problem: true, record_id: r.id, title } }); continue; }
  const type = shoeType(fl);
  const T = F.types[type];
  const acf = {};
  for (const f of F.fields) {
    if (!f.types.includes(type)) continue;
    const v = value(f, fl);
    if (v !== undefined) acf[f.acf] = v;
  }
  ctx = { shoe: txt(fl[A.shoe]), brand: txt(fl[A.brand]), category: txt(fl[A.category_text]), title, type };
  const reviewer = txt(fl[A.reviewer]);
  const body = { title, status: 'draft', shoes_type: [T.shoes_type], acf };
  const a = author(reviewer);
  if (!reviewer) { out.push({ json: { skip: 'no reviewer on the shoe in Running Shoes', problem: true, record_id: r.id, title } }); continue; }
  if (!a) { out.push({ json: { skip: 'reviewer "' + reviewer + '" is not a WordPress writer', problem: true, record_id: r.id, title } }); continue; }
  body.author = a;
  const meta = {};
  for (const f of (F.make_extras || {}).fields || []) {
    if (!f.types.includes(type)) continue;
    const v = value(f, fl);
    if (v === undefined) continue;
    if (f.post) body[f.post] = v; else meta[f.meta] = v;
  }
  out.push({ json: {
    record_id: r.id, type, post_type: T.post_type, path: T.path, title,
    shoe: txt(fl[A.shoe]), reviewer,
    images, body, meta,
  } });
}
return out;
"""

IMAGES_JS = r"""
// One item per Edited Image, in Airtable order (picture 1, 2, ...), as Make did.
const out = [];
const items = $input.all();
for (let i = 0; i < items.length; i++) {
  const b = $('Build drafts').itemMatching(i).json;
  const post = $('Create draft').itemMatching(i).json;
  b.images.forEach((img, k) => out.push({
    json: { record_id: b.record_id, post_id: post.id, post_type: b.post_type, url: img.url, filename: img.filename,
            title: b.title + ' - picture ' + (k + 1), alt: 'Picture of ' + b.shoe },
    pairedItem: { item: i },
  }));
}
return out;
"""

DONE_JS = r"""
// Back to one item per review once all its pictures are uploaded; the uploaded picture IDs, in order, become the gallery.
const byRecord = new Map();
$input.all().forEach((it, i) => {
  const img = $('One item per image').itemMatching(i).json;
  if (!byRecord.has(img.record_id)) byRecord.set(img.record_id, { record_id: img.record_id, post_id: img.post_id, post_type: img.post_type, gallery: [] });
  byRecord.get(img.record_id).gallery.push(it.json.id);
});
// The gallery field takes at most 19 pictures (field setting): the first 19, in Airtable order; swap in wp-admin if needed.
return [...byRecord.values()].map(j => ({ json: { ...j, gallery: j.gallery.slice(0, 19) } }));
"""

PROBLEMS_JS = r"""
// Reviews that cannot be inserted: make the run fail so the Error Notifier mails the list (nothing was written for them).
const bad = $input.all().map(i => i.json).filter(j => j.problem);
if (!bad.length) return [];
throw new Error('Review insert skipped ' + bad.length + ' review(s): ' + bad.map(j => j.title + ' (' + j.skip + ', ' + j.record_id + ')').join('; '));
"""


def settings_js(target, test_record=None):
    t = TARGETS[target]
    return (
        "// Generated by build_workflow.py (" + target + (", test record " + test_record if test_record else "") + ").\n"
        "return [{ json: {\n"
        f"  target: {json.dumps(target)},\n"
        f"  wp: {json.dumps(t['wp'])},\n"
        f"  pick_status: {json.dumps(t['pick'])},\n"
        f"  test_record: {json.dumps(test_record)},\n"
        f"  write_airtable: {'false' if test_record else 'true'},\n"
        "} }];\n"
    )


def node(name, typ, version, pos, params, **extra):
    n = {"id": name.lower().replace(" ", "-"), "name": name, "type": typ, "typeVersion": version,
         "position": pos, "parameters": params}
    n.update(extra)
    return n


def airtable_http(name, pos, method, url, body=None, query=None, **extra):
    p = {"method": method, "url": url, "authentication": "predefinedCredentialType",
         "nodeCredentialType": "airtableTokenApi", "options": {}}
    if query:
        p["sendQuery"] = True
        p["queryParameters"] = {"parameters": [{"name": k, "value": v} for k, v in query]}
    if body:
        p.update({"sendBody": True, "specifyBody": "json", "jsonBody": body})
    return node(name, "n8n-nodes-base.httpRequest", 4.2, pos, p, credentials=AIRTABLE_CRED, **extra)


def wp_http(name, pos, method, url, **p):
    params = {"method": method, "url": url, "authentication": "genericCredentialType",
              "genericAuthType": "httpBasicAuth", "options": {}}
    params.update(p)
    return node(name, "n8n-nodes-base.httpRequest", 4.2, pos, params, credentials=WP_CRED)


def build(target, test_record=None):
    fields = json.loads((HERE / "fields.json").read_text())
    A = fields["airtable"]
    at = f"https://api.airtable.com/v0/{A['base']}/{A['table']}"
    js = BUILD_JS.replace("__FIELDS__", json.dumps(fields, ensure_ascii=False))
    formula = ("={{ $('Settings').first().json.test_record ? \"OR(\" + $('Settings').first().json.test_record.split(',').map(r => \"RECORD_ID()='\" + r + \"'\").join(',') + \")\" : "
               "\"AND({Review Status}='\" + $('Settings').first().json.pick_status + \"', {WordPress ID}=BLANK())\" }}")

    nodes = [
        node("Run by hand", "n8n-nodes-base.manualTrigger", 1, [0, 0], {}),
        node("Every 3 hours", "n8n-nodes-base.scheduleTrigger", 1.2, [0, 200],
             {"rule": {"interval": [{"field": "hours", "hoursInterval": 3}]}}),
        node("Settings", "n8n-nodes-base.code", 2, [220, 100], {"jsCode": settings_js(target, test_record)}),
        wp_http("Get WordPress users", [440, 100], "GET", "={{ $json.wp }}/wp-json/wp/v2/users",
                sendQuery=True,
                queryParameters={"parameters": [{"name": "per_page", "value": "100"},
                                                {"name": "context", "value": "edit"},
                                                {"name": "roles", "value": "administrator,editor,author,contributor"},
                                                {"name": "_fields", "value": "id,name"}]},
                options={"pagination": {"pagination": {
                    "parameters": {"parameters": [{"name": "page", "value": "={{ $pageCount + 1 }}"}]},
                    "paginationCompleteWhen": "other",
                    "completeExpression": "={{ $response.body.length < 100 }}",
                    "limitPagesFetched": True, "maxRequests": 10}}}),
        airtable_http("Get ready reviews", [660, 100], "GET", at,
                      query=[("filterByFormula", formula), ("returnFieldsByFieldId", "true"), ("pageSize", "10")],
                      executeOnce=True),
        node("Build drafts", "n8n-nodes-base.code", 2, [880, 100], {"jsCode": js}, executeOnce=True),
        node("OK to insert?", "n8n-nodes-base.if", 2.2, [1100, 100], {
            "conditions": {"options": {"caseSensitive": True, "leftValue": "", "typeValidation": "loose", "version": 2},
                           "conditions": [{"id": "noskip", "leftValue": "={{ $json.skip }}", "rightValue": "",
                                           "operator": {"type": "string", "operation": "empty", "singleValue": True}}],
                           "combinator": "and"}, "options": {}}),
        node("Report problems", "n8n-nodes-base.code", 2, [1320, 320], {"jsCode": PROBLEMS_JS}),
        wp_http("Create draft", [1320, 100], "POST", "={{ $('Settings').first().json.wp }}/wp-json/wp/v2/{{ $json.post_type }}",
                sendBody=True, specifyBody="json", jsonBody="={{ JSON.stringify($json.body) }}"),
        node("Make extras (temporary)", "n8n-nodes-base.httpRequest", 4.2, [1430, 100], {
            "method": "POST",
            "url": "={{ $('Settings').first().json.wp }}/wp-json/wp/v2/{{ $('Build drafts').item.json.post_type }}/{{ $json.id }}",
            "authentication": "genericCredentialType", "genericAuthType": "httpHeaderAuth",
            "sendBody": True, "specifyBody": "json",
            "jsonBody": "={{ JSON.stringify({ meta: $('Build drafts').item.json.meta }) }}",
            "options": {}}, credentials=MAKE_KEY_CRED,
            notes="Writes the plain meta Make wrote (fields.json make_extras) through the Make connector plugin. Remove when make_extras has no meta left."),
        airtable_http("Write ID to Airtable", [1540, 100], "PATCH",
                      "=" + at + "/{{ $('Build drafts').item.json.record_id }}",
                      body="={{ JSON.stringify({ fields: !$('Settings').first().json.write_airtable ? {} : { '" + A["wordpress_id"] + "': $('Create draft').item.json.id, '" + A["wordpress_link"]
                           + "': $('Settings').first().json.wp + $('Build drafts').item.json.path + $('Create draft').item.json.generated_slug + '/' } }) }}"),
        node("One item per image", "n8n-nodes-base.code", 2, [1760, 100], {"jsCode": IMAGES_JS}),
        node("Download image", "n8n-nodes-base.httpRequest", 4.2, [1980, 100], {
            "url": "={{ $json.url }}",
            "options": {"response": {"response": {"responseFormat": "file"}}}}),
        wp_http("Upload image", [2200, 100], "POST", "={{ $('Settings').first().json.wp }}/wp-json/wp/v2/media",
                sendQuery=True,
                queryParameters={"parameters": [
                    {"name": "post", "value": "={{ $('One item per image').item.json.post_id }}"},
                    {"name": "title", "value": "={{ $('One item per image').item.json.title }}"},
                    {"name": "alt_text", "value": "={{ $('One item per image').item.json.alt }}"},
                    {"name": "description", "value": "={{ $('One item per image').item.json.alt }}"}]},
                sendHeaders=True,
                headerParameters={"parameters": [
                    {"name": "Content-Disposition", "value": "=attachment; filename=\"{{ $('One item per image').item.json.filename }}\""},
                    {"name": "Content-Type", "value": "={{ $binary.data.mimeType }}"}]},
                sendBody=True, contentType="binaryData", inputDataFieldName="data"),
        node("One item per review", "n8n-nodes-base.code", 2, [2420, 100], {"jsCode": DONE_JS}),
        wp_http("Set gallery", [2530, 100], "POST",
                "={{ $('Settings').first().json.wp }}/wp-json/wp/v2/{{ $json.post_type }}/{{ $json.post_id }}",
                sendBody=True, specifyBody="json",
                jsonBody="={{ JSON.stringify({ acf: { gallery: $json.gallery, ds_query: '' } }) }}"),
        airtable_http("Mark In Wordpress", [2640, 100], "PATCH",
                      "=" + at + "/{{ $('One item per review').item.json.record_id }}",
                      body="={{ JSON.stringify({ fields: !$('Settings').first().json.write_airtable ? {} : { '" + A["status"] + "': '" + fields["status_done"] + "' } }) }}"),
    ]
    chain = ["Settings", "Get WordPress users", "Get ready reviews", "Build drafts", "OK to insert?", "Create draft",
             "Make extras (temporary)", "Write ID to Airtable", "One item per image", "Download image", "Upload image",
             "One item per review", "Set gallery", "Mark In Wordpress"]
    conns = {a: {"main": [[{"node": b, "type": "main", "index": 0}]]} for a, b in zip(chain, chain[1:])}
    conns["OK to insert?"]["main"].append([{"node": "Report problems", "type": "main", "index": 0}])
    conns["Run by hand"] = {"main": [[{"node": "Settings", "type": "main", "index": 0}]]}
    conns["Every 3 hours"] = {"main": [[{"node": "Settings", "type": "main", "index": 0}]]}
    return {
        "id": WORKFLOW_ID,
        "name": "RSG — Review Insert (Airtable to WordPress)",
        "active": False,
        "nodes": nodes,
        "connections": conns,
        "settings": {"executionOrder": "v1", "errorWorkflow": ERROR_WORKFLOW, "saveManualExecutions": True},
        "pinData": {},
        "meta": {"generatedFrom": "automations/review-flow/fields.json", "target": target},
    }


if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else "staging"
    test_record = sys.argv[2] if len(sys.argv) > 2 else None
    if target not in TARGETS:
        sys.exit("target: staging or live")
    if test_record and target != "staging":
        sys.exit("a test record runs on staging only")
    out = HERE / (f"workflow-{target}.json" if not test_record else "_test-workflow-staging.json")
    out.write_text(json.dumps(build(target, test_record), indent=1, ensure_ascii=False) + "\n")
    print(out)
