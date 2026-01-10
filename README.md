# SOLESOURCE Web Application

**Live Demo:** http://dev.art2cart.shop

![Live Preview](assets/img/Screenshot.png)

SoleSource is a PHP/MySQL e-commerce web application focused on premium sneakers.  
It delivers a complete storefront experience with user accounts, checkout, admin management, and an optional AI-powered shopping assistant.

---

## Table of Contents
- Overview
- Core Features
- Tech Stack
- Project Structure
- Integrations & Data
- Vouchers & Discounts
- Local Development
- Environment Variables
- Database Setup
- Running the Application
- Admin Panel
- Background & Utility Scripts
- Testing & QA
- Deployment Notes
- Troubleshooting
- Local Development (Windows / XAMPP)
- Included Helper Files
- Security Notes

---

## Overview
SoleSource provides a full-featured sneaker e-commerce platform with a clean UI, structured backend, and modular architecture.  
It supports both customer-facing shopping flows and administrative operations, making it suitable for real-world deployment and extension.

---

## Core Features
- Product catalog with search, filters, brands, and categories
- Product detail pages with size selection and recommendations
- Shopping cart and checkout flow with delivery and payment selection
- User accounts with profile, wishlist, saved addresses, and order history
- Voucher codes issued via API/SMS that checkout, receipts, PayPal, and admin views now honor end-to-end
- AI assistant entrypoint for contextual shopping help
- Admin dashboard for managing products, users, orders, and settings

---

## Tech Stack
**Backend**
- PHP 8+
- MySQL (mysqli)

**Frontend**
- Bootstrap 5
- Bootstrap Icons
- Tom Select

**Client-Side**
- Vanilla JavaScript (Fetch API, Bootstrap components)

**Dependencies**
- Composer
- PHPMailer

---

## Project Structure
```

/
├── index.php              # Public entry point
├── pages/                 # Shop, product, cart, checkout, profile, auth
├── includes/              # Layout, auth, products, cart, orders, AI, mail
├── admin/                 # Admin dashboard and CRUD tools
├── assets/                # CSS, JS, images, SVGs, favicon
├── data/                  # JSON seed data (brands/products)
├── sql/                   # Database schemas and backups
├── scripts/               # Background and utility scripts
├── vendor/                # Composer dependencies
├── dev/                   # Development helpers

````

---

## Integrations & Data
- **Payments:** PayPal (sandbox or live via environment variables)
- **Email:** SMTP via PHPMailer
- **AI Assistant:** Configurable provider keys and endpoint
- **Optional:** SMS gateway integration

---

## Vouchers & Discounts
- Order records persist `subtotal_amount`, `voucher_code`, `voucher_discount`, and `voucher_discount_type`. Run the latest migration (`sql/migrations/2026-01-17-orders-add-voucher-columns.sql`) after pulling new code to add these columns.
- Checkout, receipts, PayPal capture, and admin/customer order views automatically display voucher usage once the migration is applied.
- Public APIs plus SMS intake are documented in [docs/voucher-api.md](docs/voucher-api.md); release notes live in [docs/voucher-change-notes.md](docs/voucher-change-notes.md).
- Each collaborator gets an API key. All voucher endpoints expect Bearer auth and kebab-case JSON payloads.

### Quick voucher issuance (PowerShell)
```powershell
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12
$headers = @{ Authorization = 'Bearer <API_KEY>'; 'Content-Type' = 'application/json' }
$body = @{ 'student-id' = 'sandbox-checkout'; 'discount-type' = 'percent'; 'discount-value' = 12 } | ConvertTo-Json
Invoke-RestMethod -Uri 'https://dev.art2cart.shop/api/vouchers/generate.php' -Headers $headers -Method Post -Body $body
```
> If you are hitting the development endpoint behind a self-signed cert, temporarily trust the certificate (recommended) or set `ServerCertificateValidationCallback` in your shell session while testing.

---

## Local Development
### Prerequisites
- PHP 8+
- MySQL 5.7+ or 8
- Composer
- Apache / Nginx or PHP built-in server

### Install Dependencies
```sh
composer install
````

### Configure Environment

```sh
cp .env.example .env
```

Create a MySQL database and import the schema (see Database Setup).

---

