#!/bin/sh
echo "--- Krayin CRM Docker Entrypoint ---"

# Function to add/update env var in .env
set_env_var() {
    VAR_NAME=$1
    VAR_VALUE=$2
    if [ ! -z "$VAR_VALUE" ]; then
        # Remove any existing line for this variable
        [ -f .env ] && sed -i "/^$VAR_NAME=/d" .env
        # Add the new value quoted
        echo "$VAR_NAME=\"$VAR_VALUE\"" >> .env
    fi
}

# Ensure .env exists
[ ! -f .env ] && touch .env

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

# Handle APP_KEY
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64" .env; then
        echo "Generating a temporary APP_KEY..."
        php artisan key:generate --show --no-interaction > /tmp/app_key
        K=$(cat /tmp/app_key | grep -oE "base64:[^ ]+")
        set_env_var "APP_KEY" "$K"
        rm /tmp/app_key
    fi
else
    case "$APP_KEY" in
        base64:*) set_env_var "APP_KEY" "$APP_KEY" ;;
        *) set_env_var "APP_KEY" "base64:$APP_KEY" ;;
    esac
fi

# Sync other vars provided by user
set_env_var "APP_TIMEZONE" "$APP_TIMEZONE"
set_env_var "APP_LOCALE" "$APP_LOCALE"
set_env_var "APP_CURRENCY" "$APP_CURRENCY"
set_env_var "LOG_CHANNEL" "$LOG_CHANNEL"
set_env_var "LOG_LEVEL" "$LOG_LEVEL"
set_env_var "SESSION_DRIVER" "$SESSION_DRIVER"
set_env_var "SESSION_LIFETIME" "$SESSION_LIFETIME"

# Permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Essential checks - don't let these crash the shell if they fail
php artisan storage:link --force || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true

# Flag check
php artisan tinker --execute="try { if (app(\Webkul\Installer\Helpers\DatabaseManager::class)->isInstalled()) { @touch(storage_path('installed')); echo 'INSTALLED_FLAG_CREATED'; } } catch (\Exception \$e) { echo 'DB_CHECK_FAILED: ' . \$e->getMessage(); }" || true

echo "Starting services..."
php-fpm -D
nginx -g 'daemon off;'
