"""Fill what is still missing on recent rows of Airtable "Running Shoes".

Cron on the OptiPlex, 12:00. Re-visits the Running Warehouse page of every row
created in the last 8 weeks that lacks a spec, a characteristic or the description.

  --since 2026-01-01   look back to a date instead of 8 weeks (one-off backfill)
  --limit 20           stop after N pages (default 15 per run)
  --dry-run            show what would be written, write nothing
"""
import argparse
import random
import sys
import time
import traceback
from datetime import datetime, timedelta, timezone

from pyairtable import Table

import rw_common as rw

PLACEHOLDER_DESC = "Description not available."
MAX_PAGES_PER_RUN = 15


def missing_fields(fields):
    missing = [f for f in rw.SPEC_FIELDS if rw.is_missing(fields.get(f))]
    missing += [f for f in rw.CHARACTERISTIC_FIELDS if not fields.get(f)]
    if not fields.get('Description') or fields.get('Description') == PLACEHOLDER_DESC:
        missing.append('Description')
    return missing


def created(record):
    return datetime.fromisoformat(record['createdTime'].replace('Z', '+00:00'))


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--since')
    parser.add_argument('--limit', type=int)
    parser.add_argument('--dry-run', action='store_true')
    parser.add_argument('--ids', help='comma-separated Airtable record IDs: check only these rows, any age')
    args = parser.parse_args()

    token = rw.load_env()
    shoes_table = Table(api_key=token, base_id=rw.BASE_ID, table_name=rw.SHOES_TABLE_NAME)
    if args.since:
        cutoff = datetime.fromisoformat(args.since).replace(tzinfo=timezone.utc)
    else:
        cutoff = datetime.now(timezone.utc) - timedelta(weeks=8)

    all_records = [r for r in shoes_table.all() if r.get('createdTime')]
    todo = []
    for record in all_records:
        fields = record['fields']
        if args.ids:
            if record['id'] not in args.ids.split(','):
                continue
        elif created(record) < cutoff or fields.get('Status') == 'IGNORE SHOE':
            continue  # ignored shoes are never checked (Ruggero 25 Nov 2026); --ids still reaches them
        if not fields.get('Shoe URL'):
            continue
        missing = missing_fields(fields)
        if missing:
            todo.append({'id': record['id'], 'url': fields['Shoe URL'], 'missing': missing,
                         'name': fields.get('Brand + Shoe Name', 'Unknown Shoe')})
    random.shuffle(todo)
    # Running Warehouse stops answering after ~40 pages in a few minutes (25 Nov 2026): stay well below.
    limit = args.limit or MAX_PAGES_PER_RUN
    todo = todo[:limit]
    print(f"{len(todo)} rows since {cutoff.date()} with something missing.")

    # Canary: the newest row that has all four specs must still parse, otherwise the page changed.
    complete = [r for r in all_records if r['fields'].get('Shoe URL')
                and not any(rw.is_missing(r['fields'].get(f)) for f in rw.SPEC_FIELDS)]
    canary = max(complete, key=created) if complete else None

    loaded = with_specs = updated = 0
    summary = []
    driver = rw.make_driver()
    try:
        canary_result = None
        if canary:
            page = rw.load_product_page(driver, canary['fields']['Shoe URL'])
            if page is not None:
                canary_result = len(rw.parse_specs(page))
            print(f"Canary {canary['fields'].get('Brand + Shoe Name')}: "
                  f"{'page did not load' if canary_result is None else str(canary_result) + ' of 4 specs read'}")

        for row in todo:
            time.sleep(random.uniform(20, 40))
            page = rw.load_product_page(driver, row['url'])
            if page is None:
                print(f"{row['name']}: page did not load")
                continue
            loaded += 1
            on_page = {}
            on_page.update(rw.parse_specs(page))
            on_page.update(rw.parse_characteristics(page))
            desc = page.select_one('#product_overview h2 p')
            if desc and desc.text.strip() and desc.text.strip() != PLACEHOLDER_DESC:
                on_page['Description'] = desc.text.strip()
            if any(f in on_page for f in rw.SPEC_FIELDS):
                with_specs += 1
            payload = {f: on_page[f] for f in row['missing'] if f in on_page}
            if not payload:
                print(f"{row['name']}: nothing new on the page (still missing {row['missing']})")
                continue
            if args.dry_run:
                print(f"{row['name']}: would write {payload}")
                continue
            try:
                shoes_table.update(row['id'], payload)
                updated += 1
                summary.append(f"{row['name']}: {', '.join(payload)}")
                print(f"{row['name']}: updated {list(payload)}")
            except Exception as e:
                print(f"{row['name']}: FAILED to update Airtable: {e}")
    finally:
        driver.quit()

    message = (f"{len(todo)} rows to check, {loaded} pages loaded, {with_specs} with specs, {updated} rows updated"
               + ("\n" + "\n".join(summary[:40]) if summary else ""))
    if canary and canary_result == 0:
        rw.ping(False, "specs not readable on a page that had them (page layout changed?). " + message)
        return 1
    if len(todo) >= 5 and loaded == 0:
        rw.ping(False, "no product page loaded (blocked?). " + message)
        return 1
    if not args.dry_run and not args.since and not args.ids:
        rw.ping(True, message)
    else:
        print(message)
    return 0


if __name__ == '__main__':
    try:
        sys.exit(main())
    except SystemExit:
        raise
    except Exception:
        rw.ping(False, "metadata updater crashed:\n" + traceback.format_exc()[-1500:])
        raise
