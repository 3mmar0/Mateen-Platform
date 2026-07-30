# Research: Laravel Backend Platform

**Feature**: `001-laravel-backend` | **Date**: 2026-07-30

All Technical Context unknowns resolved below. Format: Decision / Rationale / Alternatives.

---

## R1 — Application framework & PHP version

**Decision**: Laravel 12.x (or current stable at scaffold) on PHP 8.3+.

**Rationale**: Stakeholder-chosen stack; mature auth, queues, policies, migrations, and JSON API patterns; strong ecosystem for Sanctum, Excel/PDF, and notifications.

**Alternatives considered**:
- Laravel Lumen — lighter but weaker ecosystem for full-domain apps; not worth the savings.
- Symfony / custom PHP — more boilerplate for the same outcome.
- Keep Firebase — rejected by product direction.

---

## R2 — API authentication for existing PWA client

**Decision**: Laravel Sanctum **personal access tokens** (Bearer tokens) stored by the client after login; CSRF SPA cookie mode optional later if same-site hosting is used.

**Rationale**: Current client is multi-page static HTML/JS (not a same-origin Blade SPA by default). Token auth maps cleanly from Firebase ID tokens → `Authorization: Bearer`. Works across GitHub Pages → API host origins with CORS.

**Alternatives considered**:
- Passport (OAuth2) — heavier than needed for first-party client.
- Session cookies only — fragile across separate static hosting origins.
- JWT custom — reinvent Sanctum features.

---

## R3 — Primary database

**Decision**: MySQL 8+ (InnoDB, utf8mb4) as the system of record.

**Rationale**: Widely available on shared/VPS hosting common for regional deployments; excellent Laravel support; utf8mb4 covers Arabic text.

**Alternatives considered**:
- PostgreSQL — excellent alternative; switchable via Laravel if ops prefer it; not required for v1.
- Continue Firestore — conflicts with Laravel relational model and cutover goal.
- SQLite — fine for local demos only, not production concurrency.

---

## R4 — Authorization model (six roles)

**Decision**: Single `role` enum column on `users` (values: `admin`, `support`, `supervisor`, `teacher`, `student`, `mateen`) plus Laravel **Policies** / Gates mirroring current Mateen permission matrix. Teachers additionally have `subject_id` (or slug) for subject-scoped writes.

**Rationale**: Spec requires one primary role; existing Firestore `users.role` already works this way. Policies keep authorization testable per FR-003 / SC-002.

**Alternatives considered**:
- Spatie multi-role/permission packages — useful later if roles proliferate; overkill while single-role is mandatory.
- Casbin / external PDP — unnecessary complexity.

**Role mapping note**: Client historically uses `mateen` for “أصدقاء متين” and `student` for “بنات متين” learners — preserve these codes for migration compatibility; UI labels stay Arabic in the client.

---

## R5 — Media uploads (messages / content files)

**Decision**: Keep **Cloudinary** (or compatible unsigned/signed upload). Backend issues signed upload params or validates allowed upload; stores only secure URLs/public IDs in DB.

**Rationale**: Spec assumes external media host; Mateen already uses Cloudinary for images/voice; avoids large binary storage on the API server.

**Alternatives considered**:
- S3-compatible object storage — valid long-term; more ops work for v1.
- Store blobs in MySQL — rejected (size/perf).

---

## R6 — Push notifications

**Decision**: Firebase Cloud Messaging **HTTP v1** via a Laravel notification channel / service, storing device tokens on `user_devices` (or JSON column). Queue notification jobs so message create returns quickly while SC-005 (≤15s visible) remains achievable.

**Rationale**: Existing PWA already uses FCM; reusing tokens/migration path reduces client churn. Spec allows provider change if SC-005 holds — FCM is the lowest-risk default.

**Alternatives considered**:
- OneSignal / Pusher Beams — extra vendor; optional later.
- In-app polling only — fails SC-005 push expectation when tokens exist.

---

## R7 — Hijri / Gregorian dates

**Decision**: Persist schedule times in UTC/Gregorian (`datetime`/`date`); compute Hijri for display via a maintained PHP Hijri library (e.g. `geniusts/hijri-dates` or equivalent) in API resources.

