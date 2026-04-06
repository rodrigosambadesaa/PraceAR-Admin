FROM php:8.4-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache \
    git \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html
COPY docker/backend/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/backend/php-fpm-www.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/backend/start.sh /usr/local/bin/start-laravel

RUN chmod +x /usr/local/bin/start-laravel \
    && cd /var/www/html/laravel-docker \
    && composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --classmap-authoritative \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

CMD ["/usr/local/bin/start-laravel"]
