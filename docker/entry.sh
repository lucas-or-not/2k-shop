#!/bin/sh

# Change the working directory to /var/www
cd /var/www

environment=${CONTAINER_ENV:-local}

if [ "$environment" = "local" ]; then
    composer i
    npm i && npm run build
fi

# Run app optimization command
echo "Running app optimization command."
php artisan optimize:clear

# Wait for the MySQL service to be ready
until nc -z -w5 mysql 3306; do
    echo "Waiting for MySQL service to be ready..."
    sleep 5
done

# Run migration and seeds
echo "Running migration and seeds"
php artisan migrate --seed --force

# Start PHP-FPM and Nginx
php-fpm -D
nginx -g "daemon off;"
