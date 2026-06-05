#!/bin/sh
set -eu

mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage

chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/storage

php /var/www/html/artisan optimize:clear
php /var/www/html/artisan optimize
