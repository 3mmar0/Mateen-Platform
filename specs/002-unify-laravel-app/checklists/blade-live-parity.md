# Blade ↔ live GitHub Pages parity

**Feature**: `002-unify-laravel-app`  
**Live**: https://mateenweb.github.io/Mateen/html/home.html  
**Source**: https://github.com/mateenweb/Mateen `html/`

## Inventory diff (T001)

Local `public/Mateen/html/` and GitHub `html/` both contain the same **28 live pages**. Backups excluded: `home_backup.html`, `messages_backup.html`.

| Page | Local | GitHub | Notes |
|------|:-----:|:------:|-------|
| home, about, courses, library, news, login, onboarding, schedule | yes | yes | public |
| messages, stats, my-students | yes | yes | shared/staff |
| student, student-general, student-view | yes | yes | student |
| teacher-* (11) | yes | yes | teacher |
| admin, supervisor, support | yes | yes | staff |

## Home parity (T024 / T053)

- [x] RTL Arabic chrome, basmala, nav, hero, subject cards, dates, announcements, about, contact, register modal
- [x] `/` and `/Mateen/html/home.html` served by Blade
- [x] Cross-cutting edit path: Blade copy + `app/` string via same `artisan serve`

## Full page spot-check (T047)

Recorded after conversion — see also `asset-smoke.md`.
