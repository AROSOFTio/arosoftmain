# AROSOFT Main Website

Laravel 11 frontend shell for Arosoft Innovations (header/nav, mega menus, off-canvas, live search UI, and footer).

## Stack

- Laravel 11 (Blade)
- Tailwind CSS + Alpine.js
- Vite

## aaPanel note

If the site shows:

`missing dependency file /vendor/autoload.php`

install dependencies on the server:

```bash
composer install --no-dev --optimize-autoloader
```

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
