#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY no está configurada. Genera una con: php artisan key:generate --show" >&2
  exit 1
fi

if [ -n "${MYSQL_URL:-}" ] && [ -z "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DATABASE_URL="$MYSQL_URL"
fi

export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export PRACEAR_ASSETS_PATH="${PRACEAR_ASSETS_PATH:-/var/www/html/laravel-docker/public/assets}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
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
php artisan pracear:bootstrap --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
