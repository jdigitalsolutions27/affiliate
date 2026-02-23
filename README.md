# Red Fairy Handmade Organic - Product Website + Affiliate Tracking Platform

Production-ready Laravel 11 web application for **Red Fairy Handmade Organic** combining:
- branded product storefront
- affiliate tracking and commissions
- admin operations (products, orders, payouts, settings, audit trail)

## Stack
- PHP 8.2+
- Laravel 11
- Blade + TailwindCSS
- Laravel Breeze (session auth)
- MySQL (recommended) or SQLite (local/dev)

## Core Features

### Public Website
- Home page with editable brand content
- Product catalog:
  - `/products`
  - `/product/{slug}`
  - `/category/{slug}`
- Search and filters (category + price range + sort)
- Order form:
  - `order_request` mode (recommended for Messenger workflows)
  - `checkout_lite` mode
- Referral tracking:
  - `/r/{affiliate_code}?p={product_slug}`
  - logs click + sets referral cookie (default 30 days)
  - attributes orders if referral cookie exists

### Admin
- Admin dashboard KPIs (clicks, conversions, sales, commissions, withdrawals)
- Affiliate management (CRUD, status toggle, password reset, revoke access)
- Category management (CRUD)
- Product management:
  - category, stock, featured, best-seller
  - multi-image upload
  - CSV import (required module)
- Order management with commission trigger/reversal
- Withdrawal approval/reject/mark-paid
- Brand settings:
  - primary/icon logo upload
  - brand colors
  - typography
  - hero/about/contact/social content
- Optional Meta Graph API module:
  - page/catalog/token settings
  - test connection
  - sync products now
- Audit log for key admin actions

### Affiliate
- Personal dashboard (scoped data only)
- My links page (per-product referral links)
- Earnings + available balance + paid history
- Withdrawal request and history

## Security
- Role middleware (`admin`, `affiliate`)
- Affiliate-only data scoping (IDOR protection)
- Referral route rate-limited
- Form validation + CSRF protection
- Transactional commission and payout-critical flows
- Audit logs for admin actions

## Database
Includes tables:
- `users`, `affiliates`
- `categories`, `products`, `product_images`
- `affiliate_product_rates`
- `clicks`
- `orders`, `order_items`
- `commissions`
- `withdrawals`
- `audit_logs`
- `app_settings`

## Setup (Local)

1. Install dependencies
```bash
composer install
npm install
```

2. Environment
```bash
cp .env.example .env
php artisan key:generate
```

3. Configure DB in `.env`

SQLite quickstart:
```bash
type nul > database\database.sqlite
```
Then set:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Or MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=affiliate_platform
DB_USERNAME=root
DB_PASSWORD=secret
```

4. Run migrations + seed
```bash
php artisan migrate --seed
```

5. Build assets and link storage
```bash
npm run build
php artisan storage:link
```

6. Run app
```bash
php artisan serve
```
For frontend watch mode in development:
```bash
npm run dev
```

## Seeded Accounts
- Admin: `admin@example.com` / `password123`
- 3 sample affiliates (password: `password123`)

## How To Use

### 1) Upload logo and change branding
1. Login as admin.
2. Open `Admin > Settings` (`/admin/settings`).
3. Update:
   - `Brand Name`
   - `Primary/Secondary/Accent` colors
   - `Typography`
   - Hero/About/Contact/Social fields
   - Primary and Icon logos
4. Save settings.

### 2) Add products
1. Create categories at `Admin > Categories`.
2. Open `Admin > Products > Add Product`.
3. Fill category, slug, description, price, stock, flags, commission defaults.
4. Upload one or more product images.
5. Save.

### 3) Import products via CSV (required module)
1. Open `Admin > Products`.
2. Use `CSV Import` form.
3. Upload CSV with headers:
```csv
name,slug,description,price,category,stock,status,is_featured,is_best_seller,default_commission_type,default_commission_value,image_url
```
4. Import runs upsert by `slug`.

### 4) Enable Meta API sync (optional)
1. Open `Admin > Settings`.
2. Fill:
   - `Meta Page ID`
   - `Meta Catalog ID`
   - `Meta Access Token`
3. Save settings.
4. Click `Test Meta Connection`.
5. Click `Sync Products Now`.

If API credentials/catalog are missing or invalid, the app stays fully functional and CSV import remains available.

## Affiliate Referral Flow
- Link format: `/r/{affiliate_code}?p={product_slug}`
- Click logs `ip_hash`, `ua`, `referrer`, `timestamp`
- Cookie/session referral saved for configurable lifetime (`cookie_lifetime_days`)
- Orders created while cookie is valid are attributed to affiliate

## Commission Rules
Priority:
1. product + affiliate override
2. product default
3. affiliate default
4. global default

Commissions are created on configured trigger status (`confirmed` or `completed`) and reversed on cancelled/refunded orders.

## Tests
Run:
```bash
php artisan test
```

Included feature tests:
- referral sets cookie and logs click
- affiliate cannot access admin routes
- affiliate sees only own stats
- commission created on order status change

## Deployment Notes

### Shared Hosting / VPS (recommended)
1. Deploy code.
2. Configure web root to `/public`.
3. Set production `.env` (`APP_ENV=production`, `APP_DEBUG=false`).
4. Run:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm install
npm run build
php artisan storage:link
php artisan optimize
```
5. Ensure writable permissions for `storage/` and `bootstrap/cache/`.

### Vercel
- Project includes Vercel routing/runtime compatibility.
- If deploying from local CLI, run `npm run build` first so latest `public/build` assets are included.

