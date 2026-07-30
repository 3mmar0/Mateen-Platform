# Data Model: Laravel Backend Platform

**Feature**: `001-laravel-backend` | **Date**: 2026-07-30  
**Source**: [spec.md](./spec.md) Key Entities + FR-001–FR-019

Storage: MySQL 8+ / utf8mb4. Timestamps `created_at` / `updated_at` on all mutable tables unless noted.

---

## Enumerations

| Name | Values |
|------|--------|
| `Role` | `admin`, `support`, `supervisor`, `teacher`, `student`, `mateen` |
| `InterviewStatus` | `not_done`, `done` |
| `StudentStatusClass` | `newcomer`, `mateen_girls` (align labels with current UI; exact codes confirmed at migration) |
| `MaterialType` | `pdf`, `video`, `article`, `link` |
| `LibrarySection` | `mateen_library`, `enrichment`, `podcast`, `courses` |
| `AssignmentStatus` | `open`, `closed` |
| `SubmissionStatus` | `submitted`, `graded`, `returned` |
| `NewsStatus` | `draft`, `published` |
| `ThemeId` | predefined set of 10 color themes + optional ornament ids (as today) |

---

## Entities

### User

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| firebase_uid | string nullable unique | Migration mapping |
| name | string | required |
| email | string | required, unique, login id |
| password | string | hashed; migrated users may require reset flag |
| must_reset_password | bool | default false; true after Firebase import |
| phone | string nullable | optional; not unique for auth |
| role | Role | required; single primary role |
| subject_id | FK nullable | required when role=`teacher` |
| theme_id | string nullable | support-assigned |
| ornament_id | string nullable | |
| is_active | bool | default true |
| email_verified_at | datetime nullable | |

**Relationships**: hasOne StudentProfile (if student/mateen as applicable); hasMany enrollments, messages, devices; belongsTo Subject (teacher).

**Delete**: Hard-delete user + solely owned rows; scrub PII on shared messages; keep anonymous aggregates.

---

### UserDevice

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| user_id | FK | cascade delete |
| fcm_token | string | unique |
| platform | string nullable | web/android/ios |
| last_seen_at | datetime nullable | |

---

### Subject

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| slug | string unique | e.g. tafsir, fiqh |
| title | string | |
| subtitle | string nullable | |
| description | text nullable | |
| axes | JSON/text nullable | المحاور |
| sort_order | int | |

**Relationships**: hasMany LearningMaterials, teachers (users), enrollments, assignments, schedule entries.

**Auth notes**: Admin may edit subject metadata; supervisor may manage materials across subjects but not necessarily core metadata (match current rules); teacher writes materials only for `subject_id`.

---

### LearningMaterial

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| subject_id | FK | required |
| title | string | |
| type | MaterialType | |
| body | text nullable | article/html/text |
| url | string nullable | link/video/pdf URL |
| created_by | FK users | |
| sort_order | int | |

**Visibility**: Enrolled students (role `student`) see materials for enrolled subjects; `mateen` friends see subject descriptions only, not materials content (FR-005).

---

### Enrollment

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| user_id | FK | student |
| subject_id | FK | |
| enrolled_at | datetime | |
| unique(user_id, subject_id) | | |

Only `student` may enroll; `mateen` cannot (FR-005).

---

### StudentProfile

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| user_id | FK unique | |
| interview_status | InterviewStatus | default `not_done` |
| status_class | StudentStatusClass | |
| notes | text nullable | |
| extra | JSON nullable | migration catch-all for legacy fields |

**Relationships**: attendance/grade rows as needed for stats.

---

### ScheduleEntry

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| subject_id | FK nullable | |
| title | string | |
| starts_at | datetime | Gregorian/UTC storage |
| ends_at | datetime nullable | |
| weekday | tinyint nullable | 0–6 if recurring weekly template |
| audience | string/JSON | e.g. all students / subject cohort |
| created_by | FK users | |

API resources MUST include Hijri display fields derived from `starts_at` (research R7).

---

### Assignment

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| subject_id | FK | |
| learning_material_id | FK nullable | lecture link |
| title | string | |
| description | text nullable | |
| due_at | datetime nullable | |
| status | AssignmentStatus | |
| created_by | FK users | teacher/admin |

---

### AssignmentSubmission

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| assignment_id | FK | |
| user_id | FK | student |
| content | text nullable | |
| attachment_url | string nullable | |
| status | SubmissionStatus | |
| grade | decimal/string nullable | |
| feedback | text nullable | |
| unique(assignment_id, user_id) | | |

Solely owned by student → hard-deleted with account.

---

### LibraryItem

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| section | LibrarySection | |
| title | string | |
| description | text nullable | |
| media_url | string nullable | |
| subject_filter | string nullable | Mateen library filter |
| created_by | FK users | |
| sort_order | int | |

Write: admin/supervisor. Read: signed-in learners (student, mateen, teacher, etc.).

---

### NewsItem

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| title | string | |
| body | text | |
| status | NewsStatus | |
| published_at | datetime nullable | |
| created_by | FK users | |

---

### Conversation

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| created_at | datetime | |

**Participants**: `conversation_user` pivot (`conversation_id`, `user_id`).

---

### Message

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| conversation_id | FK | |
| sender_id | FK users nullable | null after hard-delete scrub |
| sender_display | string nullable | snapshot; set to محذوف on delete |
| body | text nullable | required unless media |
| media_url | string nullable | |
| media_type | string nullable | image/audio |
| created_at | datetime | |

**Validation**: If sender role ∈ {`student`,`mateen`} → `media_url` MUST be null (text only). Recipient pairs enforced by service rules (FR-010).

---

### AttendanceRecord / GradeRecord (stats)

Minimal normalized facts for FR-014:

| Entity | Key fields |
|--------|------------|
| AttendanceRecord | user_id, subject_id, session_date, present bool |
| GradeRecord | user_id, subject_id, score, label, recorded_at |

On user hard-delete: delete or anonymize personal rows; keep **anonymous aggregates** (e.g. subject-level averages without user_id) if needed for historical charts.

---

### ExportJob (optional)

| Field | Type | Rules |
|-------|------|--------|
| id | PK | |
| user_id | FK | requester |
| type | string | students_word, stats_xlsx, stats_pdf, … |
| params | JSON | filters/columns |
| status | string | pending/ready/failed |
| file_path | string nullable | |
| ready_at | datetime nullable | |

---

## Relationship diagram (logical)

```text
User ──< Enrollment >── Subject ──< LearningMaterial
  │                      │
  ├── StudentProfile     ├── Assignment ──< AssignmentSubmission
  ├── UserDevice         └── ScheduleEntry
  ├── theme fields
  │
  └── Conversation (M:N) ──< Message

LibraryItem (section)
NewsItem
AttendanceRecord / GradeRecord → User, Subject
```

---

## State transitions

| Entity | Transitions |
|--------|-------------|
| InterviewStatus | `not_done` → `done` (admin/supervisor) |
| NewsStatus | `draft` ↔ `published` |
| AssignmentStatus | `open` → `closed` |
| SubmissionStatus | `submitted` → `graded` / `returned` |
| User delete | active → hard-deleted (terminal) |

---

## Validation highlights

- Email unique (FR-002a); password min length per Laravel defaults (document in API).
- Teacher without `subject_id` invalid.
- Mateen cannot create Enrollment.
- Student/mateen message create rejects media.
- Library `section` must be one of four enums.
- Bulk student create: transactional; partial failure returns per-row errors without silent skip of successes unless documented batch mode.
