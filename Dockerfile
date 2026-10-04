FROM php:8.4-cli

# Ekstensi PHP: PostgreSQL (Neon) dan pendukung Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql bcmath zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Script start: migration otomatis, lalu jalankan server PHP di port dari Railway (tanpa Apache)
RUN printf '%s\n' \
    '#!/bin/sh' \
    'set -e' \
    'php artisan config:clear' \
    'php artisan migrate --force' \
    'if [ "$RUN_SEED" = "true" ]; then php artisan db:seed --force; fi' \
    'php artisan config:cache' \
    'exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php' \
    > /app/start.sh \
    && chmod +x /app/start.sh

ENV PHP_CLI_SERVER_WORKERS=4
CMD ["/app/start.sh"]
