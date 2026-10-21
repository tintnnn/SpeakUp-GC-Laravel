# SpeakUp GC

Gordon College reporting system on Laravel and MySQL.

Copy `.env.example` to `.env` on the server. Set `APP_KEY`, the MySQL settings, and `ADMIN_EMAILS`. Do not commit `.env`.

```bash
composer install --no-dev
npm ci && npm run build
php artisan key:generate
php artisan migrate --force
```

Seed accounts only when `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`, and the CODI seed values are set in `.env`. Those values stay on the server.
