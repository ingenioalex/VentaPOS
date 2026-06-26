#!/bin/bash
set -euo pipefail

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
    return 301 https://$host$request_uri;
}
NGINX

nginx -t
systemctl reload nginx

echo "=== Tests VPS directo ==="
curl -sI -H "Host: ventasagil.com" https://127.0.0.1/login -k | head -3
curl -sI -H "Host: san-martin.ventasagil.com" https://127.0.0.1/login -k | head -3
curl -sI -H "Host: ventasagil.com" http://127.0.0.1/login | head -3
echo "OK"
