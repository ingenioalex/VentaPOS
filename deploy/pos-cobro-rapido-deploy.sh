#!/bin/bash
# Fix POS: cobro rápido + SUNAT en segundo plano
set -euo pipefail
APP="/var/www/ventapos"
cp /tmp/EmitirSunatComprobanteJob.php "$APP/app/Jobs/EmitirSunatComprobanteJob.php"
cp /tmp/PosController.php "$APP/app/Http/Controllers/PosController.php"
cp /tmp/index.blade.php "$APP/resources/views/app/pos/index.blade.php"
cd "$APP"
php artisan view:clear
php artisan view:cache
php artisan config:cache
systemctl reload php8.3-fpm
echo "POS cobro rápido OK"
