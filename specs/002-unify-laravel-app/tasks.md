---
description: "Task list for Blade UI conversion, live-site parity, and data migration"
---

# Tasks: Unify App into Single Laravel Project (Blade UI + live data)

**Input**: Design documents from `/specs/002-unify-laravel-app/` plus stakeholder follow-up: move every live HTML page into Laravel Blade, match [the live GitHub Pages site](https://mateenweb.github.io/Mateen/html/home.html) (`mateenweb/Mateen`), and migrate operational data into the unified app.

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md, `specs/001-laravel-backend/` (domain API + Firebase migration)

**Tests**: Not included as separate TDD tasks (spec did not require TDD). Validate via independent story checks, live-site visual/functional parity, and `quickstart.md` + data-audit scenarios.

**Organization**: Tasks grouped by user story (US1–US4 from spec.md). Packaging elevation from the original 002 plan is treated as **already landed** on `main`; these tasks convert the authoritative UI from static `public/Mateen/html/*.html` into Blade while preserving FR-009 paths and FR-012 same-origin API.

**Scope note**: research.md R2/R5 previously deferred Blade. Stakeholder input for this task round **supersedes** that: Blade is now the authoritative UI. Static HTML remains a conversion source, then becomes obsolete.

**Live reference**:
- Site: https://mateenweb.github.io/Mateen/html/home.html
- Source repo: https://github.com/mateenweb/Mateen (`html/` = 28 live pages; exclude `*_backup*`)
- Local source copies: `public/Mateen/html/` (28 live) + CSS/JS/libs under `public/Mateen/`

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete work)
- **[Story]**: User story label (US1–US4)
- Paths assume Laravel at repository root

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Inventory live GitHub Pages vs local HTML, lock Blade/URL mapping, and plan static-file collision (public HTML would shadow Blade routes)

- [X] T001 Diff live pages from `mateenweb/Mateen` `html/` against `public/Mateen/html/` and record gaps in `specs/002-unify-laravel-app/checklists/blade-live-parity.md` (28 live pages; exclude `home_backup.html`, `messages_backup.html`)
- [X] T002 [P] Document Blade view + named-route map for every live page in `specs/002-unify-laravel-app/checklists/blade-route-map.md` (example: `home.html` → `resources/views/pages/home.blade.php` + route `mateen.home` + preserved path `/Mateen/html/home.html`)
- [X] T003 [P] Document shared chrome extracted from live `home.html` (basmala, navbar, mobile drawer, register modal, contact block, footer) in `specs/002-unify-laravel-app/checklists/blade-partials.md`
- [X] T004 [P] Document public-html shadowing rule in `specs/002-unify-laravel-app/checklists/blade-static-shadow.md`: after a page is converted, its `public/Mateen/html/<page>.html` MUST be moved out of the web root or Blade will never run
- [X] T005 Copy live GitHub HTML/CSS/JS snapshots needed for parity (if local copies differ) into `storage/app/live-site-reference/` (do not deploy; reference only)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Blade layout system, web controllers, asset helpers, and route skeleton — MUST complete before story pages

**⚠️ CRITICAL**: No user-story page conversion begins until this phase is complete

- [X] T006 Create guest layout in `resources/views/layouts/guest.blade.php` matching live RTL Arabic chrome (dir=rtl, fonts, Tabler, Bootstrap RTL, shared/mobile/islamic/notifications CSS from `/Mateen/css/` and `/Mateen/libs/`)
- [X] T007 [P] Create authenticated workspace layout in `resources/views/layouts/app.blade.php` matching role-page chrome from live `admin.html` / `student.html` (sidebar + top nav slots)
- [X] T008 [P] Extract partials into `resources/views/partials/basmala.blade.php`, `resources/views/partials/nav.blade.php`, `resources/views/partials/mobile-drawer.blade.php`, `resources/views/partials/footer.blade.php`, `resources/views/partials/register-modal.blade.php`, `resources/views/partials/head-pwa.blade.php`
- [X] T009 [P] Add `App\Support\MateenAsset` (or `app/helpers.php` + composer autoload files) so Blade uses `mateen_asset('css/home.css')` → `/Mateen/css/home.css` without rewriting static CSS/JS trees
- [X] T010 Create `app/Http/Controllers/Web/PublicPageController.php` with methods that return Blade views and pass shared view data (`subjects`, `news`, `schedules` from Eloquent / existing API models)
- [X] T011 [P] Create `app/Http/Controllers/Web/AuthPageController.php`, `app/Http/Controllers/Web/StudentPageController.php`, `app/Http/Controllers/Web/TeacherPageController.php`, `app/Http/Controllers/Web/StaffPageController.php`
- [X] T012 Register named web routes in `routes/web.php` for all 28 live pages, each exposing **both** a clean route (e.g. `/`, `/login`, `/admin`) **and** the preserved FR-009 path `/Mateen/html/<page>.html` to the same controller action
- [X] T013 Change root `/` in `routes/web.php` from redirect-to-static-html into the Blade home action (`PublicPageController@home`) while keeping `/Mateen/html/home.html` as an alias
- [X] T014 Relocate conversion-source HTML from `public/Mateen/html/` to `resources/html-source/` (keep filenames) so public paths can be served by Laravel; leave CSS/JS/libs/images in `public/Mateen/`
- [X] T015 [P] Add `ContactMessage` + `RegistrationRequest` models/migrations in `database/migrations/` and `app/Models/` for live home contact form and “طلب تسجيل” modal (not in 001 domain tables today; required for live-form parity)
- [X] T016 [P] Add web/API endpoints in `app/Http/Controllers/Web/ContactController.php`, `app/Http/Controllers/Web/RegistrationRequestController.php`, and `routes/web.php` / `routes/api.php` so home forms no longer write to Firestore
- [X] T017 Ensure `public/Mateen/js/config.js` stays `USE_LARAVEL_API=true` and `API_BASE_URL='/api/v1'` and that Blade layouts load existing page scripts via `<script type="module" src="{{ mateen_asset('js/....js') }}">`
- [X] T018 [P] Extend `app/Console/Commands/MigrateFromFirebaseCommand.php` + `app/Services/FirebaseMigrationService.php` + `app/Services/FirebaseMigration/DocumentMapper.php` to import contact messages, registration requests, and any live public content collections missing from the current mapper

**Checkpoint**: Foundation ready — hitting `/` and `/Mateen/html/home.html` can resolve through Laravel (even if home view is still a stub)

---

## Phase 3: User Story 1 — Open one project for the whole product (Priority: P1) 🎯 MVP

**Goal**: Authoritative UI lives inside the root Laravel app as Blade (`resources/views/`) plus server code (`app/`); maintainers edit home in one project. Deliver a working Blade **home** that matches https://mateenweb.github.io/Mateen/html/home.html

**Independent Test**: Clone/open repo root; open `resources/views/pages/home.blade.php` and `app/Http/Controllers/Web/PublicPageController.php`; run `php artisan serve`; visit `/` and `/Mateen/html/home.html`; compare visually and functionally to the live GitHub home (nav, hero, subjects, dates, announcements, about, contact, register modal)

### Implementation for User Story 1

- [X] T019 [US1] Convert `resources/html-source/home.html` into `resources/views/pages/home.blade.php` extending `layouts/guest.blade.php` (basmala, nav, hero, subjects, dates, announcements, about, contact, register modal, footer)
- [X] T020 [US1] Implement `PublicPageController@home` in `app/Http/Controllers/Web/PublicPageController.php` to load subjects, published news, and upcoming schedules from `app/Models/Subject.php`, `app/Models/NewsItem.php`, `app/Models/ScheduleEntry.php` and pass them to the home view
- [X] T021 [P] [US1] Port home page scripts into Blade-safe includes: `public/Mateen/js/home.js`, `public/Mateen/js/home-msg.js`, `public/Mateen/js/nav.js`, `public/Mateen/js/tour.js`, `public/Mateen/js/sw-register.js` — remove Firestore writes for contact/register; call Laravel endpoints from T016
- [X] T022 [US1] Update in-page links on home Blade (login, about, courses, library, news, register, role dashboards) to named routes / preserved `/Mateen/html/…` paths so navigation matches the live site
- [X] T023 [US1] Update root `README.md` “where things live” so authoritative UI is `resources/views/` + `public/Mateen/{css,js,libs,images}`; `resources/html-source/` is conversion source only
- [X] T024 [US1] Record home parity checklist results (layout, Arabic RTL, subject cards, contact success state, register modal, login/register CTAs) in `specs/002-unify-laravel-app/checklists/blade-live-parity.md`

**Checkpoint**: US1 independently testable — single-project Blade home matches live GitHub home (SC-001 + home subset of SC-002/SC-005)

---

## Phase 4: User Story 2 — Keep existing product behavior after the move (Priority: P1)

**Goal**: Every live Mateen screen is a Blade page with the same behavior/look as GitHub Pages / previous HTML, using Laravel data (not Firestore) for content

**Independent Test**: Walk public entry, sign-in, one staff workspace, one student workspace, one teacher subject page against Blade URLs and confirm pages load, assets resolve, and primary actions work with seeded or migrated data

### Implementation for User Story 2 — public + auth pages

- [X] T025 [P] [US2] Convert `about.html` → `resources/views/pages/about.blade.php` + `PublicPageController@about`; keep scripts `public/Mateen/js/about-1.js`, `public/Mateen/js/about-2.js`
- [X] T026 [P] [US2] Convert `courses.html` → `resources/views/pages/courses.blade.php` + `PublicPageController@courses`; render subjects/materials from DB; stop `public/Mateen/js/courses-firebase.js` Firestore path when Laravel mode is on
- [X] T027 [P] [US2] Convert `library.html` → `resources/views/pages/library.blade.php` + `PublicPageController@library`; four library sections from `app/Models/LibraryItem.php`; update `public/Mateen/js/library.js` / `public/Mateen/js/library-firebase.js` to Laravel-only for normal use
- [X] T028 [P] [US2] Convert `news.html` → `resources/views/pages/news.blade.php` + `PublicPageController@news`; published news from `app/Models/NewsItem.php`; keep `public/Mateen/js/news-page.js` / `public/Mateen/js/news-1.js` / `public/Mateen/js/news-2.js` on `/api/v1/news`
- [X] T029 [P] [US2] Convert `schedule.html` → `resources/views/pages/schedule.blade.php` + `PublicPageController@schedule`; Hijri/Gregorian display from `app/Models/ScheduleEntry.php`
- [X] T030 [US2] Convert `login.html` → `resources/views/pages/login.blade.php` + `AuthPageController@login`; keep email+password via `public/Mateen/js/login-1.js` → `POST /api/v1/auth/login`; after success redirect to the same role workspace URLs as live site
- [X] T031 [US2] Convert `onboarding.html` → `resources/views/pages/onboarding.blade.php` + `AuthPageController@onboarding` (password-reset / first-login flow for migrated users with `must_reset_password`)

### Implementation for User Story 2 — student + messaging + stats

- [X] T032 [P] [US2] Convert `student.html` → `resources/views/pages/student/dashboard.blade.php` + `StudentPageController@dashboard`; port `public/Mateen/js/student-1.js` / `public/Mateen/js/student.js`
- [X] T033 [P] [US2] Convert `student-general.html` → `resources/views/pages/student/general.blade.php` + `StudentPageController@general`
- [X] T034 [P] [US2] Convert `student-view.html` → `resources/views/pages/student/view.blade.php` + `StudentPageController@view`; port `public/Mateen/js/student-view.js`
- [X] T035 [P] [US2] Convert `messages.html` → `resources/views/pages/messages.blade.php` + `StaffPageController@messages` (shared inbox UI); port `public/Mateen/js/messages.js` to `/api/v1/conversations`
- [X] T036 [P] [US2] Convert `stats.html` → `resources/views/pages/stats.blade.php` + `StaffPageController@stats`; port `public/Mateen/js/stats.js` / `public/Mateen/js/export.js` to `/api/v1/stats/*`

### Implementation for User Story 2 — teacher pages

- [X] T037 [P] [US2] Convert teacher subject pages into Blade under `resources/views/pages/teacher/` from `teacher-tafseer.html`, `teacher-fiqh.html`, `teacher-aqeedah.html`, `teacher-hadeeth.html`, `teacher-quran1.html`, `teacher-quran2.html`, `teacher-ithraiyat.html` via `TeacherPageController` actions; reuse one subject-workspace partial `resources/views/partials/teacher-subject-workspace.blade.php` where markup matches
- [X] T038 [P] [US2] Convert `teacher-library.html` → `resources/views/pages/teacher/library.blade.php` + `TeacherPageController@library`
- [X] T039 [P] [US2] Convert `teacher-profile.html` → `resources/views/pages/teacher/profile.blade.php` + `TeacherPageController@profile`
- [X] T040 [P] [US2] Convert `teacher-schedule.html` → `resources/views/pages/teacher/schedule.blade.php` + `TeacherPageController@schedule`
- [X] T041 [P] [US2] Convert `teacher-students.html` → `resources/views/pages/teacher/students.blade.php` + `TeacherPageController@students`

