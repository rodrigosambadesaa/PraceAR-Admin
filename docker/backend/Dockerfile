FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY docker/backend/php.ini /usr/local/etc/php/conf.d/app.ini
