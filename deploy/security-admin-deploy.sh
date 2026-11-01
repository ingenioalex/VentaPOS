#!/bin/bash
# Despliegue seguridad admin + prospectos. Ejecutar desde la raíz del repo en Windows (Git Bash) o Linux.
set -euo pipefail

VPS="root@2.25.166.12"
APP_DIR="/var/www/ventapos"
ARCHIVE="/tmp/ventapos-deploy.tar.gz"
ADMIN_PATH="${ADMIN_PANEL_PATH:?Defina ADMIN_PANEL_PATH antes de ejecutar}"

echo "=== Empaquetar ==="
tar -czf "$ARCHIVE" \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=.git \
  --exclude=storage/logs \
  --exclude=storage/framework/cache \
  --exclude=storage/framework/sessions \
  --exclude=storage/framework/views \
  --exclude=.env \
  -C "$(dirname "$0")/.." .

echo "=== Subir a VPS ==="
scp "$ARCHIVE" "${VPS}:/root/ventapos-deploy.tar.gz"

echo "=== Desplegar en VPS ==="
ssh "$VPS" bash -s <<REMOTE
set -euo pipefail
APP_DIR="$APP_DIR"
ADMIN_PATH='$ADMIN_PATH'

cp -a "\$APP_DIR/.env" /root/ventapos.env.backup.\$(date +%Y%m%d%H%M)

tar -xzf /root/ventapos-deploy.tar.gz -C "\$APP_DIR" --exclude='.env'

# ADMIN_PANEL_PATH en .env
if grep -q '^ADMIN_PANEL_PATH=' "\$APP_DIR/.env"; then
  sed -i "s|^ADMIN_PANEL_PATH=.*|ADMIN_PANEL_PATH=\"\${ADMIN_PATH}\"|" "\$APP_DIR/.env"
else
  echo "ADMIN_PANEL_PATH=\"\${ADMIN_PATH}\"" >> "\$APP_DIR/.env"
fi
if grep -q '^ADMIN_BLOCK_LEGACY_PATH=' "\$APP_DIR/.env"; then
  sed -i 's|^ADMIN_BLOCK_LEGACY_PATH=.*|ADMIN_BLOCK_LEGACY_PATH=true|' "\$APP_DIR/.env"
else
  echo 'ADMIN_BLOCK_LEGACY_PATH=true' >> "\$APP_DIR/.env"
fi

cd "\$APP_DIR"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
mkdir -p storage/framework/{sessions,views,cache/data}
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan tinker --execute="
\\\$n = App\\Models\\Prospecto::query()
  ->where(function (\\\$q) {
    \\\$q->where('nombre_contacto', 'like', '%Otis%')
      ->orWhere('nombre_contacto', 'like', '%Jaylan%')
      ->orWhere('nombre_contacto', 'like', '%DavidBof%')
      ->orWhere('nombre_negocio', 'like', '%DavidBof%');
  })
  ->update(['estado' => 'descartado']);
echo \"Prospectos marcados descartados: \\\$n\\n\";
"

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
systemctl reload php8.3-fpm 2>/dev/null || systemctl reload php8.2-fpm 2>/dev/null || true

echo "=== OK ==="
php artisan --version
grep ADMIN_PANEL_PATH "\$APP_DIR/.env"
REMOTE

echo "Listo. Login: https://ventasagil.com/$(python3 -c "import urllib.parse; print(urllib.parse.quote('$ADMIN_PATH', safe=''))" 2>/dev/null || echo '223305%40dey%40')/login"