### Implementation for User Story 2 — staff workspaces

- [X] T042 [US2] Convert `admin.html` → `resources/views/pages/admin/dashboard.blade.php` + `StaffPageController@admin`; port `public/Mateen/js/admin-1.js` / `public/Mateen/js/admin-news.js` to Laravel API only
- [X] T043 [P] [US2] Convert `supervisor.html` → `resources/views/pages/supervisor/dashboard.blade.php` + `StaffPageController@supervisor`
- [X] T044 [P] [US2] Convert `support.html` → `resources/views/pages/support/dashboard.blade.php` + `StaffPageController@support`; theme assignment via `/api/v1/support/users/{user}/theme`
- [X] T045 [P] [US2] Convert `my-students.html` → `resources/views/pages/staff/my-students.blade.php` + `StaffPageController@myStudents`
- [X] T046 [US2] Update `public/Mateen/js/nav.js`, `public/Mateen/js/session.js`, `public/Mateen/js/ui.js`, and role scripts so internal links point at Blade routes / preserved `.html` aliases (no leftover relative links to missing static files)
- [X] T047 [US2] Spot-check all 28 Blade pages vs live GitHub Pages (styles, scripts, media, primary actions) and record results in `specs/002-unify-laravel-app/checklists/blade-live-parity.md` and `specs/002-unify-laravel-app/checklists/asset-smoke.md` (≥10 pages required by SC-005; target 28/28)

**Checkpoint**: US2 independently testable — full screen parity at preserved public paths (FR-003, FR-005, FR-009, FR-010, SC-002, SC-005)

---

## Phase 5: User Story 3 — Edit interface and server work in one place (Priority: P2)

**Goal**: One local run path; UI (Blade) and server (`app/`) edits land in the same root app; pages call same-origin `/api/v1` without a separate front API host

**Independent Test**: Change home copy in `resources/views/pages/home.blade.php` and a validation/API string in `app/`; reload; both apply. Network tab shows `/api/v1/…` on the same host. No second product tree required.

### Implementation for User Story 3

- [X] T048 [US3] Document the single local workflow in root `README.md`: `composer install`, `php artisan migrate --seed`, `php artisan serve`, open `/` (Blade), API at `/api/v1`
- [X] T049 [US3] Audit remaining Firebase data-path usage under `public/Mateen/js/` (`home.js`, `courses-firebase.js`, `library-firebase.js`, `home-1.js`, `home-2.js`, `home-msg.js`, etc.) and gate it behind `USE_LARAVEL_API=false` only; normal Blade pages must not require Firestore
- [X] T050 [P] [US3] Add `@vite` only if a page needs compiled assets; otherwise keep serving existing `/Mateen/css` + `/Mateen/js` (no forced SPA rewrite) and document the choice in `specs/002-unify-laravel-app/contracts/layout-and-urls.md`
- [X] T051 [US3] Update `specs/002-unify-laravel-app/contracts/layout-and-urls.md` so authoritative UI is Blade under `resources/views/` served by `routes/web.php`, with static assets still at `/Mateen/{css,js,libs,…}` and HTML paths preserved as aliases
- [X] T052 [US3] Update `specs/002-unify-laravel-app/contracts/environments.md` smoke URLs to Blade (`/` and `/Mateen/html/login.html` both 200 via Laravel) and same-origin `/api/v1/auth/login`
- [X] T053 [US3] Verify cross-cutting edit path (Blade text + `app/` message) through the same running app; note result in `specs/002-unify-laravel-app/checklists/blade-live-parity.md`

**Checkpoint**: US3 independently testable — one edit/run workflow (FR-004, FR-012, SC-003)

---

## Phase 6: User Story 4 — Retire the old split layout cleanly (Priority: P2)

**Goal**: Static HTML is no longer live UI; GitHub Pages / obsolete trees are non-authoritative; staging then production serve Blade from the unified Laravel `public/` docroot; live data is on the new site

**Independent Test**: No live `public/Mateen/html/*.html` files shadowing routes; `_obsolete` / `resources/html-source` marked do-not-edit-as-live; staging `/` is Blade home matching live behavior; production cutover no longer uses `mateenweb.github.io` as the live UI source

### Implementation for User Story 4

