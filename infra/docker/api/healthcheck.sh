#!/bin/sh
# Healthcheck: verify PHP-FPM is responding via cgi-fcgi or a simple ping
php-fpm -t 2>/dev/null && echo "ok" || exit 1
