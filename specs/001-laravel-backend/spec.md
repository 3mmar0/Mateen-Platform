# Feature Specification: Laravel Backend Platform

**Feature Branch**: `001-laravel-backend`

**Created**: 2026-07-30

**Status**: Draft

**Input**: User description: "i want to make the backend using laravel for this project"

## Clarifications

### Session 2026-07-30

- Q: For the production go-live of the new backend, should existing Mateen records be migrated from the current system, or should the new backend start empty? → A: Migrate existing operational data into the new backend before/at cutover
- Q: For the first production-ready backend release, should every current Mateen capability ship together, or should delivery be phased? → A: **Revised** — Backend covers **all** Mateen features for production; build order may still follow P1→P3 priorities, but production cutover waits until full parity (supersedes earlier “Phase 1 production-ready” answer)
- Q: How should users sign in to the new backend? → A: Email + password (phone optional on profile only)
- Q: During Phase 1 production, what should happen to features not yet on the new backend? → A: Make the backend for all features — no hybrid split; single production cutover when the new backend serves the full Mateen capability set
- Q: When an account is permanently deleted, what should happen to that person’s past messages, submissions, and related records? → A: Hard-delete personal identity and content they solely own; keep only anonymous aggregates needed for stats

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Sign in and reach role-appropriate workspace (Priority: P1)

As a registered user (admin, support, supervisor, teacher, student, or Mateen friend), I sign in with my **email and password** and am taken into the areas my role is allowed to use. Guests can only see public pages and register.

**Why this priority**: Without reliable identity and role access, no other platform capability is usable or secure.

**Independent Test**: Create one account per role, sign in, and confirm each role only reaches permitted areas; reject invalid credentials with a clear message.

**Acceptance Scenarios**:

1. **Given** a valid account with role "student", **When** the user signs in, **Then** they access their student workspace and cannot open admin-only management screens.
2. **Given** invalid credentials, **When** the user attempts to sign in, **Then** access is denied and a clear error is shown without revealing which field was wrong.
3. **Given** an admin account, **When** the admin signs in, **Then** they can open the full management workspace.
4. **Given** a signed-in user, **When** they choose to permanently delete their account (or an admin deletes them), **Then** they can no longer sign in, their personal identity and solely owned content are hard-deleted, and retained statistics do not identify them.

---

### User Story 2 - Manage scientific subjects and learning content (Priority: P1)

As admin or supervisor, I manage the scientific subjects and add/edit/remove learning materials (documents, video, articles, links). Teachers manage content only for their assigned subject. Students see materials only for subjects they enrolled in.

**Why this priority**: Core educational value of Mateen is subject content delivery under role rules.

**Independent Test**: With sample subjects and users, verify create/update/delete permissions per role and that students only see enrolled subject content.

**Acceptance Scenarios**:

1. **Given** an admin, **When** they add material to any subject, **Then** enrolled students of that subject can view it.
2. **Given** a teacher assigned to one subject, **When** they try to edit another subject's content, **Then** the action is rejected.
3. **Given** a student enrolled in Tafsir only, **When** they open subjects, **Then** they see Tafsir content and not content from unenrolled subjects.
4. **Given** a Mateen friend account, **When** they view subjects, **Then** they see subject descriptions only and cannot enroll.

---

### User Story 3 - Manage students, interviews, and schedules (Priority: P2)

As admin or supervisor, I add students (individually or in bulk), track interview and acceptance status, and maintain weekly study schedules with Hijri/Gregorian date awareness. Teachers view students related to their subject.

**Why this priority**: Operations and admissions depend on student records and scheduling; needed soon after auth and content.

**Independent Test**: Add students, update interview/status fields, assign schedule entries, and export student data; confirm teachers only see their related students.

**Acceptance Scenarios**:

1. **Given** an admin, **When** they add a student (single or bulk), **Then** the student appears in the student list with correct status defaults.
2. **Given** interview status "not done", **When** admin marks it completed and updates acceptance status, **Then** the updated status is visible to authorized roles.
3. **Given** a weekly schedule entry, **When** a student opens their schedule, **Then** they see their sessions with correct date context.
4. **Given** an admin exporting student data, **When** they choose columns and time grouping, **Then** they receive a downloadable report matching the selection.

---

### User Story 4 - Assignments, library, news, and messaging (Priority: P2)

As teachers/admins, I create assignments linked to a lecture/subject and review submissions. Authorized roles manage the four library sections and news. Users message each other under role rules (students and friends: text only; staff roles may send images/voice).

**Why this priority**: Completes day-to-day teaching and communication; required for production cutover along with all other Mateen capabilities.

**Independent Test**: Create an assignment and submit/grade it; publish library and news items; send messages under each role’s media rules and confirm delivery.

