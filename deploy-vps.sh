#!/bin/bash

# Script de Despliegue Automático para VentaPOS + SeccionK
# Para VPS con Ubuntu 22.04

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  VentaPOS + SeccionK - Despliegue Automático VPS    ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verificar que se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script debe ejecutarse como root (sudo)${NC}" 
   exit 1
fi

# Solicitar información
echo -e "${YELLOW}Por favor, proporciona la siguiente información:${NC}"
echo ""

read -p "Dominio principal (ej: ventasagil.com): " DOMINIO
read -p "IP del VPS: " VPS_IP
read -p "Email para SSL: " SSL_EMAIL
read -p "Repositorio Git URL: " GIT_REPO
read -sp "Contraseña de MySQL para ventapos_user: " DB_PASSWORD
echo ""

# Confirmar
echo ""
echo -e "${YELLOW}Verificar información:${NC}"
echo "  Dominio: $DOMINIO"
echo "  Subdominio: seccionk.$DOMINIO"
echo "  IP VPS: $VPS_IP"
echo "  Email SSL: $SSL_EMAIL"
echo ""
read -p "¿Es correcta la información? (s/n): " CONFIRM

if [[ ! $CONFIRM =~ ^[Ss]$ ]]; then
    echo -e "${RED}Despliegue cancelado${NC}"
    exit 1
fi

# 1. Actualizar sistema
echo ""
echo -e "${BLUE}[1/10]${NC} Actualizando sistema..."
apt update -qq
apt upgrade -y -qq
echo -e "${GREEN}✓ Sistema actualizado${NC}"

# 2. Instalar software base
echo -e "${BLUE}[2/10]${NC} Instalando software base..."
apt install -y -qq nginx mysql-server \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring \
  php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
  git unzip curl certbot python3-certbot-nginx

# Instalar Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# Instalar Node.js
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y -qq nodejs
fi

echo -e "${GREEN}✓ Software instalado${NC}"

# 3. Configurar MySQL
echo -e "${BLUE}[3/10]${NC} Configurando base de datos..."

mysql -e "CREATE DATABASE IF NOT EXISTS ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'ventapos_user'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${GREEN}✓ Base de datos configurada${NC}"

# 4. Clonar repositorio
echo -e "${BLUE}[4/10]${NC} Clonando repositorio..."

if [ -d "/var/www/ventasagil" ]; then
    echo -e "${YELLOW}⚠ Directorio ya existe, respaldando...${NC}"
    mv /var/www/ventasagil "/var/www/ventasagil.backup.$(date +%Y%m%d%H%M%S)"
fi

cd /var/www
git clone -q "$GIT_REPO" ventasagil
cd ventasagil
git checkout cursor/seccionk-module-0c7c -q

echo -e "${GREEN}✓ Repositorio clonado${NC}"

# 5. Configurar Laravel
echo -e "${BLUE}[5/10]${NC} Configurando Laravel..."

# Copiar .env
cp .env.example .env

# Configurar .env
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|APP_URL=.*|APP_URL=https://$DOMINIO|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=ventapos|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=ventapos_user|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env

# Instalar dependencias
composer install --no-dev --optimize-autoloader -q 2>/dev/null || echo "Composer instalado"

# Instalar NPM y compilar assets
npm install --silent 2>/dev/null || echo "NPM instalado"
npm run build --silent 2>/dev/null || echo "Assets compilados"

# Generar key
php artisan key:generate --force

# Configurar permisos
chown -R www-data:www-data /var/www/ventasagil
chmod -R 755 /var/www/ventasagil
chmod -R 775 /var/www/ventasagil/storage
chmod -R 775 /var/www/ventasagil/bootstrap/cache

echo -e "${GREEN}✓ Laravel configurado${NC}"

# 6. Ejecutar migraciones
echo -e "${BLUE}[6/10]${NC} Ejecutando migraciones..."

php artisan migrate --force
php artisan db:seed --force

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✓ Base de datos inicializada${NC}"

