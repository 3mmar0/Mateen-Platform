# Environment & Deploy Contracts

**Feature**: `001-laravel-backend` | **Date**: 2026-07-30  
**Related**: [openapi.yaml](./openapi.yaml) · [quickstart.md](../quickstart.md) · [research.md](../research.md)

This contract describes **where** the monorepo runs. API request/response shapes remain in OpenAPI. **No passwords, SSH keys, or API tokens belong in this file.**

---

## Repository

| Item | Contract |
|------|----------|
| Layout | Single monorepo: static client + `backend/` |
| Deploy source | https://github.com/3mmar0/Mateen-Platform |
| Staging API (live) | `http://187.127.71.130/api/v1` — smoke login OK 2026-07-30 |
| Staging UI | `http://187.127.71.130/Mateen/html/login.html` |
| Staging seed | `admin@mateen.test` / `password` (and other `<role>@mateen.test`) |
| Default branch | `main` |
| Legacy remotes | Existing Mateen remotes retained until cutover; Functions workflow retires after Laravel-only prod |

---

## Environments

| Environment | Host / URL | Role | Deploy gate |
|-------------|------------|------|-------------|
| Local | `http://127.0.0.1:8000` API; static via Live Server | Dev | Manual |
| Staging | VPS `187.127.71.130` (HTTPS when TLS configured) | Test deploy **first** | CI green + auto or `workflow_dispatch` |
| Production API | VPS `31.97.122.143` | Live API **after** staging smoke | Staging smoke pass + environment approval |
| Production web | `https://mateen.academy/Mateen/…` (login: `/Mateen/html/login.html`) | Live static UI | Config flip of `API_BASE_URL` / CORS |

---

## Client configuration contract

File: `js/config.js` (or env-injected equivalent at build/deploy time).

| Variable | Staging | Production |
|----------|---------|------------|
| `USE_LARAVEL_API` | `true` | `true` (cutover); `false` only for emergency rollback |
| `API_BASE_URL` | `https://<staging-api-host>/api/v1` | `https://<prod-api-host>/api/v1` |

Must not leave production clients pointing at staging after promote.

---

## Server environment contract (`backend/.env`)

Required keys (values only on server / GitHub Secrets):

- `APP_ENV`, `APP_DEBUG` (`false` on staging/prod)
- `APP_URL` — public API base (scheme + host)
- `FRONTEND_URL` / `CORS_ALLOWED_ORIGINS` — include `https://mateen.academy` (and staging frontend origin if used)
- `DB_*` — MySQL 8 on that VPS (or managed DB)
- `CLOUDINARY_*`, FCM credentials as needed for parity tests
- `MAIL_*` if password-reset emails are exercised

---

## CI/CD contract

| Workflow | Trigger | Must |
|----------|---------|------|
| Backend CI | PR + push `main` | `composer` + Pest in `backend/` exit 0 |
| Deploy staging | Push `main` and/or manual | SSH to staging; install; migrate; health check |
| Deploy production | Manual / approved environment | Same steps targeting prod host; only after staging smoke |

Health check minimum: `GET /api/v1` or `GET /up` (Laravel) returns success; then `POST /auth/login` + `GET /auth/me` with a staging seed user.

---

## Deploy order (normative)

1. Push monorepo to new GitHub repo; configure Secrets (`SSH_HOST_STAGING`, `SSH_USER`, `SSH_KEY` or equivalent — **not** passwords in git).
2. Deploy → **staging** (`187.127.71.130`).
3. Run [quickstart](../quickstart.md) scenarios V1 + V8 against staging.
4. Deploy → **production** (`31.97.122.143`).
5. Point production client `API_BASE_URL` at prod API; confirm CORS for `mateen.academy`.
