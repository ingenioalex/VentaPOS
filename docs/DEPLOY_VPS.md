# 🚀 Guía de Despliegue a VPS - VentaPOS + Módulo SeccionK

## 📋 Requisitos del VPS

### Mínimos Recomendados
- **OS**: Ubuntu 22.04 LTS o superior
- **RAM**: 2GB mínimo (4GB recomendado)
- **CPU**: 2 cores
- **Disco**: 20GB SSD
- **IP**: Pública estática

### Software Necesario
- Nginx o Apache
- PHP 8.3
- MySQL 8.0
- Composer
- Node.js (para assets)
- Certbot (SSL)

---

## 🔧 Preparación del VPS

### 1. Conectar al VPS

```bash
ssh root@TU_IP_VPS
# O con usuario no-root:
ssh usuario@TU_IP_VPS
```

### 2. Actualizar Sistema

```bash
sudo apt update
sudo apt upgrade -y
```

### 3. Instalar Software Base

```bash
# Instalar Nginx, PHP 8.3, MySQL
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-cli php8.3-mysql \
  php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath \
  php8.3-intl php8.3-redis git unzip curl

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js (para compilar assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 📁 Estructura de Directorios

```bash
# Crear estructura
sudo mkdir -p /var/www/ventasagil
sudo chown -R $USER:$USER /var/www/ventasagil
```

---

## 📤 Subir Código al VPS

### Opción A: Git (Recomendado)

```bash
# En el VPS
cd /var/www
git clone https://github.com/ingenioalex/VentaPOS.git ventasagil
cd ventasagil

# Cambiar a la rama con SeccionK
git checkout cursor/seccionk-module-0c7c
```

### Opción B: SCP/SFTP Manual

```bash
# Desde tu máquina local
cd /ruta/a/VentaPOS

# Crear archivo tar
tar -czf ventapos.tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  .

# Subir al VPS
scp ventapos.tar.gz usuario@TU_IP_VPS:/tmp/

# En el VPS
cd /var/www
sudo mkdir -p ventasagil
cd ventasagil
sudo tar -xzf /tmp/ventapos.tar.gz
sudo chown -R www-data:www-data /var/www/ventasagil
```

---

## ⚙️ Configuración de Laravel

### 1. Instalar Dependencias

```bash
cd /var/www/ventasagil

# Instalar dependencias de Composer
composer install --no-dev --optimize-autoloader

# Instalar dependencias de NPM
npm install
npm run build
```

### 2. Configurar .env

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar configuración
nano .env
```

**Contenido del .env para Producción:**

```env
APP_NAME=VentaPOS
APP_ENV=production
APP_KEY=base64:GENERAR_CON_COMANDO
APP_DEBUG=false
APP_URL=https://ventasagil.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventapos
DB_USERNAME=ventapos_user
DB_PASSWORD=PASSWORD_SEGURO_AQUI

# Cache y sesiones
CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail (configurar según proveedor)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ventasagil.com
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp
VENTAPOS_WHATSAPP=51973034118
```

### 3. Generar APP_KEY

```bash
php artisan key:generate
```

### 4. Configurar Permisos

```bash
sudo chown -R www-data:www-data /var/www/ventasagil
sudo chmod -R 755 /var/www/ventasagil
sudo chmod -R 775 /var/www/ventasagil/storage
sudo chmod -R 775 /var/www/ventasagil/bootstrap/cache
```

---

## 🗄️ Configuración de MySQL

### 1. Crear Base de Datos

```bash
sudo mysql
```

```sql
-- Crear base de datos
CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario
CREATE USER 'ventapos_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURO_AQUI';

-- Otorgar permisos
GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Salir
EXIT;
```

### 2. Ejecutar Migraciones

```bash
cd /var/www/ventasagil

# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders (incluye SeccionK)
php artisan db:seed --force
```

### 3. Optimizar

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🌐 Configuración DNS

### En tu Proveedor de Dominio (ej: Namecheap, GoDaddy)

Crear estos registros DNS:

```
Tipo  | Host           | Valor        | TTL
------|----------------|--------------|-----
A     | @              | TU_IP_VPS    | 3600
A     | www            | TU_IP_VPS    | 3600
A     | seccionk       | TU_IP_VPS    | 3600
```

**Verificar propagación:**
```bash
# Esperar 5-30 minutos, luego verificar
nslookup ventasagil.com
nslookup seccionk.ventasagil.com
```

---

## 🔧 Configuración de Nginx

### 1. Crear Configuración

```bash
sudo nano /etc/nginx/sites-available/ventasagil
```

**Contenido (sin SSL por ahora):**

```nginx
# VentaPOS Principal - HTTP (temporal)
server {
    listen 80;
    server_name ventasagil.com www.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/ventasagil-access.log;
    error_log /var/log/nginx/ventasagil-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

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

# Módulo SeccionK - HTTP (temporal)
server {
    listen 80;
    server_name seccionk.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/seccionk-access.log;
    error_log /var/log/nginx/seccionk-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

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

### 2. Activar Sitio

```bash
# Crear enlace simbólico
sudo ln -s /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/

# Eliminar sitio por defecto
sudo rm /etc/nginx/sites-enabled/default

# Verificar configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

---