**Rationale**: Spec requires dual calendar awareness without dual storage ambiguity; single source of truth avoids drift.

**Alternatives considered**:
- Store both calendars — duplicate state, sync bugs.
- Client-only Hijri conversion — weaker consistency for exports/admin.

---

## R8 — Exports (Excel / Word / PDF)

**Decision**: Server-generated downloads — spreadsheet via Maatwebsite Excel (or PhpSpreadsheet), PDF via DomPDF/Snappy, Word via PhpWord (or HTML→DOCX pipeline). Authorize via policies; stream or short-lived signed download URL.

**Rationale**: Matches current `export.js` capabilities and FR-014 / SC-006; generation on server keeps secrets and filters consistent.

**Alternatives considered**:
- Client-only export — harder to enforce filters and large cohorts.
- Third-party report SaaS — unnecessary dependency for v1.

---

## R9 — Firebase → Laravel migration

**Decision**: One-shot (repeatable) Artisan migration commands: export Firestore collections + Auth users → transform → upsert into MySQL. Password strategy: force **password reset on first login** for migrated users (Firebase password hashes are not portable to Laravel’s hasher), unless a verified import path exists. Preserve stable external IDs (`firebase_uid`) for audit/mapping; new primary keys are UUIDs or bigints.

**Rationale**: FR-016 / SC-007 require ≥99% continuity of records; auth credentials cannot be blindly copied. Email remains unique key for login (FR-002a).

**Alternatives considered**:
- Dual-write period — rejected by no-hybrid production rule for go-live; may still use staging dual-read during dry runs.
- Empty start — rejected in clarifications.

**Collections to map** (from README): `users`, `students`, `materials`, `staticSubjects`/`subjects`, `libraryItems`, `assignments`, `conversations`(+messages), `news`, plus schedule/theme/FCM token fields as present in documents.

---

## R10 — Account hard-delete

**Decision**: Transactional delete: user row, profile, solely owned content (submissions, news they solely own if applicable), device tokens; scrub message sender PII (replace display name with “محذوف” / null user_id); retain anonymous numeric aggregates for stats.

**Rationale**: Matches clarification Q5 and FR-002; mirrors current Cloud Function that deletes Auth when user doc is removed — Laravel becomes the single authority.

**Alternatives considered**: Soft-delete / anonymize-in-place — rejected in clarifications.

---

## R11 — Frontend integration approach

**Decision**: Introduce a small `js/api.js` (fetch + token storage) and migrate modules off Firebase SDK domain-by-domain while API is built; production cutover flips config to API-only when all domains pass SC-009.

**Rationale**: Spec keeps existing client; avoids UI rewrite. Build order may follow P1→P3 stories; cutover is still all-or-nothing.

**Alternatives considered**: Livewire/Inertia rewrite — out of scope.
- GraphQL — REST OpenAPI is simpler for current page scripts.

---

## R12 — Hosting & CORS

**Decision**: API on HTTPS Linux VPS (PHP-FPM + Nginx/Apache + MySQL). Allow CORS from Mateen web origin(s) via `FRONTEND_URL` / `CORS_ALLOWED_ORIGINS`. Queues via database or Redis worker for notifications/exports. Client `API_BASE_URL` points at the active VPS API (`/api/v1`).

**Rationale**: Ops now targets dedicated VPS hosts (staging then production) rather than relying on a third-party PaaS as the primary path. Token + CORS remains correct while static files and API may share a host or use academy origin → API origin.

**Alternatives considered**:
- Serve only from Laravel `public/` (Blade/Inertia) — out of scope; keep existing HTML/JS.
- GitHub Pages-only frontend forever — still viable for static assets, but staging/prod VPS can also serve static Mateen paths for one-box testing.

---

## R13 — Testing strategy

**Decision**: Pest feature tests per API resource group; policy unit tests for each role × action matrix (SC-002); migration dry-run tests on fixture JSON dumps; contract smoke against OpenAPI paths; **post-deploy staging smoke** (login + `/auth/me` + one authorized list) before production promote.

**Rationale**: Authorization defects are the highest product risk; automated matrix beats manual-only checks; staging gate prevents bad cutovers.

