#!/bin/bash

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi
mkdir -p /var/www/html/storage
mkdir -p /var/www/html/storage/framework
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage
php artisan optimize:clear
php artisan migrate --force

# Start PHP-FPM
php-fpm