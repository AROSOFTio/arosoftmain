#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

log() {
  echo "[deploy] $*"
}

fail() {
  echo "[deploy][error] $*" >&2
  exit 1
}

trap 'fail "failed at line $LINENO"' ERR

find_php() {
  if command -v php >/dev/null 2>&1; then
    command -v php
    return 0
  fi

  for candidate in \
    /www/server/php/84/bin/php \
    /www/server/php/83/bin/php \
    /www/server/php/82/bin/php \
    /www/server/php/81/bin/php \
    /usr/local/bin/php \
    /usr/bin/php; do
    if [ -x "$candidate" ]; then
      echo "$candidate"
      return 0
    fi
  done

  return 1
}

find_npm() {
  if command -v npm >/dev/null 2>&1; then
    command -v npm
    return 0
  fi

  for pattern in /www/server/nodejs/v*/bin/npm /usr/local/bin/npm /usr/bin/npm; do
    for candidate in $pattern; do
      if [ -x "$candidate" ]; then
        echo "$candidate"
        return 0
      fi
    done
  done

  return 1
}

find_composer_bin() {
  if command -v composer >/dev/null 2>&1; then
    command -v composer
    return 0
  fi

  for candidate in /usr/local/bin/composer /usr/bin/composer; do
    if [ -x "$candidate" ]; then
      echo "$candidate"
      return 0
    fi
  done

  return 1
}

PHP_BIN="$(find_php || true)"
[ -n "$PHP_BIN" ] || fail "php binary not found"
log "php: $PHP_BIN"

COMPOSER_BIN=""
COMPOSER_MODE="bin"

if BIN="$(find_composer_bin || true)"; then
  COMPOSER_BIN="$BIN"
  COMPOSER_MODE="bin"
elif [ -f "$PROJECT_DIR/composer.phar" ]; then
  COMPOSER_BIN="$PROJECT_DIR/composer.phar"
  COMPOSER_MODE="phar"
else
  log "composer not found, downloading local composer.phar"
  INSTALLER="$PROJECT_DIR/.composer-setup.php"

  if command -v curl >/dev/null 2>&1; then
    EXPECTED_SIG="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSL https://getcomposer.org/installer -o "$INSTALLER"
  elif command -v wget >/dev/null 2>&1; then
    EXPECTED_SIG="$(wget -qO- https://composer.github.io/installer.sig)"
    wget -qO "$INSTALLER" https://getcomposer.org/installer
  else
    fail "composer missing and cannot download installer (curl/wget unavailable)"
  fi

  ACTUAL_SIG="$("$PHP_BIN" -r "echo hash_file('sha384', '$INSTALLER');")"
  if [ "$EXPECTED_SIG" != "$ACTUAL_SIG" ]; then
    rm -f "$INSTALLER"
    fail "composer installer signature mismatch"
  fi

  "$PHP_BIN" "$INSTALLER" --quiet --install-dir="$PROJECT_DIR" --filename="composer.phar"
  rm -f "$INSTALLER"

  COMPOSER_BIN="$PROJECT_DIR/composer.phar"
  COMPOSER_MODE="phar"
fi

composer_exec() {
  if [ "$COMPOSER_MODE" = "phar" ]; then
    "$PHP_BIN" "$COMPOSER_BIN" "$@"
  else
    "$COMPOSER_BIN" "$@"
  fi
}

NPM_BIN="$(find_npm || true)"
[ -n "$NPM_BIN" ] || fail "npm binary not found. Install Node.js in aaPanel first."
log "npm: $NPM_BIN"

log "project: $PROJECT_DIR"

if [ ! -f .env ]; then
  log "creating .env from .env.example"
  cp .env.example .env
fi

log "composer install"
composer_exec install --no-dev --optimize-autoloader --no-interaction --prefer-dist

if ! grep -q '^APP_KEY=base64:' .env; then
  log "generating app key"
  "$PHP_BIN" artisan key:generate --force
fi

log "artisan optimize clear"
"$PHP_BIN" artisan optimize:clear

log "artisan caches"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

log "storage link"
"$PHP_BIN" artisan storage:link || true

if [ -f package-lock.json ]; then
  log "npm ci"
  "$NPM_BIN" ci --no-audit --no-fund
else
  log "npm install"
  "$NPM_BIN" install --no-audit --no-fund
fi

log "npm run build"
"$NPM_BIN" run build

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

if id -u www >/dev/null 2>&1; then
  chown -R www:www storage bootstrap/cache || true
elif id -u www-data >/dev/null 2>&1; then
  chown -R www-data:www-data storage bootstrap/cache || true
fi

if [ -d public/build ]; then
  rm -rf build
  cp -r public/build build
  log "mirrored public/build to ./build for root fallback"
fi

log "done"
