# Root merge notes (elevate `backend/` → repo root)

**Feature**: `002-unify-laravel-app` | **Date**: 2026-07-30  
**Task**: T002

## Collision rules

| Path | Rule |
|------|------|
| `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `resources/`, `routes/`, `storage/`, `tests/`, `vendor/` | Move from `backend/` to root (no root equivalents) |
| `artisan`, `composer.json`, `composer.lock`, `phpunit.xml`, `vite.config.js`, `package.json`, `.editorconfig`, `.gitattributes`, `.npmrc` | Move from `backend/` to root |
| `.env`, `.env.example` | Move to root if absent; if both exist prefer `backend/` content as Laravel source of truth |
| `public/` | Create root `public/`; move Laravel `index.php`, `.htaccess`, `robots.txt`, `favicon.ico` here; reserve `public/Mateen/` for UI |
| `.gitignore` | Merge: Laravel patterns at root (no `backend/` prefix) + keep root OS/IDE/Node/functions ignores + `_obsolete/` |
| `README.md` | Keep/rewrite root README for unified app (US1); discard or archive `backend/README.md` |
| `specs/`, `.specify/`, `.github/`, `.cursor/`, `html/`, `js/`, `css/`, `libs/`, `functions/` | Stay at root until UI copy / obsolete move |

## Order

1. Move Laravel dirs/files to root  
2. Merge `.gitignore`  
3. Ensure `.env.example` at root  
4. Ensure `public/` Laravel entry files  
5. Remove `backend/` app directory  
6. `composer install` + `php artisan --version`
