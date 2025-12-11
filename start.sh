#!/bin/bash

echo "Starting Buttercloud Bakery..."
echo "Database connection: $DB_CONNECTION"
echo "Database host: $DB_HOST"

# Clear any cached config first
php artisan config:clear || true

# Run migrations (continue even if fails)
echo "Running migrations..."
php artisan migrate --force || echo "Migration failed or already done"

# Seed database only if products table is empty
echo "Checking if seeding is needed..."
php artisan db:seed --class=ProductSeeder --force || echo "Seeding failed or already done"

# Cache configuration
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start the server
echo "Starting server on port $PORT..."
php -S 0.0.0.0:$PORT -t public
