FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# install redis
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Cache views
RUN php artisan view:cache

# make sure the storage folders exists for laravel
RUN mkdir -p /var/www/html/storage/cache/data
RUN mkdir -p /var/www/html/storage/sessions
RUN mkdir -p /var/www/html/storage/views
RUN mkdir -p /var/www/html/storage/logs

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage

# Copy start script and make it executable
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["/usr/local/bin/start.sh"]