"""New arrivals at Running Warehouse (men's) -> new rows in Airtable "Running Shoes".

Cron on the OptiPlex, 06:00 and 18:00. Specs missing on the page stay empty;
metadata_updater.py fills them later.
"""
import re
import sys
import time
import traceback
from collections import defaultdict

from bs4 import BeautifulSoup
from pyairtable import Table

import rw_common as rw

ARRIVALS_URL = "https://www.runningwarehouse.com/Mens_New_Footwear_Arrivals/catpage-MNAFOOT.html"


def get_brand_map(brands_table):
    brand_map = {}
    for record in brands_table.all():
        brand_name = record['fields'].get('Name')
        if brand_name:
            brand_map[re.sub(r'[^a-zA-Z0-9]', '', brand_name).lower()] = record['id']
    print(f"Found {len(brand_map)} brands in the mapping.")
    return brand_map


def main():
    token = rw.load_env()
    shoes_table = Table(api_key=token, base_id=rw.BASE_ID, table_name=rw.SHOES_TABLE_NAME)
    brands_table = Table(api_key=token, base_id=rw.BASE_ID, table_name=rw.BRANDS_TABLE_NAME)
    brand_map = get_brand_map(brands_table)

    model_set = set()
    for record in shoes_table.all():
        name = record['fields'].get('Brand + Shoe Name')
        if name:
            model_set.add(name.strip().lower())
    print(f"Found {len(model_set)} existing shoe models in Airtable.")

    # The list is read with the browser: plain requests get HTTP 406 (seen 25 Nov 2026).
    driver = rw.make_driver()
    try:
        driver.get(ARRIVALS_URL)
        time.sleep(5)
        soup = BeautifulSoup(driver.page_source, 'html.parser')
    except Exception:
        driver.quit()
        raise
    shoe_links = [a['href'] for a in soup.select('div.cattable-wrap-cell-imgwrap a[href]')]
    if not shoe_links:
        driver.quit()
        rw.ping(False, "new arrivals page gave no shoe links: page changed or blocked")
        return 1

    new_models = defaultdict(list)
    for link in shoe_links:
        try:
            model_name = link.split('/')[-2].replace('_', ' ')
        except IndexError:
            continue
        if model_name.lower() not in model_set:
            new_models[model_name].append(link)
    print(f"Found {len(new_models)} new shoe models to scrape.")
    if not new_models:
        driver.quit()
        return 0

    created, failed = 0, []
    try:
        for model_name, links in new_models.items():
            print(f"\n--- Scraping new model: {model_name} ---")
            payload, images = {}, []
            for i, link in enumerate(links):
                page = rw.load_product_page(driver, link)
                if page is None:
                    print(f"     -> Page content did not load. Skipping {link}")
                    continue
                data_div = page.select_one('div.gtm_detail')
                if not data_div:
                    print(f"     -> No product data on {link}. Skipping.")
                    continue
                image = page.select_one('img.main_image')
                if image and image.get('src'):
                    images.append({'url': re.sub('nw=455', 'nw=1462', image['src']),
                                   'filename': data_div.get('data-gtm_detail_name', model_name)})
                if payload:  # other colours: picture only
                    continue
                payload["Brand + Shoe Name"] = model_name
                payload["MSRP"] = float(data_div.get('data-gtm_detail_price', 0.0))
                full_name = data_div.get('data-gtm_detail_name', '').lower()
                payload["Status"] = "IGNORE SHOE" if 'spike' in full_name else "New Release"
                brand = data_div.get('data-gtm_detail_brand')
                if brand:
                    key = re.sub(r'[^a-zA-Z0-9]', '', brand).lower()
                    if key in brand_map:
                        payload["Brand"] = [brand_map[key]]
                    else:
                        print(f"     -> WARNING: Brand '{brand}' not in Airtable 'Brands' table.")
                payload.update(rw.parse_specs(page))
                payload.update(rw.parse_characteristics(page))
                payload["Shoe URL"] = link
                payload["Priority"] = "Medium"
            if not payload:
                failed.append(model_name)
                continue
            if images:
                payload["Stock Image"] = images
            try:
                shoes_table.create(payload)
                model_set.add(model_name.lower())
                created += 1
                specs = [f for f in rw.SPEC_FIELDS if f in payload]
                print(f"  -> Created '{model_name}': {len(images)} image(s), specs found: {specs or 'none yet'}")
            except Exception as e:
                failed.append(model_name)
                print(f"  -> FAILED to create '{model_name}': {e}")
    finally:
        driver.quit()

    if failed and not created:
        rw.ping(False, f"new arrivals: {len(failed)} new models found, none could be read or saved: {failed[:5]}")
        return 1
    print(f"\nDone: {created} created, {len(failed)} failed.")
    return 0


if __name__ == '__main__':
    try:
        sys.exit(main())
    except SystemExit:
        raise
    except Exception:
        rw.ping(False, "new arrivals script crashed:\n" + traceback.format_exc()[-1500:])
        raise
