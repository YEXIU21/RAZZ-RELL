#!/bin/sh

cd /var/www/html

# Create necessary directories with proper permissions
mkdir -p storage/app/public/images/DefaultProfile
mkdir -p storage/app/public/packages
mkdir -p storage/app/public/avatars
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p public/storage

# Set proper permissions
chmod -R 775 storage public
chown -R www-data:www-data storage public bootstrap/cache

# Test database connection
echo "Testing database connection..."
php artisan db:monitor

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

# Remove existing storage link and recreate it
rm -rf public/storage
php artisan storage:link

# Cache configuration and routes
php artisan config:cache
php artisan route:cache

# Ensure storage directory is writable
chmod -R 775 storage
chown -R www-data:www-data storage

# Start the server
php artisan serve --host=0.0.0.0 --port=80 