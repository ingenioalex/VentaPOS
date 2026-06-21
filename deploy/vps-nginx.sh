#!/bin/bash
set -euo pipefail

cat > /etc/nginx/sites-available/ventapos <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name 2.25.166.12 _;
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

ln -sf /etc/nginx/sites-available/ventapos /etc/nginx/sites-enabled/ventapos
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl restart php8.3-fpm
chown -R www-data:www-data /var/www/ventapos/storage /var/www/ventapos/bootstrap/cache
chmod -R 775 /var/www/ventapos/storage /var/www/ventapos/bootstrap/cache
( crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "* * * * * cd /var/www/ventapos && php artisan schedule:run >> /dev/null 2>&1" ) | crontab -
curl -sI http://127.0.0.1/login | head -8
echo "NGINX OK"
