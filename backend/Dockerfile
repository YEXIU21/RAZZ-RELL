# Use PHP 8.1 FPM Alpine as base image
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    $PHPIZE_DEPS \
    openssl-dev \
    postgresql-libs \
    oniguruma-dev \
    libxml2-dev \
    postgresql-client \
    supervisor \
    nginx

# Create necessary directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/app/public/images/DefaultProfile \
    && mkdir -p /var/www/html/storage/app/public/packages \
    && mkdir -p /var/www/html/storage/app/public/avatars \
    && mkdir -p /var/www/html/public/storage

# Install PHP extensions
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring xml bcmath

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public \
    && chmod -R 775 /var/log/supervisor \
    && chmod +x start.sh

# Create storage link
RUN php artisan storage:link

# Expose port 80
EXPOSE 80

# Start services
CMD ["./start.sh"]