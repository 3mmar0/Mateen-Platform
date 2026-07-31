# Data Model: Unify App into Single Laravel Project

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Spec**: [spec.md](./spec.md) · **Research**: [research.md](./research.md)

This feature does **not** introduce new domain tables or change Mateen business entities (those remain under `specs/001-laravel-backend/data-model.md`). The model below describes **packaging entities** used for migration, validation, and cutover.

---

## Entities

### Application project

| Attribute | Description |
|-----------|-------------|
| Root | Repository root after elevation |
| Contains | Laravel server code + `public/Mateen` UI + meta (`specs/`, `.github/`) |
| Must not be | Nested `backend/` as the real app root |

**Validation**: `composer.json`, `artisan`, and `public/index.php` exist at repository root.

---

### User interface surface (authoritative)

| Attribute | Description |
|-----------|-------------|
| Location | `public/Mateen/{html,js,css,…}` |
| Public URL prefix | `/Mateen/…` |
| Content | Live pages/styles/scripts only (no `*_backup*` as live) |
| Config | `public/Mateen/js/config.js` (or equivalent) with same-origin API base |

**Relationships**: Served by the same Application project web root as the API.

**Validation**: Representative live screens from pre-move inventory are present and reachable at prior paths.

---

### Server application

| Attribute | Description |
|-----------|-------------|
| Location | Repo-root Laravel (`app/`, `routes/`, `database/`, …) |
| API prefix | `/api/v1` (unchanged contract from `001`) |
| Domain scope | Unchanged — see `001` data model |

**Relationships**: Same origin as User interface surface after cutover.

---

### Authoritative asset

| Attribute | Description |
|-----------|-------------|
| Definition | The single live copy of a page or static file editors should change |
| Location rule | Under `public/Mateen/…` (or other `public/` path required for URL parity) |
| Non-examples | Files under `_obsolete/frontend/` |

**Validation**: For each inventoried live path, exactly one authoritative file exists under `public/`.

---

### Superseded layout

| Attribute | Description |
|-----------|-------------|
| Location | `_obsolete/frontend/` |
| Marker | `DO_NOT_EDIT.md` (required) |
| Status | Temporary; deletion is a follow-up feature/task |
| Authority | None — must not be deployed as live UI source |

**State transitions**:

```text
[standalone root front] --copy live→ [public/Mateen]
                    \--move remainder→ [_obsolete/frontend + marker]
[_obsolete/frontend] --follow-up delete→ [removed]
```

---

### Public path mapping

| Attribute | Description |
|-----------|-------------|
| Pre-cutover example | `/Mateen/html/login.html` |
| Post-cutover | Same path, served from `public/Mateen/html/login.html` |
| API | `/api/v1/…` same host as UI |

**Validation rules**:
- No path change required for normal bookmarked live pages (FR-009).
- `API_BASE_URL` for normal use is same-origin relative (`/api/v1`) (FR-012).

---

### Deploy target

| Attribute | Description |
|-----------|-------------|
| App directory | Contains root Laravel (not `…/backend`) |
| Web root | `{app}/public` |
| Env file | `{app}/.env` (root) |
| Order | Staging → production after smoke |

---

## Out of scope (explicit)

- New User/Subject/Message/etc. fields
- OpenAPI domain endpoint changes
- Firebase Auth/Firestore data model changes
- Permanent deletion of `_obsolete/frontend` (follow-up)
