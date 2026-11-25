"""Shared parts of the Running Warehouse scraper (RW-2025.py, metadata_updater.py).

Settings come from a `.env` file next to this file (never in git):
  AIRTABLE_PAT=...   Airtable token (base "RSG Product Testing")
  HC_URL=...         healthchecks.io ping URL (optional; no alarm without it)
"""
import os
import re
import time
from pathlib import Path

import requests
from bs4 import BeautifulSoup
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
from selenium.common.exceptions import TimeoutException
from selenium_stealth import stealth

BASE_ID = 'appLoQNoAydB65Vop'
SHOES_TABLE_NAME = 'Running Shoes'
BRANDS_TABLE_NAME = 'Brands'

# Airtable field -> (label on the Running Warehouse page, unit)
SPEC_FIELDS = {
    'Weight (in Oz)': ('Weight', 'oz'),
    'Heel': ('Heel Stack', 'mm'),
    'Forefoot': ('Forefoot Stack', 'mm'),
    'Heel Drop': ('Heel-Toe Offset', 'mm'),
}
CHARACTERISTIC_FIELDS = {
    'Best Use': 'Best Use',
    'Cushioning Level': 'Cushioning',
    'Surface': 'Surface',
    'Stability Level': 'Stability',
}


def load_env():
    env_file = Path(__file__).resolve().parent / '.env'
    if env_file.exists():
        for line in env_file.read_text().splitlines():
            line = line.strip()
            if line and not line.startswith('#') and '=' in line:
                key, value = line.split('=', 1)
                os.environ.setdefault(key.strip(), value.strip().strip('"\''))
    token = os.environ.get('AIRTABLE_PAT')
    if not token:
        raise SystemExit('AIRTABLE_PAT is missing: put it in .env next to the scripts.')
    return token


def ping(ok, message):
    """Tell healthchecks.io how the run went. Never raises."""
    url = os.environ.get('HC_URL', '').rstrip('/')
    print(('OK: ' if ok else 'FAIL: ') + message)
    if not url:
        return
    try:
        requests.post(url if ok else url + '/fail', data=message[:9000].encode(), timeout=10)
    except Exception as e:  # the alarm must not break the run
        print(f'(could not reach healthchecks: {e})')


def make_driver():
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("start-maximized")
    chrome_options.add_experimental_option("excludeSwitches", ["enable-automation"])
    chrome_options.add_experimental_option('useAutomationExtension', False)
    driver = webdriver.Chrome(options=chrome_options)
    stealth(driver, languages=["en-US", "en"], vendor="Google Inc.", platform="Win32",
            webgl_vendor="Intel Inc.", renderer="Intel Iris OpenGL Engine", fix_hairline=True)
    return driver


def load_product_page(driver, url):
    """Open a product page; return its soup, or None if the content did not load."""
    driver.get(url.strip())
    try:
        reject_button = WebDriverWait(driver, 5).until(
            EC.element_to_be_clickable((By.ID, "onetrust-reject-all-handler")))
        reject_button.click()
        time.sleep(1)
    except TimeoutException:
        pass
    try:
        WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "div.descpage-top_wrap")))
    except TimeoutException:
        return None
    return BeautifulSoup(driver.page_source, 'html.parser')


def _number(text, unit):
    match = re.search(r'([\d.]+)\s*' + unit, text or '')
    if not match:
        return None
    try:
        value = float(match.group(1))
    except ValueError:
        return None
    return int(value) if value == int(value) else value


def parse_specs(soup):
    """Weight, stack heights and drop from the "Shoe Specs" list.

    Returns only the fields found on the page (no placeholder values).
    Page format since 2025: <li><strong>Weight:</strong><span> 10.7 oz | 303 g</span></li>
    """
    found = {}
    block = soup.select_one('#product_sizing') or soup
    by_label = {}
    for item in block.select('li'):
        strong = item.find('strong')
        if strong:
            label = strong.get_text(' ', strip=True).rstrip(':').strip().lower()
            by_label[label] = item.get_text(' ', strip=True)
    for field, (label, unit) in SPEC_FIELDS.items():
        text = by_label.get(label.lower())
        if text is None:  # fall back to the block's plain text
            match = re.search(re.escape(label) + r'\s*:\s*([^|<]{0,30})', block.get_text(' '))
            text = match.group(1) if match else None
        value = _number(text, unit)
        if value is not None:
            found[field] = value
    return found


def get_characteristic(soup, characteristic_name):
    """The highlighted pill under a heading such as "Best Use" or "Cushioning"."""
    for header in soup.select('h3.header_bestuse'):
        if characteristic_name in header.get_text(' '):
            container = header.find_next_sibling('div')
            if container:
                active_pill = container.select_one('.is-active, .active')
                if active_pill:
                    return active_pill.get_text(' ', strip=True)
    return None


def parse_characteristics(soup):
    found = {}
    for field, name in CHARACTERISTIC_FIELDS.items():
        value = get_characteristic(soup, name)
        if value:
            found[field] = value
    return found


def is_missing(value):
    """Empty, or the old 999 placeholder."""
    return value in (None, '', 999)