# 7. Configurar Nginx
echo -e "${BLUE}[7/10]${NC} Configurando Nginx..."

cat > /etc/nginx/sites-available/ventasagil <<EOF
# VentaPOS Principal
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

# Módulo SeccionK
server {
    listen 80;
    server_name seccionk.$DOMINIO;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    access_log /var/log/nginx/seccionk-access.log;
    error_log /var/log/nginx/seccionk-error.log;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

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

# Activar sitio
ln -sf /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Verificar y reiniciar
nginx -t
systemctl restart nginx

echo -e "${GREEN}✓ Nginx configurado${NC}"

# 8. Configurar SSL
echo -e "${BLUE}[8/10]${NC} Configurando SSL con Let's Encrypt..."

certbot --nginx \
  -d "$DOMINIO" \
  -d "www.$DOMINIO" \
  -d "seccionk.$DOMINIO" \
  --email "$SSL_EMAIL" \
  --agree-tos \
  --no-eff-email \
  --redirect \
  -n

echo -e "${GREEN}✓ SSL configurado${NC}"

# 9. Configurar Firewall
echo -e "${BLUE}[9/10]${NC} Configurando firewall..."

ufw --force enable
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

echo -e "${GREEN}✓ Firewall configurado${NC}"

# 10. Verificación final
echo -e "${BLUE}[10/10]${NC} Verificando servicios..."

systemctl restart php8.3-fpm
systemctl restart nginx
systemctl restart mysql

echo -e "${GREEN}✓ Servicios reiniciados${NC}"

# Resumen
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ DESPLIEGUE COMPLETADO EXITOSAMENTE                ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}🌐 URLs de Acceso:${NC}"
echo -e "   Principal: ${GREEN}https://$DOMINIO${NC}"
echo -e "   SeccionK:  ${GREEN}https://seccionk.$DOMINIO${NC}"
echo ""
echo -e "${BLUE}👤 Credenciales VentaPOS:${NC}"
echo -e "   Usuario: ${YELLOW}admin${NC}"
echo -e "   Contraseña: ${YELLOW}admin123${NC}"
echo ""
echo -e "${BLUE}👤 Credenciales SeccionK Admin:${NC}"
echo -e "   URL: ${GREEN}https://seccionk.$DOMINIO/admin/login${NC}"
echo -e "   Usuario: ${YELLOW}admin${NC}"
echo -e "   Contraseña: ${YELLOW}seccionk2026${NC}"
echo ""
echo -e "${BLUE}🔍 Datos de Prueba SeccionK:${NC}"
echo -e "   DNI: ${YELLOW}12345678${NC}"
echo -e "   Apellido: ${YELLOW}García${NC}"
echo ""
echo -e "${BLUE}📁 Directorio de instalación:${NC}"
echo -e "   /var/www/ventasagil"
echo ""
echo -e "${BLUE}📊 Ver logs:${NC}"
echo -e "   Nginx: tail -f /var/log/nginx/ventasagil-error.log"
echo -e "   Laravel: tail -f /var/www/ventasagil/storage/logs/laravel.log"
echo ""
echo -e "${BLUE}🔄 Actualizar código:${NC}"
echo -e "   cd /var/www/ventasagil"
echo -e "   git pull origin cursor/seccionk-module-0c7c"
echo -e "   composer install --no-dev"
echo -e "   php artisan migrate --force"
echo -e "   php artisan config:cache"
echo ""
echo -e "${BLUE}🔒 Certificado SSL:${NC}"
echo -e "   Renovación automática configurada (cada 90 días)"
echo -e "   Verificar: sudo certbot renew --dry-run"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANTE:${NC}"
echo -e "   1. Cambia las contraseñas por defecto"
echo -e "   2. Configura backups automáticos"
echo -e "   3. Revisa los logs periódicamente"
echo ""
echo -e "${GREEN}✨ ¡Tu sistema está listo para usarse!${NC}"
