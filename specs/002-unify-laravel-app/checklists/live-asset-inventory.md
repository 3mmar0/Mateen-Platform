# Live asset inventory

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Task**: T001

## Live HTML (`html/` → `public/Mateen/html/`)

Include (28):

- about.html, admin.html, courses.html, home.html, library.html, login.html
- messages.html, my-students.html, news.html, onboarding.html, schedule.html
- stats.html, student-general.html, student-view.html, student.html, supervisor.html
- support.html, teacher-aqeedah.html, teacher-fiqh.html, teacher-hadeeth.html
- teacher-ithraiyat.html, teacher-library.html, teacher-profile.html
- teacher-quran1.html, teacher-quran2.html, teacher-schedule.html
- teacher-students.html, teacher-tafseer.html

Exclude (backup, not live):

- home_backup.html
- messages_backup.html

## Live JS (`js/` → `public/Mateen/js/`)

Include all `js/*` except:

- messages_backup.js

## Live CSS (`css/` → `public/Mateen/css/`)

Include all `css/*` except:

- home_backup.css
- messages_backup.css

## Live libs

- `libs/` → `public/Mateen/libs/` (entire tree)

## Root static (URL / PWA parity)

| File | Target |
|------|--------|
| index.html | `public/index.html` (optional; web route may redirect `/`) — also keep Mateen entry via `/Mateen/html/…` |
| sw.js | `public/sw.js` and/or `public/Mateen/sw.js` if registered under Mateen scope |
| firebase-messaging-sw.js | `public/firebase-messaging-sw.js` (must be site root for FCM) |
| manifest.json | `public/manifest.json` and copy under `public/Mateen/manifest.json` if pages reference relative Mateen path |
| favicon.ico | `public/favicon.ico` (Laravel public already has one — prefer Mateen branding if distinct) |
| logo.png, logo_transparent.png, hero-bg.png, hero-bg2.png, schedule.jpg, schedule-wird.jpg, shuraka-logo.png, star-ornament.png, star-ornament-transparent.png | `public/Mateen/` (and/or `public/` if referenced from root `index.html`) |
| _redirects | obsolete (Firebase Hosting) — do not promote as live |
| mateen.apk | `public/Mateen/mateen.apk` optional download asset |

## Notes

- Authoritative live UI after unification: `public/Mateen/**`
- Backup/unused copies stay only under `_obsolete/frontend/` after US4 move
