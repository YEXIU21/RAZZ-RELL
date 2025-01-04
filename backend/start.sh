#!/bin/sh

cd /var/www/html

# Test database connection
echo "Testing database connection..."
php artisan db:monitor

# Generate application key
php artisan key:generate --force

# Run database migrations and seeding
php artisan migrate --force
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