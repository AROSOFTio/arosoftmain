# AROSOFT Main Website

Laravel 11 frontend shell for Arosoft Innovations (header/nav, mega menus, off-canvas, live search UI, and footer).

## Stack

- Laravel 11 (Blade)
- Tailwind CSS + Alpine.js
- Vite

## Zero-touch deploy flow (aaPanel)

Use aaPanel Git Manager script tab with this single command:

```bash
bash /www/wwwroot/arosoft.io/deploy/aapanel-deploy.sh
```

If you see `Missing file: /vendor/autoload.php` in browser, it means the deploy script has not run yet for that release.

After this one-time setup, each git deployment runs automatically:

1. `composer install --no-dev`
2. Laravel optimize/cache commands
3. `npm ci` (or `npm install`) + `npm run build`
4. Permission fixes for `storage` and `bootstrap/cache`
5. Build mirroring from `public/build` to `build` for root fallback mode

## Root fallback mode

`index.php` exists at repository root to avoid `403 Forbidden` if server root is accidentally set to project root instead of `/public`.

Recommended setup is still:

- web root: `/www/wwwroot/arosoft.io/public`
- rewrite: Laravel rewrite

## Local run

```bash
cp .env.example .env
composer install
php artisan key:generate
npm install
npm run dev
php artisan serve
```
