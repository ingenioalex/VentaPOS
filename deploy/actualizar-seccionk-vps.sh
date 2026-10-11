#!/bin/bash
# Actualiza SeccionK en el VPS sin tocar la data de VentaPOS.
# Ejecutar en el servidor: bash actualizar-seccionk-vps.sh

set -euo pipefail

APP=""
for dir in /var/www/ventapos /var/www/ventasagil /var/www/html; do
    if [ -f "$dir/artisan" ]; then
        APP="$dir"
        break
    fi
done

if [ -z "$APP" ]; then
    APP=$(find /var/www -name artisan -not -path '*/vendor/*' 2>/dev/null | head -1 | xargs -r dirname)
fi

if [ -z "$APP" ] || [ ! -f "$APP/artisan" ]; then
    echo "No se encontro Laravel en /var/www"
    exit 1
fi

echo "App: $APP"
cd "$APP"

TMP=/tmp/seccionk-update
rm -rf "$TMP"
git clone --depth 1 --branch cursor/seccionk-module-0c7c https://github.com/ingenioalex/VentaPOS.git "$TMP"

copy_path() {
    local rel="$1"
    mkdir -p "$(dirname "$APP/$rel")"
    if [ -d "$TMP/$rel" ]; then
        mkdir -p "$APP/$rel"
        cp -a "$TMP/$rel/." "$APP/$rel/"
    elif [ -f "$TMP/$rel" ]; then
        cp -a "$TMP/$rel" "$APP/$rel"
    fi
}

copy_path app/Http/Controllers/SeccionK
copy_path app/Http/Middleware/SeccionKAuth.php
copy_path app/Models/SeccionKPromocion.php
copy_path app/Models/SeccionKEvento.php
copy_path app/Models/SeccionKParticipacion.php
copy_path app/Models/SeccionKUsuario.php
copy_path resources/views/seccionk
copy_path routes/seccionk.php
copy_path database/migrations/2026_10_11_000001_crear_tablas_seccionk.php
copy_path database/seeders/SeccionKSeeder.php
copy_path database/sql/seccionk_instalar.sql
copy_path public/uploads/seccionk
copy_path bootstrap/app.php
copy_path config/auth.php

if [ -f "$APP/database/sql/seccionk_instalar.sql" ]; then
    php -r '
        $env = file($argv[1]);
        $c = [];
        foreach ($env as $line) {
            if (str_contains($line, "=") && !str_starts_with(trim($line), "#")) {
                [$k, $v] = explode("=", trim($line), 2);
                $c[$k] = trim($v, "\"'");
            }
        }
        $db = $c["DB_DATABASE"] ?? "ventapos";
        $user = $c["DB_USERNAME"] ?? "root";
        $pass = $c["DB_PASSWORD"] ?? "";
        $host = $c["DB_HOST"] ?? "127.0.0.1";
        echo "$host|$db|$user|$pass";
    ' "$APP/.env" > /tmp/seccionk-db.txt
    IFS='|' read -r DB_HOST DB_NAME DB_USER DB_PASS < /tmp/seccionk-db.txt
    rm -f /tmp/seccionk-db.txt
    mysql -h"$DB_HOST" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < "$APP/database/sql/seccionk_instalar.sql"
    echo "Tablas seccionk listas"
fi

cd "$APP"
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
chown -R www-data:www-data "$APP/storage" "$APP/bootstrap/cache" "$APP/public/uploads" 2>/dev/null || true
rm -rf "$TMP"

echo "SeccionK publicado en /seccionk"
echo "Admin: https://ventasagil.com/seccionk/admin/login  admin / seccionk2026"
