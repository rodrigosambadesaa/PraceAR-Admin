FROM php:8.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libfreetype6-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring intl zip gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY laravel-docker /var/www/html/laravel-docker
COPY railway/app/entrypoint.sh /usr/local/bin/railway-entrypoint
COPY docker/backend/php.ini /usr/local/etc/php/conf.d/app.ini

RUN sed -ri 's!/var/www/html!/var/www/html/laravel-docker/public!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && printf '%s\n' 'ServerTokens Prod' 'ServerSignature Off' > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening \
    && sed -i 's/\r$//' /usr/local/bin/railway-entrypoint \
    && chmod +x /usr/local/bin/railway-entrypoint \
    && cd /var/www/html/laravel-docker \
    && composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --classmap-authoritative \
    && mkdir -p public/assets storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["/bin/sh", "/usr/local/bin/railway-entrypoint"]
