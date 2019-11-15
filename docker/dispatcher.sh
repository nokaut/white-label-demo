#!/usr/bin/env bash

case $1 in
    'shell')
        /bin/bash ;;
    'install')
        php -d memory_limit=-1 /usr/local/bin/composer install --no-interaction --ansi;;
    *)
        php app/console server:run 0.0.0.0 ;;
esac