- [X] T054 [US4] Confirm `public/Mateen/html/` is empty or removed; conversion sources live only in `resources/html-source/` with `resources/html-source/DO_NOT_EDIT.md` (source archive, not live UI)
- [X] T055 [US4] Update `_obsolete/frontend/DO_NOT_EDIT.md` (and root `README.md`) to state Blade + `public/Mateen` assets are authoritative; old HTML trees and GitHub Pages are not editable product source
- [X] T056 [P] [US4] Update `.github/workflows` smoke checks (CI / `deploy-vps.yml` / related) to assert `GET /` and `GET /Mateen/html/home.html` return Blade (look for layout markers / 200) instead of requiring a static file on disk
- [X] T057 [US4] Update PWA/service worker in `public/Mateen/sw.js` and `public/sw.js` so cached page URLs still work after Blade conversion (do not cache stale static HTML from GitHub Pages or removed files)
- [X] T058 [US4] Deploy to staging (`contracts/environments.md`) and smoke: Blade home, login, one staff page, one student page, `POST /api/v1/auth/login` same origin
- [X] T059 [US4] Production cutover: serve unified Laravel Blade UI as live site; confirm previous front-only host (`https://mateenweb.github.io/Mateen/html/home.html` / Firebase Hosting) is not the live UI source (FR-011); keep old `.html` paths working on the new origin

**Checkpoint**: US4 complete — Blade is live UI, obsolete HTML marked, cutover done

---

## Phase 7: Operational data migration (supports US2 + US4 / 001 FR-016)

**Purpose**: Move all existing Mateen operational data onto the new Laravel website so Blade pages are not an empty shell

**Independent Test**: `php artisan mateen:audit-migration` on the export reports collection counts; sample ≥50 users show role/enrollments/subjects/schedules; Blade home/news/library/admin lists show migrated records (001 SC-007)

- [X] T060 Obtain a full Firebase / live-site JSON export (users, students, subjects/materials, library, assignments, conversations/messages, news, schedules, devices, contact/registration collections) into `storage/app/migration-fixtures/` (gitignored; never commit secrets or PII dumps)
- [X] T061 Run dry-run `php artisan mateen:migrate-firebase storage/app/migration-fixtures/<export>.json --dry-run` and fix mapper gaps in `app/Services/FirebaseMigration/DocumentMapper.php` / `app/Services/FirebaseMigrationService.php` until audit counts match source collections
- [X] T062 Import for real on staging with `php artisan mateen:migrate-firebase storage/app/migration-fixtures/<export>.json` then `php artisan mateen:audit-migration`; confirm `must_reset_password=true` for imported users
- [X] T063 [P] Seed or upsert public catalog content that the live home/courses/library/news/schedule pages show (subjects copy, published news, library items, important dates) via seeder updates in `database/seeders/MateenDemoSeeder.php` **or** the Firebase import — Blade public pages must not look empty compared to GitHub Pages
- [X] T064 [P] Verify media URLs (Cloudinary / existing hosts) still resolve after import for materials, library, and message attachments; document broken-URL handling in `specs/002-unify-laravel-app/checklists/data-migration-audit.md`
- [X] T065 Walk Blade admin/student/teacher screens on staging against migrated records (login with reset flow, messages, schedules, students) and record the ≥99% sample audit in `specs/002-unify-laravel-app/checklists/data-migration-audit.md`
- [X] T066 Production data load (after T059 gate): run the same import against production MySQL, re-run audit, and confirm live Blade UI shows the migrated dataset (001 FR-016 / SC-007)

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Docs, parity cleanup, quickstart validation

