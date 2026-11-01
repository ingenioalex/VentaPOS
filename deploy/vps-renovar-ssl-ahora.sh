#!/bin/bash
# Renueva SSL de ventasagil.com por HTTP-01 (sin API Hostinger).
# El wildcard falla con token 401; este método deja el dominio principal seguro
# y permite que certbot.timer lo renueve solo cada 60 días.
set -euo pipefail

DOMAIN="ventasagil.com"
EMAIL="ingeniovargas@gmail.com"
APP_DIR="/var/www/ventapos"
WEBROOT="${APP_DIR}/public"
LIVE="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"

mkdir -p "${WEBROOT}/.well-known/acme-challenge"
chown -R www-data:www-data "${WEBROOT}/.well-known" || true

DOMAINS=(-d "${DOMAIN}" -d "www.${DOMAIN}")

if [[ -f "${APP_DIR}/artisan" ]]; then
  SUBS="$(php "${APP_DIR}/artisan" tinker --execute="echo implode(' ', \\App\\Models\\Empresa::where('activo',1)->pluck('codigo')->all());" 2>/dev/null || true)"
  for codigo in ${SUBS}; do
    [[ -n "${codigo}" ]] && DOMAINS+=(-d "${codigo}.${DOMAIN}")
  done
fi

echo "Renovando: ${DOMAINS[*]}"

certbot certonly --webroot -w "${WEBROOT}" \
  --cert-name "${DOMAIN}" \
  --expand \
  "${DOMAINS[@]}" \
  --non-interactive \
  --agree-tos -m "${EMAIL}" \
  --force-renewal

nginx -t
systemctl reload nginx

echo "=== Nuevo certificado ==="
openssl x509 -in "${LIVE}" -noout -dates -ext subjectAltName
echo "=== HTTPS ==="
curl -sI "https://${DOMAIN}/" | head -3
