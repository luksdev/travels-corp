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

    php artisan key:generate --force

    php artisan jwt:secret

    echo "Running migrations..."
    php artisan migrate --force

    if [ $(php artisan tinker --execute="echo \App\Models\User::count();") -eq 0 ]; then
        echo "Running seeders..."
        php artisan db:seed --force
    fi
else
    echo "Container already initialized, skipping setup..."
fi

echo "Clearing Laravel cache..."
php artisan optimize:clear
#php artisan migrate --seed

echo "Starting PHP-FPM..."
exec php-fpm
