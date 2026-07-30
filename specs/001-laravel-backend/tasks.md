# Tasks: Laravel Backend Platform

**Input**: Design documents from `/specs/001-laravel-backend/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Not included as separate tasks (spec did not require TDD). Validate via quickstart scenarios during polish.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete work)
- **[Story]**: User story label (US1–US5)
- Paths follow `backend/` Laravel app + existing `js/` client from plan.md

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold Laravel API project and shared tooling

- [X] T001 Create Laravel 12 (PHP 8.3+) application in `backend/` per plan.md structure
- [X] T002 Configure `.env.example` in `backend/.env.example` with DB, FRONTEND_URL, CLOUDINARY_*, FCM_*, MAIL_*, CORS placeholders
- [X] T003 [P] Install Sanctum and publish config in `backend/config/sanctum.php` and `backend/config/cors.php`
- [X] T004 [P] Add Composer deps for Excel/PDF/Word exports, Hijri helper, and Cloudinary SDK in `backend/composer.json`
- [X] T005 [P] Configure Pest (or PHPUnit) bootstrap in `backend/tests/` and `backend/phpunit.xml`
- [X] T006 [P] Add Arabic validation language lines in `backend/lang/ar/validation.php` and set locale defaults in `backend/config/app.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared schema, auth primitives, enums, policies base — MUST complete before user stories

**⚠️ CRITICAL**: No user story work begins until this phase is complete

- [X] T007 Create `Role` and shared enums in `backend/app/Enums/Role.php`, `MaterialType.php`, `LibrarySection.php`, `InterviewStatus.php`, `NewsStatus.php`
- [X] T008 Create users migration extending auth users for Mateen fields in `backend/database/migrations/xxxx_create_users_table.php` (email unique, role, phone, subject_id nullable, theme_id, firebase_uid, must_reset_password)
- [X] T009 Create `subjects` migration in `backend/database/migrations/xxxx_create_subjects_table.php`
- [X] T010 Implement `User` and `Subject` models in `backend/app/Models/User.php` and `backend/app/Models/Subject.php`
- [X] T011 Configure Sanctum token abilities and `HasApiTokens` on `backend/app/Models/User.php`; register API middleware in `backend/bootstrap/app.php`
- [X] T012 Create base API exception/JSON error formatter with Arabic-friendly messages in `backend/app/Exceptions/Handler.php` or `backend/bootstrap/app.php`
- [X] T013 Create abstract `ApiController` helpers and API Resource base in `backend/app/Http/Controllers/Api/Controller.php`
- [X] T014 Define route group prefix `api/v1` and auth middleware stubs in `backend/routes/api.php`
- [X] T015 Create role Gate/`RolePolicy` helpers and register policies in `backend/app/Providers/AuthServiceProvider.php` (or `AppServiceProvider`)
- [X] T016 Create database seeder for six roles sample users + five subjects in `backend/database/seeders/DatabaseSeeder.php` and `MateenDemoSeeder.php`
- [X] T017 [P] Add client API config flag and base URL in `js/config.js` (`API_BASE_URL`, `USE_LARAVEL_API`)
- [X] T018 [P] Create shared fetch client with Bearer token storage in `js/api.js`

**Checkpoint**: Foundation ready — user stories can proceed

---

## Phase 3: User Story 1 — Sign in & role-appropriate workspace (Priority: P1) 🎯 MVP

**Goal**: Email/password register/login/logout, password reset, `/auth/me`, hard-delete account, six-role enforcement for workspace access

**Independent Test**: Login as each role; wrong password rejected; role mismatch with `expected_role` returns 403; delete account blocks further login (quickstart V1)

### Implementation for User Story 1

- [X] T019 [P] [US1] Create auth Form Requests in `backend/app/Http/Requests/Auth/LoginRequest.php`, `RegisterRequest.php`, `ForgotPasswordRequest.php`, `ResetPasswordRequest.php`
- [X] T020 [P] [US1] Create `UserResource` in `backend/app/Http/Resources/UserResource.php`
- [X] T021 [US1] Implement `AuthController` (register, login, logout, me, forgot/reset password) in `backend/app/Http/Controllers/Api/AuthController.php`
- [X] T022 [US1] Wire auth routes matching `contracts/openapi.yaml` Auth paths in `backend/routes/api.php`
- [X] T023 [US1] Implement account hard-delete service (identity + solely owned content scrub) in `backend/app/Services/AccountDeletionService.php`
- [X] T024 [US1] Implement `DELETE /users/{id}` in `backend/app/Http/Controllers/Api/UserController.php` with self-or-admin authorization
- [X] T025 [US1] Add password-reset notification/mailables in `backend/app/Notifications/ResetPasswordNotification.php`
- [X] T026 [US1] Switch login/register/reset UI to Laravel API when `USE_LARAVEL_API` in `js/login-1.js` using `js/api.js`
- [X] T027 [US1] Persist token/role/redirect after API login in `js/login-1.js` (preserve existing `computeBaseRedirect` behavior)

**Checkpoint**: US1 independently testable — MVP auth works without Firebase Auth

---

## Phase 4: User Story 2 — Scientific subjects & learning content (Priority: P1)

**Goal**: Subjects CRUD (admin metadata), materials CRUD with teacher scope, enrollment rules for students vs mateen friends

**Independent Test**: Teacher cannot edit other subject; enrolled student sees materials; mateen cannot enroll (quickstart V2)

### Implementation for User Story 2

- [X] T028 [P] [US2] Create `learning_materials` and `enrollments` migrations in `backend/database/migrations/`
- [X] T029 [P] [US2] Create `LearningMaterial` and `Enrollment` models in `backend/app/Models/LearningMaterial.php` and `Enrollment.php`
- [X] T030 [P] [US2] Create `SubjectPolicy` and `LearningMaterialPolicy` in `backend/app/Policies/SubjectPolicy.php` and `LearningMaterialPolicy.php`
- [X] T031 [US2] Implement `SubjectController` and resources in `backend/app/Http/Controllers/Api/SubjectController.php` and `backend/app/Http/Resources/SubjectResource.php`
- [X] T032 [US2] Implement `MaterialController` for subject-scoped materials in `backend/app/Http/Controllers/Api/MaterialController.php`
- [X] T033 [US2] Implement enrollment endpoint `POST /subjects/{id}/enrollments` with mateen forbidden in `backend/app/Http/Controllers/Api/EnrollmentController.php`
- [X] T034 [US2] Wire Subjects/Materials/Enrollment routes per `contracts/openapi.yaml` in `backend/routes/api.php`
- [X] T035 [US2] Adapt courses/subjects client reads/writes to API in `js/courses-firebase.js` and `js/subjects.js` behind `USE_LARAVEL_API`
- [X] T036 [US2] Adapt teacher subject pages material CRUD to API in relevant `js/teacher-*.js` modules behind `USE_LARAVEL_API`

**Checkpoint**: US1 + US2 work independently for content delivery

---

## Phase 5: User Story 3 — Students, interviews & schedules (Priority: P2)

**Goal**: Student profiles, bulk add, interview/status updates, weekly schedules with Hijri display, student export

**Independent Test**: Bulk create students; update interview; schedule returns hijri fields; export downloads (quickstart V3)

### Implementation for User Story 3

- [X] T037 [P] [US3] Create `student_profiles` and `schedule_entries` migrations in `backend/database/migrations/`
- [X] T038 [P] [US3] Create `StudentProfile` and `ScheduleEntry` models in `backend/app/Models/StudentProfile.php` and `ScheduleEntry.php`
- [X] T039 [P] [US3] Create Hijri formatting helper/service in `backend/app/Services/HijriDateService.php`
- [X] T040 [US3] Implement `StudentController` (list/create/update/bulk) in `backend/app/Http/Controllers/Api/StudentController.php` with policies in `backend/app/Policies/StudentProfilePolicy.php`
- [X] T041 [US3] Implement `ScheduleController` returning Gregorian + `hijri_display` in `backend/app/Http/Controllers/Api/ScheduleController.php` and `ScheduleResource.php`
- [X] T042 [US3] Implement student export service (docx/xlsx/pdf) in `backend/app/Services/StudentExportService.php` and `POST /students/export` action
- [X] T043 [US3] Wire Students/Schedules routes per OpenAPI in `backend/routes/api.php`
- [X] T044 [US3] Adapt admin/supervisor student management UI to API in `js/admin-1.js` and `js/supervisor-1.js` behind `USE_LARAVEL_API`
- [X] T045 [US3] Adapt schedule page client to API in `js/` schedule-related modules and `html/schedule.html` scripts behind `USE_LARAVEL_API`

**Checkpoint**: Admissions/ops flows work on API for US3

---

## Phase 6: User Story 4 — Assignments, library, news & messaging (Priority: P2)

**Goal**: Assignments/submissions, four library sections, news CRUD, messaging with media rules, FCM notifications, signed media upload

**Independent Test**: Assignment submit/grade; library/news visible; student media rejected; notification ≤15s with queue worker (quickstart V4)

### Implementation for User Story 4

- [X] T046 [P] [US4] Create migrations for `assignments`, `assignment_submissions`, `library_items`, `news_items`, `conversations`, `conversation_user`, `messages`, `user_devices` in `backend/database/migrations/`
- [X] T047 [P] [US4] Create models for Assignment, AssignmentSubmission, LibraryItem, NewsItem, Conversation, Message, UserDevice in `backend/app/Models/`
- [X] T048 [P] [US4] Create policies for Assignment, LibraryItem, NewsItem, Conversation in `backend/app/Policies/`
- [X] T049 [US4] Implement `AssignmentController` + submission grade/submit actions in `backend/app/Http/Controllers/Api/AssignmentController.php`
- [X] T050 [US4] Implement `LibraryController` and `NewsController` in `backend/app/Http/Controllers/Api/LibraryController.php` and `NewsController.php`
- [X] T051 [US4] Implement messaging service enforcing role/media rules in `backend/app/Services/MessagingService.php`
- [X] T052 [US4] Implement `ConversationController` / message endpoints in `backend/app/Http/Controllers/Api/ConversationController.php`
- [X] T053 [US4] Implement Cloudinary signed upload endpoint in `backend/app/Http/Controllers/Api/MediaController.php` and `backend/app/Services/CloudinaryUploadService.php`
- [X] T054 [US4] Implement FCM notification job/channel on new message in `backend/app/Jobs/SendMessagePushNotification.php` and `backend/app/Services/FcmService.php`
- [X] T055 [US4] Implement device token registration `POST /devices` in `backend/app/Http/Controllers/Api/DeviceController.php`
- [X] T056 [US4] Wire Assignments/Library/News/Messaging/Media/Devices routes in `backend/routes/api.php`
- [X] T057 [US4] Adapt assignments UI to API in `js/assignments.js` and `js/assignments-ui.js` behind `USE_LARAVEL_API`
- [X] T058 [US4] Adapt library and news clients to API in `js/library-firebase.js`, `js/library.js`, `js/admin-news.js`, `js/news-1.js` behind `USE_LARAVEL_API`
- [X] T059 [US4] Adapt messaging + Cloudinary flow to API in `js/messages.js` and `js/cloud-upload.js` behind `USE_LARAVEL_API`
- [X] T060 [US4] Adapt FCM token registration to `POST /devices` in `js/notifications.js` behind `USE_LARAVEL_API`

**Checkpoint**: Teaching/communication domain complete on API

---

## Phase 7: User Story 5 — Support tools, themes, stats & continuity (Priority: P3)

**Goal**: Support user list/theme assignment, attendance/grade stats + export, Firebase→MySQL migration commands, cutover readiness

**Independent Test**: Theme persists; stats export &lt;2 min; migration audit ≥99% on fixtures; API-only pilot (quickstart V5–V7)

### Implementation for User Story 5

- [X] T061 [P] [US5] Create `attendance_records` and `grade_records` migrations/models in `backend/database/migrations/` and `backend/app/Models/`
- [X] T062 [P] [US5] Optional `export_jobs` migration/model in `backend/database/migrations/` and `backend/app/Models/ExportJob.php`
- [X] T063 [US5] Implement support user list + theme patch in `backend/app/Http/Controllers/Api/SupportController.php`
- [X] T064 [US5] Implement stats summary + export service in `backend/app/Services/StatsService.php` and `backend/app/Http/Controllers/Api/StatsController.php`
- [X] T065 [US5] Wire Support/Stats routes per OpenAPI in `backend/routes/api.php`
- [X] T066 [US5] Adapt support/admin theme tooling to API in `js/support-1.js` and `js/custom-theme.js` behind `USE_LARAVEL_API`
- [X] T067 [US5] Adapt stats/export client to API in `js/stats.js` and `js/export.js` behind `USE_LARAVEL_API`
- [X] T068 [US5] Build Firebase export fixture loader + Artisan migrate command in `backend/app/Console/Commands/MigrateFromFirebaseCommand.php` and `backend/app/Services/FirebaseMigrationService.php`
- [X] T069 [US5] Map Firestore collections (users, students, subjects/materials, library, assignments, conversations/messages, news, schedules/tokens) in `backend/app/Services/FirebaseMigration/` mappers
- [X] T070 [US5] Set `must_reset_password=true` for imported users and document reset flow in `backend/app/Services/FirebaseMigrationService.php`
- [X] T071 [US5] Add migration dry-run audit report command output in `backend/app/Console/Commands/AuditMigrationCommand.php`
- [X] T072 [US5] Extend `AccountDeletionService` to purge US3–US5 owned rows and anonymize stats aggregates in `backend/app/Services/AccountDeletionService.php`

