#!/bin/bash
set -euo pipefail

DB_PASS="VentaPOS_Vl110185"
APP_DIR="/var/www/ventapos"

echo "=== MySQL ==="
mysql <<SQL
CREATE DATABASE IF NOT EXISTS ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS 'ventapos'@'localhost';
CREATE USER 'ventapos'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "=== Extraer proyecto ==="
mkdir -p "$APP_DIR"
cd "$APP_DIR"
tar -xzf /root/ventapos-deploy.tar.gz

echo "=== Composer ==="
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

echo "=== .env ==="
cp .env.example .env
php artisan key:generate --force
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=http://2.25.166.12|' .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=ventapos/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=ventapos/' .env
sed -i "s/# DB_PASSWORD=/DB_PASSWORD=${DB_PASS}/" .env

echo "=== Restaurar BD showcase ==="
gunzip -c /root/ventapos-showcase-pro.sql.gz | mysql -u ventapos -p"${DB_PASS}" ventapos

echo "=== Laravel ==="
mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache
php artisan storage:link --force 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Nginx ==="
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

( crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "* * * * * cd /var/www/ventapos && php artisan schedule:run >> /dev/null 2>&1" ) | crontab -

echo "=== Verificación ==="
mysql -u ventapos -p"${DB_PASS}" ventapos -e "SELECT COUNT(*) AS ventas FROM comprobantes; SELECT COUNT(*) AS empresas FROM empresas;"
echo "DEPLOY OK"
