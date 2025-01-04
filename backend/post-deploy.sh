#!/bin/bash

# Generate application key if not set
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Seed the database
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize 