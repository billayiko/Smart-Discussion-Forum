# syntax=docker/dockerfile:1

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
RUN npm run build

# ---- PHP application ----
FROM php:8.3-apache AS app

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
