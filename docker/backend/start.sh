#!/bin/sh
set -eu

cd /var/www/html/laravel-docker

assets_directory="/var/www/html/laravel-docker/public/assets"

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    "$assets_directory"

chown -R www-data:www-data storage bootstrap/cache
chown -R www-data:www-data "$assets_directory"
find "$assets_directory" -type d -exec chmod 0755 {} +
find "$assets_directory" -type f -exec chmod 0644 {} +

if ! su www-data -s /bin/sh -c "test -w '$assets_directory'"; then
    echo "El almacenamiento de imágenes no es escribible por PHP-FPM." >&2
    exit 1
fi

php artisan optimize:clear >/dev/null 2>&1 || true
php artisan migrate --force
php artisan pracear:bootstrap
php artisan pracear:security-check
php artisan config:cache >/dev/null 2>&1 || true
php artisan route:cache >/dev/null 2>&1 || true
php artisan view:cache >/dev/null 2>&1 || true

exec php-fpm
