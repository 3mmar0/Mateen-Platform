# CI / deploy delta (root Laravel app)

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Task**: T003

## Current assumptions

### `.github/workflows/ci-backend.yml`

- Path filters: `backend/**`
- `defaults.run.working-directory: backend`
- Composer/tests run inside `backend/`

### `.github/workflows/deploy-vps.yml`

- Backs up `$APP_DIR/backend/.env`
- Rsync excludes `backend/.env`, `backend/storage/...`, `backend/vendor/`
- `cd $APP_DIR/backend` then `composer install` / `artisan`
- Docroot implied as `backend/public` (see `scripts/*_staging.py`)

## Required edits

| File | Change |
|------|--------|
| `ci-backend.yml` | Watch `app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `tests/`, `composer.*`, `phpunit.xml`, workflow file; remove `working-directory: backend` |
| `deploy-vps.yml` | Use `$APP_DIR/.env`; exclude root `storage/` caches + `vendor/`; `cd $APP_DIR`; migrate env from old `backend/.env` once if present |
| `scripts/deploy_staging.py`, `finish_staging.py`, `diagnose_staging.py` | Docroot `{APP_DIR}/public` not `backend/public` |

## Smoke after deploy

1. `GET /Mateen/html/login.html`  
2. `POST /api/v1/auth/login` same origin
