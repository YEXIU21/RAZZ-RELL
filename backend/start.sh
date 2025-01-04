#!/bin/sh

cd /var/www/html

# Create necessary directories
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set proper permissions
chmod -R 775 storage
chown -R www-data:www-data storage

# Test database connection
echo "Testing database connection..."
php artisan db:monitor

# Generate application key
php artisan key:generate --force

# Run database migrations and seeding
php artisan migrate:fresh --force
php artisan db:seed --force

# Remove existing storage link if it exists
rm -rf public/storage

# Create storage link
php artisan storage:link

# Cache configuration and routes
php artisan config:cache
php artisan route:cache

# Clear any cached data
php artisan cache:clear

# Start the server
php artisan serve --host=0.0.0.0 --port=80 