# برنامج متين العلمي (Mateen)

منصة تعليمية عربية RTL — تطبيق Laravel موحّد: واجهة Blade والـ API في مشروع واحد.

## أين الأشياء؟

| ماذا | أين |
|------|-----|
| تطبيق Laravel (الخادم) | جذر المستودع: `app/`, `routes/`, `database/`, `artisan` |
| صفحات الواجهة (حية) | `resources/views/` (Blade) |
| أصول ثابتة (CSS/JS/صور) | `public/Mateen/{css,js,libs}` + صور تحت `public/Mateen/` |
| أرشيف HTML قبل التحويل (لا تعدّل كمنتج) | `resources/html-source/` |
| الواجهة القديمة المنفصلة (لا تعدّل) | `_obsolete/frontend/` |
| المواصفات | `specs/` |

لا يوجد مجلد `backend/` كتطبيق منفصل — افتح جذر المستودع.

الصفحة الرئيسية الحية هي `/`. المسارات القديمة مثل `/Mateen/html/login.html` و `/Mateen/html/about.html` تعيد التوجيه إلى `/login` و `/about`.

## التشغيل المحلي

```bash
composer install
cp .env.example .env   # إن لم يكن موجوداً
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

ثم افتح:

- الواجهة: http://127.0.0.1:8000/
- تسجيل الدخول: http://127.0.0.1:8000/login
- API: http://127.0.0.1:8000/api/v1

إعداد العميل: `public/Mateen/js/config.js` يستخدم `API_BASE_URL = '/api/v1'` (نفس الموقع). لا حاجة لـ Vite في المسار العادي — CSS/JS تُقدَّم من `/Mateen/`.

مرجع الشكل الحي السابق: https://mateenweb.github.io/

## استيراد البيانات

```bash
php artisan mateen:audit-migration storage/app/migration-fixtures/export.json
php artisan mateen:migrate-firebase storage/app/migration-fixtures/export.json --dry-run
php artisan mateen:migrate-firebase storage/app/migration-fixtures/export.json
```

لا تضع صادرات Firebase (فيها بيانات شخصية) داخل git.

## النشر

- CI: `.github/workflows/ci-backend.yml` (جذر المشروع + `resources/views`)
- Deploy: `.github/workflows/deploy-vps.yml` — document root = `{APP_DIR}/public`
- Staging أولاً، ثم الإنتاج بعد التحقق

تفاصيل العقود: `specs/002-unify-laravel-app/contracts/`

## ملاحظة

عدّل المنتج في `resources/views/` و`app/` و`public/Mateen/{css,js,libs}` فقط — ليس `_obsolete/frontend/` ولا `resources/html-source/`.
