#!/bin/bash

# Render Build Script for Laravel
echo "Starting Laravel build process..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Create SQLite database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "Created SQLite database file"
fi

# Set proper permissions
chmod 664 database/database.sqlite
chmod -R 775 storage bootstrap/cache

# Cache configurations for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Build process completed successfully!"