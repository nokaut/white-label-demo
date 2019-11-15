#!/usr/bin/env bash

case $1 in
    'server')
        php app/console server:run 0.0.0.0 ;;
    'shell')
        /bin/bash ;;
    'install')
        php -d memory_limit=-1 /usr/local/bin/composer install --no-interaction --ansi;;
    *)
        echo "OK";;
esac
