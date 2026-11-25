#!/bin/sh
set -e

# Render memberikan nomor port melalui variabel PORT (bawaan 10000).
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache

# Membuat/memperbarui tabel. --force wajib pada mode production.
php artisan migrate --force

# Isi data awal bila SEED_DATA=true (aman diulang).
if [ "$SEED_DATA" = "true" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground
