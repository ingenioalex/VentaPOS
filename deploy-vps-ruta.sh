#!/bin/bash

# Script de Despliegue VentaPOS + SeccionK en VPS
# SeccionK en ruta: ventasagil.com/seccionk (sin subdominio)

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Despliegue VentaPOS + SeccionK en VPS               ║${NC}"
echo -e "${BLUE}║  SeccionK en: ventasagil.com/seccionk                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verificar que se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script debe ejecutarse como root (sudo)${NC}" 
   exit 1
fi

# Solicitar información
echo -e "${YELLOW}Configuración:${NC}"
echo ""
read -p "Dominio (sin www): " DOMINIO
read -p "Email para SSL: " SSL_EMAIL
read -sp "Contraseña MySQL para ventapos_user: " DB_PASSWORD
echo ""
echo ""

echo -e "${BLUE}[1/10]${NC} Actualizando sistema..."
apt update -qq && apt upgrade -y -qq

echo -e "${BLUE}[2/10]${NC} Instalando software..."
apt install -y -qq nginx mysql-server \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring \
  php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
  git unzip curl certbot python3-certbot-nginx

# Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# Node.js
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y -qq nodejs
fi

echo -e "${BLUE}[3/10]${NC} Configurando MySQL..."
mysql -e "CREATE DATABASE IF NOT EXISTS ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'ventapos_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${BLUE}[4/10]${NC} Clonando repositorio..."
if [ -d "/var/www/ventasagil" ]; then
    mv /var/www/ventasagil "/var/www/ventasagil.backup.$(date +%Y%m%d%H%M%S)"
fi

cd /var/www
git clone -q https://github.com/ingenioalex/VentaPOS.git ventasagil
cd ventasagil
git checkout cursor/seccionk-module-0c7c -q

echo -e "${BLUE}[5/10]${NC} Configurando Laravel..."
cp .env.example .env

sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|APP_URL=.*|APP_URL=https://$DOMINIO|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=ventapos|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=ventapos_user|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env

composer install --no-dev --optimize-autoloader -q 2>/dev/null || true
npm install --silent 2>/dev/null || true
npm run build --silent 2>/dev/null || true

php artisan key:generate --force

chown -R www-data:www-data /var/www/ventasagil
chmod -R 755 /var/www/ventasagil
chmod -R 775 /var/www/ventasagil/storage
chmod -R 775 /var/www/ventasagil/bootstrap/cache

echo -e "${BLUE}[6/10]${NC} Migraciones y datos..."
php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${BLUE}[7/10]${NC} Configurando Nginx..."

cat > /etc/nginx/sites-available/ventasagil <<EOF
server {
    listen 80;
    server_name $DOMINIO www.$DOMINIO;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    access_log /var/log/nginx/ventasagil-access.log;
    error_log /var/log/nginx/ventasagil-error.log;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # VentaPOS + SeccionK (mismo dominio, diferentes rutas)
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl restart nginx

echo -e "${BLUE}[8/10]${NC} Configurando SSL..."
certbot --nginx \
  -d "$DOMINIO" \
  -d "www.$DOMINIO" \
  --email "$SSL_EMAIL" \
  --agree-tos \
  --no-eff-email \
  --redirect \
  -n

echo -e "${BLUE}[9/10]${NC} Configurando firewall..."
ufw --force enable
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

echo -e "${BLUE}[10/10]${NC} Reiniciando servicios..."
systemctl restart php8.3-fpm
systemctl restart nginx
systemctl restart mysql

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ DESPLIEGUE COMPLETADO EXITOSAMENTE                ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}🌐 URLs de Acceso:${NC}"
echo -e "   VentaPOS:  ${GREEN}https://$DOMINIO${NC}"
echo -e "   SeccionK:  ${GREEN}https://$DOMINIO/seccionk${NC}"
echo ""
echo -e "${BLUE}👤 Credenciales VentaPOS:${NC}"
echo -e "   URL:       ${GREEN}https://$DOMINIO/login${NC}"
echo -e "   Usuario:   ${YELLOW}admin${NC}"
echo -e "   Contraseña:${YELLOW}admin123${NC}"
echo ""
echo -e "${BLUE}👤 Credenciales SeccionK Admin:${NC}"
echo -e "   URL:       ${GREEN}https://$DOMINIO/seccionk/admin/login${NC}"
echo -e "   Usuario:   ${YELLOW}admin${NC}"
echo -e "   Contraseña:${YELLOW}seccionk2026${NC}"
echo ""
echo -e "${BLUE}🔍 Datos de Prueba SeccionK:${NC}"
echo -e "   URL:       ${GREEN}https://$DOMINIO/seccionk${NC}"
echo -e "   DNI:       ${YELLOW}12345678${NC}"
echo -e "   Apellido:  ${YELLOW}García${NC}"
echo ""
echo -e "${BLUE}📁 Instalación:${NC}"
echo -e "   /var/www/ventasagil"
echo ""
echo -e "${BLUE}📊 Ver logs:${NC}"
echo -e "   tail -f /var/log/nginx/ventasagil-error.log"
echo -e "   tail -f /var/www/ventasagil/storage/logs/laravel.log"
echo ""
echo -e "${GREEN}✨ ¡Todo listo!${NC}"
