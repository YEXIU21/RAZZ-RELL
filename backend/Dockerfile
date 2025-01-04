# Use PHP 8.1 FPM Alpine as base image
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libzip-dev \
    unzip \
    git \
    $PHPIZE_DEPS \
    openssl-dev \
    postgresql-libs \
    oniguruma-dev \
    libxml2-dev \
    postgresql-client

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

# Create startup script
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php artisan key:generate --force' >> /start.sh && \
    echo 'php artisan serve --host=0.0.0.0 --port=80' >> /start.sh && \
    chmod +x /start.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Expose port 80
EXPOSE 80

# Start using the startup script
CMD ["/start.sh"] 