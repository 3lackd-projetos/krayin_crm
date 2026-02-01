#!/bin/sh
set -e

# Wait for database if needed (simplified for Easypanel as it usually handles dependencies)

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations (only if DB is ready and it's appropriate for this environment)
# php artisan migrate --force

# Create storage links
php artisan storage:link

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g 'daemon off;'
