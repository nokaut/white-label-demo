FROM php:8.2-fpm

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git zip unzip zlib1g-dev libicu-dev libzip-dev libxml2-dev libzip-dev libmemcached-dev && \
    pecl install memcached && \
    docker-php-ext-install zip intl && \
    docker-php-ext-enable memcached

