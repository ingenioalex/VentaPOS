#!/bin/bash
set -euo pipefail
cd /var/www/ventapos

mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan view:clear 2>/dev/null || true

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache

curl -sI http://127.0.0.1/login | head -3
curl -sI http://127.0.0.1/admin/login | head -3
echo "FIX OK"
