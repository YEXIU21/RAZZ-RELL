#!/bin/sh

cd /var/www/html

# Generate application key
php artisan key:generate --force

# Run database migrations and seeding
php artisan migrate --force
php artisan db:seed --force

# Cache configuration and routes
php artisan config:cache
php artisan route:cache

# Start the server
php artisan serve --host=0.0.0.0 --port=80 