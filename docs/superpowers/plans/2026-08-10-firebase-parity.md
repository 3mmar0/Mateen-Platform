# Firebase parity Implementation Plan

> **For agentic workers:** Implement task-by-task. Steps use checkbox syntax.

**Goal:** Make mateen.academy home flow and data match GitHub Pages Firebase content.

**Architecture:** Extend FirebaseMigrationService for events + nested messages; add artisan sync that pulls Firestore on the VPS; fix home.js Laravel boot path.

**Tech Stack:** Laravel 12, Firestore REST, Sanctum, Blade/home.js

## Global Constraints
- Never commit `storage/app/migration-fixtures/export.json` or passwords.
- Preserve admin login password on import for the known admin email.

---

### Task 1: Migrator events + messages + admin password
**Files:** `DocumentMapper.php`, `FirebaseMigrationService.php`, `MigrateFromFirebaseCommand.php`
- [ ] Map events → schedule
- [ ] Merge conversationMessages / nested messages
- [ ] `--admin-email` / `--admin-password` options

### Task 2: home.js + api.js flow parity
**Files:** `public/Mateen/js/api.js`, `public/Mateen/js/home.js`
- [ ] `api.contact.recipients`
- [ ] Port home-1 sidebar/hero into `bootHomeLaravel`

### Task 3: Production sync command + deploy hook
**Files:** `SyncFirebaseCommand.php`, `deploy-vps.yml`
- [ ] Pull+import on VPS when `FIREBASE_IMPORT_EMAIL`/`PASSWORD` set
- [ ] Push and verify live API counts / login
