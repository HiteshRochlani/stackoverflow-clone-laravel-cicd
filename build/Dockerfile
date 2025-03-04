FROM php:8.2-fpm

WORKDIR /var/www

# Installing dependencies
RUN apt-get update && apt-get install -y vim nano \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libxml2-dev \
    zip unzip \
    nodejs npm


# libfreetype6-dev, libjpeg62-turbo-dev, libpng-dev: Required for the GD extension in PHP, used for image processing.
# libonig-dev Used by the mbstring extension for multibyte string processing.
# libzip-dev: Required for handling zip files in PHP.
# libxml2-dev: Required for XML parsing (e.g., xmlreader extensions).

# Clearing cache to reduce image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# docker-php-ext-install is a helper script provided by the official PHP Docker image.
# It compiles and installs PHP extensions from source.
# It automatically handles dependencies and configuration for the extensions.

# Install extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring intl zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-source delete

COPY composer.lock composer.json ./

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-scripts

COPY package*.json ./

RUN npm install 

COPY ./build/php/laravel.ini /usr/local/etc/php/conf.d/laravel.ini

COPY . .

RUN composer dump-autoload

RUN npm run build

RUN php artisan storage:link

ENTRYPOINT build/app-entrypoint.bash
