#!/bin/bash

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Set storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage directories if they don't exist
mkdir -p storage/app/public/avatars
mkdir -p storage/app/public/packages
mkdir -p storage/app/public/portfolios
mkdir -p storage/app/public/portfolio_albums

# Set proper permissions for storage directories
chmod -R 775 storage/app/public/avatars
chmod -R 775 storage/app/public/packages
chmod -R 775 storage/app/public/portfolios
chmod -R 775 storage/app/public/portfolio_albums