#!/usr/bin/env sh
set -e

php artisan config:clear
php artisan route:clear
php artisan view:clear

if [ -n "${MYSQL_CA_CERT:-}" ]; then
    printf '%s\n' "$MYSQL_CA_CERT" > /tmp/mysql-ca.pem
    export MYSQL_ATTR_SSL_CA=/tmp/mysql-ca.pem
fi

php artisan migrate --force

php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
