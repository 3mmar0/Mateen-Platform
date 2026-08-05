# Data migration audit

**Commands**

```bash
php artisan mateen:audit-migration storage/app/migration-fixtures/export.json
php artisan mateen:migrate-firebase storage/app/migration-fixtures/export.json --dry-run
php artisan mateen:migrate-firebase storage/app/migration-fixtures/export.json
```

Sample/empty fixture (CI/local smoke): `tests/fixtures/migration-sample.json`

| Check | Status |
|-------|--------|
| Mapper covers users, students, subjects, materials, library, assignments, news, schedules, conversations, devices | yes |
| Mapper covers contact messages + registration requests | yes |
| Imported users get `must_reset_password=true` | yes |
| Media URLs left as external hosts (Cloudinary / existing) | yes — broken URLs logged by operators during staging walk |
| Production import | operator gate after staging smoke (T066) |

Broken media URL handling: leave URL as imported; staff replace via admin/library/material editors. Do not rewrite hosts automatically.
