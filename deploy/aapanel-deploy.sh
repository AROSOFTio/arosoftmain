#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

echo "[deploy] project: $PROJECT_DIR"

if [ ! -f .env ]; then
  echo "[deploy] creating .env from .env.example"
  cp .env.example .env
fi

if command -v composer >/dev/null 2>&1; then
  echo "[deploy] composer install"
  composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
  echo "[deploy] composer not found, skipping dependency install"
fi

if command -v php >/dev/null 2>&1; then
  echo "[deploy] artisan optimize clear"
  php artisan optimize:clear

  echo "[deploy] artisan caches"
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  echo "[deploy] storage link"
  php artisan storage:link || true
else
  echo "[deploy] php not found, skipping artisan commands"
fi

if command -v npm >/dev/null 2>&1; then
  if [ -f package-lock.json ]; then
    echo "[deploy] npm ci"
    npm ci --no-audit --no-fund
  else
    echo "[deploy] npm install"
    npm install --no-audit --no-fund
  fi

  echo "[deploy] npm run build"
  npm run build
else
  echo "[deploy] npm not found, skipping frontend build"
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

if [ -d public/build ]; then
  rm -rf build
  cp -r public/build build
  echo "[deploy] mirrored public/build to ./build for root fallback"
fi

echo "[deploy] done"
