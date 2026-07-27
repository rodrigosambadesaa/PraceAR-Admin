#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY no está configurada. Genera una con: php artisan key:generate --show" >&2
  exit 1
fi

if [ -n "${MYSQL_URL:-}" ] && [ -z "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DATABASE_URL="$MYSQL_URL"
fi

if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
  export APP_URL="${APP_URL:-https://${RAILWAY_PUBLIC_DOMAIN}}"
  export APP_ALLOWED_HOSTS="${APP_ALLOWED_HOSTS:-${RAILWAY_PUBLIC_DOMAIN}}"
fi

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="false"
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export PRACEAR_ASSETS_PATH="${PRACEAR_ASSETS_PATH:-/var/www/html/laravel-docker/public/assets}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
export SESSION_SAME_SITE="${SESSION_SAME_SITE:-strict}"
export SESSION_ENCRYPT="${SESSION_ENCRYPT:-true}"
export TRUSTED_PROXIES="${TRUSTED_PROXIES:-*}"
export CACHE_STORE="${CACHE_STORE:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

cd /var/www/html/laravel-docker

mkdir -p \
  public/assets \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data public/assets storage bootstrap/cache

a2dismod mpm_event >/dev/null 2>&1 || true
a2dismod mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

php artisan optimize:clear
php artisan migrate --force
php artisan pracear:bootstrap
php artisan pracear:security-check --production
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
