## .env.example — copy to .env and fill in values
APP_NAME="FinFlow"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Mexico_City
APP_LOCALE=es

# ─── Database ───────────────────────────────────────────
# SQLite (prototype — zero config)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite

# MariaDB (staging/production — uncomment below)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=finflow_db
# DB_USERNAME=finflow_user
# DB_PASSWORD=secret
# DB_CHARSET=utf8mb4
# DB_COLLATION=utf8mb4_unicode_ci

# ─── Cache / Queue / Session ─────────────────────────────
CACHE_DRIVER=file          # Change to redis in production
QUEUE_CONNECTION=sync      # Change to redis in production
SESSION_DRIVER=file        # Change to database or redis in production
SESSION_LIFETIME=120

# Redis (production)
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

# ─── Mail ────────────────────────────────────────────────
MAIL_MAILER=log            # log|smtp|ses
MAIL_FROM_ADDRESS=no-reply@finflow.com
MAIL_FROM_NAME="${APP_NAME}"

# SMTP (production)
# MAIL_HOST=smtp.example.com
# MAIL_PORT=587
# MAIL_USERNAME=
# MAIL_PASSWORD=
# MAIL_ENCRYPTION=tls

# AWS SES (production alternative)
# MAIL_MAILER=ses
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1

# ─── MFA / 2FA ───────────────────────────────────────────
# TOTP is handled by pragmarx/google2fa — no extra config needed
# SMS OTP via Twilio (optional second MFA method)
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_FROM=

# ─── App encryption key ──────────────────────────────────
# Run: php artisan key:generate
