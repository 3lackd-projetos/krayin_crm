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
        [ ! -f .env ] && touch .env
        php artisan key:generate --show --no-interaction > /tmp/app_key
        export APP_KEY=$(cat /tmp/app_key | grep -oE "base64:[^ ]+")
        echo "Generated: $APP_KEY"
        # Append only if not present to avoid duplicates
        if ! grep -q "APP_KEY=" .env; then
            echo "APP_KEY=$APP_KEY" >> .env
        fi
        rm /tmp/app_key
    fi
else
    echo "Using APP_KEY from environment."
    # Only write to .env if not already there with same value
    if [ -f .env ]; then
        if ! grep -q "APP_KEY=$APP_KEY" .env; then
            # Remove any old APP_KEY and append new one
            sed -i '/APP_KEY=/d' .env
            echo "APP_KEY=$APP_KEY" >> .env
        fi
    else
        echo "APP_KEY=$APP_KEY" > .env
    fi
fi

# Diagnostic: check for diagnostic files
if [ -f public/env_check.php ]; then
    echo "Diagnostic file public/env_check.php exists."
else
    echo "ERROR: Diagnostic file public/env_check.php is MISSING!"
fi

# Ensure storage links exist
echo "Checking storage links..."
php artisan storage:link --force || true

# Check if installed - if so, ensure the flag file exists
echo "Checking database state..."
php artisan tinker --execute="try { if (app(\Webkul\Installer\Helpers\DatabaseManager::class)->isInstalled()) { @touch(storage_path('installed')); echo 'INSTALLED_FLAG_CREATED'; } } catch (\Exception \$e) { echo 'DB_CHECK_FAILED: ' . \$e->getMessage(); }" || true

# Fix permissions one last time for the web user
echo "Setting permissions..."
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public

# Clear caches to ensure env changes are picked up
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "Starting services..."
php-fpm -D
nginx -g 'daemon off;'
