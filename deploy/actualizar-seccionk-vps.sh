#!/bin/bash
# Actualiza SeccionK en el VPS sin tocar la data de VentaPOS.

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

env_get() {
    grep -E "^${1}=" "$APP/.env" | tail -1 | cut -d= -f2- | tr -d '"' | tr -d "'"
}

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
copy_path app/Support/SeccionKExport.php
copy_path resources/views/seccionk
copy_path routes/seccionk.php
copy_path database/migrations/2026_10_11_000001_crear_tablas_seccionk.php
copy_path database/seeders/SeccionKSeeder.php
copy_path database/sql/seccionk_instalar.sql
copy_path public/uploads/seccionk
copy_path bootstrap/app.php
copy_path config/auth.php

SQL_FILE="$APP/database/sql/seccionk_instalar.sql"
if [ -f "$SQL_FILE" ]; then
    DB_HOST=$(env_get DB_HOST)
    DB_NAME=$(env_get DB_DATABASE)
    DB_USER=$(env_get DB_USERNAME)
    DB_PASS=$(env_get DB_PASSWORD)
    DB_HOST=${DB_HOST:-127.0.0.1}
    DB_NAME=${DB_NAME:-ventapos}
    DB_USER=${DB_USER:-root}
    export MYSQL_PWD="${DB_PASS:-}"
    mysql -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" < "$SQL_FILE"
    unset MYSQL_PWD
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
