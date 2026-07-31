# Tasks: Unify App into Single Laravel Project

**Input**: Design documents from `/specs/002-unify-laravel-app/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Not included as separate TDD tasks (spec did not require TDD). Validate via `quickstart.md` scenarios in polish and per-story independent tests.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete work)
- **[Story]**: User story label (US1–US4)
- Paths follow plan.md target: Laravel at repo root + `public/Mateen/` + `_obsolete/frontend/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Inventory and collision plan before moving files

- [X] T001 Inventory live vs backup UI surfaces in `html/`, `js/`, `css/`, `libs/`, and root static files (`index.html`, `sw.js`, `manifest.json`, images) into `specs/002-unify-laravel-app/checklists/live-asset-inventory.md`
- [X] T002 [P] Document root-collision merge rules for elevating `backend/` (`.gitignore`, `README.md`, `package.json`, `.env.example`, `public/`) in `specs/002-unify-laravel-app/checklists/root-merge-notes.md`
- [X] T003 [P] Confirm current deploy/CI path assumptions in `.github/workflows/ci-backend.yml` and `.github/workflows/deploy-vps.yml` and list required edits in `specs/002-unify-laravel-app/checklists/ci-deploy-delta.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Elevate Laravel to repository root — MUST complete before user stories

**⚠️ CRITICAL**: No user story work begins until this phase is complete

- [X] T004 Move Laravel application contents from `backend/` to repository root (`app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `tests/`, `artisan`, `composer.json`, `composer.lock`, `phpunit.xml`, `vite.config.js`, `package.json` if present, etc.) per `plan.md`
- [X] T005 Merge root `.gitignore` with former `backend/.gitignore` into root `.gitignore` (keep Mateen meta ignores for `specs/`, `_obsolete/`, secrets)
- [X] T006 Merge env templates into root `.env.example` from former `backend/.env.example`; ensure `APP_URL` documents unified origin
- [X] T007 Relocate Laravel web root files into root `public/` (`public/index.php`, `.htaccess`, `robots.txt`, `favicon.ico`) without deleting space reserved for `public/Mateen/`
- [X] T008 Remove emptied `backend/` directory (or replace with a short pointer file only if a temporary stub is required — must not remain a real app root)
- [X] T009 Verify `composer install` and `php artisan --version` succeed from repository root; fix autoload/path issues in `composer.json` / `bootstrap/` if needed
- [X] T010 [P] Update any hard-coded `backend/` path references in `scripts/` and `docs-user-guide.md` that would break root-app workflows

**Checkpoint**: Foundation ready — `artisan` lives at repo root; user stories can proceed

---

## Phase 3: User Story 1 — Open one project for the whole product (Priority: P1) 🎯 MVP

**Goal**: Repository root is the single Mateen application; maintainers find server code at root without a nested `backend/` app

**Independent Test**: Fresh checkout shows `artisan` + `app/` at root; no real `backend/` app root; README explains single-app layout (quickstart V1)

### Implementation for User Story 1

- [X] T011 [US1] Update root `README.md` to describe the unified Laravel app at repo root (where `app/`, `public/`, `artisan` live; how to run locally)
- [X] T012 [US1] Add a short contributor “where things live” section to `README.md` mapping UI → `public/Mateen/` and server → `app/` (even if UI copy lands in US2)
- [X] T013 [US1] Ensure root project markers match `contracts/layout-and-urls.md` (presence of `artisan`, `composer.json`, `public/index.php`; absence of nested app-only `backend/`)
- [X] T014 [US1] Align `specs/002-unify-laravel-app/contracts/environments.md` local setup paths with root-app reality (no `cd backend`)

**Checkpoint**: US1 independently testable — single project discoverability (SC-001)

---

## Phase 4: User Story 2 — Keep existing product behavior after the move (Priority: P1)

**Goal**: Live Mateen screens are served from the unified app at preserved `/Mateen/…` paths with working assets

**Independent Test**: `php artisan serve` → `/Mateen/html/login.html` and representative role pages load with CSS/JS (quickstart V2, V4)

### Implementation for User Story 2

- [X] T015 [P] [US2] Copy live pages from `html/` into `public/Mateen/html/` (exclude `*_backup*` and unused duplicates per inventory T001)
- [X] T016 [P] [US2] Copy live scripts from `js/` into `public/Mateen/js/`
- [X] T017 [P] [US2] Copy live styles from `css/` into `public/Mateen/css/`
- [X] T018 [P] [US2] Copy `libs/` into `public/Mateen/libs/` (and any other inventoried live static trees required by pages)
- [X] T019 [US2] Place root-level live assets needed for URL/PWA parity (`manifest.json`, `sw.js`, logos/images referenced by live pages) under the correct `public/` or `public/Mateen/` paths per inventory so existing public URLs keep working
- [X] T020 [US2] Add optional convenience route in `routes/web.php` redirecting `/` to the Mateen public entry (e.g. `/Mateen/html/…`) without changing bookmarked `/Mateen/…` paths
- [X] T021 [US2] Smoke-test at least login + one staff + one student HTML page under `/Mateen/html/` via `php artisan serve` and fix broken relative asset paths if any

**Checkpoint**: US2 independently testable — path/asset parity for live screens (FR-009, FR-005)

---

## Phase 5: User Story 3 — Edit interface and server work in one place (Priority: P2)

**Goal**: Same-origin API config and one documented local run path so UI + server edits happen in the root app

**Independent Test**: `API_BASE_URL` is `/api/v1`; login hits same host; small UI + server edit both show without a second product tree (quickstart V3, V6)

### Implementation for User Story 3

- [X] T022 [US3] Set same-origin API config in `public/Mateen/js/config.js` (`USE_LARAVEL_API = true`, `API_BASE_URL = '/api/v1'`)
- [X] T023 [US3] Audit `public/Mateen/js/api.js` (and related clients) for absolute-host assumptions; adjust to work with relative `/api/v1`
- [X] T024 [P] [US3] Document single local command path (`composer install`, `php artisan migrate --seed`, `php artisan serve`) in root `README.md`
- [X] T025 [US3] Verify cross-cutting edit path: change copy in one `public/Mateen/html/` page and a trivial string in `app/`; confirm both apply through the same running app

**Checkpoint**: US3 independently testable — one edit/run workflow (FR-004, FR-012)

---

## Phase 6: User Story 4 — Retire the old split layout cleanly (Priority: P2)

**Goal**: Old standalone front is marked obsolete; CI/deploy target root app; staging then production serve UI from unified `public/`

**Independent Test**: `_obsolete/frontend/DO_NOT_EDIT.md` exists; workflows no longer use `backend/` as app root; staging smoke UI+API same origin (quickstart V5, V7, V8)

### Implementation for User Story 4

- [X] T026 [US4] Move former standalone front trees (`html/`, `js/`, `css/`, `libs/`, and other superseded root front files from inventory) into `_obsolete/frontend/`
- [X] T027 [US4] Write `_obsolete/frontend/DO_NOT_EDIT.md` stating non-authoritative status and follow-up deletion (per FR-007)
- [X] T028 [US4] Update `.github/workflows/ci-backend.yml` to run Composer/tests from repository root (remove `working-directory: backend` / `backend/**` path filters as appropriate)
- [X] T029 [US4] Update `.github/workflows/deploy-vps.yml` to rsync/install/migrate against root app and `{APP_DIR}/.env` (not `{APP_DIR}/backend/.env`)
- [X] T030 [US4] Add or update server docroot notes for Nginx/Apache → `{APP_DIR}/public` in `specs/002-unify-laravel-app/contracts/environments.md` and/or root `README.md`
- [X] T031 [US4] Deploy to staging and run smoke: `GET /Mateen/html/login.html`, `POST /api/v1/auth/login` on same origin (quickstart V7)
- [X] T032 [US4] Production cutover checklist execution: promote unified app, confirm live `/Mateen/html/login.html` + same-origin `/api/v1`, confirm previous separate front-only host is not live UI (quickstart V8 / FR-011)

