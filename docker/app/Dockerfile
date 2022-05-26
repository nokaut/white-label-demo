FROM phpdockerio/php:7.4-fpm

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ADD docker/dispatcher.sh /usr/bin/dispatcher.sh
RUN chmod +x /usr/bin/dispatcher.sh

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git zip unzip

ENTRYPOINT [ "dispatcher.sh" ]
