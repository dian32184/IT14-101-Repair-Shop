#!/bin/sh
set -e
php artisan migrate --force
chown -R www-data:www-data storage bootstrap/cache
exec apache2-foreground
