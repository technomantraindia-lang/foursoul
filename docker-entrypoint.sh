#!/bin/bash
set -e

# Configure Apache port based on Render's $PORT env (defaults to 10000)
PORT="${PORT:-10000}"
sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Determine APP_KEY
APP_KEY="${APP_KEY:-base64:ggTCDvjioblk7jQtv+vdN8lq8YG6DY06Yt+/8Vs8ZQg=}"

# Determine DB settings
DB_CONNECTION="${DB_CONNECTION:-sqlite}"
DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"

# Prepare SQLite directory and file with full permissions
if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 777 /var/www/html/database
    chmod 666 /var/www/html/database/database.sqlite
fi

# Write .env file so Apache and Laravel have all necessary environment variables
cat <<EOF > /var/www/html/.env
APP_NAME=FourSouls
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=true
APP_URL=https://foursouls-live.onrender.com

LOG_CHANNEL=stderr
LOG_LEVEL=debug

DB_CONNECTION=${DB_CONNECTION}
DB_DATABASE=${DB_DATABASE}

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
EOF

chown www-data:www-data /var/www/html/.env
chmod 644 /var/www/html/.env

# Ensure storage directories exist with write permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/app/public/blog-images
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear all caches so fresh config is read
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Create storage symlink
php artisan storage:link --force || true

# Run database migrations and seeding
php artisan migrate --force || true
php artisan db:seed --force || true

echo "Starting FourSouls (Laravel + Filament + React) on port $PORT..."
exec apache2-foreground