**Alternatives considered**: Manual QA only — insufficient for six-role matrix.

---

## R14 — Monorepo vs separate backend repository

**Decision**: **Keep one repository** containing static Mateen client + `backend/` Laravel app. Do **not** move the backend to a separate GitHub project.

**Rationale**: Stakeholder preference is a single project for visibility and coordinated cutover (`USE_LARAVEL_API` + API deploy). The tree already matches this layout. Splitting would duplicate CI, complicate CORS/version alignment, and slow full-parity release (FR-019).

**Alternatives considered**:
- Backend-only new repo + keep frontend on Mateenweb/Mateen — rejected by merge preference.
- Git submodule for backend — extra friction without benefit at this scale.

---

## R15 — New GitHub repository & remotes

**Decision**: Create a **new GitHub repository** as the deploy/CI source of truth for the monorepo. Keep existing remotes (`origin` / `upstream`) as historical references until cutover; add the new repo as deploy remote (e.g. `deploy` or replace `origin` when ready). Protect `main`; require CI green before merge.

**Rationale**: Clean CI/CD secrets and deploy workflows without inheriting only-Firebase Actions from the old Functions pipeline. Enables SSH deploy secrets scoped to this product.

**Alternatives considered**: Only push workflows to existing `3mmar0/Mateen` — acceptable if naming stays, but stakeholder asked for a new repo for deploy + CI/CD clarity.

**Note**: Repository name/org to be set at create time; document URL in `contracts/environments.md` when created. Do not commit credentials.

---

## R16 — Staging-then-production VPS deploy order

**Decision**:
1. **Staging first**: deploy to VPS host `187.127.71.130` (SSH as `root`). Run migrations/seed or migration dry-run, point a staging `API_BASE_URL` / CORS at this host, smoke-test login and core APIs.
2. **Production later**: deploy to VPS host `31.97.122.143` only after staging smoke passes. Production frontend reference remains `https://mateen.academy/Mateen/html/login.html` with CORS allowing `https://mateen.academy`.

**Rationale**: Explicit stakeholder order — test environment before production cutover host. Reduces blast radius.

**Alternatives considered**: Deploy production first — rejected. Dual-write Firebase during prod — rejected by FR-019.

**Security**: SSH passwords/keys and DB passwords are **operator secrets** (GitHub Actions secrets / server `.env` only). Never write them into specs, plan, or git.

---

## R17 — CI/CD pipeline shape

**Decision**: GitHub Actions on the new repo:
- **CI** (`ci-backend.yml`): on PR/`main` — `composer install`, `php artisan test` (Pest) in `backend/`; fail merge if red.
- **Deploy staging** (`deploy-vps.yml`): on push to `main` (or `workflow_dispatch`) — SSH to staging, `git pull` (or rsync), `composer install --no-dev`, `php artisan migrate --force`, reload PHP-FPM, optional queue restart.
- **Deploy production**: separate job/environment with **required approval** (GitHub Environment protection) after staging verification; same script targeting prod host.

**Rationale**: Matches monorepo + two-VPS gate; keeps Firebase Functions workflow until retirement but does not block Laravel deploys.

**Alternatives considered**:
- Manual FTP/SCP only — error-prone, no audit trail.
- Docker/Kubernetes — overkill for single-tenant VPS at current scale.

---

## R18 — How static site and API are co-hosted

**Decision (staging default)**: On staging VPS, serve Laravel under a subdomain or path (e.g. `https://<staging-host>/api` or `api.<staging>`) and optionally sync static `html/`/`js/`/`css/` for integrated browser tests. Client config: `USE_LARAVEL_API=true` and `API_BASE_URL` → staging API `/api/v1`.

**Decision (production)**: Keep academy static site at `mateen.academy` paths; point `API_BASE_URL` at the production VPS API HTTPS URL; set CORS to academy origin. Optionally later reverse-proxy API under the same domain (`mateen.academy/api`) to simplify cookies — not required for Sanctum token mode.

**Rationale**: Production UI already lives at Mateen Academy; API can move to the new VPS without relocating every static asset on day one.

**Alternatives considered**: Force all static files onto production VPS immediately — possible but unnecessary for first staging validation.
