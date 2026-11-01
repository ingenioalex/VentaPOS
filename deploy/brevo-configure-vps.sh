#!/bin/bash
# Configura Brevo SMTP en el VPS. No guardar claves en el repositorio.
set -euo pipefail

VPS="${VPS:-root@2.25.166.12}"
APP_DIR="${APP_DIR:-/var/www/ventapos}"

: "${BREVO_SMTP_USER:?Defina BREVO_SMTP_USER (email login Brevo)}"
: "${BREVO_SMTP_KEY:?Defina BREVO_SMTP_KEY (clave xsmtpsib-...)}"
BREVO_FROM="${BREVO_FROM:-$BREVO_SMTP_USER}"

ssh "$VPS" bash -s <<REMOTE
set -euo pipefail
ENV="$APP_DIR/.env"
cp -a "\$ENV" "/root/ventapos.env.backup.brevo.\$(date +%Y%m%d%H%M)"

set_var() {
  local key="\$1" val="\$2"
  if grep -q "^\${key}=" "\$ENV"; then
    sed -i "s|^\${key}=.*|\${key}=\${val}|" "\$ENV"
  else
    echo "\${key}=\${val}" >> "\$ENV"
  fi
}

set_var MAIL_MAILER smtp
set_var MAIL_SCHEME null
set_var MAIL_HOST smtp-relay.brevo.com
set_var MAIL_PORT 587
set_var MAIL_USERNAME "$BREVO_SMTP_USER"
set_var MAIL_PASSWORD "$BREVO_SMTP_KEY"
set_var MAIL_FROM_ADDRESS "$BREVO_FROM"
set_var 'MAIL_FROM_NAME' '"VentasÁgil"'

cd "$APP_DIR"
php artisan config:cache
php artisan ventapos:probar-correo "$BREVO_FROM"
REMOTE

echo "Listo. Revise el correo de prueba en $BREVO_FROM"
