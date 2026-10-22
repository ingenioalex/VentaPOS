# 🚀 Despliegue Inmediato a VPS - ventasagil.com

## 📋 Información Necesaria

Antes de empezar, ten a mano:

- 🌐 IP del VPS
- 🔑 Usuario SSH (root o usuario con sudo)
- 📧 Email para SSL
- 🔐 Contraseña SSH (o clave privada)

---

## 🎯 Método 1: Script Automático (Recomendado)

### Paso 1: Conectar al VPS

```bash
ssh root@ventasagil.com
# O
ssh root@TU_IP_VPS
```

### Paso 2: Descargar y Ejecutar Script

```bash
# Descargar script de despliegue
wget https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps.sh

# Dar permisos de ejecución
chmod +x deploy-vps.sh

# Ejecutar
./deploy-vps.sh
```

### Paso 3: Proporcionar Información

El script pedirá:

```
Dominio principal: ventasagil.com
IP del VPS: [Se detecta automáticamente]
Email para SSL: tu-email@gmail.com
Repositorio Git: https://github.com/ingenioalex/VentaPOS.git
Contraseña MySQL: [Crear una segura]
```

### Paso 4: Esperar 10-15 minutos

El script instalará y configurará TODO automáticamente.

### Paso 5: ¡Listo!

Acceder a:
- ✅ https://ventasagil.com (VentaPOS)
- ✅ https://seccionk.ventasagil.com (SeccionK)

---

## 🎯 Método 2: Paso a Paso Manual

### 1. Conectar al VPS

```bash
ssh root@ventasagil.com
```

### 2. Actualizar Sistema

```bash
apt update && apt upgrade -y
```

### 3. Instalar Software Necesario

```bash
# Instalar Nginx, PHP 8.3, MySQL
apt install -y nginx mysql-server \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml \
  php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd \
  php8.3-bcmath php8.3-intl git unzip curl \
  certbot python3-certbot-nginx

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

### 4. Configurar MySQL

```bash
mysql
```

```sql
CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ventapos_user'@'localhost' IDENTIFIED BY 'TU_PASSWORD_SEGURO_AQUI';
GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Clonar Repositorio

```bash
cd /var/www
git clone https://github.com/ingenioalex/VentaPOS.git ventasagil
cd ventasagil
git checkout cursor/seccionk-module-0c7c
```

### 6. Configurar Laravel

```bash
# Copiar .env
cp .env.example .env

# Editar .env
nano .env
```

**Configurar estos valores en .env:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ventasagil.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventapos
DB_USERNAME=ventapos_user
DB_PASSWORD=TU_PASSWORD_MYSQL
```

```bash
# Instalar dependencias
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Generar APP_KEY
php artisan key:generate

# Establecer permisos
chown -R www-data:www-data /var/www/ventasagil
chmod -R 755 /var/www/ventasagil
chmod -R 775 /var/www/ventasagil/storage
chmod -R 775 /var/www/ventasagil/bootstrap/cache
```

### 7. Ejecutar Migraciones

```bash
php artisan migrate --force
php artisan db:seed --force

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Configurar Nginx

```bash
nano /etc/nginx/sites-available/ventasagil
```

**Pegar esta configuración:**

```nginx
# VentaPOS Principal
server {
    listen 80;
    server_name ventasagil.com www.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    access_log /var/log/nginx/ventasagil-access.log;
    error_log /var/log/nginx/ventasagil-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Módulo SeccionK
server {
    listen 80;
    server_name seccionk.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    access_log /var/log/nginx/seccionk-access.log;
    error_log /var/log/nginx/seccionk-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Activar sitio
ln -s /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Verificar configuración
nginx -t

# Reiniciar Nginx
systemctl restart nginx
```

### 9. Configurar DNS

En tu proveedor de dominio, asegúrate de tener estos registros:

```
Tipo A: @        → IP_DE_TU_VPS
Tipo A: www      → IP_DE_TU_VPS
Tipo A: seccionk → IP_DE_TU_VPS
```

### 10. Configurar SSL con Let's Encrypt

```bash
# Obtener certificados SSL
certbot --nginx \
  -d ventasagil.com \
  -d www.ventasagil.com \
  -d seccionk.ventasagil.com \
  --email tu-email@gmail.com \
  --agree-tos \
  --no-eff-email \
  --redirect

# Verificar renovación automática
certbot renew --dry-run
```

### 11. Configurar Firewall

```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

---

## ✅ Verificación

### Probar en Navegador

Abrir:
- ✅ https://ventasagil.com
- ✅ https://seccionk.ventasagil.com

### Credenciales de Prueba

**VentaPOS:**
- URL: https://ventasagil.com/login
- Usuario: `admin`
- Contraseña: `admin123`

**SeccionK Admin:**
- URL: https://seccionk.ventasagil.com/admin/login
- Usuario: `admin`
- Contraseña: `seccionk2026`

**SeccionK Público:**
- URL: https://seccionk.ventasagil.com
- DNI: `12345678`
- Apellido: `García`

---

## 🔄 Actualizar Código Después

```bash
cd /var/www/ventasagil

# Backup
cp .env .env.backup

# Pull cambios
git pull origin cursor/seccionk-module-0c7c

# Actualizar
composer install --no-dev
npm install && npm run build
php artisan migrate --force

# Limpiar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar
systemctl restart php8.3-fpm
systemctl reload nginx
```

---

## 📊 Ver Logs

```bash
# Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Nginx - VentaPOS
tail -f /var/log/nginx/ventasagil-error.log

# Nginx - SeccionK
tail -f /var/log/nginx/seccionk-error.log
```

---

## 🆘 Problemas Comunes

### DNS no Resuelve

```bash
# Verificar propagación
nslookup ventasagil.com
nslookup seccionk.ventasagil.com

# Esperar 5-30 minutos para propagación DNS
```

### Error 502 Bad Gateway

```bash
systemctl restart php8.3-fpm
systemctl restart nginx
```

### Error 500

```bash
# Verificar permisos
chown -R www-data:www-data /var/www/ventasagil/storage
chmod -R 775 /var/www/ventasagil/storage

# Ver logs
tail -f /var/www/ventasagil/storage/logs/laravel.log
```

---

## ⏱️ Tiempo Estimado

- **Método Automático**: 10-15 minutos
- **Método Manual**: 30-45 minutos

---

## 💰 Costo

Todo el software es **GRATIS**:
- ✅ Nginx: Gratis
- ✅ PHP: Gratis
- ✅ MySQL: Gratis
- ✅ SSL (Let's Encrypt): Gratis
- Solo pagas el VPS (~$5-10/mes)

---

## 📞 Soporte

Si tienes problemas durante el despliegue, revisa:
- `docs/DEPLOY_VPS.md` - Documentación completa
- `DEPLOY_VPS_RAPIDO.md` - Guía visual

---

**¡Tu sistema estará en producción en pocos minutos!** 🚀
