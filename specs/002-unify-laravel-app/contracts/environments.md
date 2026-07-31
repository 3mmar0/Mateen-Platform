# Environment & Deploy Contracts (Unified App)

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Related**: [layout-and-urls.md](./layout-and-urls.md) · [quickstart.md](../quickstart.md)  
**Supersedes for packaging**: split `backend/` + root-static assumptions in `specs/001-laravel-backend/contracts/environments.md` (domain/API content in `001` OpenAPI still applies).

**No passwords, SSH keys, or API tokens belong in this file.**

---

## Repository

| Item | Contract |
|------|----------|
| Layout | Single Laravel app at **repository root** + `public/Mateen` UI |
| Deploy source | https://github.com/3mmar0/Mateen-Platform (or current remote) |
| Default branch | `main` |
| App on server | Clone/rsync root app; **not** `…/backend` as app root |
| Env file | `{APP_DIR}/.env` at application root |

---

## Environments

| Environment | Host / URL | Role | Deploy gate |
|-------------|------------|------|-------------|
| Local | `http://127.0.0.1:8000` (artisan serve) — UI `/Mateen/html/…`, API `/api/v1` | Dev | Manual |
| Staging | VPS `187.127.71.130` — same origin UI+API, docroot `public/` | Test **first** | CI green + deploy |
| Production | Live Mateen host / VPS after staging smoke — same origin UI+API | Live | Staging smoke + approval |

### Reference smoke URLs (post-unification)

| Check | Staging example |
|-------|-----------------|
| Login page | `http://187.127.71.130/Mateen/html/login.html` |
| API | `http://187.127.71.130/api/v1` |
| Seed login | `admin@mateen.test` / local-seed password (server only) |

Production academy URLs keep the same `/Mateen/html/…` path shape on the unified origin after cutover.

---

## Client configuration contract

Authoritative file: `public/Mateen/js/config.js`

| Variable | Staging | Production | Local |
|----------|---------|------------|-------|
| `USE_LARAVEL_API` | `true` | `true` | `true` |
| `API_BASE_URL` | `'/api/v1'` | `'/api/v1'` | `'/api/v1'` |

Must not require a second front-only absolute API host for normal use.

---

## Server environment contract (`.env` at repo/app root)

Required keys (values only on server / GitHub Secrets):

- `APP_ENV`, `APP_DEBUG` (`false` on staging/prod)
- `APP_URL` — public origin (scheme + host) for the **unified** site
- `DB_*`, mail/Cloudinary/FCM as required by `001`
- CORS: same-origin UI reduces need for broad frontend origins; keep explicit allows only if a legacy secondary origin remains during transition

---

## Web server (Nginx)

Document root **must** be `{APP_DIR}/public`.

Example (simplified):

```nginx
server {
    listen 80;
    root /var/www/mateen/public;
    index index.php index.html;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }
}
```

`/Mateen/**` is served as static files under `public/Mateen/**`. `/api/v1/**` goes through `public/index.php`.

---

## CI/CD contract

| Workflow | Trigger | Must |
|----------|---------|------|
| App CI (former backend CI) | PR + push `main` | `composer` + tests at **repo root** exit 0 |
| Deploy staging | Push `main` and/or manual | SSH; rsync root app; `composer install`; `migrate`; smoke UI path + API login |
| Deploy production | Manual / approved environment | Same against prod; only after staging smoke |

Deploy scripts MUST:

- Use `{APP_DIR}/.env` (not `{APP_DIR}/backend/.env`)
- Exclude `vendor/`, `storage` caches, `.env` from destructive sync appropriately
- Set web server document root to `{APP_DIR}/public`

Health check minimum:

1. `GET /up` or API base success  
2. `GET /Mateen/html/login.html` returns 200  
3. `POST /api/v1/auth/login` with seed user succeeds  

---

## Deploy order (normative)

1. Land unification on branch; CI green at root.  
2. Deploy → **staging**; run [quickstart](../quickstart.md) scenarios.  
3. Confirm obsolete tree is not the live docroot.  
4. Deploy → **production**; verify `/Mateen/html/login.html` and same-origin API.  
5. Confirm previous separate front-only host is no longer the live UI source.
