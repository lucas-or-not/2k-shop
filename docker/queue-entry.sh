#!/bin/bash

# Create supervisor log directory
mkdir -p /var/log/supervisor
chmod -R 777 /var/log/supervisor

# Copy supervisor configurations if they don't exist
if [ ! -f /etc/supervisor/supervisord.conf ]; then
    cp /var/www/docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
fi

if [ ! -f /etc/supervisor/conf.d/queue-worker.conf ]; then
    cp /var/www/docker/supervisor/queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf
fi

# Wait for database and redis to be ready
echo "Waiting for MySQL..."
while ! nc -z mysql 3306; do
    sleep 1
done

echo "Waiting for Redis..."
while ! nc -z redis 6379; do
    sleep 1
done

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf -n

