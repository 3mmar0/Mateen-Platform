# Static HTML shadowing rule

Laravel / Nginx `try_files $uri` serves **real files under `public/` before** `routes/web.php`.

If `public/Mateen/html/home.html` exists, `/Mateen/html/home.html` will **never** hit a Blade controller.

**Rule**: after conversion, live HTML must leave the web root:

- Conversion archive: `resources/html-source/`
- Marker: `resources/html-source/DO_NOT_EDIT.md`
- CSS/JS/libs/images stay in `public/Mateen/`
