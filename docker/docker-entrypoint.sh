#!/bin/sh
set -e

echo "--- Krayin CRM Docker Entrypoint ---"

# Ensure APP_KEY is set to avoid session loops
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set in environment variables!"
    if [ -f .env ] && grep -q "APP_KEY=base64" .env; then
        echo "Found APP_KEY in .env file."
    else
        echo "Generating a temporary APP_KEY..."
        # We need a dummy .env or it fails
        touch .env
        php artisan key:generate --show --no-interaction > /tmp/app_key
        export APP_KEY=$(cat /tmp/app_key | grep -oE "base64:[^ ]+")
        echo "Generated: $APP_KEY"
        rm /tmp/app_key
    fi
fi

# Ensure storage links exist
echo "Checking storage links..."
php artisan storage:link --force || true

# Check if installed - if so, ensure the flag file exists
echo "Checking database state..."
# Use php-fpm user context if possible, but here we just ensure the file exists
php artisan tinker --execute="if (app(\Webkul\Installer\Helpers\DatabaseManager::class)->isInstalled()) { @touch(storage_path('installed')); echo 'INSTALLED_FLAG_CREATED'; }"

# Fix permissions one last time for the web user
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Clear caches to ensure env changes are picked up
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear

echo "Starting services..."
php-fpm -D
nginx -g 'daemon off;'
