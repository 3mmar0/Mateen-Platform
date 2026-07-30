# Mateen API

Laravel 13 JSON API for the Mateen learning platform. Base URL: `/api/v1`.

## Setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -Force
php artisan migrate --force
php artisan db:seed --force
php artisan serve
php artisan queue:work
```

Demo accounts are `admin@mateen.test`, `support@mateen.test`, `supervisor@mateen.test`,
`teacher@mateen.test`, `student@mateen.test`, and `mateen@mateen.test`; password: `password`.

Use the Bearer token from `/api/v1/auth/login`. Set `FRONTEND_URL` for CORS. Production must
use HTTPS, MySQL 8/utf8mb4, secure cookies, and a persistent queue worker. A Supervisor/systemd
process should run `php artisan queue:work --sleep=1 --tries=3 --timeout=60`.

Cloudinary and FCM use safe stubs/logging without credentials. Configure their environment
variables before production. Firebase export commands:

```powershell
php artisan mateen:audit-migration path/to/export.json
php artisan mateen:migrate-firebase path/to/export.json --dry-run
php artisan mateen:migrate-firebase path/to/export.json
```
