#!/bin/bash
# Actualiza SeccionK en el VPS desde CUALQUIER carpeta (incluso ~).
# Uso:
#   curl -fsSL https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/actualizar-seccionk-vps.sh | bash
# o:
#   wget -O /tmp/seccionk.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/actualizar-seccionk-vps.sh && bash /tmp/seccionk.sh

set -euo pipefail

BRANCH="${SECCIONK_BRANCH:-cursor/seccionk-module-0c7c}"
REPO_URL="https://github.com/ingenioalex/VentaPOS.git"

APP=""
for dir in \
    /var/www/ventapos \
    /var/www/ventasagil \
    /var/www/html \
    /var/www/html/ventapos \
    /home/*/public_html \
    /home/*/htdocs
do
    if [ -f "$dir/artisan" ]; then
        APP="$dir"
        break
    fi
done

if [ -z "$APP" ]; then
    APP=$(find /var/www /home /opt -name artisan -not -path '*/vendor/*' 2>/dev/null | head -1 | xargs -r dirname)
fi

if [ -z "$APP" ] || [ ! -f "$APP/artisan" ]; then
    echo "No se encontro Laravel (archivo artisan) en /var/www"
    echo "Indica la ruta:  APP=/ruta/de/la/app bash $0"
    exit 1
fi

echo "App: $APP"
cd "$APP"

env_get() {
    grep -E "^${1}=" "$APP/.env" | tail -1 | cut -d= -f2- | tr -d '"' | tr -d "'"
}

TMP=/tmp/seccionk-update
rm -rf "$TMP"
git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$TMP"

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
copy_path app/Models/SeccionKDeclaracion.php
copy_path app/Support
copy_path app/Console/Commands/SeccionKLimpiarCuotasCommand.php
copy_path app/Console/Commands/SeccionKAsegurarEsquemaCommand.php
copy_path resources/views/seccionk
copy_path routes/seccionk.php
copy_path database/migrations/2026_10_11_000001_crear_tablas_seccionk.php
copy_path database/migrations/2026_10_22_000001_seccionk_cierre_eventos.php
copy_path database/seeders/SeccionKSeeder.php
copy_path database/sql/seccionk_instalar.sql
copy_path database/sql/seccionk_declaraciones.sql
copy_path public/uploads/seccionk
copy_path bootstrap/app.php
copy_path config/auth.php
copy_path deploy
copy_path actualizar-seccionk-vps.sh

if [ -f "$TMP/actualizar-seccionk-vps.sh" ]; then
    cp -a "$TMP/actualizar-seccionk-vps.sh" "$HOME/actualizar-seccionk-vps.sh"
    chmod +x "$HOME/actualizar-seccionk-vps.sh" "$APP/actualizar-seccionk-vps.sh" 2>/dev/null || true
fi

SQL_FILE="$APP/database/sql/seccionk_instalar.sql"
DECL_SQL="$APP/database/sql/seccionk_declaraciones.sql"
if [ -f "$SQL_FILE" ]; then
    DB_HOST=$(env_get DB_HOST)
    DB_NAME=$(env_get DB_DATABASE)
    DB_USER=$(env_get DB_USERNAME)
    DB_PASS=$(env_get DB_PASSWORD)
    DB_HOST=${DB_HOST:-127.0.0.1}
    DB_NAME=${DB_NAME:-ventapos}
    DB_USER=${DB_USER:-root}
    export MYSQL_PWD="${DB_PASS:-}"
    TABLA_EXISTE=$(mysql -h"$DB_HOST" -u"$DB_USER" -N -s "$DB_NAME" -e "SHOW TABLES LIKE 'seccionk_promociones'" || true)
    if [ -n "$TABLA_EXISTE" ]; then
        echo "Tablas seccionk ya existen: no se inserta ni se borra data del VPS"
        if [ -f "$DECL_SQL" ]; then
            mysql -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" < "$DECL_SQL"
            echo "Tabla seccionk_declaraciones verificada (CREATE TABLE IF NOT EXISTS)"
        fi
    else
        mysql -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" < "$SQL_FILE"
        echo "Tablas seccionk creadas"
    fi
    unset MYSQL_PWD
fi

cd "$APP"
php artisan seccionk:asegurar-esquema || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
chown -R www-data:www-data "$APP/storage" "$APP/bootstrap/cache" "$APP/public/uploads" 2>/dev/null || true
rm -rf "$TMP"

echo
echo "SeccionK publicado en /seccionk"
echo "Admin: https://ventasagil.com/seccionk/admin/login  admin / seccionk2026"
echo
echo "La proxima vez, desde cualquier carpeta:"
echo "  bash $HOME/actualizar-seccionk-vps.sh"
echo "o:"
echo "  curl -fsSL https://raw.githubusercontent.com/ingenioalex/VentaPOS/${BRANCH}/actualizar-seccionk-vps.sh | bash"
