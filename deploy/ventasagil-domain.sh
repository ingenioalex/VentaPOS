#!/bin/bash
set -euo pipefail

APP_DIR="/var/www/ventapos"
DOMAIN="ventasagil.com"
EMAIL="ingeniovargas@gmail.com"

echo "=== Nginx ventasagil.com ==="
cat > /etc/nginx/sites-available/ventasagil <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name ventasagil.com www.ventasagil.com *.ventasagil.com;
    root /var/www/ventapos/public;
    index index.php;

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
NGINX

ln -sf /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/ventasagil
rm -f /etc/nginx/sites-enabled/ventapos
nginx -t
systemctl reload nginx

echo "=== Certbot ==="
apt-get install -y -qq certbot python3-certbot-nginx 2>/dev/null || true

if [ ! -f "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" ]; then
    certbot --nginx \
        -d "${DOMAIN}" \
        -d "www.${DOMAIN}" \
        --non-interactive \
        --agree-tos \
        -m "${EMAIL}" \
        --redirect
fi

echo "=== Laravel .env ==="
cd "$APP_DIR"
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
grep -q '^SESSION_DOMAIN=' .env && sed -i "s|^SESSION_DOMAIN=.*|SESSION_DOMAIN=.${DOMAIN}|" .env || echo "SESSION_DOMAIN=.${DOMAIN}" >> .env

php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

chown -R www-data:www-data storage bootstrap/cache

echo "=== Verificación ==="
curl -sI "https://${DOMAIN}/login" | head -5
curl -sI "http://san-martin.${DOMAIN}/login" | head -3
echo "DONE"
