FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip \
    && docker-php-ext-install pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html