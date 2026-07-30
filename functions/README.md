# Cloud Functions — retirement (post-Laravel cutover)

After Mateen runs on the Laravel API only (`USE_LARAVEL_API=true` in `js/config.js`), retire Firebase Cloud Functions.

## Preconditions

1. Production client points at Laravel (`API_BASE_URL` + `USE_LARAVEL_API=true`).
2. Migration audit passed (Artisan `mateen:audit-migration` / `mateen:migrate-from-firebase` dry-run ≥99%).
3. Messaging push uses Laravel queue + FCM (`SendMessagePushNotification` / `FcmService`), not Functions.
4. No remaining Firestore writes from the PWA for auth, students, materials, library, news, schedules, or messaging.

## Retirement steps

1. **Freeze Functions deploys** — stop shipping changes to `functions/index.js`.
2. **Disable triggers** in Firebase Console (HTTPS callables / Firestore / Auth / scheduled jobs used by Mateen).
3. **Confirm zero traffic** for 7–14 days (Cloud Logging / Functions metrics).
4. **Delete Functions** (`firebase functions:delete …` or Console) once metrics are idle.
5. **Revoke unused Firebase Admin / service keys** that only served Functions.
6. **Archive this folder** in git history; optional: remove `functions/` from the default deploy path.

## Do not remove yet if

- One-off migration scripts still read Firestore exports (those run from Laravel Artisan, not Functions).
- Legacy admin tools outside the PWA still call callables — migrate those first.

## Related

- Laravel API: `backend/`
- Client flag: `js/config.js` → `USE_LARAVEL_API`
- Spec cutover: `specs/001-laravel-backend/quickstart.md` V7
