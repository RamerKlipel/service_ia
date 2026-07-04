FROM php:8.2-fpm

# RUN apt-get update && apt-get install -y \
#     git unzip libzip-dev libcurl4-openssl-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev && \
#     docker-php-ext-configure gd --with-jpeg --with-freetype && \
#     docker-php-ext-install zip pdo_mysql curl mbstring gd \
#     && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install zip pdo_mysql curl mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize

EXPOSE 9000
