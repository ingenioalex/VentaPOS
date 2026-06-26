#!/bin/bash
# Certificado SSL para ventasagil.com + subdominios de empresas.
#
# Modo A (recomendado, automático): token API Hostinger + wildcard *.ventasagil.com
#   export HOSTINGER_API_TOKEN="tu-token"
#   bash deploy/ventasagil-ssl-wildcard.sh wildcard
#
# Modo B (sin API): incluye cada subdominio activo vía HTTP-01
#   bash deploy/ventasagil-ssl-wildcard.sh expand
#
set -euo pipefail

APP_DIR="/var/www/ventapos"
DOMAIN="ventasagil.com"
EMAIL="ingeniovargas@gmail.com"
MODE="${1:-expand}"
CREDS="/root/.secrets/hostinger.ini"
WEBROOT="${APP_DIR}/public"
DB_PASS="$(grep '^DB_PASSWORD=' "${APP_DIR}/.env" 2>/dev/null | cut -d= -f2- | tr -d '"' || true)"

mkdir -p /root/.secrets
chmod 700 /root/.secrets

ensure_nginx() {
    cat > /etc/nginx/sites-available/ventasagil <<'NGINX'
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name ventasagil.com www.ventasagil.com *.ventasagil.com;
    root /var/www/ventapos/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/ventasagil.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ventasagil.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

server {
    listen 80;
    listen [::]:80;
    server_name ventasagil.com www.ventasagil.com *.ventasagil.com;
    root /var/www/ventapos/public;

    location /.well-known/acme-challenge/ {
        root /var/www/ventapos/public;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}
NGINX
    ln -sf /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/ventasagil
    rm -f /etc/nginx/sites-enabled/ventapos
    nginx -t
    systemctl reload nginx
}

empresa_domains() {
    mysql -N -u ventapos -p"${DB_PASS}" ventapos \
        -e "SELECT CONCAT(codigo, '.${DOMAIN}') FROM empresas WHERE activo = 1 ORDER BY codigo;" 2>/dev/null \
        | tr '\n' ' '
}

expand_http() {
    local subs
    subs="$(empresa_domains)"
    echo "Subdominios empresa: ${subs:-ninguno}"

    local -a args=(-d "${DOMAIN}" -d "www.${DOMAIN}")
    for sub in ${subs}; do
        [[ -n "${sub}" ]] && args+=(-d "${sub}")
    done

    # Certbot necesita HTTP en :80 para validar subdominios nuevos
    certbot certonly --webroot -w "${WEBROOT}" \
        --cert-name "${DOMAIN}" \
        --expand \
        "${args[@]}" \
        --non-interactive \
        --agree-tos -m "${EMAIL}" \
        --keep-until-expiring

    ensure_nginx
}

CERTBOT="/opt/certbot-dns/bin/certbot"
CERTBOT_VENV="/opt/certbot-dns"

wildcard_dns() {
    local token="${HOSTINGER_API_TOKEN:-}"
    if [[ -z "${token}" && -f "${CREDS}" ]]; then
        token="$(grep -E '^dns_hostinger_api_token\s*=' "${CREDS}" | cut -d= -f2- | tr -d ' \"')"
    fi
    if [[ -z "${token}" ]]; then
        echo "ERROR: Define HOSTINGER_API_TOKEN o ${CREDS}"
        echo "Token: hPanel → Perfil → API → generar con permiso DNS"
        exit 1
    fi

    printf 'dns_hostinger_api_token = %s\n' "${token}" > "${CREDS}"
    chmod 600 "${CREDS}"

    if [[ ! -x "${CERTBOT}" ]]; then
        apt-get update -qq
        apt-get install -y -qq python3-venv
        python3 -m venv "${CERTBOT_VENV}"
        "${CERTBOT_VENV}/bin/pip" install -q certbot certbot-dns-hostinger
    fi

    "${CERTBOT}" certonly \
        --authenticator dns-hostinger \
        --dns-hostinger-credentials "${CREDS}" \
        --dns-hostinger-propagation-seconds 120 \
        --cert-name "${DOMAIN}" \
        -d "${DOMAIN}" \
        -d "*.${DOMAIN}" \
        --non-interactive \
        --agree-tos -m "${EMAIL}" \
        --force-renewal

    ensure_nginx
    echo "Renovación: ${CERTBOT} renew (cron del sistema o certbot.timer)"
}

echo "=== SSL VentasÁgil (${MODE}) ==="
ensure_nginx

case "${MODE}" in
    wildcard) wildcard_dns ;;
    expand)   expand_http ;;
    *) echo "Uso: $0 [expand|wildcard]"; exit 1 ;;
esac

echo "=== Certificado ==="
openssl x509 -in "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" -noout -text | grep -A1 'Subject Alternative Name'

echo "=== Test ==="
curl -sI "https://${DOMAIN}/login" | head -3
for sub in $(empresa_domains 2>/dev/null | head -3); do
    [[ -n "${sub}" ]] && curl -sI "https://${sub}/login" | head -1
done
echo "OK"
