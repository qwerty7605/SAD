FROM php:8.2-fpm
WORKDIR /var/www/html

# Install everything
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev \
 && docker-php-ext-install pdo_mysql zip \
 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && rm -rf /var/lib/apt/lists/*

# Create www-data user with specific UID/GID and proper home directory
ARG UID=1000
ARG GID=1000
RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data \
 && mkdir -p /home/www-data/.composer \
 && chown -R www-data:www-data /home/www-data \
 && usermod -d /home/www-data www-data

# Set Composer home environment variable
ENV COMPOSER_HOME=/home/www-data/.composer

# Copy and install
COPY --chown=www-data:www-data . .
RUN composer install --no-interaction --optimize-autoloader

# Permissions for critical directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]