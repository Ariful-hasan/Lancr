# ##############################################################################
# 1. BASE IMAGE: The foundation for all stages
# ##############################################################################
FROM php:8.4-fpm-alpine AS php_base

# Install essential system dependencies
RUN apk add --no-cache \
    acl fcgi file gettext git bash icu-dev libzip-dev mysql-dev libpng-dev zlib-dev

# Install PHP extensions
RUN docker-php-ext-install intl pdo_mysql zip gd opcache

# Copy optimized configurations
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY docker/php/fpm.conf /usr/local/etc/php-fpm.d/fpm.conf

# Global tool: Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /srv/app

# Shared "Interface" - Inherited by all final stages
EXPOSE 9000
CMD ["php-fpm"]


# ##############################################################################
# 2. DEVELOPMENT STAGE: Optimized for local coding
# Target: php_dev
# ##############################################################################
FROM php_base AS php_dev

ARG USER_ID=1000
ARG GROUP_ID=1000

ENV APP_ENV=dev

# Sync container user with host user to avoid permission issues on Linux
RUN apk add --no-cache shadow && \
    usermod -u ${USER_ID} www-data && \
    groupmod -g ${GROUP_ID} www-data

# Install all dependencies (including dev tools like PHPUnit)
# --no-scripts prevents Symfony from running cache:clear before DB is ready
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-progress --no-interaction --no-scripts

# Copy source code (Note: overwritten by volumes in docker-compose)
COPY . .

# Ensure permissions for Symfony var directory
RUN mkdir -p var && chown -R www-data:www-data var

USER www-data


# ##############################################################################
# 3. PRODUCTION BUILD: Preparation (Intermediary)
# ##############################################################################
FROM php_base AS php_build

# Install only production dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application source
COPY . .

# Optimize the autoloader for production performance
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative


# ##############################################################################
# 4. FINAL PRODUCTION IMAGE: Lean and Secure
# Target: php_prod
# ##############################################################################
FROM php_base AS php_prod

ENV APP_ENV=prod

# Copy the optimized application from the build stage
# We use --chown to set ownership in a single layer (Industry Standard)
COPY --from=php_build --chown=www-data:www-data /srv/app /srv/app

# Run as a non-privileged user for security
USER www-data
