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
rm -rf storage/app/public

# Create storage directories with proper permissions
mkdir -p storage/app/public/packages
mkdir -p storage/app/public/package_albums
mkdir -p storage/app/public/avatars
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

# Set permissions recursively for storage
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

# Create storage link using multiple methods to ensure success
php artisan storage:link

# Manual symlink creation as backup
if [ ! -L "public/storage" ] || [ ! -d "storage/app/public" ]; then
    echo "Artisan storage:link failed, trying manual symlink..."
    rm -rf public/storage
    ln -sf ../storage/app/public public/storage
fi

# Double-check symlink
if [ ! -L "public/storage" ]; then
    echo "Manual symlink failed, trying final method..."
    cd public
    ln -sf ../storage/app/public storage
    cd ..
fi

# Verify storage setup
echo "Verifying storage setup..."
if [ -L "public/storage" ] && [ -d "storage/app/public" ]; then
    echo "✓ Storage link created successfully"
    ls -la public/storage
    echo "✓ Storage directories:"
    ls -la storage/app/public
else
    echo "⚠ Warning: Storage link verification failed"
    echo "Current storage status:"
    ls -la public/
    ls -la storage/app/
fi

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize
php artisan optimize

echo "Deployment completed!"