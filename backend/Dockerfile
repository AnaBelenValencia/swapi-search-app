# backend/Dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpq \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    autoconf \
    g++ \
    make

# Install PHP extensions (ajusta según lo que uses)
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies first (better layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy the rest of the application
COPY . .

# Permissions para storage y cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

CMD ["php-fpm"]
