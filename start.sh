#!/bin/bash

echo "Starting Buttercloud Bakery..."
echo "Database connection: $DB_CONNECTION"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed database only if products table is empty
echo "Checking if seeding is needed..."
php artisan db:seed --class=ProductSeeder --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the server
php -S 0.0.0.0:$PORT -t public
