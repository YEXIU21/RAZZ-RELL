#!/bin/bash

echo "Running deployment script..."

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Storage setup
rm -rf public/storage
php artisan storage:link

# Optimize
php artisan optimize

echo "Deployment completed!"