#!/bin/bash
set -e

wait_for_db() {
    echo "Waiting for database..."
    until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" > /dev/null 2>&1; do
        echo "Database is unavailable - sleeping"
        sleep 1
    done
    echo "Database is ready!"
}

if [ ! -f /var/www/.initialized ]; then
    echo "First time setup - initializing Laravel..."

    wait_for_db

    if ! php artisan config:show app.key > /dev/null 2>&1 || [ -z "$(php artisan config:show app.key)" ]; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi

    if ! php artisan config:show jwt.secret > /dev/null 2>&1 || [ -z "$(php artisan config:show jwt.secret)" ]; then
        echo "Generating JWT secret..."
        php artisan jwt:secret --force
    fi

    echo "Running migrations..."
    php artisan migrate --force

    if [ $(php artisan tinker --execute="echo \App\Models\User::count();") -eq 0 ]; then
        echo "Running seeders..."
        php artisan db:seed --force
    fi

    touch /var/www/.initialized
    echo "Initialization complete."
else
    echo "Container already initialized, skipping setup..."
fi

echo "Clearing Laravel cache..."
php artisan optimize:clear
php artisan migrate --seed

echo "Starting PHP-FPM..."
exec php-fpm
