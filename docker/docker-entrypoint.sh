#!/bin/sh
set -e

echo "--- Krayin CRM Docker Entrypoint ---"

# Function to add/update env var in .env
set_env_var() {
    VAR_NAME=$1
    VAR_VALUE=$2
    if [ ! -z "$VAR_VALUE" ]; then
        if [ -f .env ] && grep -q "^$VAR_NAME=" .env; then
            # Use double quotes for the value in sed and escape any internal double quotes
            # We use a different delimiter for sed since the value might contain '|'
            SAFE_VALUE=$(echo "$VAR_VALUE" | sed 's/"/\\"/g')
            sed -i "s|^$VAR_NAME=.*|$VAR_NAME=\"$SAFE_VALUE\"|" .env
        else
            echo "$VAR_NAME=\"$VAR_VALUE\"" >> .env
        fi
    fi
}

# Ensure .env exists
[ ! -f .env ] && touch .env

# Reconstruct crucial .env values from environment variables
# This repairs cases where .env was truncated or lost
echo "Syncing environment variables to .env..."
set_env_var "APP_NAME" "$APP_NAME"
set_env_var "APP_ENV" "$APP_ENV"
set_env_var "APP_DEBUG" "${APP_DEBUG:-false}"
set_env_var "APP_URL" "$APP_URL"
set_env_var "DB_CONNECTION" "${DB_CONNECTION:-mysql}"
set_env_var "DB_HOST" "$DB_HOST"
set_env_var "DB_PORT" "${DB_PORT:-3306}"
set_env_var "DB_DATABASE" "$DB_DATABASE"
set_env_var "DB_USERNAME" "$DB_USERNAME"
set_env_var "DB_PASSWORD" "$DB_PASSWORD"
set_env_var "CHATWOOT_BRIDGE_SECRET" "$CHATWOOT_BRIDGE_SECRET"

# Handle APP_KEY separately to ensure it is always base64 encoded if provided
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64" .env; then
        echo "Generating a temporary APP_KEY..."
        php artisan key:generate --show --no-interaction > /tmp/app_key
        K=$(cat /tmp/app_key | grep -oE "base64:[^ ]+")
        set_env_var "APP_KEY" "$K"
        rm /tmp/app_key
    fi
else
    echo "Using APP_KEY from environment."
    # Ensure it starts with base64:
    case "$APP_KEY" in
        base64:*) set_env_var "APP_KEY" "$APP_KEY" ;;
        *) set_env_var "APP_KEY" "base64:$APP_KEY" ;;
    esac
fi

# Additional variables from user's latest list
set_env_var "APP_TIMEZONE" "$APP_TIMEZONE"
set_env_var "APP_LOCALE" "$APP_LOCALE"
set_env_var "APP_CURRENCY" "$APP_CURRENCY"
set_env_var "LOG_CHANNEL" "$LOG_CHANNEL"
set_env_var "LOG_LEVEL" "$LOG_LEVEL"
set_env_var "SESSION_DRIVER" "$SESSION_DRIVER"
set_env_var "SESSION_LIFETIME" "$SESSION_LIFETIME"

# Diagnostic: check for diagnostic files
if [ -f public/env_check.php ]; then
    echo "Diagnostic file public/env_check.php exists."
fi

# Ensure storage links exist
echo "Checking storage links..."
php artisan storage:link --force || true

# Check if installed - if so, ensure the flag file exists
echo "Checking database state..."
php artisan tinker --execute="try { if (app(\Webkul\Installer\Helpers\DatabaseManager::class)->isInstalled()) { @touch(storage_path('installed')); echo 'INSTALLED_FLAG_CREATED'; } } catch (\Exception \$e) { echo 'DB_CHECK_FAILED: ' . \$e->getMessage(); }" || true

# Fix permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "Starting services..."
php-fpm -D
nginx -g 'daemon off;'
