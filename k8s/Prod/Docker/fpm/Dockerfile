FROM php:8.3-fpm-alpine AS base

RUN apk add --update zlib-dev libpng-dev libzip-dev $PHPIZE_DEPS

RUN docker-php-ext-install exif
RUN docker-php-ext-install gd
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo_mysql
RUN pecl install apcu
RUN docker-php-ext-enable apcu

# Install and enable pcntl extension
RUN docker-php-ext-install pcntl

WORKDIR /var/www/html
COPY . /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY /composer.json composer.json

RUN composer install --prefer-dist --no-ansi --no-dev --no-autoloader

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

RUN composer dump-autoload -o

FROM base AS build-fpm

COPY --from=base /var/www/html /var/www/html

FROM build-fpm AS fpm

COPY --from=build-fpm /var/www/html /var/www/html

EXPOSE 9000
