#!/bin/bash

echo "Running deployment script..."

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Remove existing storage link and directory
rm -rf public/storage

# Create storage directories with proper permissions
mkdir -p storage/app/public/packages
mkdir -p storage/app/public/package_albums
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

# Set permissions recursively
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Create storage link
php artisan storage:link

# Ensure storage link exists and is correct
if [ ! -L "public/storage" ]; then
    echo "Storage link not created properly, trying alternative method..."
    cd public
    ln -sf ../storage/app/public storage
    cd ..
fi

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize
php artisan optimize

echo "Deployment completed!"