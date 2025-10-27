#!/bin/bash

# Render Start Script for Laravel
echo "Starting Laravel application..."

# Run migrations
php artisan migrate --force

# Seed database if needed (optional for production)
php artisan db:seed --force

# Start PHP built-in server
echo "Starting server on port $PORT..."
php -S 0.0.0.0:$PORT -t public