**Acceptance Scenarios**:

1. **Given** a teacher, **When** they create an assignment for their subject, **Then** enrolled students can submit and the teacher can review/grade.
2. **Given** admin or supervisor, **When** they add an item to any library section, **Then** students and friends can view it.
3. **Given** a student, **When** they send a message, **Then** only text is allowed and recipients are limited to teachers, supervisor, and admin.
4. **Given** a new message for a user, **When** that user is reachable for notifications, **Then** they receive a timely in-app and/or push notification of the message.

---

### User Story 5 - Support tools, themes, stats, and continuity (Priority: P3)

As support staff, I view/manage users, message them, and assign account themes. Admins/supervisors view attendance/grades statistics and export reports (spreadsheet, document, or PDF). Existing platform data remains available after the backend switch so operations are not reset.

**Why this priority**: Completes operations parity; required in the same production cutover as other features, with full data migration.

**Independent Test**: Assign a theme and confirm it applies for that account; generate a stats export; verify migrated or seeded operational data matches expected records for a sample of users.

**Acceptance Scenarios**:

1. **Given** support staff, **When** they assign a theme to a user, **Then** that user’s interface uses the chosen theme on subsequent visits.
2. **Given** attendance and grade data, **When** an authorized user opens statistics and exports a report, **Then** the file reflects the on-screen metrics for the chosen filters.
3. **Given** existing production user and content records, **When** the new backend goes live after migration, **Then** authorized users can continue working with those migrated records without re-entering them manually.

---

### Edge Cases

- What happens when a user loses network mid-save? Partial updates are not left in an inconsistent visible state; user sees a recoverable error and can retry.
- How does the system handle duplicate registration (same email already used)? Registration is rejected with a clear message; phone alone does not define account uniqueness.
- What happens when a teacher is removed from a subject that still has their content? Content remains available under admin/supervisor control; teacher loses further edit rights.
- What happens when a student is deleted while having submissions/messages? Personal identity and solely owned content are hard-deleted; conversation threads may remain for other participants without the deleted user’s personal identifiers; only anonymous aggregates remain for statistics.
- What happens when a media upload fails or is oversized? Upload is rejected with a clear limit/error; conversation/content save does not pretend success.
- What happens when role permissions conflict (e.g., user marked with multiple roles)? System enforces a single primary role per account unless explicitly configured otherwise.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST authenticate users and enforce exactly the six platform roles: admin, support, supervisor, teacher, student, and Mateen friend, plus public guest access for home/about/registration only.
- **FR-002**: System MUST allow registration and sign-in using **email + password** as the sole login identifier; phone MAY be stored on the profile but MUST NOT be required for authentication. System MUST support permanent account deletion that removes the user’s ability to authenticate, **hard-deletes** their personal identity and content they solely own (e.g., their profile, sole-owned drafts/submissions as applicable), and MUST retain only anonymous aggregates required for statistics so individuals cannot be re-identified from retained stats.
- **FR-002a**: System MUST treat email as unique across accounts; duplicate email registration MUST be rejected.
- **FR-003**: System MUST authorize every sensitive action by role (subjects, library, students, messages, news, support tools, exports) matching current Mateen permission rules.
- **FR-004**: System MUST persist and serve scientific subjects and their learning materials, including admin edits to subject metadata and teacher-scoped content management.
- **FR-005**: System MUST support student enrollment rules (students enroll; Mateen friends view descriptions only and cannot enroll).
- **FR-006**: System MUST manage student records including interview status, acceptance/status classification, bulk add, and authorized viewing by teachers for related students.
- **FR-007**: System MUST manage weekly study schedules and present dates with Hijri and Gregorian awareness for users who rely on both calendars.
- **FR-008**: System MUST support assignments linked to a subject/lecture, student submission, and teacher review/grading.
- **FR-009**: System MUST manage four library sections (Mateen library, enrichment path, podcast, diverse courses) with admin/supervisor write access and broad read access for signed-in learners.
- **FR-010**: System MUST support direct messaging between allowed role pairs, restricting students and friends to text-only messages and allowing staff roles to attach images and voice recordings.
- **FR-011**: System MUST notify recipients of new messages in a timely way when notification delivery is available for that user.
- **FR-012**: System MUST support news/announcements create, update, delete (authorized roles) and read for signed-in users.
- **FR-013**: System MUST allow support staff to list users, message them, and assign per-account visual themes from the approved theme set.
- **FR-014**: System MUST provide statistics for attendance and grades (including highs/lows and subject breakdowns) and export selected reports as spreadsheet, document, or PDF.
- **FR-015**: System MUST expose a stable server-backed interface that the existing Mateen client can use so UI pages keep working after leaving the current hosted-database backend.
- **FR-016**: System MUST migrate existing operational data (users/roles, students, subjects/materials, library, assignments, conversations, news, and related records) from the current system into the new backend before or at production cutover so the platform continues without a cold start. Production cutover MUST migrate the full operational dataset for all Mateen domains going live together.
- **FR-017**: System MUST validate inputs, reject unauthorized access, and return clear Arabic-friendly error messages suitable for end users.
- **FR-018**: System MUST protect credentials and personal data in transit and at rest using industry-standard practices appropriate for an education platform.
- **FR-019**: The new backend MUST implement **all** current Mateen server-side capabilities (authentication/roles, subjects/materials, students/schedules, assignments, library, news, messaging with notifications, statistics/exports, and support tools/themes) before production cutover. Development MAY still follow story priority order (P1→P3), but production MUST NOT run a hybrid split where some features stay on the old backend while others use the new one.

### Key Entities

- **User**: Account identity keyed by unique email, password credential, optional phone on profile, role, theme preference, notification reachability.
- **Student Profile**: Extended learner record (interview status, acceptance/status class, enrollment links, grades/attendance summaries).
- **Subject**: Scientific subject metadata (title, subtitle, description, axes) and ownership/teaching assignment.
- **Learning Material**: Content item under a subject (type such as document, video, article, link; visibility tied to enrollment rules).
- **Library Item**: Content in one of four library sections.
- **Assignment**: Task tied to subject/lecture; includes submissions and grades.
- **Conversation / Message**: Thread between participants; message body and optional media per role rules.
- **News Item**: Announcement with title, body, publish state/timestamps.
- **Schedule Entry**: Weekly session timing linked to users/subjects as applicable.
- **Statistic Snapshot / Export Job**: Filtered metrics view and generated downloadable report.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of the six roles can complete sign-in and land in an allowed workspace within 30 seconds on a normal connection; unauthorized screens are blocked in testing for each role.
- **SC-002**: In acceptance testing, role permission checks for subjects, library, students, messaging, and news pass for all documented allow/deny cases with zero critical permission defects.
- **SC-003**: Teachers can create an assignment and students can submit it end-to-end in under 3 minutes in a guided test script.
- **SC-004**: Authorized staff can publish a library or news item and a student can see it within 1 minute under normal load.
- **SC-005**: Message send-to-notification path delivers a visible notification to an online recipient within 15 seconds in test conditions when notifications are enabled.
- **SC-006**: Statistics export for a standard student cohort completes and downloads successfully in under 2 minutes for typical cohort sizes used by the program.
- **SC-007**: After production cutover, a sample audit of at least 50 existing user records and their critical linked data (role, enrollments or student status, subject/material ownership, schedules, and other migrated domains) shows ≥ 99% correct continuity versus the pre-cutover source of truth.
- **SC-008**: During a supervised pilot with real staff roles covering the full capability set, ≥ 90% of participants complete their primary weekly tasks without needing a fallback to the old backend.
- **SC-009**: Production cutover is accepted only when SC-001 through SC-008 pass for the full Mateen capability set on the new backend.

## Assumptions

- Stakeholders chose **Laravel** as the server framework for this backend; planning and implementation will follow that choice while this specification stays focused on user-facing capabilities and outcomes.
- Sign-in uses email + password only; phone is an optional profile field and is not used as a login identifier.
- Permanent account deletion is a hard delete of personal identity and solely owned content; only anonymous statistical aggregates may remain.
- The existing Arabic RTL web/PWA client remains the primary interface; this feature delivers the server side and the contract needed for the client to talk to it (client adaptation is in scope as needed for cutover).
- Current Mateen role matrix and full feature set (users, subjects, library, assignments, messages, news, students, schedules, stats, themes, support tools) define required parity for production cutover. Story priorities (P1→P3) guide build order only; they do not authorize a partial production release.
- Media for messages continues to use an external media hosting service comparable to today’s approach unless replaced later; the backend remains responsible for authorizing uploads and storing references.
- Push/in-app notifications remain part of messaging parity; exact provider may change as long as SC-005 is met.
- Hijri ↔ Gregorian presentation remains required wherever schedules and relevant dates are shown.
- Data migration from the current Firebase-backed store into the new backend is required for the single full-feature production cutover (FR-016 / SC-007). An empty-data go-live and a hybrid old/new production split are out of scope.
- Constitution principles in this repo are still template placeholders; this feature does not wait on constitution customization.
- Out of scope for this feature unless later expanded: rebuilding the entire UI from scratch, native mobile apps beyond the existing PWA/APK packaging, and non-Mateen multi-tenant SaaS.