## Environment Variables

Configure the `.env` file with the following values:

**Database**

* `DB_HOST`
* `DB_PORT`
* `DB_NAME`
* `DB_USER`
* `DB_PASS`

**PayPal**

* `PAYPAL_CLIENT_ID`
* `PAYPAL_CLIENT_SECRET`
* `PAYPAL_BASE_URL` (sandbox or live)

**Mail**

* `MAIL_HOST`
* `MAIL_PORT`
* `MAIL_USER`
* `MAIL_PASS`
* `MAIL_FROM_EMAIL`
* `MAIL_FROM_NAME`

**AI**

* Provider API key(s)
* API endpoint URL

---

## Database Setup

* SQL schemas and backups are located in the `/sql` directory
* Common import command:

```sh
mysql -u <user> -p <db_name> < sql/complete.sql
```

* All required indexes and foreign keys are included in the SQL dumps

---

## Running the Application

### PHP Built-in Server (Development)

```sh
php -S localhost:8000 -t .
```

### Apache / Nginx (Production-Style)

* Set the document root to the project root
* Ensure PHP-FPM or mod_php is configured
* Session and temporary directories must be writable

---

## Admin Panel

* Access via `/admin/index.php`
* Requires an authenticated admin account
* Admin users can be created manually or seeded in the database

---

## Background & Utility Scripts

Run manually or schedule via cron / Task Scheduler:

```sh
php scripts/auto-advance-orders.php
php scripts/worker-email.php
```

---

## Testing & QA

* Browse products and validate filters
* Add items to cart and complete checkout
* Test PayPal sandbox transactions
* Verify SMTP email delivery
* Check responsive layout on mobile and tablet devices

---

## Deployment Notes

* Never commit `.env` or sensitive credentials
* Use HTTPS in production
* Configure secure session cookies
* Schedule background scripts if using automated workflows

---

## Troubleshooting

**Blank pages**

* Enable `display_errors` in development
* Check PHP error logs

**Database issues**

* Verify credentials and user permissions
* Ensure the mysqli extension is enabled

**PayPal**

* Confirm sandbox vs live credentials
* Verify base URL and webhook settings if enabled

**Email not sending**

* Recheck SMTP credentials and ports
* Enable PHPMailer debug logging

**AI assistant issues**

* Validate API keys and endpoint
* Ensure outbound network access is allowed

---

# Local Development (Windows / XAMPP)

A practical guide for running SoleSource locally on Windows using XAMPP.

---

## Prerequisites

* XAMPP (Apache + MySQL)
* Git
* Composer (if `vendor/` is missing)

---

## Quick Start

```powershell
git clone <repo-url> D:\xampp\htdocs\solesource
cd D:\xampp\htdocs\solesource
composer install
```

Import the database via phpMyAdmin and configure the environment variables.

Open in browser:

```
http://localhost/solesource/
```

---

## Recommended `.htaccess`

Place this file in the project root:

```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

RewriteRule ^(.+)$ index.php?page=$1 [L,QSA]
```

---

## Local Virtual Host (Recommended)

Run the project at `http://solesource.local`:

```apache
<VirtualHost *:80>
  ServerName solesource.local
  DocumentRoot "D:/xampp/htdocs/solesource"
  <Directory "D:/xampp/htdocs/solesource">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

Add to `hosts` file:

```
127.0.0.1 solesource.local
```

Restart Apache.

---

## Fixing Asset 404 Errors

If the project is served from `/solesource/` and uses root-relative paths:

```html
<base href="/solesource/">
```

Alternatively, use a virtual host for cleaner URLs.

---

## PHP Built-in Server (No Apache)

Create `router.php` in the project root:

```php
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && file_exists($file)) {
    return false;
}

require __DIR__ . '/index.php';
```

Run:

```powershell
php -S localhost:8000 router.php
```

---

## Included Helper Files

* `router.php` – Router for PHP built-in server
* `dev/vhosts-example.conf` – Example Apache virtual hosts
* `dev/add-hosts.ps1` – PowerShell helper for editing the hosts file

---

## Security Notes

* Always validate or whitelist the `page` parameter in `index.php`
* Protect admin routes with authentication and roles
* Never expose credentials or secrets in version control

```