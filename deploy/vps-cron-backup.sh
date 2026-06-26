#!/bin/bash
# Cron Laravel (schedule:run) + verificación de backups en VPS VentasÁgil.
set -euo pipefail

APP_DIR="/var/www/ventapos"
PHP_BIN="$(command -v php)"
CRON_FILE="/etc/cron.d/ventapos"
LOG_FILE="${APP_DIR}/storage/logs/scheduler.log"

echo "=== Zona horaria (backup 03:00 Lima) ==="
if grep -q '^APP_TIMEZONE=' "${APP_DIR}/.env" 2>/dev/null; then
    sed -i 's|^APP_TIMEZONE=.*|APP_TIMEZONE=America/Lima|' "${APP_DIR}/.env"
else
    echo 'APP_TIMEZONE=America/Lima' >> "${APP_DIR}/.env"
fi

echo "=== Cron VentasÁgil ==="
cat > "${CRON_FILE}" <<EOF
# Laravel scheduler — ejecuta backups 03:00, WhatsApp resumen 20:00, etc.
* * * * * www-data cd ${APP_DIR} && ${PHP_BIN} artisan schedule:run >> ${LOG_FILE} 2>&1
EOF
chmod 644 "${CRON_FILE}"

echo "=== Directorio backups ==="
mkdir -p "${APP_DIR}/storage/app/backups"
chown -R www-data:www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

echo "=== mysqldump ==="
if ! command -v mysqldump >/dev/null; then
    apt-get update -qq
    apt-get install -y -qq mysql-client
fi
which mysqldump

echo "=== Prueba backup manual ==="
cd "${APP_DIR}"
sudo -u www-data bash -c "cd ${APP_DIR} && ${PHP_BIN} artisan config:clear"
sudo -u www-data bash -c "cd ${APP_DIR} && ${PHP_BIN} artisan config:cache"

sudo -u www-data bash -c "cd ${APP_DIR} && ${PHP_BIN} artisan ventapos:backup-db --programado"

echo "=== Tareas programadas ==="
sudo -u www-data bash -c "cd ${APP_DIR} && ${PHP_BIN} artisan schedule:list"

echo "=== Cron instalado ==="
cat "${CRON_FILE}"

echo "=== Últimas copias ==="
ls -lah "${APP_DIR}/storage/app/backups/" | tail -5

echo "OK — backup automático diario a las 03:00 (America/Lima según APP_TIMEZONE)"
