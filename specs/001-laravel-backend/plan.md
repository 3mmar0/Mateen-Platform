# Implementation Plan: Laravel Backend Platform

**Branch**: `001-laravel-backend` | **Date**: 2026-07-30 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-laravel-backend/spec.md`

## Summary

Replace Mateen’s Firebase Auth/Firestore/Functions backend with a **Laravel JSON API** that the existing Arabic RTL HTML/JS/PWA client calls. The backend must achieve **full feature parity** (auth/roles, subjects, students/schedules, assignments, library, news, messaging + notifications, stats/exports, support themes) before a **single production cutover**, with **full data migration** from Firebase and **email+password** auth. Media stays on an external host (Cloudinary-compatible); push notifications remain required for messaging.

## Technical Context

**Language/Version**: PHP 8.3+ / Laravel 12.x (or current stable Laravel LTS at scaffold time)

**Primary Dependencies**: Laravel Sanctum (API token auth for SPA/PWA), Spatie Laravel Permission or native enum+policies for six roles, Cloudinary PHP SDK (or signed upload tokens), FCM HTTP v1 (or Laravel Notification channel) for push, Maatwebsite Excel / DomPDF (or equivalent) for exports, Hijri calendar helper package

**Storage**: MySQL 8+ (primary relational store); Redis optional for queues/cache in production; local/object storage only for generated export files if not streamed

**Testing**: Pest (preferred) or PHPUnit — feature/API tests + policy/authorization tests; contract tests against `contracts/openapi.yaml`

**Target Platform**: Linux server (API) + existing static/PWA client (GitHub Pages or equivalent); CORS-enabled API for browser origin

**Project Type**: Web service (Laravel API) + existing static frontend adapted to API

**Performance Goals**: Align with spec — sign-in workspace ready &lt;30s wall-clock (SC-001); message notification visible ≤15s when reachable (SC-005); cohort export ≤2 min (SC-006); interactive API p95 &lt;500ms for typical list/detail under program-scale load

**Constraints**: Arabic-friendly validation/errors (FR-017); HTTPS only; hard-delete on account removal with anonymous stats retained (FR-002); no hybrid Firebase+Laravel production split (FR-019); single primary role per user

**Scale/Scope**: Single-tenant Mateen program (hundreds to low thousands of users expected); ~10 major domain modules; six roles; four library sections; five scientific subjects (configurable, not hard-coded forever)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Project constitution (`.specify/memory/constitution.md`) is still the **unfilled template**. No enforceable project-specific gates exist yet.

| Gate | Status | Notes |
|------|--------|-------|
| Constitution principles defined | N/A / deferred | Template placeholders only; spec explicitly allows proceeding |
| Spec-driven design artifacts | Pass | plan, research, data-model, contracts, quickstart produced |
| Full-feature cutover constraint | Pass | Plan/contracts cover all domains; no partial production hybrid |
| Security baseline | Pass | Sanctum tokens, hashed passwords, role policies, HTTPS assumed |

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
│   └── openapi.yaml
└── tasks.md             # /speckit-tasks — not created here
```

### Source Code (repository root)

```text
backend/                      # New Laravel application (API)
├── app/
│   ├── Models/
│   ├── Http/Controllers/Api/
│   ├── Http/Requests/
│   ├── Http/Resources/
│   ├── Policies/
│   ├── Services/             # Migration, notifications, exports, media
│   └── Enums/                # Role, library section, material type, etc.
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/api.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── ...

js/                           # Existing client — switch Firebase SDK calls → API client
html/
css/
functions/                    # Legacy Firebase Functions — retire after cutover
```

**Structure Decision**: Add a new `backend/` Laravel app alongside the existing static frontend. Keep current `html/`/`js/`/`css/` as the client and progressively replace Firebase usage with calls to the Laravel API documented in `contracts/openapi.yaml`. Do not rebuild the UI from scratch.

## Complexity Tracking

> No constitution violations requiring justification (constitution is unset).

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |
