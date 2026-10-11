# Despliegue VentaPOS + SeccionK en VPS

**Configuración:** SeccionK en ruta `ventasagil.com/seccionk` (sin subdominio)

---

## 🚀 Despliegue Automatizado Rápido

### Opción 1: Script Automático (Recomendado)

```bash
# 1. Conectar al VPS
ssh root@tu-servidor-ip

# 2. Descargar y ejecutar script
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh
chmod +x deploy.sh
./deploy.sh
```

El script te pedirá:
- **Dominio:** ventasagil.com
- **Email SSL:** tu@email.com
- **Contraseña MySQL:** (elige una segura)

---

## 📋 Despliegue Manual

### 1. Requisitos del Servidor

```bash
# Sistema operativo
Ubuntu 22.04 LTS / Debian 11+

# Recursos mínimos
- 1 vCPU
- 2 GB RAM
- 20 GB SSD
- IP pública
```

### 2. Configurar DNS

**En tu proveedor de dominio (GoDaddy, Namecheap, etc.):**

| Tipo | Nombre | Valor | TTL |
|------|--------|-------|-----|
| A | @ | IP_DE_TU_VPS | 300 |
| A | www | IP_DE_TU_VPS | 300 |

⏱️ Espera 5-15 minutos para propagación DNS.

### 3. Instalación Base

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar software
sudo apt install -y nginx mysql-server \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml \
  php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd \
  php8.3-bcmath php8.3-intl git unzip curl

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt install -y nodejs
```

### 4. Configurar MySQL

```bash
sudo mysql

# En el prompt de MySQL:
CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ventapos_user'@'localhost' IDENTIFIED BY 'TU_CONTRASEÑA_SEGURA';
GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Clonar y Configurar Aplicación

```bash
# Clonar repositorio
cd /var/www
sudo git clone https://github.com/ingenioalex/VentaPOS.git ventasagil
cd ventasagil
sudo git checkout cursor/seccionk-module-0c7c

# Copiar y configurar .env
sudo cp .env.example .env
sudo nano .env
```

**Edita `.env`:**

```env
APP_NAME="VentaPOS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ventasagil.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventapos
DB_USERNAME=ventapos_user
DB_PASSWORD=TU_CONTRASEÑA_MYSQL

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu@email.com
MAIL_PASSWORD=tu_contraseña_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu@email.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Instalar Dependencias

```bash
# Dependencias PHP
sudo composer install --no-dev --optimize-autoloader

# Dependencias JS y construir assets
npm install
npm run build

# Generar key de Laravel
sudo php artisan key:generate

# Permisos
sudo chown -R www-data:www-data /var/www/ventasagil
sudo chmod -R 755 /var/www/ventasagil
sudo chmod -R 775 /var/www/ventasagil/storage
sudo chmod -R 775 /var/www/ventasagil/bootstrap/cache
```

### 7. Migraciones y Seeders

```bash
# Ejecutar migraciones
sudo php artisan migrate --force

# Cargar datos de prueba
sudo php artisan db:seed --force

# Optimizar
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

### 8. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/ventasagil
```

**Contenido del archivo:**

```nginx
server {
    listen 80;
    server_name ventasagil.com www.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    access_log /var/log/nginx/ventasagil-access.log;
    error_log /var/log/nginx/ventasagil-error.log;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # VentaPOS + SeccionK (mismo dominio)
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

**Activar sitio:**

```bash
sudo ln -s /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### 9. Instalar SSL (HTTPS)

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d ventasagil.com -d www.ventasagil.com
```

Sigue las instrucciones:
1. Ingresa tu email
2. Acepta términos (Y)
3. Elige si compartir email (N)
4. Selecciona: **2 (Redirect)**

### 10. Configurar Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
sudo ufw status
```

### 11. Configurar Cron Jobs

```bash
sudo crontab -e -u www-data
```

**Agregar:**

```cron
* * * * * cd /var/www/ventasagil && php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Verificación

### URLs de Acceso

| Sistema | URL |
|---------|-----|
| VentaPOS Principal | `https://ventasagil.com` |
| VentaPOS Login | `https://ventasagil.com/login` |
| **SeccionK Público** | `https://ventasagil.com/seccionk` |
| **SeccionK Admin** | `https://ventasagil.com/seccionk/admin/login` |

### Credenciales de Prueba

**VentaPOS:**
- Usuario: `admin`
- Contraseña: `admin123`

**SeccionK Admin:**
- Usuario: `admin`
- Contraseña: `seccionk2026`

**SeccionK - Búsqueda Pública:**
- DNI: `12345678`
- Apellido: `García`

---

## 🔧 Comandos Útiles

```bash
# Ver logs de Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Ver logs de Nginx
tail -f /var/log/nginx/ventasagil-error.log

# Reiniciar servicios
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mysql

# Limpiar caché Laravel
cd /var/www/ventasagil
sudo php artisan cache:clear
sudo php artisan config:clear
sudo php artisan route:clear
sudo php artisan view:clear

# Actualizar código
cd /var/www/ventasagil
sudo git pull origin cursor/seccionk-module-0c7c
sudo composer install --no-dev --optimize-autoloader
npm install && npm run build
sudo php artisan migrate --force
sudo php artisan config:cache
sudo php artisan route:cache
sudo systemctl restart nginx php8.3-fpm
```

---

## 🐛 Solución de Problemas

### Error 404 en SeccionK

```bash
# Verificar rutas
cd /var/www/ventasagil
php artisan route:list | grep seccionk

# Limpiar caché
php artisan route:clear
php artisan config:clear
php artisan cache:clear
systemctl restart nginx php8.3-fpm
```

### Error 500 Internal Server Error

```bash
# Ver logs
tail -30 /var/www/ventasagil/storage/logs/laravel.log
tail -30 /var/log/nginx/ventasagil-error.log

# Verificar permisos
sudo chown -R www-data:www-data /var/www/ventasagil
sudo chmod -R 775 /var/www/ventasagil/storage
```

### Base de datos no conecta

```bash
# Verificar MySQL
sudo systemctl status mysql
sudo mysql -u ventapos_user -p ventapos

# Si falla, recrear usuario:
sudo mysql
DROP USER 'ventapos_user'@'localhost';
CREATE USER 'ventapos_user'@'localhost' IDENTIFIED BY 'nueva_contraseña';
GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Actualizar .env con la nueva contraseña
sudo nano /var/www/ventasagil/.env
```

---

## 📞 Soporte

Para problemas o dudas:
- **Email:** support@ventasagil.com
- **GitHub Issues:** https://github.com/ingenioalex/VentaPOS/issues

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

---

**Última actualización:** Octubre 2026  
**Versión:** 2.0 (SeccionK en ruta)
