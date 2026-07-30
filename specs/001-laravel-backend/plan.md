# Implementation Plan: Laravel Backend Platform

**Branch**: `001-laravel-backend` | **Date**: 2026-07-30 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-laravel-backend/spec.md`  
**Ops addendum (2026-07-30)**: Keep frontend + Laravel backend in **one monorepo**; publish to a **new GitHub repository** with CI/CD; **stage-test deploy** on VPS `187.127.71.130` before production VPS `31.97.122.143`; production web UI remains reachable at `https://mateen.academy/Mateen/html/login.html` (and related Mateen static paths).

## Summary

Replace Mateen’s Firebase Auth/Firestore/Functions backend with a **Laravel JSON API** that the existing Arabic RTL HTML/JS/PWA client calls. The backend must achieve **full feature parity** (auth/roles, subjects, students/schedules, assignments, library, news, messaging + notifications, stats/exports, support themes) before a **single production cutover**, with **full data migration** from Firebase and **email+password** auth. Media stays on an external host (Cloudinary-compatible); push notifications remain required for messaging.

**Repository & deploy**: Keep static client (`html/`, `js/`, `css/`, …) and `backend/` in the **same project** (already true today — do **not** split into a separate backend-only repo). Push this monorepo to a **new GitHub remote**, add GitHub Actions for test + deploy, validate on the **staging VPS**, then promote to the **production VPS**. Secrets (SSH keys/passwords, `.env`) live only in GitHub Secrets / server env files — never in git.

## Technical Context

**Language/Version**: PHP 8.3+ / Laravel 12.x (or current stable Laravel LTS at scaffold time); static ES modules for the existing PWA client

**Primary Dependencies**: Laravel Sanctum (API token auth for SPA/PWA), role enum + policies for six roles, Cloudinary PHP SDK (or signed upload tokens), FCM HTTP v1 for push, Maatwebsite Excel / DomPDF (or equivalent) for exports, Hijri calendar helper; GitHub Actions for CI/CD; Nginx (or Apache) + PHP-FPM + MySQL 8 on Linux VPS

**Storage**: MySQL 8+ (primary relational store); Redis optional for queues/cache in production; local/object storage only for generated export files if not streamed

**Testing**: Pest (preferred) or PHPUnit — feature/API tests + policy/authorization tests; contract tests against `contracts/openapi.yaml`; post-deploy smoke against staging base URL

**Target Platform**:
- **Client**: Existing static/PWA at Mateen Academy paths (prod reference: `https://mateen.academy/Mateen/…`)
- **API**: Linux VPS with HTTPS
  - **Staging (test first)**: host `187.127.71.130` — deploy and validate here before production
  - **Production (later)**: host `31.97.122.143` — only after staging smoke passes
- **Source of truth**: New GitHub repository (monorepo); CI/CD deploys from `main` (or release branch) via SSH

**Project Type**: Monorepo web app — Laravel API (`backend/`) + static frontend (repo root `html/`/`js/`/`css/`) in one GitHub project

**Performance Goals**: Align with spec — sign-in workspace ready &lt;30s wall-clock (SC-001); message notification visible ≤15s when reachable (SC-005); cohort export ≤2 min (SC-006); interactive API p95 &lt;500ms for typical list/detail under program-scale load

**Constraints**: Arabic-friendly validation/errors (FR-017); HTTPS only in staging/prod; hard-delete on account removal with anonymous stats retained (FR-002); no hybrid Firebase+Laravel production split (FR-019); single primary role per user; **no credentials in repo**; staging before production; CORS must allow Mateen Academy origin(s) and staging frontend origin

**Scale/Scope**: Single-tenant Mateen program (hundreds to low thousands of users expected); ~10 major domain modules; six roles; four library sections; five scientific subjects (configurable); two VPS environments + one GitHub Actions pipeline

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Project constitution (`.specify/memory/constitution.md`) is still the **unfilled template**. No enforceable project-specific gates exist yet.

| Gate | Status | Notes |
|------|--------|-------|
| Constitution principles defined | N/A / deferred | Template placeholders only; spec explicitly allows proceeding |
| Spec-driven design artifacts | Pass | plan, research, data-model, contracts, quickstart produced |
| Full-feature cutover constraint | Pass | Plan/contracts cover all domains; no partial production hybrid |
| Security baseline | Pass | Sanctum tokens, hashed passwords, role policies, HTTPS assumed; VPS/GitHub secrets out of band |
| Monorepo preference | Pass | Keep FE+BE together; no separate backend repo |
| Staging-before-prod | Pass | Deploy order: staging VPS → production VPS |

**Post–Phase 1 re-check**: Still Pass. Recommend `/speckit-constitution` later so future features inherit real gates.

## Project Structure

### Documentation (this feature)

```text
specs/001-laravel-backend/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── openapi.yaml
│   └── environments.md
└── tasks.md             # /speckit-tasks — not created here
```

### Source Code (repository root — monorepo)

```text
backend/                      # Laravel application (API)
├── app/
│   ├── Models/
│   ├── Http/Controllers/Api/
│   ├── Http/Requests/
│   ├── Http/Resources/
│   ├── Policies/
│   ├── Services/             # Migration, notifications, exports, media
│   └── Enums/
├── database/
├── routes/api.php
├── tests/
└── ...

html/                         # Existing client pages (login, workspaces, …)
js/                           # API client + domain modules (USE_LARAVEL_API)
css/
.github/workflows/
├── deploy-functions.yml      # Legacy Firebase — retire after cutover
├── ci-backend.yml            # NEW: composer test / lint on PR + main
└── deploy-vps.yml            # NEW: SSH deploy to staging (then prod after approval)
functions/                    # Legacy Firebase Functions — retire after cutover
```

**Structure Decision**: **Keep the monorepo**. Frontend and Laravel backend already live in one tree; do not extract `backend/` to a second repository. Publish this same tree to the **new GitHub repository**, configure CORS/`API_BASE_URL` per environment, and deploy API (+ optionally static files) via GitHub Actions over SSH. Staging VPS is the mandatory first deploy target; production VPS is promotion-only after smoke tests.

## Complexity Tracking

> No constitution violations requiring justification (constitution is unset).

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |
