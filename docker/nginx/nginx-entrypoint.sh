#!/bin/sh

envsubst '${PHP_FPM_HOST}' < /etc/nginx/conf.d/default.template > /etc/nginx/conf.d/default.conf

exec "$@"
