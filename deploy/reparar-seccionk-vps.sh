#!/bin/bash
# Repara SeccionK si el script anterior fallo: crea tablas y limpia cache.

set -euo pipefail

APP=/var/www/ventapos
if [ ! -f "$APP/artisan" ]; then
    APP=$(find /var/www -name artisan -not -path '*/vendor/*' 2>/dev/null | head -1 | xargs -r dirname)
fi

echo "App: $APP"
cd "$APP"

env_get() {
    grep -E "^${1}=" "$APP/.env" | tail -1 | cut -d= -f2- | tr -d '"' | tr -d "'"
}

SQL_FILE="$APP/database/sql/seccionk_instalar.sql"
if [ ! -f "$SQL_FILE" ]; then
    mkdir -p "$APP/database/sql"
    curl -fsSL https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/database/sql/seccionk_instalar.sql -o "$SQL_FILE"
fi

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

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
chown -R www-data:www-data storage bootstrap/cache public/uploads 2>/dev/null || true

echo "Listo. Prueba https://ventasagil.com/seccionk"
