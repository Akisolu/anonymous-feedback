FROM php:8.4-cli-alpine

# 1. Install the official PHP extension manager for Docker
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# 2. Install basic system tools that your app might need
RUN apk add --no-cache git zip unzip

# 3. Install the required PHP extensions (handles all internal dependencies)
RUN install-php-extensions pdo_pgsql zip redis

# 4. Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]