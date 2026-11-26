#!/bin/sh
set -e
cd /var/www/html

# Render tells the container which port to listen on
PORT="${PORT:-10000}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

php artisan package:discover --ansi
php artisan config:cache
php artisan view:cache
php artisan migrate --force

# Demo data: only runs when RUN_SEED=true (the seeder skips itself if the data already exists)
if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground
