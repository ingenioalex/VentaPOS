#!/bin/bash
# Ejecutar en el VPS después de subir ventapos-deploy.tar.gz a /root/
set -euo pipefail

APP_DIR="/var/www/ventapos"
ARCHIVE="/root/ventapos-deploy.tar.gz"

if [[ ! -f "$ARCHIVE" ]]; then
    echo "No existe $ARCHIVE"
    exit 1
fi

echo "=== Backup .env ==="
cp -a "$APP_DIR/.env" /root/ventapos.env.backup

echo "=== Extraer código (sin sobrescribir .env) ==="
tar -xzf "$ARCHIVE" -C "$APP_DIR" --exclude='.env'

echo "=== Composer ==="
cd "$APP_DIR"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

echo "=== Laravel ==="
mkdir -p storage/framework/{sessions,views,cache/data}
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Permisos ==="
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "=== PHP-FPM ==="
systemctl reload php8.3-fpm

echo "=== OK ==="
php artisan --version
