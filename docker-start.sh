#!/bin/sh
set -e
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force || echo "Admin seeding failed"
if [ "$SEED_SAMPLE_DATA" = "true" ]; then
  php artisan db:seed --class=SampleDataSeeder --force || echo "Sample data seeding failed"
fi
chown -R www-data:www-data storage bootstrap/cache
exec apache2-foreground
