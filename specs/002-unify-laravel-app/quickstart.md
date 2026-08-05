# Quickstart: Unify App into Single Laravel Project

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Contracts**: [layout-and-urls.md](./contracts/layout-and-urls.md) · [environments.md](./contracts/environments.md)  
**Data model**: [data-model.md](./data-model.md)

Validation runbook after the Laravel root elevation and UI move. Not an implementation dump.

---

## Prerequisites

- PHP 8.3+, Composer, MySQL 8+
- Repo on branch `002-unify-laravel-app` (or `main` after merge) with unification applied
- Browser for UI checks
- Staging SSH access only for deploy validation (secrets out of band)

---

## Setup (local — single app)

From **repository root** (not a `backend/` subdirectory):

```bash
cp .env.example .env
# Set DB_*, APP_URL=http://127.0.0.1:8000
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Confirm layout markers:

- `artisan` and `composer.json` at repo root
- Live UI under `resources/views/` + assets in `public/Mateen/{css,js,libs}`
- `_obsolete/frontend/DO_NOT_EDIT.md` and `resources/html-source/DO_NOT_EDIT.md` present
- No nested `backend/` as the real app root
- No static files in `public/Mateen/html/` (Blade aliases only)

---

## Client config check

Open `public/Mateen/js/config.js`:

- `USE_LARAVEL_API === true`
- `API_BASE_URL === '/api/v1'` (same-origin relative)

---

## Validation scenarios

### V1 — Single project discoverability (SC-001)

1. Open the repo root in the editor.  
2. Locate a UI page under `public/Mateen/html/` and a server class under `app/`.  
**Expect**: Both found without opening a second product tree or `backend/` app root (&lt;5 minutes).

### V2 — Public path parity (FR-009, SC-002)

With `php artisan serve` running:

1. Open `http://127.0.0.1:8000/` and `http://127.0.0.1:8000/Mateen/html/home.html`  
2. Open `http://127.0.0.1:8000/Mateen/html/login.html`  
3. Open at least one staff and one student workspace under `/Mateen/html/…`  
**Expect**: 200 Blade responses; pages render.

### V3 — Same-origin API (FR-012)

1. Sign in from the login page (seeded admin).  
2. In browser network tab, confirm API calls go to `http://127.0.0.1:8000/api/v1/…` (same host).  
**Expect**: Login succeeds; no dependency on a separate absolute API host in config.

### V4 — Asset integrity spot-check (FR-005, SC-005)

Spot-check ≥10 live pages (mix of public + role pages):

**Expect**: CSS/JS/fonts/images load (no systemic 404s from the move). Relative `../css` / `../js` style links still resolve under `/Mateen/`.

### V5 — Obsolete tree non-authority (FR-007)

1. Read `_obsolete/frontend/DO_NOT_EDIT.md`.  
2. Confirm deploy/docs point at root Laravel + `public/Mateen`.  
**Expect**: Obsolete tree clearly marked; not used as live source.

### V6 — Cross-cutting edit (SC-003)

1. Change visible copy on `resources/views/pages/home.blade.php`.  
2. Change a trivial server-facing string or validation message in `app/`.  
3. Reload the running app.  
**Expect**: Both changes visible without editing `_obsolete` or `resources/html-source`.

### V7 — Staging smoke (FR-008, FR-011)

After staging deploy:

1. `GET /Mateen/html/login.html` → 200  
2. `POST /api/v1/auth/login` with seed user → success  
3. Confirm web docroot is `{APP_DIR}/public`  
**Expect**: UI and API on same staging origin; former front-only host not required.

### V8 — Production cutover check (FR-011, SC-004)

After production promote:

1. Live login URL path still `/Mateen/html/login.html` (same path shape).  
2. API same origin `/api/v1`.  
3. Previous separate front-only host is not the live UI.  
**Expect**: Single live serving path; CI/deploy docs reference root app only.

---

## Failure signals

| Symptom | Likely cause |
|---------|----------------|
| 404 on `/Mateen/html/login.html` | Assets not under `public/Mateen` or wrong docroot |
| API calls to old absolute host | `config.js` not updated to `/api/v1` |
| CI fails looking for `backend/` | Workflows not updated to root |
| Edits “don’t show” | Edited `_obsolete/frontend` instead of `public/Mateen` |

---

### V9 — Live home parity

Compare local `/` to https://mateenweb.github.io/Mateen/html/home.html (nav, hero, subjects, contact, register).

### V10 — Data migration audit

```bash
php artisan mateen:audit-migration tests/fixtures/migration-sample.json
php artisan mateen:migrate-firebase tests/fixtures/migration-sample.json --dry-run
```

## Out of scope for this quickstart

- Full domain parity suite (use `001` quickstart for API domain scenarios)
- Permanent deletion of `_obsolete/frontend`
- Operator SSH staging/production (see cutover.md)
