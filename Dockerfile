# syntax=docker/dockerfile:1

# ---- Composer deps (needed by the frontend stage too: resources/css/app.css
# imports vendor/livewire/flux/dist/flux.css, so Vite can't build without it) ----
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --ignore-platform-reqs

# ---- Frontend assets (Vite/Tailwind) ----
# Debian-based (glibc), not alpine: Tailwind v4/Rollup's native binaries in
# package.json (@rollup/rollup-linux-x64-gnu, lightningcss, tailwind oxide)
# are glibc builds and fail to load under alpine's musl libc.
FROM node:20-bookworm-slim AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=composer-deps /app/vendor ./vendor
RUN npm run build

# ---- PHP application ----
# symfony/* (pulled in transitively by laravel/framework) is locked to the
# 8.1.x line, which requires PHP >=8.4.1 — php:8.3-apache is too old for it.
FROM php:8.4-apache AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev unzip git \
    && docker-php-ext-install pdo_mysql pdo_pgsql pdo_sqlite mbstring bcmath zip exif pcntl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first so this layer is cached (and packages pulled from
# the network) unless composer.json/lock change. --no-scripts because the
# app/ tree isn't copied in yet, so package:discover etc. can't run.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

COPY . .
COPY --from=frontend /app/public/build ./public/build

# Re-run with the full app present so the optimized autoloader/classmap is
# built correctly and composer's normal post-install scripts fire.
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
