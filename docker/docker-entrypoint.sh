#!/bin/sh
set -e

# Wait for database if needed (simplified for Easypanel)

# Ensure APP_KEY is set
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generating one..."
    # Create a temporary .env if it doesn't exist for key generation
    if [ ! -f .env ]; then
        cp .env.example .env
    fi
    php artisan key:generate --show --no-interaction > /tmp/app_key
    GENERATED_KEY=$(cat /tmp/app_key)
    export APP_KEY=$GENERATED_KEY
    echo "Generated APP_KEY: $APP_KEY"
    rm /tmp/app_key
fi

# Ensure storage permissions are correct (re-apply because of volumes)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Create storage links
php artisan storage:link || true

# Optimizations for production
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
nginx -g 'daemon off;'
