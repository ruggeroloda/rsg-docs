# New Datafeed Studio - Project Conventions

## Project Overview
Price comparison system for shoes. Imports affiliate feeds from merchants, matches products against a master catalog synced from Airtable, and provides internal tooling for parser creation and feed imports.

Replaces 14-year-old PHP/MySQL system with modern Python stack.

## Dev Ticket System
All sprint tickets are in **Airtable** — base `appLoQNoAydB65Vop`, table "Dev Tickets" (ID: `tbl9TvbH0sjZKMacR`).
Check there for current sprint status before starting a new session. Mark tickets Done when work is complete.

## Tech Stack
- **Backend**: Python 3.11+
- **Web Framework**: Flask 3.x
- **Admin UI**: Flask-Admin (auto-generated CRUD)
- **Production Database**: PostgreSQL 15+
- **Preview/Testing**: DuckDB (ephemeral, not in production)
- **API**: Flask routes (simple REST)
- **Auth**: Flask-Login
- **Deployment**: Local dev first, Docker later

## Database Strategy
- **PostgreSQL**: All production data (`master_products`, `parsers`, `merchants`, `merchant_offers`)
- **DuckDB**: Preview only - load uploaded CSVs, test filters, then discard
- **Raw SQL preferred**: Use SQLAlchemy minimally for table definitions, write raw SQL for queries

## Core Data Model
```
parsers (reusable ETL/parser configs)
  └─ merchants (feed sources + parser association)
      └─ merchant_offers (normalized offers, match target = master_products)

master_products (catalog synced from Airtable)
```

## Key Workflows

### 1. Merchant Creation
- Admin creates merchant in "Merchants" admin page
- Enters merchant name, feed URL, etc.
- Clicks "Save"
- **Backend downloads feed file immediately** to verify URL works
- **If download fails**: Error shown, merchant creation blocked
- **If download succeeds**: Merchant saved with `parser_id = NULL`, file stored in `downloads/feeds/{merchant_name}/`
- Merchant exists but is **not importable** until parser is linked

### 2. Parser Creation (Ultra-Minimal Builder)
- Navigate to "Parsers" admin page → click "Create Parser"
- **File selection** (for testing only):
  - Dropdown shows all files in `downloads/feeds/` (newest first)
  - Select merchant's downloaded file (or any similar feed)
  - File path is **NOT saved** to parser config (ephemeral testing only)
- **Parser development** (single-page form):
  - DuckDB loads CSV in-memory for column detection
  - **Required fields** (must map):
    - `product_name` - Product title/name
    - `price_msrp` - MSRP/list price
    - `merchant_product_id` - SKU/product ID
    - `affiliate_url` - Product link
  - **Optional fields**:
    - `brand` - Brand name (for matching / title-extraction guard)
    - `price_sale` - Sale/discounted price
    - `currency` - Currency code
    - `merchant_url` - Product URL
    - `description` - Product description
    - `gender`, `age_group`, `color`, `size`, `item_group_id`, `gtin`, `mpn`
  - Note: `image_url` and `category` are NOT in the builder — no `merchant_offers` columns exist for them
  - Click "Preview" → see first 10 rows with mapped fields (actual import output: preprocessed, prices computed, validation applied)
- **Pricing card** (Step 5):
  - Radio: *Sale column is final sale price* (default)
  - Radio: *Sale column is discount amount off MSRP*
  - Radio: *Ignore sale column — always use MSRP*
  - Saved as `config.pricing.sale_price_mode`
- **Title Extraction card** (Step 6):
  - **Gender** radio: *From mapped feed field* (default) or *Extract from product name title*
  - **Brand** checkbox: *Extract brand from product name title* + textarea for parser-specific multi-word brands (one per line, e.g. "New Balance"); saved as `preprocessing.steps[0].brand_multi_word`
  - Gender + brand combine into one `extract_from_title` preprocessing step; feed-mapped fields always take precedence
  - Preview shows brand coverage table (top 40 brands with master-products match flag) when `brand` field is mapped
- **Save parser** (Step 7):
  - Parser name (e.g., "Generic Shopify" or "Nike CJ Parser")
  - Description (optional)
  - Currency fallback (optional, e.g., "USD") - used when currency field is empty
  - Click "Save Parser" → config saved to PostgreSQL `parsers` table
  - Redirect to parser list page
  - DuckDB data discarded

**Note**: Filters also available in parser builder (Step 4) including brand-in-master filter checkbox.

### 3. Link Parser to Merchant
- Edit merchant in "Merchants" admin
- Select parser from dropdown
- Save → `merchant.parser_id = {parser.id}`
- Merchant is now **importable**

