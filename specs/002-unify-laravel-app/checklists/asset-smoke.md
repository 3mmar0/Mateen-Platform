# Asset / Blade smoke results (local)

**Feature**: `002-unify-laravel-app` | **Date**: 2026-08-05  
**Server**: `php artisan serve` / PHPUnit feature tests

| Path | Status |
|------|--------|
| / | 200 Blade home |
| /Mateen/html/home.html | 200 Blade alias |
| /Mateen/html/login.html | 200 |
| /Mateen/html/admin.html | 200 |
| /Mateen/html/student.html | 200 |
| /Mateen/html/messages.html | 200 |
| /Mateen/html/schedule.html | 200 |
| /Mateen/html/library.html | 200 |
| /Mateen/html/news.html | 200 |
| /Mateen/html/supervisor.html | 200 |
| /Mateen/html/support.html | 200 |
| /Mateen/css/login.css | 200 static |
| /Mateen/js/config.js | 200 static |
| /Mateen/js/api.js | 200 static |
| /Mateen/logo.png | 200 static |
| /Mateen/manifest.json | 200 static |
| /up | 200 |

Config check: `API_BASE_URL = '/api/v1'` present (same-origin).

Layout markers: `artisan` at root; `backend/` absent; Blade views in `resources/views/`; `public/Mateen/html/` empty of static pages.
