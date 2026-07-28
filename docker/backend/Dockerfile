FROM php:8.4-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache \
    git \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    freetype-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring intl zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY laravel-docker /var/www/html/laravel-docker
COPY docker/backend/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/backend/php-fpm-www.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/backend/start.sh /usr/local/bin/start-laravel

RUN sed -i 's/\r$//' /usr/local/bin/start-laravel \
    && chmod +x /usr/local/bin/start-laravel \
    && cd /var/www/html/laravel-docker \
    && composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --classmap-authoritative \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/assets \
    && chown -R www-data:www-data storage bootstrap/cache public/assets \
    && find public/assets -type d -exec chmod 0755 {} + \
    && find public/assets -type f -exec chmod 0644 {} +

CMD ["/usr/local/bin/start-laravel"]
