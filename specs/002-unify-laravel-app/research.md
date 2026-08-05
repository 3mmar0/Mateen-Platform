# Research: Unify App into Single Laravel Project

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Spec**: [spec.md](./spec.md)

All Technical Context unknowns resolved below.

---

## R1 — Repository root layout (elevate Laravel)

**Decision**: Move the contents of `backend/` to the repository root so Composer/`artisan`/`app/`/`public/` live at root. Do **not** keep a nested `backend/` as the real application root.

**Rationale**: Clarification Q1 requires repo-root application for navigability (FR-001, SC-001). Contributors already expect `php artisan` at the project they open.

**Alternatives considered**:
- Keep nested `backend/` and only move UI inside it — rejected by clarification.
- Nested now, elevate later — rejected; increases double migration cost.

**Merge mechanics (research notes)**:
- Move Laravel files up; resolve collisions for root `.gitignore`, `README`, `.env.example`, `package.json` by preferring Laravel app versions and folding Mateen-specific ignore/docs entries into them.
- Keep `specs/`, `.specify/`, `.cursor/`, `.github/` at root (meta, not a second product app).
- Legacy `functions/`, `firebase.json`, and similar may remain as non-authoritative residue (FR-011 / assumptions).

---

## R2 — Where authoritative UI assets live

**Decision (updated 2026-08-05)**: Pages are **Blade** under `resources/views/`, served by `routes/web.php`. Static CSS/JS/libs/images stay under `public/Mateen/`. Preserved URLs `/Mateen/html/<page>.html` are Blade aliases (static HTML must not remain in `public/Mateen/html/` or it will shadow routes).

```text
resources/views/pages/…     # Authoritative pages
public/Mateen/js/…
public/Mateen/css/…
public/Mateen/libs/…
resources/html-source/      # Conversion archive only
```

**Rationale**: Stakeholder follow-up required Laravel Blade as the page system while keeping live look/paths (GitHub Pages parity).

**Supersedes**: earlier “static HTML only / Blade out of scope” note.

**Alternatives considered**:
- Keep static `public/Mateen/html` forever — rejected by Blade conversion request.
- `public/html` without `/Mateen` prefix — breaks bookmarks.
- Vite-bundled SPA — rejected; large rewrite, not requested.

---

## R3 — Obsolete front tree (temporary keep)

**Decision**: After copying live assets into `public/Mateen/…`, move the former standalone front directories (`html/`, `js/`, `css/`, and related root static product files that were the old source of truth) into `_obsolete/frontend/` with a top-level `DO_NOT_EDIT.md` (and optional `README`) stating they are non-authoritative and scheduled for deletion in a follow-up.

**Rationale**: Clarification Q3 — temporary keep with clear marker; delete later (FR-007).

**Alternatives considered**:
- Delete immediately — rejected by clarification.
- Leave unmarked at former paths — conflicts with Laravel root elevation and confuses editors.

---

## R4 — Same-site API base URL

**Decision**: Change client config so normal use resolves the API as a **same-origin relative** base, e.g. `API_BASE_URL = '/api/v1'` (or equivalent derived from `window.location.origin`). Remove the requirement to set a separate absolute API host in front config for local/staging/production normal operation.

**Rationale**: Clarification Q5 / FR-012. Eliminates CORS friction for same-host UI+API and the “wrong API_BASE_URL” class of bugs.

**Alternatives considered**:
- Keep absolute `API_BASE_URL` always — rejected for normal use.
- Same-site only in production — rejected; local should match production mental model.

**Note**: FCM / Firebase web config may remain for push only (existing `001` pattern); that is not a second data API host.

---

## R5 — Web routes vs pure static public files

**Decision (updated 2026-08-05)**: Deliver pages through Laravel web controllers + Blade. `/` is the Blade home. `/Mateen/html/<page>.html` remains an alias to the same action (FR-009). Static CSS/JS stay in `public/Mateen/`.

**Rationale**: Blade conversion request + same-origin app; bookmarks preserved without a static-file shadow.

**Alternatives considered**: Pure static public HTML — superseded.

---

## R6 — Production & staging cutover

**Decision**: This feature updates Nginx (or equivalent) so the **document root** for Mateen’s live host(s) is Laravel `public/`, serving both `/Mateen/…` UI and `/api/v1/…` from the same origin. Staging first (`187.127.71.130`), then production VPS / academy host after smoke (align with `001` staging-before-prod). Previous separate front-only host (Firebase Hosting or static-only vhost) must not remain the live UI source after cutover (FR-011).

**Rationale**: Clarification Q2.

**Alternatives considered**: Unify repo only, leave Firebase Hosting as live UI — rejected.

---

## R7 — CI/CD path updates

**Decision**: Update `.github/workflows/ci-backend.yml` and `deploy-vps.yml` so working directory and rsync paths target **repository root** Laravel (`composer`, `artisan`, `.env` at root — not `backend/`). Path filters should watch root PHP app paths instead of `backend/**`.

**Rationale**: FR-008; deploy currently hard-codes `backend/` (see current workflow).

**Alternatives considered**: Keep deploying `backend/` subdirectory forever — incompatible with FR-001.

---

## R8 — Path inventory & asset reference fix strategy

**Decision**: Before cutover, inventory live pages under `html/` (exclude `*_backup*` and clearly unused duplicates). After copy into `public/Mateen/`, smoke-test relative links (`../css`, `../js`, etc.). Because the relative tree `html`/`js`/`css` stays sibling under `Mateen/`, most relative references keep working without rewrite.

**Rationale**: FR-005, SC-005; edge case on relative assets.

**Alternatives considered**: Global search-replace of all asset paths to absolute — higher churn, only if inventory finds broken cases.

---

## R9 — Service worker / PWA

**Decision**: Update `sw.js` (and any manifest) so cache/fetch scopes match the unified origin and `/Mateen/…` asset locations; do not leave the SW registering against the obsolete tree or a retired host.

**Rationale**: Spec edge case on service workers.

---

## R10 — Domain scope boundary

**Decision**: No new domain models, OpenAPI domains, or feature parity work in this feature. Domain behavior remains owned by `specs/001-laravel-backend`. This feature only relocates packaging, serving, config, and deploy.

**Rationale**: Spec assumptions and FR scope.
