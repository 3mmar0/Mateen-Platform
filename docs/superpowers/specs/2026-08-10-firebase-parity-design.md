# Firebase → mateen.academy parity

**Date:** 2026-08-10  
**Status:** Approved (approach 1)

## Problem
[mateen.academy](https://mateen.academy/) serves demo seed data and incomplete Laravel home JS. [GitHub Pages](https://mateenweb.github.io/Mateen/html/home.html) reads live Firestore.

## Design
1. Pull Firestore (users, students, subjects, materials, news, events, conversations+messages) into MySQL via migrator.
2. Map `events` → `schedule_entries` (label/date → title/starts_at).
3. Nest conversation messages; preserve admin password for `rahmakishk575@gmail.com` on import.
4. Fix home.js: public `/contact/recipients`, guest hero CTAs, role sidebar from home-1.js.
5. Production: `mateen:sync-firebase` on VPS using import credentials in env (no PII in git).

## Out of scope
Library/assignments empty in Firestore stay empty. Account-deletion API stays stub for now.
