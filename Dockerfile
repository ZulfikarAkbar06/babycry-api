FROM php:8.4-apache

   RUN apt-get update && apt-get install -y --no-install-recommends \
           git unzip libpq-dev libzip-dev \
       && docker-php-ext-install pdo_pgsql pdo_mysql bcmath zip \
       && a2enmod rewrite headers \
       && rm -rf /var/lib/apt/lists/*

   # Pastikan hanya satu MPM yang aktif (Railway kadang memuat lebih dari satu)
   RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
       && a2enmod mpm_prefork

   COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Document root harus mengarah ke folder public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Script start (menjalankan migration otomatis). sed menghapus CRLF jika file dibuat di Windows.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000
CMD ["entrypoint.sh"]
