#!/bin/sh

cd /var/www/html

# Create necessary directories with proper permissions
mkdir -p storage/app/images/DefaultProfile
mkdir -p storage/app/packages
mkdir -p storage/app/avatars
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Generate application key
php artisan key:generate --force

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run database migrations and seeding
php artisan migrate:fresh --force
php artisan db:seed --force

# Cache configuration and routes
php artisan config:cache
php artisan route:cache

# Start the server
php artisan serve --host=0.0.0.0 --port=80 