#!/bin/bash

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi
chown -R www-data:www-data /var/www/html/storage
php artisan optimize:clear
php artisan migrate --force

# Start PHP-FPM
php-fpm