### 4. Production Import (PostgreSQL)
- Cron/scheduler runs import for active merchants
- **Skip merchants with NULL parser_id** (not configured yet)
- For each configured merchant:
  - Download fresh feed from `merchant.feed_url` (NOT parser's test file)
  - Load parser config from linked `parsers` table
  - Apply parser runtime (mapping, derivations, filters, validation)
  - Match products against `master_products` catalog
  - Upsert to `merchant_offers` table
  - Track offer lifecycle (`is_active`, `last_seen_at`, timestamps)
  - Deactivate offers missing from latest run (`is_active = false`)
  - Persist per-attempt logs in `import_runs`
- Scheduler retries failed runs with backoff (configurable attempt count/delay)
- Support `--dry-run` imports for safe validation before write runs

### 5. API Layer
- Public WordPress API is currently deferred
- Internal focus is admin tooling + import pipeline first

## Matching Logic (Rule-Based)
Products matched using:
- `match_keywords` array (e.g., ["pegasus 41", "peg 41"])
- `exclude_keywords` array (e.g., ["trail", "premium"])
- Title normalization (lowercase, remove punctuation)
- Brand normalization (alias map + fallback mode) before matching/import when enabled
- No ML/LLM - pure string matching

## Flask-Admin Usage
- Use Flask-Admin ModelView for standard CRUD (`master_products`, `merchants`, `parsers`)
- Custom views for:
  - Parser Builder (`/admin/parser-builder/`) - file-based parser creation with DuckDB preview
  - Import dashboard (trigger manual imports, view logs)
- Merchant bulk actions include manual scheduler pipeline execution (full or dry-run import)
- Admin dashboard at `/admin/` shows KPI cards, system resources (CPU/RAM/disk), per-merchant stats, recent import runs
- **Settings** navbar dropdown (Flask-Admin `category='Settings'`) contains:
  - **Gender Normalization** — CRUD for the `gender_normalization` lookup table; wired to runtime (fetched per-import by `fetch_gender_map(cursor)` in `import_feed.py` and by `load_gender_map()` for preview)
  - **Title Brand Detection** — CRUD for the `global_brand_aliases` multi-word brand list; wired to runtime (fetched per-import by `fetch_global_brand_aliases(cursor)` and merged as base into any `extract_from_title` preprocessing step; per-parser brand overrides take precedence)
- Keep admin simple - this is an internal tool

## Database Schema
See [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) for complete table definitions and design decisions.

**Core Tables (MVP)**:
- `master_products` - Shoe catalog from Airtable
- `parsers` - Reusable ETL configurations
- `merchants` - Feed source configurations
- `merchant_offers` - Current product offers from feeds
- `import_runs` - Scheduler/import attempt logs for operations

**Settings Tables**:
- `gender_normalization` - Editable map of raw feed gender tokens → canonical values (Men/Women/Unisex/Kids)
- `global_brand_aliases` - Multi-word brand phrases for title-based brand extraction

**Pricing schema**:
- `merchant_offers.price` stores calculated display price
- `merchant_offers.price_msrp` stores mapped MSRP/list price
- `merchant_offers.price_sale` stores optional mapped sale price
- Display-price policy:
  - `sale_price_mode=final`: sale is treated as final price
  - `sale_price_mode=discount_amount`: sale is treated as discount off MSRP
  - `sale_price_mode=msrp_only`: sale column ignored; display = MSRP
  - Sale equal to or above MSRP is discarded; display falls back to MSRP
  - If only sale is valid, sale is promoted as MSRP/display

**Brand schema**:
- `merchant_offers.raw_brand` stores merchant-provided brand text
- `merchant_offers.brand_normalized` stores canonical/normalized brand

**Variant + ID schema**:
- `merchant_offers.color`, `merchant_offers.size`, `merchant_offers.item_group_id` store variant context
- `merchant_offers.gtin`, `merchant_offers.mpn` store standardized product identifiers

## File Naming & Structure
*To be defined - postponed for now*

## Authentication
- Flask-Login: username/password
- Single admin user initially (can add more later)
- Admin can remain open in local-only development; lock down before shared/staging
- API: no public API in MVP phase

## Code Style
- Python: Follow PEP 8
- SQL: Uppercase keywords, readable formatting
- Comments: Why not what
- Functions: Small, single purpose
- Flask routes: Keep business logic in separate modules

## What NOT to Do
- Don't use Celery yet (overkill for 30 daily jobs)
- Don't add Docker until asked
- Don't use ORM for complex queries (raw SQL is fine)
- Don't use pandas in production imports (DuckDB or PostgreSQL only)
- Don't call external LLM APIs
- Don't over-engineer - this replaces a simple PHP system

## Dependencies to Install
```
Flask>=3.0
Flask-Admin
Flask-Login
Flask-SQLAlchemy  # Just for table definitions
psycopg2-binary
duckdb
python-dotenv
requests
pyairtable
apscheduler
```

## Environment Variables
```
DATABASE_URL=postgresql://user:pass@localhost/newdatafeedstudio
AIRTABLE_API_KEY=xxx
AIRTABLE_BASE_ID=xxx
SECRET_KEY=xxx
FLASK_ENV=development
```

## Development Setup
```bash
# Install PostgreSQL via Homebrew (macOS)
brew install postgresql@15

# Create database
createdb newdatafeedstudio

# Python virtual environment
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Run migrations (if using Flask-Migrate)
flask db upgrade

# Run dev server
flask run
```

## Project Philosophy
- **Simple over clever**: This replaces a working PHP system. Don't overcomplicate.
- **Iterate fast**: Get basic version working, then improve.
- **Solo dev focus**: No need for enterprise patterns.
- **Cost conscious**: Should run on similar $70/month VPS.

## Future Considerations (Not Now)
- Docker deployment
- Celery for scheduling
- Price history tracking
- Advanced admin UI improvements
- Webhook notifications
- Multi-user support
