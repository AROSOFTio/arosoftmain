#!/bin/sh

# Minimal aaPanel compatibility script.
# It is intentionally non-strict and never hard-fails deployments.

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_DIR" || exit 0

echo "[deploy] start: $PROJECT_DIR"

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env 2>/dev/null || true
fi

if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist || true
fi

if command -v php >/dev/null 2>&1; then
  php artisan optimize:clear || true
fi

echo "[deploy] done"
exit 0