## 🔒 Configurar SSL (HTTPS)

### 1. Instalar Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Obtener Certificados SSL

```bash
# Obtener certificados para ambos dominios
sudo certbot --nginx \
  -d ventasagil.com \
  -d www.ventasagil.com \
  -d seccionk.ventasagil.com \
  --email tu-email@gmail.com \
  --agree-tos \
  --no-eff-email
```

**Certbot automáticamente:**
- ✅ Genera certificados
- ✅ Modifica configuración de Nginx
- ✅ Configura redirección HTTP → HTTPS
- ✅ Programa renovación automática

### 3. Verificar Renovación Automática

```bash
# Probar renovación (dry-run)
sudo certbot renew --dry-run

# Ver timer de renovación
sudo systemctl status certbot.timer
```

---

## 🔥 Configurar Firewall

```bash
# Permitir conexiones necesarias
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS

# Activar firewall
sudo ufw enable

# Verificar estado
sudo ufw status
```

---

## 🎯 Verificación Final

### 1. Probar Conexión

```bash
# Verificar que Nginx está corriendo
sudo systemctl status nginx

# Verificar que PHP-FPM está corriendo
sudo systemctl status php8.3-fpm

# Verificar que MySQL está corriendo
sudo systemctl status mysql
```

### 2. Probar en Navegador

Acceder a:
- ✅ https://ventasagil.com
- ✅ https://seccionk.ventasagil.com

**Debe mostrar:**
- 🔒 Candado verde
- ✅ "Seguro"
- ✅ Sitio cargando correctamente

### 3. Probar Login

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

## 🔄 Actualización de Código

```bash
cd /var/www/ventasagil

# Hacer backup de .env
cp .env .env.backup

# Pull cambios desde Git
git pull origin cursor/seccionk-module-0c7c

# Actualizar dependencias
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Ejecutar migraciones nuevas
php artisan migrate --force

# Limpiar y regenerar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar servicios
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

---

## 📊 Monitoreo

### Logs

```bash
# Logs de Nginx
sudo tail -f /var/log/nginx/ventasagil-access.log
sudo tail -f /var/log/nginx/ventasagil-error.log
sudo tail -f /var/log/nginx/seccionk-access.log
sudo tail -f /var/log/nginx/seccionk-error.log

# Logs de Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Logs de PHP-FPM
sudo tail -f /var/log/php8.3-fpm.log
```

### Espacio en Disco

```bash
df -h
du -sh /var/www/ventasagil
```

---

## 🔐 Seguridad Adicional

### 1. Configurar Fail2ban

```bash
sudo apt install fail2ban -y

# Configurar
sudo nano /etc/fail2ban/jail.local
```

```ini
[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/*error.log

[php-url-fopen]
enabled = true
port = http,https
logpath = /var/log/nginx/*access.log
```

```bash
sudo systemctl restart fail2ban
```

### 2. Desactivar Root Login por SSH

```bash
sudo nano /etc/ssh/sshd_config
```

Cambiar:
```
PermitRootLogin no
PasswordAuthentication no  # Usar solo claves SSH
```

```bash
sudo systemctl restart sshd
```

---

## 📋 Checklist de Despliegue

```
Preparación:
□ VPS con Ubuntu 22.04
□ Dominio apuntando al VPS
□ SSH configurado

Instalación:
□ Software instalado (Nginx, PHP, MySQL)
□ Código subido al VPS
□ Dependencias instaladas (composer, npm)
□ .env configurado
□ APP_KEY generada
□ Permisos configurados

Base de Datos:
□ MySQL instalado
□ Base de datos creada
□ Usuario creado con permisos
□ Migraciones ejecutadas
□ Seeders ejecutados

Servidor Web:
□ Nginx configurado
□ Sitios habilitados
□ DNS propagado

SSL:
□ Certbot instalado
□ Certificados obtenidos
□ HTTPS funcionando
□ Renovación automática configurada

Seguridad:
□ Firewall configurado
□ Fail2ban instalado
□ Root login deshabilitado
□ Permisos correctos

Verificación:
□ Sitio carga correctamente
□ Login funciona
□ SeccionK funciona
□ HTTPS activo (candado verde)
□ Logs funcionando

Producción:
□ APP_DEBUG=false
□ APP_ENV=production
□ Cache configurado
□ Backups programados
```

---

## 🆘 Troubleshooting

### Error 502 Bad Gateway

```bash
# Verificar PHP-FPM
sudo systemctl status php8.3-fpm
sudo systemctl restart php8.3-fpm

# Verificar logs
sudo tail -f /var/log/nginx/error.log
```

### Error 500 Internal Server Error

```bash
# Ver logs de Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Verificar permisos
sudo chown -R www-data:www-data /var/www/ventasagil/storage
sudo chmod -R 775 /var/www/ventasagil/storage
```

### Base de Datos no Conecta

```bash
# Verificar MySQL
sudo systemctl status mysql

# Probar conexión
mysql -u ventapos_user -p ventapos
```

---

## 📞 Soporte

Para dudas o problemas:
- Email: soporte@ventasagil.com
- WhatsApp: +51 973 034 118

---

**Tiempo total de despliegue**: 30-60 minutos  
**Última actualización**: 2026-10-11  
**Versión**: 1.0
