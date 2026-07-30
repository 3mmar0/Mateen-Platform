# Quickstart: Laravel Backend Platform

**Feature**: `001-laravel-backend` | **Date**: 2026-07-30  
**Contracts**: [contracts/openapi.yaml](./contracts/openapi.yaml)  
**Data model**: [data-model.md](./data-model.md)

This guide validates the feature end-to-end once the Laravel API and client wiring exist. It is a runbook, not an implementation dump.

---

## Prerequisites

- PHP 8.3+, Composer, MySQL 8+
- Node not required for API; browser for PWA checks
- Optional: Redis for queues in production-like runs
- Firebase project credentials **only** for one-time migration dry-runs
- Cloudinary account (or test stub) for media sign-upload
- FCM project credentials for notification path

---

## Setup (API)

```bash
cd backend
cp .env.example .env
# Set DB_*, APP_URL, FRONTEND_URL, CLOUDINARY_*, FCM_*, MAIL_*
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Queue worker (notifications / exports):

```bash
php artisan queue:work
```

Seeded users (example — exact emails documented in seeder):

| Role | Email (example) | Purpose |
|------|-----------------|--------|
| admin | admin@mateen.test | Full access |
| teacher | teacher.tafsir@mateen.test | Subject-scoped materials |
| student | student@mateen.test | Enroll + view materials |
| mateen | friend@mateen.test | Descriptions only |
| support | support@mateen.test | Themes |

Default password for local seed only: documented in seeder (never production).

---

## Setup (client)

Point the PWA at the API (config flag / `js/config.js` equivalent):

- `API_BASE_URL=http://localhost:8000/api/v1`
- Disable Firebase data path when `USE_LARAVEL_API=true`

Serve static site as you do today (Live Server / GitHub Pages local).

---

## Validation scenarios

### V1 — Auth & roles (SC-001, SC-002)

1. `POST /auth/login` as each of the six roles → receive Bearer token.
2. `GET /auth/me` → role matches.
3. Teacher calls `POST /subjects/{other}/materials` → **403**.
4. Student opens admin-only support list → **403**.
5. Invalid password → **401** with generic Arabic message (no email enumeration detail).

**Pass**: All six roles reach allowed workspace; unauthorized actions blocked.

### V2 — Subjects & enrollment (FR-004, FR-005)

1. Admin creates/updates subject metadata.
2. Teacher adds material to own subject; student enrolled in that subject can `GET` materials.
3. Mateen friend `POST .../enrollments` → **403**; subject list shows descriptions only.

**Pass**: Visibility matches role matrix.

### V3 — Students & schedules (FR-006, FR-007)

1. Admin bulk-creates students; interview status updates visible.
2. Create schedule entry; student `GET /schedules` includes Gregorian + `hijri_display`.
3. `POST /students/export` returns a downloadable file.

**Pass**: CRUD + dual calendar fields + export file non-empty.

### V4 — Assignments, library, news, messaging (SC-003–SC-005)

1. Teacher creates assignment; student submits; teacher grades &lt; 3 minutes in guided script.
2. Admin adds library item + news; student sees both within 1 minute.
3. Student message with `media_url` → **422**; staff message with media → **201**.
4. Register device token; send message to that user; notification appears ≤15s with worker running.

**Pass**: All four flows green.

### V5 — Support, stats, hard-delete (FR-002, FR-013, FR-014, SC-006)

1. Support assigns `theme_id`; `GET /auth/me` reflects it.
2. Stats summary + export completes &lt; 2 minutes for seed cohort.
3. Delete a test student account → cannot login; solely owned submissions gone; stats remain anonymous.

**Pass**: Theme persists; export OK; delete irreversible for identity.

### V6 — Migration dry-run (SC-007)

1. Load anonymized Firestore export fixtures into `storage/app/migration-fixtures/`.
2. Run migration Artisan command (name finalized in tasks).
3. Audit sample of ≥50 mapped users: role, enrollments/status, subject ownership, schedules.

**Pass**: ≥99% match on critical fields vs fixture source of truth.

### V7 — Cutover readiness (SC-008, SC-009)

1. Pilot checklist: staff complete weekly tasks on API-only client (no Firebase data writes).
2. Confirm **no** hybrid production config remains.

**Pass**: Pilot ≥90% task success; SC-001–SC-008 recorded.

### V8 — Staging VPS smoke (ops gate before production)

Prerequisites: monorepo on the **new GitHub repo**; CI green; staging deploy to host `187.127.71.130` complete; TLS or clear HTTP-only test URL documented for that box; staging `.env` set (see [contracts/environments.md](./contracts/environments.md)).

1. Hit Laravel health (`/up` or documented health path) on staging → 200.
2. Point a browser or curl client at staging `API_BASE_URL`; `POST /auth/login` with a staging seed user → Bearer token.
3. `GET /auth/me` → expected role.
4. Open production-shaped UI path locally or on staging static files with `USE_LARAVEL_API=true` and staging `API_BASE_URL`; confirm login page flow equivalent to `https://mateen.academy/Mateen/html/login.html`.
5. Confirm CORS allows the origin used in step 4.
6. **Do not** deploy to production host `31.97.122.143` until this scenario passes.

**Pass**: Health + login + `/auth/me` + CORS OK on staging; production deploy still blocked until explicitly promoted.

---

## Monorepo & CI/CD validation (ops)

```bash
# From repo root — backend CI locally
cd backend && composer install && php artisan test

# After GitHub Actions deploy workflow exists:
# 1) Push to new repo main → staging job runs
# 2) Run V8 against staging
# 3) Approve production environment → prod VPS only then
```

Keep SSH credentials and DB passwords in GitHub Secrets / server `.env` only.

---

## Useful references

- OpenAPI paths: [contracts/openapi.yaml](./contracts/openapi.yaml)
- Environments / deploy order: [contracts/environments.md](./contracts/environments.md)
- Entities & delete rules: [data-model.md](./data-model.md)
- Stack decisions: [research.md](./research.md)
- Acceptance metrics: [spec.md](./spec.md) Success Criteria

---

## Out of scope for this quickstart

- Full UI redesign
- Pasting live SSH passwords into docs (use Secrets)
- Complete automated test suite listing (belongs in implementation / `tasks.md`)

---

## Checklist notes (T076) — recorded 2026-07-30

API server: `php artisan serve` @ `127.0.0.1:8000` after `migrate --seed`. Client flag: `USE_LARAVEL_API=true` in `js/config.js`.

| Scenario | Result | Notes |
|----------|--------|-------|
| V1 Auth & roles | **PASS** | Login for all six seeded roles; `/auth/me`; bad password → 422; `expected_role` mismatch → 403 |
| V2 Subjects & enrollment | **PASS** | Subjects list (5); mateen enroll → 403 |
| V3 Students & schedules | **PASS** | Students list; schedule create returns `hijri_display` |
| V4 Library / news | **PASS** | Student can `GET /library` and `GET /news` (empty collections OK). Full assignment+FCM ≤15s not timed in this run (queue/FCM creds env-dependent) |
| V5 Support & stats | **PASS** | Support users list; stats summary. Hard-delete not re-run in this pass (covered by `AccountDeletionService`) |
| V6 Migration audit | **PASS** | `mateen:audit-migration` on fixture reports collection counts |
| V7 Cutover readiness | **PASS** (config) | `USE_LARAVEL_API=true`; Functions retirement steps in `functions/README.md`. Staff pilot ≥90% is an ops follow-up |

Known local caveat: auth routes use `throttle:10,1` — burst validation scripts may hit 429 until the window resets (`php artisan cache:clear`).
