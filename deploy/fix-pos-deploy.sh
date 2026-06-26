#!/bin/bash
set -euo pipefail
cp /tmp/Producto.php /var/www/ventapos/app/Models/Producto.php
cp /tmp/LoteService.php /var/www/ventapos/app/Services/LoteService.php
cp /tmp/VentaService.php /var/www/ventapos/app/Services/VentaService.php
cp /tmp/index.blade.php /var/www/ventapos/resources/views/app/pos/index.blade.php
cp /tmp/movil.blade.php /var/www/ventapos/resources/views/app/pos/movil.blade.php
cd /var/www/ventapos
php artisan view:clear
php artisan view:cache
mysql -u ventapos -pVentaPOS_Vl110185 ventapos < /tmp/fix-lotes.sql
echo "FIX OK"
