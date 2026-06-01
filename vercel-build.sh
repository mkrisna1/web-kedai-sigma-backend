#!/usr/bin/env sh
set -eu

php artisan config:clear --no-ansi
php artisan route:clear --no-ansi
php artisan view:clear --no-ansi

php artisan migrate --force --no-interaction --no-ansi
php artisan db:seed --class=AdminSeeder --force --no-interaction --no-ansi
