#!/bin/bash
set -e
umask 000

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
    chmod -R 777 /var/www/html/database
    chmod 666 /var/www/html/database/database.sqlite
fi

# Ensure storage directories exist with write permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/app/public/blog-images
mkdir -p /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Write .env file using database cache to eliminate any cache file permission errors
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
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
EOF

chmod 666 /var/www/html/.env

# Clear all caches so fresh config is read
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Create storage symlink
php artisan storage:link --force || true

# Run database migrations and seeding
php artisan migrate --force || true
php artisan db:seed --force || true

# Publish Filament and Livewire assets
php artisan filament:assets || true
php artisan livewire:publish --assets || true

# Final permission fix: guarantee www-data ownership and 777 permissions across storage and database
find /var/www/html/storage -type d -exec chmod 777 {} + 2>/dev/null || true
find /var/www/html/storage -type f -exec chmod 666 {} + 2>/dev/null || true
find /var/www/html/database -type d -exec chmod 777 {} + 2>/dev/null || true
find /var/www/html/database -type f -exec chmod 666 {} + 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public

echo "Starting FourSouls (Laravel + Filament + React) on port $PORT..."
exec apache2-foreground
