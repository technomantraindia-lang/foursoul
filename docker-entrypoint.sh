#!/bin/bash
set -e

# Configure Apache port based on Render's $PORT env (defaults to 10000)
PORT="${PORT:-10000}"
sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:ggTCDvjioblk7jQtv+vdN8lq8YG6DY06Yt+/8Vs8ZQg="
fi

# Default DB to SQLite if DB_CONNECTION is not mysql or postgres
if [ -z "$DB_CONNECTION" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
fi

if [ "$DB_CONNECTION" = "sqlite" ]; then
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod 664 /var/www/html/database/database.sqlite
fi

# Ensure storage directories exist with write permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/app/public/blog-images
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink
php artisan storage:link --force || true

# Run database migrations and seeding
php artisan migrate --force || true
php artisan db:seed --force || true

# Clear & cache Laravel configs
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting FourSouls (Laravel + Filament + React) on port $PORT..."
exec apache2-foreground
