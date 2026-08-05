# Contract: Layout & Public URLs

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Related**: [environments.md](./environments.md) · [data-model.md](../data-model.md) · [research.md](../research.md)

Normative packaging and URL contracts for the unified application. Domain API shapes remain in `specs/001-laravel-backend/contracts/openapi.yaml`.

---

## Repository layout contract

| Item | Required |
|------|----------|
| Application root | Repository root |
| Laravel markers | `artisan`, `composer.json`, `app/`, `public/index.php` at root |
| Nested `backend/` app | **Must not** be the real application root after this feature |
| Authoritative UI | Blade in `resources/views/` + static assets `public/Mateen/{css,js,libs,…}` |
| Obsolete front | `_obsolete/frontend/**` + `_obsolete/frontend/DO_NOT_EDIT.md` |
| Spec / tooling | `specs/`, `.specify/`, `.github/` may remain at root |

---

## Public URL contract (UI)

| Pre-unification (reference) | Post-Blade (required) |
|-----------------------------|------------------------|
| `/Mateen/html/<page>.html` | Same path → `routes/web.php` Blade action (no static file in `public/Mateen/html/`) |
| `/` | Blade home (`mateen.home`) |
| `/Mateen/js/...` | Same → `public/Mateen/js/...` |
| `/Mateen/css/...` | Same → `public/Mateen/css/...` |
| Other live `/Mateen/...` assets | Same path under `public/Mateen/...` |

**Rules**:
- Live bookmarked paths MUST NOT require a new path or redirect for normal use.
- Backup / unused pages (`*_backup*`, etc.) are not required as Blade routes.
- CSS/JS are **not** Vite-bundled for normal use (existing `/Mateen/css` + `/Mateen/js`).

---

## Public URL contract (API)

| Item | Contract |
|------|----------|
| API prefix | `/api/v1` on the **same origin** as the UI |
| OpenAPI | Unchanged from `001` unless a separate feature amends it |
| Health | `GET /up` (Laravel) and/or existing API smoke login |

---

## Client config contract

File: `public/Mateen/js/config.js` (authoritative after move).

| Variable | Normal use (local / staging / production) |
|----------|-------------------------------------------|
| `USE_LARAVEL_API` | `true` |
| `API_BASE_URL` | `'/api/v1'` (same-origin relative; **not** a separate absolute API host) |

Optional: derive from `window.location.origin + '/api/v1'` if needed for exotic embeds — must still be same site for normal Mateen use.

Firebase web config may remain **only** for FCM push (per `001`); it is not the data API base.

---

## Obsolete tree contract

| Item | Contract |
|------|----------|
| Path | `_obsolete/frontend/` |
| Marker | `DO_NOT_EDIT.md` stating non-authoritative + follow-up deletion |
| Deploy | MUST NOT be published as the live document root or live UI source |
| Edits | Product changes go only to `public/Mateen/` (+ Laravel server code) |

---

## Web server contract

| Item | Contract |
|------|----------|
| Document root | `{app}/public` |
| Serves | Blade via `index.php` for `/` and `/Mateen/html/**`; static `/Mateen/{css,js,libs,images}`; `/api/v1/**` |
| Former front-only host | Not live UI after production cutover |
