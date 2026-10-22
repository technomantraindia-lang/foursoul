FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libsqlite3-dev \
    zip \
    unzip \
    dos2unix \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20 & npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Convert Windows line endings on entrypoint script just in case
RUN dos2unix docker-entrypoint.sh && chmod +x docker-entrypoint.sh

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Publish Filament and Livewire assets
RUN php artisan filament:assets || true
RUN php artisan livewire:publish --assets || true

# Build React frontend
RUN npm --prefix frontend install && npm --prefix frontend run build

# Copy React build into Laravel public directory
RUN cp -r frontend/dist/* public/

# Configure Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# PHP performance and upload limits
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/docker-php-settings.ini

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

EXPOSE 10000

ENTRYPOINT ["./docker-entrypoint.sh"]
