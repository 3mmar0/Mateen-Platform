# برنامج متين العلمي (Mateen)

منصة تعليمية عربية RTL — تطبيق Laravel موحّد: الواجهة والـ API في مشروع واحد.

## أين الأشياء؟

| ماذا | أين |
|------|-----|
| تطبيق Laravel (الخادم) | جذر المستودع: `app/`, `routes/`, `database/`, `artisan` |
| واجهة المستخدم (حية) | `public/Mateen/` (`html/`, `js/`, `css/`, `libs/`) |
| نقطة الويب | `public/` (document root) |
| الواجهة القديمة (لا تعدّل) | `_obsolete/frontend/` — انظر `DO_NOT_EDIT.md` |
| المواصفات | `specs/` |

لا يوجد مجلد `backend/` كتطبيق منفصل — افتح جذر المستودع.

## التشغيل المحلي

```bash
composer install
cp .env.example .env   # إن لم يكن موجوداً
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

ثم افتح:

- الواجهة: http://127.0.0.1:8000/Mateen/html/login.html
- API: http://127.0.0.1:8000/api/v1
- الجذر `/` يعيد التوجيه إلى الصفحة الرئيسية

إعداد العميل: `public/Mateen/js/config.js` يستخدم `API_BASE_URL = '/api/v1'` (نفس الموقع).

## النشر

- CI: `.github/workflows/ci-backend.yml` (جذر المشروع)
- Deploy: `.github/workflows/deploy-vps.yml` — document root = `{APP_DIR}/public`
- Staging أولاً، ثم الإنتاج بعد التحقق

تفاصيل العقود: `specs/002-unify-laravel-app/contracts/`

## ملاحظة

`_obsolete/frontend/` نسخة مؤقتة غير حية — أي تعديل للمنتج يكون في `public/Mateen/` و`app/` فقط.
