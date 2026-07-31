# Staging / production cutover checklist

**Feature**: `002-unify-laravel-app` | **Tasks**: T031, T032  
**Date**: 2026-07-30

Automation and config are updated for root Laravel + `public/` docroot. Remote cutover requires GitHub Secrets / SSH and merging this branch to `main`.

## Pre-flight (done in repo)

- [x] Laravel at repository root (`artisan`, `app/`, no `backend/` app)
- [x] Live UI at `public/Mateen/`
- [x] `API_BASE_URL = '/api/v1'` in `public/Mateen/js/config.js`
- [x] `_obsolete/frontend/DO_NOT_EDIT.md` present
- [x] `.github/workflows/ci-backend.yml` and `deploy-vps.yml` updated for root app
- [x] `scripts/*staging*.py` docroot → `{APP_DIR}/public`

## Staging (operator)

1. Merge/push branch so staging deploy can clone the unified tree  
2. Ensure Nginx `root` is `$APP_DIR/public` (re-run finish/deploy script if needed)  
3. Smoke:
   - `GET http://187.127.71.130/Mateen/html/login.html` → 200  
   - `POST http://187.127.71.130/api/v1/auth/login` with seed user → success  
4. Confirm obsolete tree is **not** the document root  

**Status 2026-07-30**: Staging unified deploy verified — UI/API/up all 200 on IP and `https://mateen.ammarelgndy.cloud`.

## Production (operator)

1. After staging smoke passes, `workflow_dispatch` production deploy  
2. Confirm live `/Mateen/html/login.html` on unified origin  
3. Confirm same-origin `/api/v1`  
4. Retire previous separate front-only host as live UI source  

## Local smoke (dev machine)

See `asset-smoke.md` and `php artisan serve` checks recorded with implementation.
