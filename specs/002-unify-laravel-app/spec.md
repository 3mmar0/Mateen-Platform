# Feature Specification: Unify App into Single Laravel Project

**Feature Branch**: `002-unify-laravel-app`

**Created**: 2026-07-30

**Status**: Draft

**Input**: User description: "the project still seperated i need the front and back to be in the laravel project so its become easy to read and edit in future"

## Clarifications

### Session 2026-07-30

- Q: Should the unified Mateen application live at the repository root, or stay inside a single nested folder (for example today’s `backend/`)? → A: Elevate the Laravel app to the repository root; front assets move into that root app
- Q: For this feature, must production also start serving the Mateen UI from the unified application, or is it enough to unify the repo for development while production UI stays on the current separate hosting for now? → A: This feature includes production cutover: live UI is served from the unified application
- Q: After the UI moves into the root application, should the old standalone front folders be deleted from the repository, or left in place but clearly marked as obsolete? → A: Keep old folders temporarily with a clear obsolete/do-not-edit marker; delete in a follow-up
- Q: After cutover, should users’ existing public page addresses keep the same paths, or is it acceptable if some paths change as long as old addresses redirect automatically? → A: Preserve existing public paths for live pages (no path change required for normal use)
- Q: After the UI and server run as one application, should the pages call the server on the same site automatically, or keep a separate configurable server address like today’s front config? → A: Same-site by default: UI calls the unified app’s own server without a separate front API base URL for normal use

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Open one project for the whole product (Priority: P1)

As a developer or maintainer, I open a single application project and immediately find both the user-facing interface (pages, styles, scripts) and the server-side application in that same project tree—not in two sibling top-level folders.

**Why this priority**: The stated goal is future readability and editability; a split layout is the pain being removed.

**Independent Test**: Clone or open the repo, confirm the repository root is the application project and contains both interface assets and server code, with no parallel “front only” and “back only” product trees and no nested app-only folder such as `backend/` as the real project root.

**Acceptance Scenarios**:

1. **Given** a fresh checkout of the repository, **When** a maintainer looks for Mateen UI pages and server application code, **Then** both live at the repository root application (not under a nested `backend/`-style product folder).
2. **Given** the unified project, **When** a maintainer searches for a screen and its related server behavior, **Then** they do not need a second top-level product folder outside the repository root application.
3. **Given** documentation for local setup, **When** a new contributor follows it, **Then** they set up and run the application from the repository root (not two separate product apps).

---

### User Story 2 - Keep existing product behavior after the move (Priority: P1)

As an end user (any role), I continue to use the same Mateen screens and flows I already have: public pages, sign-in, and role workspaces behave as before the unification.

**Why this priority**: Unification must not ship a broken or incomplete product surface; continuity is required for cutover confidence.

**Independent Test**: Walk primary journeys (home/public entry, sign-in, at least one staff and one student workspace page) against the unified app and confirm pages load and core actions still work.

**Acceptance Scenarios**:

1. **Given** the unified application is running, **When** a guest opens the public entry experience, **Then** they see the expected public pages and navigation.
2. **Given** a valid account, **When** the user signs in, **Then** they reach their role-appropriate workspace with the same core screens available as before unification.
3. **Given** a previously working screen (for example messages, schedule, or subject materials), **When** an authorized user opens it after unification, **Then** the screen loads and its primary actions remain usable.

---

### User Story 3 - Edit interface and server work in one place (Priority: P2)

As a developer, when I change a screen or fix a bug that spans UI and server behavior, I edit files inside the same application project and use one local run/dev workflow described for that project.

**Why this priority**: Day-to-day editability is the long-term benefit after the structural merge.

**Independent Test**: Make a small UI text change and a small server-facing behavior change in the unified project, reload the app, and verify both take effect without touching a second product tree.

**Acceptance Scenarios**:

1. **Given** the unified project, **When** a developer updates copy or layout on an existing page, **Then** the change is saved inside that project and appears when the app is refreshed.
2. **Given** the unified project, **When** a developer adjusts a server-side rule used by a page, **Then** the change lives in the same project and is exercised through the same running app.
3. **Given** contributor docs, **When** a developer starts local work, **Then** a single documented command path (or short sequence) covers serving the unified product.

---

### User Story 4 - Retire the old split layout cleanly (Priority: P2)

As a maintainer, after unification I am not left confused about where to edit. Old separate front packaging remains in the repo only temporarily, clearly marked obsolete / do-not-edit, while the unified application is the sole authoritative source. Permanent deletion of those obsolete folders is a follow-up after this feature.

**Why this priority**: Leaving unmarked duplicates would recreate confusion; deleting before a follow-up window risks losing unmoved material.

**Independent Test**: Confirm the former standalone front tree is marked obsolete and non-authoritative, CI/deploy docs point only at the unified application, and maintainers are directed not to edit the old folders.

**Acceptance Scenarios**:

1. **Given** unification is complete, **When** a maintainer looks for the previous standalone front product tree, **Then** it remains only as a temporary obsolete copy with a clear do-not-edit marker—not as the live source of UI.
2. **Given** deployment or CI configuration, **When** a maintainer checks what gets built and published, **Then** it targets the unified application only.
3. **Given** a search for page assets, **When** comparing old vs new locations, **Then** the unified project holds the authoritative live copy; the obsolete folders are not treated as editable product source.
4. **Given** production after this feature’s cutover, **When** an end user opens the live Mateen site, **Then** the UI is served from the unified application—not from the previous separate front-only host.

