#!/bin/bash

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate application key if not set
php artisan key:generate --force

# Run database migrations with force flag
php artisan migrate:fresh --force

# Seed the database with force flag
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Optimize the application
php artisan optimize 