**Checkpoint**: US4 complete — obsolete marked, CI/deploy unified, cutover done

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: PWA/service worker, docs consistency, full quickstart pass

- [X] T033 [P] Update `public/Mateen/sw.js` and/or `public/firebase-messaging-sw.js` (whichever is live) so cache scopes match unified `/Mateen/…` paths and same origin
- [X] T034 [P] Spot-check ≥10 representative pages for CSS/JS/media 404s; record results in `specs/002-unify-laravel-app/checklists/asset-smoke.md`
- [X] T035 [P] Cross-link packaging supersession from `specs/001-laravel-backend/contracts/environments.md` to `specs/002-unify-laravel-app/contracts/environments.md` (domain OpenAPI stays in `001`)
- [X] T036 Run full `specs/002-unify-laravel-app/quickstart.md` scenarios V1–V8 and fix any gaps
- [X] T037 [P] Final README pass: warn editors never to change `_obsolete/frontend/`; authoritative UI is `public/Mateen/`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Setup — **BLOCKS** all user stories
- **US1 (Phase 3)**: After Foundational — MVP discoverability
- **US2 (Phase 4)**: After Foundational (can follow or overlap lightly with US1 docs, but needs root `public/`)
- **US3 (Phase 5)**: After US2 (needs authoritative `public/Mateen/js/config.js`)
- **US4 (Phase 6)**: After US2 (move old trees only after copy verified); CI can start after Foundational but deploy smoke needs US2+US3
- **Polish (Phase 7)**: After desired stories complete (ideally after US4)

### User Story Dependencies

| Story | Depends on | Notes |
|-------|------------|-------|
| US1 | Phase 2 | Docs + layout markers |
| US2 | Phase 2 | UI copy into `public/Mateen/` |
| US3 | US2 | Same-origin config on authoritative JS |
| US4 | US2 (+ US3 before prod smoke) | Obsolete move + CI/deploy + cutover |

### Parallel Opportunities

- T001–T003 (setup) can run in parallel after T001 inventory starts
- T015–T018 (copy html/js/css/libs) parallel once Phase 2 done
- T033–T035, T037 polish items marked [P] in parallel after cutover

---

## Parallel Example: User Story 2

```text
# After Phase 2, launch asset copies together:
Task: "Copy live pages from html/ into public/Mateen/html/"
Task: "Copy live scripts from js/ into public/Mateen/js/"
Task: "Copy live styles from css/ into public/Mateen/css/"
Task: "Copy libs/ into public/Mateen/libs/"
```

---

## Parallel Example: User Story 4 (CI vs obsolete docs)

```text
# After US2 copy verified:
Task: "Update .github/workflows/ci-backend.yml for repo root"
Task: "Write _obsolete/frontend/DO_NOT_EDIT.md"  # after move, or draft marker text in parallel with CI edits
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup  
2. Complete Phase 2: Foundational (elevate Laravel)  
3. Complete Phase 3: US1 (README + layout markers)  
4. **STOP and VALIDATE**: quickstart V1 — single project discoverability  

### Incremental Delivery

1. Setup + Foundational → root Laravel app  
2. US1 → navigable single project (MVP)  
3. US2 → UI served at `/Mateen/…`  
4. US3 → same-origin API + one dev workflow  
5. US4 → obsolete marker + CI/deploy + staging/prod cutover  
6. Polish → SW, smoke, quickstart V1–V8  

### Suggested MVP scope

**US1 only** (after Phase 2): proves the repo is one Laravel app at root. Deliver US2 next before any production UI cutover.

---

## Notes

- [P] = different files, no incomplete-task dependencies  
- Do not edit `_obsolete/frontend/` for product fixes after T026  
- Domain API behavior remains owned by `001`; this feature is packaging/serving only  
- Permanent deletion of `_obsolete/frontend/` is explicitly out of scope (follow-up)  
- Commit after each task or logical group  
- Stop at checkpoints to validate each story independently
