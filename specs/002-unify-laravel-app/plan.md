# Implementation Plan: Unify App into Single Laravel Project

**Branch**: `002-unify-laravel-app` | **Date**: 2026-07-30 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-unify-laravel-app/spec.md`

## Summary

Elevate the existing Laravel app from `backend/` to the **repository root**, place the live Mateen HTML/CSS/JS under `public/Mateen/` so current public paths (`/Mateen/html/…`) keep working, switch the client to **same-origin** `/api/v1` calls, update CI/deploy to the root app, cut over staging then production so UI+API are served from one `public/` document root, and park the old standalone front tree under `_obsolete/frontend/` (do-not-edit) for a later delete follow-up.

## Technical Context

**Language/Version**: PHP 8.3+ / Laravel 12.x (existing); static ES modules for existing PWA client (no framework rewrite)

**Primary Dependencies**: Existing Laravel stack (Sanctum, policies, etc. from `001`); Nginx (or Apache) + PHP-FPM on VPS; GitHub Actions CI/CD

**Storage**: Unchanged MySQL 8+ (no schema change required for this packaging feature)

**Testing**: Pest/PHPUnit at repo root; smoke checks for static path parity + same-origin login; update CI working-directory

**Target Platform**:
- **Local**: `php artisan serve` (or Valet/Herd) from repo root — UI at `/Mateen/html/…`, API at `/api/v1`
- **Staging**: VPS `187.127.71.130` — document root → Laravel `public/`
- **Production**: VPS / academy host — same unified origin after staging smoke; previous separate front-only host retired as live UI

**Project Type**: Single Laravel web application at repository root (UI static assets + API)

**Performance Goals**: No regression vs current; page+asset load success on spot-check of ≥10 pages (SC-005); contributor finds both UI and server in &lt;5 minutes (SC-001)

**Constraints**: Preserve `/Mateen/…` public paths (FR-009); same-site API (FR-012); no UI redesign; domain scope unchanged (`001`); obsolete front kept temporarily marked (FR-007); staging before production

**Scale/Scope**: Packaging/migration of existing ~dozens of HTML pages + js/css trees; CI/deploy path updates; one staging + one production cutover

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Project constitution (`.specify/memory/constitution.md`) remains the **unfilled template**. No project-specific enforceable gates yet.

| Gate | Status | Notes |
|------|--------|-------|
| Constitution principles defined | N/A / deferred | Template only |
| Spec-driven design artifacts | Pass | plan, research, data-model, contracts, quickstart |
| Clarification decisions honored | Pass | Root app, prod cutover, obsolete keep, path preserve, same-site API |
| No unjustified domain expansion | Pass | Packaging only; `001` owns domain |
| Staging-before-prod | Pass | Inherited from `001` / research R6 |
| Security baseline | Pass | No new auth model; same-origin reduces CORS surface for normal use |

**Post–Phase 1 re-check**: Still Pass. Recommend `/speckit-constitution` when ready for real gates.

## Project Structure

### Documentation (this feature)

```text
specs/002-unify-laravel-app/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── layout-and-urls.md
│   └── environments.md
└── tasks.md             # /speckit-tasks — not created here
```

### Source Code (target repository root — after unification)

```text
app/                      # Laravel (elevated from backend/app)
bootstrap/
config/
database/
public/
├── index.php
├── Mateen/
│   ├── html/             # Live pages (authoritative)
│   ├── js/               # Includes config.js → API_BASE_URL='/api/v1'
│   ├── css/
│   └── …                 # libs, images, other live static as needed
└── …                     # root-level public files if required for URL parity
routes/
├── api.php               # Existing /api/v1 (unchanged domain)
└── web.php               # Optional convenience redirect `/` → Mateen home
tests/                    # Former backend/tests
composer.json             # At repo root
artisan
.env / .env.example
_obsolete/
└── frontend/             # Former root html/js/css (+ DO_NOT_EDIT.md)
specs/                    # Spec Kit (unchanged location)
.specify/
.github/workflows/
├── ci-backend.yml        # Root working-directory (rename optional later)
└── deploy-vps.yml        # Deploy root Laravel; .env at root
functions/                # Legacy — non-authoritative residue
firebase.json             # Legacy — non-authoritative residue
```

**Structure Decision**: Single Laravel application at **repository root**. Authoritative UI is static files under `public/Mateen/` to preserve `/Mateen/html/…` URLs. Old standalone front moves to `_obsolete/frontend/` with do-not-edit marking. Meta folders (`specs/`, `.github/`) stay at root beside the app. Domain API remains `/api/v1` from the same origin.

## Complexity Tracking

> No constitution violations requiring justification (constitution is unset).

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |
