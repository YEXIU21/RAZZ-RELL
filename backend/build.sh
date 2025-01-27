#!/bin/sh

# Navigate to the backend directory
cd /opt/render/project/src/

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Set up Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Remove public storage link if it exists
rm -rf public/storage

# Set up storage directories in app folder
mkdir -p storage/app/avatars
mkdir -p storage/app/packages
mkdir -p storage/app/portfolios
mkdir -p storage/app/portfolio_albums

# Set permissions for storage
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 storage/app/*

# Run migrations
php artisan migrate --force