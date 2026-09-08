#!/bin/sh
set -e

# Dynamically set port if Render provides $PORT
PORT="${PORT:-80}"
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/sites-available/default

# Storage link and cache
php artisan storage:link || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations automatically if DB_HOST is set
if [ -n "$DB_HOST" ]; then
    echo "Running migrations..."
    php artisan migrate --force || true
fi

# Start supervisord
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
