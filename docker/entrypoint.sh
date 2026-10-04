#!/bin/sh
set -e

# Render memberi port lewat variabel PORT (default 10000)
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

php artisan config:clear

# Paket gratis Render tidak punya Shell, jadi migration dijalankan otomatis saat start
php artisan migrate --force

# Isi data contoh hanya jika RUN_SEED=true (seeder sudah aman, tidak menduplikasi data)
if [ "${RUN_SEED}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
