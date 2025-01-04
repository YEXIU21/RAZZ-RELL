#!/bin/bash

echo "Running deployment script..."

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Storage setup - ensure all directories exist with proper permissions
rm -rf public/storage
mkdir -p storage/app/public/portfolios
mkdir -p storage/app/public/portfolio_albums
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

# Set permissions - make sure web server can write to these directories
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Create storage link
php artisan storage:link

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize
php artisan optimize

echo "Deployment completed!"