---

### Edge Cases

- What happens if a page referenced relative assets (images, fonts, styles, scripts) that assumed the old folder layout? Those references must still resolve after the move.
- How does the system handle bookmarks or links to old public paths? Existing public paths for live pages must be preserved so bookmarks and shared links keep working without requiring redirects for normal use.
- What if backup or alternate HTML/JS copies existed alongside live pages? Only live product surfaces move into the authoritative tree; obsolete backups are not promoted as live.
- What happens to environment-specific front config (API base URL, feature flags)? For normal use the UI MUST call the unified application on the same site (no separate front API base URL required); remaining config must not depend on a second app’s config file as the source of truth.
- How are service-worker or offline assets handled if they pointed at the old root? They must be updated so they do not serve stale paths from the retired layout.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The repository root MUST be the single Mateen application project and MUST contain both the user interface assets and the server-side application (no nested app-only folder such as `backend/` as the real project root).
- **FR-002**: Maintainers MUST be able to locate and edit UI pages, styles, and scripts inside that repository-root application—not in a separate sibling product tree.
- **FR-003**: The unified application MUST continue to expose the existing Mateen user-facing screens required for normal use (public entry, authentication-related pages, and role workspaces already in the product).
- **FR-004**: After unification, running the application MUST serve the product UI and server capabilities together so users do not depend on a second, separately started front-only app for day-to-day use.
- **FR-005**: Asset references used by live pages (styles, scripts, images, fonts, and similar) MUST resolve correctly under the unified layout.
- **FR-006**: Contributor and deployment documentation MUST describe a single application setup and run path for local and production-oriented workflows.
- **FR-007**: The previous standalone front layout MUST cease to be an authoritative source of truth: it MUST remain only temporarily with a clear obsolete / do-not-edit marker; permanent deletion of those folders is deferred to a follow-up after this feature.
- **FR-008**: Build, CI, and deploy instructions that previously assumed a split front/back product layout MUST be updated to the unified application.
- **FR-009**: Existing public paths for live pages MUST be preserved after cutover so users’ bookmarks and shared links keep working without requiring path changes or redirects for normal use.
- **FR-010**: Unification MUST NOT drop or hide role-based screens that were part of the live product before the move; parity of available screens is required unless a screen was already documented as unused backup.
- **FR-011**: This feature MUST include production cutover so the live Mateen UI is served from the unified application; after cutover the previous separate front-only host MUST NOT be the live UI source.
- **FR-012**: For normal local and production use, the UI MUST call the unified application’s own server on the same site—without requiring a separate front-only API base URL configuration.

### Key Entities

- **Application project**: The repository root after unification; holds both interface and server code (Laravel app elevated out of any nested folder such as `backend/`).
- **User interface surface**: The set of live pages, styles, and scripts end users interact with.
- **Server application**: The backend capabilities (identity, data, business rules) already specified under the Laravel backend feature; this feature relocates packaging, not product domain scope.
- **Authoritative asset**: The one live copy of a page or static resource that should be edited going forward.
- **Superseded layout**: The former separate front (and any duplicate packaging) kept temporarily with an obsolete / do-not-edit marker; not authoritative; scheduled for deletion in a follow-up.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A new contributor can identify the repository root as the application and find both UI and server code there in under 5 minutes without asking where the “other half” of the product lives or where a nested `backend/` folder is.
- **SC-002**: 100% of live product screens that existed before unification are reachable from the unified application at their previous public paths (no missing primary role workspace or public entry page, and no path change required for normal use).
- **SC-003**: A maintainer can complete a cross-cutting edit (one UI change + one related server behavior change) without opening a second product tree outside the unified application.
- **SC-004**: After unification and production cutover, automated checks and deploy docs reference only the unified application—zero remaining required steps that depend on the old standalone front tree or separate front-only host as the live UI source.
- **SC-005**: Spot-check of at least 10 representative pages shows styles, scripts, and media loading correctly (no broken asset paths attributable to the move).
- **SC-006**: At least 9 out of 10 maintainers in a short walkthrough agree the project is easier to navigate for reading and editing than the previous split layout.

## Assumptions

- The stakeholder-chosen stack remains the existing Laravel-based server application; this feature is about packaging the current front with that application, not redesigning Mateen’s domain features.
- “Front” means the existing live HTML/CSS/JS (and related static assets) already used by Mateen users—not a brand-new UI redesign.
- After moving live UI into the root app, the old standalone front folders are kept temporarily with a clear obsolete / do-not-edit marker; deleting them permanently is a follow-up, not a gate for this feature’s completion.
- Backup or unused alternate page copies are out of scope as live surfaces; they need not be preserved as first-class product pages.
- Domain capabilities (auth, subjects, messaging, etc.) stay governed by the existing backend feature specification; this feature does not expand or shrink that functional scope.
- The Laravel application is elevated to the repository root; front assets move into that root app. Nested app-only layouts (e.g. keeping everything under `backend/`) are rejected for this feature.
- Production cutover is in scope for this feature: live UI is served from the unified application; previous separate front-only hosting (e.g. Firebase) must not remain the live UI source after cutover. Historical config may remain in the repo only as non-authoritative migration residue.
- Local and deployed environments use same-site UI→server calls within the unified application (no permanent requirement to run a second front-only static server, and no separate front API base URL required for normal use).
