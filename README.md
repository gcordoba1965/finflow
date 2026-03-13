# FinFlow — 50·30·20 Personal Finance App
## PHP 8.3 · Laravel 11 · Fortify + Sanctum · MFA · SQLite / MariaDB

---

## 📁 Project Structure

```
finflow/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php       ← Consumer dashboard
│   │   │   ├── TransactionController.php     ← Add/edit/delete expenses & income
│   │   │   ├── IncomeController.php          ← Manage income sources
│   │   │   ├── SavingsGoalController.php     ← Savings goals
│   │   │   └── Admin/
│   │   │       ├── DashboardController.php   ← Admin overview + stats
│   │   │       └── UserController.php        ← Create/manage client accounts
│   │   ├── Middleware/
│   │   │   ├── EnsureAdmin.php               ← Blocks non-admins from /admin
│   │   │   ├── EnsureMfaVerified.php         ← Enforces 2FA session
│   │   │   ├── EnsureAccountActive.php       ← Blocks deactivated accounts
│   │   │   └── LogActivity.php               ← Auto-logs write actions
│   │   └── Requests/                         ← Form validation (StoreTransactionRequest etc.)
│   ├── Models/
│   │   ├── User.php                          ← Sanctum + TwoFactorAuthenticatable
│   │   ├── Transaction.php                   ← expense | income
│   │   ├── IncomeSource.php                  ← monthly/weekly/annual income
│   │   ├── Budget.php                        ← Monthly 50/30/20 snapshot
│   │   ├── SavingsGoal.php                   ← Savings targets
│   │   └── AuditLog.php                      ← Activity log
│   ├── Services/
│   │   └── BudgetService.php                 ← Core 50/30/20 calculation engine
│   ├── Notifications/
│   │   ├── BudgetExceededNotification.php    ← Email alert when limit exceeded
│   │   └── WelcomeNotification.php           ← Sent by admin when creating account
│   └── Policies/                             ← Authorization (who owns what)
├── database/
│   ├── migrations/                           ← All 5 table schemas
│   └── seeders/DatabaseSeeder.php            ← Admin + 6 sample consumers
├── resources/views/
│   ├── layouts/app.blade.php                 ← Sidebar shell
│   ├── dashboard/index.blade.php             ← Consumer dashboard + charts
│   ├── components/transaction-modal.blade.php← Add expense/income modal
│   └── admin/
│       └── clients/
│           ├── index.blade.php               ← Client list + create modal
│           └── show.blade.php                ← Client detail + charts
├── routes/web.php                            ← All routes with middleware groups
├── config/fortify.php                        ← MFA + rate limiting config
├── .env.example                              ← SQLite (default) or MariaDB
└── composer.json                             ← All dependencies
```

---

## 🚀 Quick Start

### 1. Install Laravel & dependencies

```bash
composer create-project laravel/laravel finflow
cd finflow

# Auth + MFA packages
composer require laravel/sanctum laravel/fortify
composer require pragmarx/google2fa-laravel bacon/bacon-qr-code

# Admin panel (Filament)
composer require filament/filament

# Dev tools
composer require --dev laravel/telescope
```

### 2. Copy project files into place
Copy all files from this archive into your `finflow/` directory.

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate

# SQLite (prototype - default, no extra setup needed)
touch database/database.sqlite

# MariaDB (production) — edit .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=finflow_db
# DB_USERNAME=finflow_user
# DB_PASSWORD=your_password
```

### 4. Publish & configure Fortify

```bash
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
# This creates config/fortify.php and app/Actions/Fortify/
```

### 5. Run migrations + seed

```bash
php artisan migrate
php artisan db:seed

# Seeded accounts:
# Admin:    admin@finflow.com   / Admin1234!
# Consumer: maria@email.com    / User1234!
# Consumer: carlos@email.com   / User1234!
# (+ 4 more sample users)
```

### 6. Install Filament admin panel

```bash
php artisan filament:install --panels
php artisan make:filament-user
# → Use: admin@finflow.com / Admin1234!
```

### 7. Install & build frontend assets

```bash
npm install
npm install -D tailwindcss @tailwindcss/forms autoprefixer
npx tailwindcss init -p
npm run dev
```

### 8. Start dev server

```bash
php artisan serve
# → http://localhost:8000
```

---

## 🔐 Authentication Flow

```
User visits /          →  redirected to /login (if not auth)
POST /login            →  validate email + password
                           └── if MFA enabled → redirect to /two-factor-challenge
POST /two-factor-challenge → validate TOTP code (pragmarx/google2fa)
                              └── session: mfa_verified = true
                              └── issue Sanctum token
                              └── redirect to dashboard

Admin visits /admin    →  middleware: auth + verified + ensure.mfa + admin
Consumer visits /      →  middleware: auth + verified + ensure.mfa + ensure.active
```

---

## 📊 50/30/20 Engine (BudgetService)

```php
$service = app(BudgetService::class);

// Get limits
$limits = $service->calculate(4500.00);
// → ['needs' => 2250.00, 'wants' => 1350.00, 'savings' => 900.00]

// Full dashboard summary
$summary = $service->getDashboardSummary($user, '2026-03');
// → income, balance, spent per category, progress %, alerts

// 6-month trend
$trend = $service->getSpendingTrend($user, 6);
```

---

## 🗄️ Key Artisan Commands

```bash
# Run migrations fresh + reseed
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# Queue worker (for email notifications)
php artisan queue:work

# Telescope (dev debugging)
php artisan telescope:install
php artisan migrate

# Run tests
php artisan test
```

---

## 🌐 Route Summary

| Method | URI | Middleware | Action |
|--------|-----|------------|--------|
| GET | / | auth, mfa | Dashboard |
| GET/POST | /transactions | auth, mfa | List / Create |
| PUT/DELETE | /transactions/{id} | auth, mfa, policy | Update / Delete |
| POST | /transactions/import-csv | auth, mfa | CSV Import |
| GET/POST | /income | auth, mfa | Income sources |
| GET/POST | /goals | auth, mfa | Savings goals |
| GET | /admin | auth, mfa, admin | Admin dashboard |
| GET | /admin/clients | auth, mfa, admin | Client list |
| POST | /admin/clients | auth, mfa, admin | Create client account |
| GET | /admin/clients/{id} | auth, mfa, admin | Client detail + charts |
| PATCH | /admin/clients/{id}/toggle-status | auth, mfa, admin | Activate/deactivate |