**Checkpoint**: Full parity path ready for cutover validation

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Cutover hardening, docs, client flag cleanup

- [X] T073 [P] Add queue/Horizon-or-database-queue notes and supervisor example in `backend/README.md`
- [X] T074 [P] Document local seed credentials and quickstart mapping in `backend/README.md` and align with `specs/001-laravel-backend/quickstart.md`
- [X] T075 Ensure CORS + Sanctum production settings documented in `backend/.env.example`
- [X] T076 Run full quickstart V1–V7 checklist and record results in `specs/001-laravel-backend/quickstart.md` (checklist notes section)
- [X] T077 Remove or gate remaining Firebase data-path calls behind `USE_LARAVEL_API=false` only in `js/` modules touched by US1–US5
- [X] T078 Mark Cloud Functions retirement steps in `functions/README.md` or `docs-user-guide.md` appendix (post-cutover)
- [X] T079 Security pass: rate-limit auth routes, ensure HTTPS-only notes, review hard-delete paths in `backend/routes/api.php` and `AccountDeletionService.php`
- [X] T080 Final cutover config: production `USE_LARAVEL_API=true` and API base URL in `js/config.js`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: Start immediately
- **Phase 2 (Foundational)**: Depends on Setup — **blocks all stories**
- **Phase 3 (US1)**: After Foundational — **MVP**
- **Phase 4 (US2)**: After Foundational; ideally after US1 for client token reuse (can parallelize API work)
- **Phase 5 (US3)**: After Foundational; uses User/Subject from foundation/US2
- **Phase 6 (US4)**: After Foundational; assignments benefit from Subject/Material (US2)
- **Phase 7 (US5)**: After US1–US4 domains exist for migration mapping completeness
- **Phase 8 (Polish)**: After all stories intended for cutover (all required by FR-019)

### User Story Dependencies

| Story | Depends on | Notes |
|-------|------------|--------|
| US1 | Foundation | No other stories |
| US2 | Foundation (+ US1 token for client) | API can start in parallel with US1 |
| US3 | Foundation, Subject/User | Teacher subject scope from US2 helpful |
| US4 | Foundation, Subject (US2) | Messaging independent of US3 |
| US5 | US1–US4 preferred | Migration needs all tables |

### Parallel Opportunities

- T003–T006 (setup tooling) in parallel
- T017–T018 (client config) parallel with backend foundation after T007–T014 shape exists
- Within US2: T028–T030 parallel; within US4: T046–T048 parallel
- After Foundation: API for US2/US3/US4 can proceed on separate files while US1 client wiring finishes
- US5 migration mappers (T069) parallelizable by collection once tables exist

---

## Parallel Example: User Story 2

```text
# After Foundation, launch in parallel:
T028 Create learning_materials + enrollments migrations
T029 Create LearningMaterial + Enrollment models
T030 Create SubjectPolicy + LearningMaterialPolicy

# Then sequential:
T031 SubjectController → T032 MaterialController → T033 Enrollment → T034 routes → T035/T036 client
```

## Parallel Example: User Story 4

```text
T046 migrations | T047 models | T048 policies  (parallel)
Then: T049 Assignments | T050 Library/News | T051–T055 Messaging stack
Then: T056 routes → T057–T060 client adapters (parallel by file)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1 Setup  
2. Phase 2 Foundational  
3. Phase 3 US1  
4. **STOP & VALIDATE** quickstart V1  
5. Demo email/password API auth replacing Firebase Auth for login page  

### Incremental Delivery (dev order; production still full cutover)

1. US1 auth → US2 subjects → US3 students/schedules → US4 teaching/comms → US5 support/stats/migration  
2. Keep `USE_LARAVEL_API` false in production until SC-009  
3. Single production cutover after T076–T080  

### Parallel Team Strategy

1. Team finishes Setup + Foundational together  
2. Dev A: US1 client+auth · Dev B: US2 API · Dev C: US3 API  
3. Then US4 messaging/media · US5 migration  

---

## Notes

- [P] = different files, safe parallel  
- Story labels US1–US5 map to spec.md priorities  
- Production MUST implement all stories before cutover (FR-019) even though MVP validates US1 first  
- Commit after each task or logical group  
- Avoid reintroducing hybrid Firebase+Laravel production splits  
