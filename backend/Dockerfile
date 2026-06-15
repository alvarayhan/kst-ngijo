FROM php:8.4-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies & PostgreSQL driver
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/html/

# Install Laravel dependencies (no dev)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Hapus .env fisik (kalo misal gak sengaja kesalin) biar Laravel MURNI baca OS Variables dari Railway
RUN rm -f .env

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port yang digunakan PHP built-in server
EXPOSE 8080

# Jalankan Laravel pake PHP built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]