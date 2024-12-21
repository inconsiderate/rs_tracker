FROM php:8.3-fpm

# Install necessary extensions and tools
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Install Symfony Skeleton
RUN composer create-project symfony/skeleton rs_tracker

# Set permissions (optional, adjust as necessary)
RUN chown -R www-data:www-data /var/www/html