- [X] T067 [P] Update `specs/002-unify-laravel-app/quickstart.md` scenarios: V2/V4/V8 use Blade URLs; add V9 live-home parity vs https://mateenweb.github.io/Mateen/html/home.html; add V10 data-migration audit
- [X] T068 [P] Update `specs/002-unify-laravel-app/research.md` R2/R5 to record the Blade decision change (static public HTML superseded)
- [X] T069 [P] Final README pass: authoritative edit paths (`resources/views/`, `app/`, `public/Mateen/{css,js,libs}`); do not edit `resources/html-source/` or `_obsolete/frontend/` for product changes
- [X] T070 Run full `specs/002-unify-laravel-app/quickstart.md` (updated V1–V10) and fix remaining gaps
- [X] T071 [P] Cross-link 001 environments packaging notes to the Blade URL contract in `specs/001-laravel-backend/contracts/environments.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Setup — **BLOCKS** all user stories
- **US1 (Phase 3)**: After Foundational — MVP Blade home + single-project docs
- **US2 (Phase 4)**: After Foundational (needs layouts/routes); can start public pages in parallel with finishing US1 docs; login/role pages should follow home
- **US3 (Phase 5)**: After US1 home exists; best after US2 page set is in place for Firebase audit
- **US4 (Phase 6)**: After US2 (all pages converted) + US3 config; staging/prod need Phase 7 data for a non-empty site
- **Data migration (Phase 7)**: Mapper work can start after T018; staging import after US2 pages exist; production import after/with US4 cutover
- **Polish (Phase 8)**: After desired stories + staging data smoke

### User Story Dependencies

| Story | Depends on | Notes |
|-------|------------|-------|
| US1 | Phase 2 | Blade home is the MVP surface |
| US2 | Phase 2 (+ US1 layout patterns) | Remaining 27 pages; independently testable per page group |
| US3 | US1 (needs Blade home + config) | Same-origin + single workflow |
| US4 | US2 + US3 | Retire static HTML + cutover |
| Data | T018 + US2 pages for verification | Required for non-empty live parity |

### Within Each User Story

- Layout/partials before page views
- Controller + route before moving a static HTML file out of `public/`
- Page Blade + scripts before parity checklist
- Story complete before production cutover

### Parallel Opportunities

- T001–T005 setup docs/checklists in parallel after T001 starts
- T006–T008 layouts/partials in parallel with T009 helper and T011 controllers
- T025–T029 public pages in parallel
- T032–T036 student/messages/stats in parallel
- T037–T041 teacher pages in parallel
- T043–T045 staff pages in parallel (T042 admin is larger; avoid colliding on `routes/web.php` / shared partials)
- T060 mapper fixes can overlap late US2
- T067–T069 polish docs in parallel

---

## Parallel Example: User Story 1

```text
# After Phase 2:
Task: "Convert home.html into resources/views/pages/home.blade.php"
Task: "Implement PublicPageController@home with subjects/news/schedules"
Task: "Point home.js / home-msg.js at Laravel contact + registration endpoints"
```

---

## Parallel Example: User Story 2 (public pages)

```text
# After Phase 2 / US1 layout exists:
Task: "Convert about.html → resources/views/pages/about.blade.php"
Task: "Convert courses.html → resources/views/pages/courses.blade.php"
Task: "Convert library.html → resources/views/pages/library.blade.php"
Task: "Convert news.html → resources/views/pages/news.blade.php"
Task: "Convert schedule.html → resources/views/pages/schedule.blade.php"
```

---

## Parallel Example: Data migration

```text
Task: "Extend DocumentMapper for contact + registration collections"
Task: "Prepare gitignored export under storage/app/migration-fixtures/"
Task: "Draft data-migration-audit.md checklist template"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (live diff + route map)
2. Complete Phase 2: Foundational (layouts, routes, unshadow HTML)
3. Complete Phase 3: US1 Blade home matching https://mateenweb.github.io/Mateen/html/home.html
4. **STOP and VALIDATE**: visual/functional home parity + `/` and `/Mateen/html/home.html`

### Incremental Delivery

1. Setup + Foundational → Blade platform ready
2. US1 → live-matching home (MVP)
3. US2 → remaining public, auth, student, teacher, staff Blade pages
4. Phase 7 staging data import → pages show real Mateen records
5. US3 → same-origin + one workflow docs
6. US4 → retire static HTML + staging/prod cutover
7. Phase 7 production import + Phase 8 polish

### Suggested MVP scope

**US1 only** (after Phase 2): Blade home at `/` and `/Mateen/html/home.html` matching the live GitHub home, with subjects/news/schedules from Laravel. Do not cut over production until US2 + staging data migration pass.

---

## Notes

- [P] = different files, no incomplete-task dependencies
- [US1]–[US4] map to spec.md stories; Phase 7 data work supports US2/US4 and `001` FR-016
- Do not commit Firebase exports, `.env`, or PII dumps
- Do not edit `_obsolete/frontend/` or `resources/html-source/` as live product UI after conversion
- Domain API remains `/api/v1` (001); Blade is the new page layer
- Preserve FR-009 paths: users may keep bookmarking `/Mateen/html/home.html`
- Live comparison source: [Mateen GitHub Pages home](https://mateenweb.github.io/Mateen/html/home.html) and https://github.com/mateenweb/Mateen
- Commit after each task or logical group
- Stop at checkpoints to validate each story independently
