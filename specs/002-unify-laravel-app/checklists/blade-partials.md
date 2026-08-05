# Shared chrome partials (from live home.html)

| Partial | Role |
|---------|------|
| `resources/views/partials/theme-boot.blade.php` | Early custom-theme CSS variables |
| `resources/views/partials/head-pwa.blade.php` | manifest / apple / theme-color |
| `resources/views/partials/basmala.blade.php` | Basmala bar |
| `resources/views/partials/nav.blade.php` | Public navbar + mobile menu + offcanvas sidebar |
| `resources/views/partials/footer.blade.php` | Footer + social + dua |
| `resources/views/partials/register-modal.blade.php` | Registration request modal |
| `resources/views/partials/teacher-subject-workspace.blade.php` | Shared teacher subject chrome hook |

Layouts:

- `resources/views/layouts/guest.blade.php` — public RTL shell
- `resources/views/layouts/app.blade.php` — authenticated workspace shell (yields full converted pages via `@yield('body')` when needed)
