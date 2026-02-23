# Affiliate Sales & Commission Tracking Platform

Production-ready web application built with Laravel 11, PHP 8.2, MySQL, TailwindCSS, Blade, and Laravel Breeze (session auth).

## Features

### Public
- Landing page with product offers and Affiliate Login CTA
- Public offer pages: `/offer/{slug}`
- Referral tracking route: `/r/{code}?p={product_slug}`
- Public order form per offer: `/order/{product_slug}`
- Referral cookie/session attribution (default 30 days)
- Click logging with IP hash, user agent, referrer

### Admin
- Full role-based access (`admin`)
- Dashboard with KPIs, daily trends, top affiliates/products, latest orders, pending withdrawals
- Affiliate management:
  - Create/edit/delete
  - Activate/deactivate
  - Reset password
  - Revoke sessions/tokens (session + remember token invalidation)
  - Per-affiliate default commission
  - Per-product-per-affiliate commission overrides
- Product/offer management (CRUD)
- Order management:
  - Manual order creation
  - Status updates (`pending`, `confirmed`, `completed`, `cancelled`, `refunded`)
  - Commission trigger/reversal flow
- Settings:
  - Global commission type/value
  - Cookie lifetime (days)
  - Commission trigger status
  - Minimum payout
  - Payout methods label
- Withdrawal processing:
  - Approve/reject
  - Mark paid with transaction reference
- Audit logging for key admin actions

### Affiliate
- Role-based access (`affiliate`)
- Access automatically revoked when affiliate is inactive
- Personal dashboard only (scoped data)
- My Links page with generated tracking links for each product
- Withdrawal request form + history
- KPIs: total earnings, available balance, paid amounts, clicks, conversions, sales

## Tech Stack
- Laravel `11.x`
- PHP `8.2+`
- TailwindCSS + Blade
- Laravel Breeze (session auth)
- MySQL (recommended for production)
- SQLite supported for local quickstart/tests

## Security & Architecture
- Authorization via Laravel policies + role middleware
- IDOR prevention via strict affiliate scoping
- Referral route rate-limited (`throttle:referral`)
- Full request validation for all forms
- CSRF protected forms
- IP stored as SHA-256 hash for privacy
- Transactions used for:
  - Commission creation/reversal on order status change
  - Withdrawal paid processing
  - Critical create/update flows
- Audit logs for key admin activities

## Database Schema
Includes these core tables:
- `users` (with `role`)
- `affiliates`
- `products`
- `affiliate_product_rates`
- `clicks`
- `orders`
- `order_items`
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

2. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env`

For MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=affiliate_platform
DB_USERNAME=root
DB_PASSWORD=secret
```

4. Run migrations + seed demo data
```bash
php artisan migrate --seed
```

5. Build frontend assets
```bash
npm run build
```
For development watch mode:
```bash
npm run dev
```

6. Run app
```bash
php artisan serve
```

App URL: `http://127.0.0.1:8000`

## Demo Accounts

- Admin:
  - Email: `admin@example.com`
  - Password: `password123`
- Sample affiliates are seeded with `password123`

## Running Tests

```bash
php artisan test
```

Included feature tests:
- Affiliate cannot access admin routes
- Affiliate only sees own scoped data
- Referral tracking logs click and sets cookie
- Commission creation on order status change

## Key Configuration Points

Manage in **Admin > Settings** (`/admin/settings`):

- `cookie_lifetime_days`
  - Controls referral cookie/session lifespan
  - Default: `30`

- `commission_trigger_status`
  - Order status that creates commissions
  - Allowed: `confirmed` or `completed`
  - Default: `confirmed`

- `global_commission_type` + `global_commission_value`
  - Fallback rate used when no override is found
  - Rate priority:
    1. Affiliate+Product override
    2. Product default
    3. Affiliate default
    4. Global default

- `minimum_payout`
  - Minimum withdrawal request amount

- `payout_methods_label`
  - Informational label shown to affiliates (manual payout only)

## Commission Lifecycle
- On trigger status (`confirmed`/`completed`): create commission entries per order item
- On `cancelled`/`refunded`: reverse related commission entries
- Statuses: `pending`, `approved`, `paid`, `reversed`

## Deployment Notes

### Shared Hosting (Generic)
1. Upload project files
2. Point web root to `/public`
3. Create production `.env` (set `APP_ENV=production`, `APP_DEBUG=false`, DB/mail/cache/session)
4. Run:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm run build
php artisan optimize
```
5. Ensure writable permissions for `storage/` and `bootstrap/cache/`

### VPS (Nginx/Apache)
1. Provision PHP 8.2+, Composer, Node, MySQL, web server
2. Deploy code to server
3. Configure virtual host root to `/public`
4. Set `.env` production values
5. Run same production commands above
6. Add queue worker/scheduler only if you later enable async jobs

## Notes
- Public registration is intentionally disabled; only admin can create affiliates.
- Payout processing is manual; no paid third-party dependency required.
- Admin can immediately revoke affiliate access by setting affiliate status to inactive.
