#!/usr/bin/env bash
set -euo pipefail

# AROMOTION SaaS production deployment helper.
# Run from the AROSOFT Labs Laravel project root after the Windows binary has
# been placed at storage/app/private/aromotion/AROMOTION-Windows-x64.exe.

ROOT="$(pwd)"
BIN_DIR="$ROOT/storage/app/private/aromotion"
BIN="$BIN_DIR/AROMOTION-Windows-x64.exe"

printf '\n== AROMOTION production deploy ==\n'

if [[ ! -f artisan || ! -f composer.json ]]; then
  echo "ERROR: run this script from the Laravel project root." >&2
  exit 1
fi

mkdir -p "$BIN_DIR"

if [[ ! -f "$BIN" ]]; then
  echo "WARNING: Windows binary is not present yet: $BIN" >&2
  echo "The website will deploy, but authenticated downloads will show 'build is being published'." >&2
fi

composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "WARNING: npm is unavailable; existing compiled frontend assets will be kept." >&2
fi

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [[ -f "$BIN" ]]; then
  chmod 0644 "$BIN"
  echo "Windows download ready: $BIN"
fi

printf '\nDeployment complete. Verify:\n'
printf '  /solutions/aromotion\n'
printf '  /solutions/aromotion/account\n'
printf '  /solutions/aromotion/manifest.json\n'
printf '  /up\n'
