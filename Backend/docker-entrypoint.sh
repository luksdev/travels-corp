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

echo "First time setup - initializing Laravel..."

wait_for_db

php artisan key:generate --force

php artisan jwt:secret --force

echo "Running migrations..."
php artisan migrate --force

touch /var/www/.initialized

echo "Clearing Laravel cache..."
php artisan optimize:clear

echo "Starting PM2 queue workers..."
pm2 start ecosystem.config.cjs --no-daemon &

php artisan db:seed --force

echo "Starting PHP-FPM..."
exec php-fpm
