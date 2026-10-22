# 🚀 Despliegue VPS - Guía Rápida Visual

## ⚡ Despliegue Automático (Recomendado)

```
┌─────────────────────────────────────────┐
│  1. Conectar al VPS                     │
│  ssh root@TU_IP_VPS                     │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  2. Descargar script                    │
│  wget https://github.com/ingenioalex/   │
│       VentaPOS/raw/cursor/seccionk-     │
│       module-0c7c/deploy-vps.sh         │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  3. Ejecutar                            │
│  sudo bash deploy-vps.sh                │
│                                         │
│  Proporcionará:                         │
│  - Dominio (ej: ventasagil.com)         │
│  - IP del VPS                           │
│  - Email para SSL                       │
│  - URL del repositorio Git              │
│  - Contraseña de MySQL                  │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  4. Esperar 10-15 minutos               │
│  El script hace TODO automáticamente:   │
│  ✓ Instala software                     │
│  ✓ Configura base de datos              │
│  ✓ Descarga código                      │
│  ✓ Configura Nginx                      │
│  ✓ Obtiene SSL (Let's Encrypt)          │
│  ✓ Ejecuta migraciones                  │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  5. ✅ ¡LISTO!                          │
│  https://ventasagil.com                 │
│  https://seccionk.ventasagil.com        │
└─────────────────────────────────────────┘
```

---

## 📋 Requisitos Previos

```
□ VPS con Ubuntu 22.04
□ Dominio comprado (ej: ventasagil.com)
□ DNS configurado:
  - A record: @ → IP_VPS
  - A record: www → IP_VPS
  - A record: seccionk → IP_VPS
□ Acceso SSH al VPS
```

---

## 🔧 Despliegue Manual (Paso a Paso)

### Paso 1: Conectar al VPS
```bash
ssh root@TU_IP_VPS
```

### Paso 2: Actualizar Sistema
```bash
sudo apt update && sudo apt upgrade -y
```

### Paso 3: Instalar Software
```bash
# Nginx, PHP, MySQL, Composer, Node.js
sudo apt install -y nginx mysql-server php8.3-fpm \
  php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring \
  php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath \
  git unzip curl

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Paso 4: Clonar Repositorio
```bash
cd /var/www
git clone https://github.com/ingenioalex/VentaPOS.git ventasagil
cd ventasagil
git checkout cursor/seccionk-module-0c7c
```

### Paso 5: Configurar Laravel
```bash
cp .env.example .env
nano .env  # Editar configuración

composer install --no-dev --optimize-autoloader
npm install && npm run build

php artisan key:generate
```

### Paso 6: Base de Datos
```bash
sudo mysql
```
```sql
CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ventapos_user'@'localhost' IDENTIFIED BY 'PASSWORD';
GRANT ALL PRIVILEGES ON ventapos.* TO 'ventapos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
```bash
php artisan migrate --force
php artisan db:seed --force
```

### Paso 7: Configurar Nginx
```bash
sudo nano /etc/nginx/sites-available/ventasagil
```
Ver configuración completa en: `docs/DEPLOY_VPS.md`

```bash
sudo ln -s /etc/nginx/sites-available/ventasagil /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Paso 8: SSL con Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx -y

sudo certbot --nginx \
  -d ventasagil.com \
  -d www.ventasagil.com \
  -d seccionk.ventasagil.com
```

### Paso 9: Configurar Firewall
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## ✅ Verificación

### Probar en Navegador
```
✓ https://ventasagil.com          → VentaPOS
✓ https://seccionk.ventasagil.com → SeccionK
```

### Credenciales de Prueba

**VentaPOS:**
```
URL: https://ventasagil.com/login
Usuario: admin
Contraseña: admin123
```

**SeccionK Admin:**
```
URL: https://seccionk.ventasagil.com/admin/login
Usuario: admin
Contraseña: seccionk2026
```

**SeccionK Público:**
```
URL: https://seccionk.ventasagil.com
DNI: 12345678
Apellido: García
```

---

## 🔄 Actualizar Código

```bash
cd /var/www/ventasagil

# Backup
cp .env .env.backup

# Pull cambios
git pull origin cursor/seccionk-module-0c7c

# Actualizar dependencias
composer install --no-dev
npm install && npm run build

# Migraciones
php artisan migrate --force

# Limpiar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar servicios
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

---

## 📊 Monitoreo

### Ver Logs en Tiempo Real
```bash
# Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Nginx - VentaPOS
tail -f /var/log/nginx/ventasagil-error.log

# Nginx - SeccionK
tail -f /var/log/nginx/seccionk-error.log
```

### Verificar Servicios
```bash
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status mysql
```

---

## 🆘 Problemas Comunes

### Error 502 Bad Gateway
```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

### Error 500 Internal Server Error
```bash
# Verificar permisos
sudo chown -R www-data:www-data /var/www/ventasagil/storage
sudo chmod -R 775 /var/www/ventasagil/storage

# Ver log de errores
tail -f /var/www/ventasagil/storage/logs/laravel.log
```

### DNS no Resuelve
```bash
# Verificar propagación (esperar 5-30 min)
nslookup ventasagil.com
nslookup seccionk.ventasagil.com

# Verificar Nginx
sudo nginx -t
sudo systemctl status nginx
```

---

## 📁 Estructura en VPS

```
/var/www/ventasagil/
├── app/
├── bootstrap/
├── config/
├── database/
│   └── migrations/
│       └── 2026_10_11_000001_crear_tablas_seccionk.php
├── docs/
│   ├── DEPLOY_VPS.md
│   └── SSL_HTTPS_CONFIGURACION.md
├── public/
├── resources/
│   └── views/
│       └── seccionk/
├── routes/
│   ├── web.php
│   └── seccionk.php
├── storage/
│   └── logs/
│       └── laravel.log
├── .env
└── deploy-vps.sh
```

---

## 🔐 Configuración DNS Completa

En tu proveedor de dominio (Namecheap, GoDaddy, etc.):

```
┌──────┬────────────┬──────────────┬──────┐
│ Tipo │   Host     │    Valor     │ TTL  │
├──────┼────────────┼──────────────┼──────┤
│  A   │     @      │  TU_IP_VPS   │ 3600 │
│  A   │    www     │  TU_IP_VPS   │ 3600 │
│  A   │  seccionk  │  TU_IP_VPS   │ 3600 │
└──────┴────────────┴──────────────┴──────┘
```

---

## ⏱️ Tiempo Estimado

| Tarea | Automático | Manual |
|-------|------------|--------|
| Preparación VPS | 5 min | 10 min |
| Instalación software | 5 min | 15 min |
| Configuración | - | 15 min |
| SSL | 2 min | 5 min |
| **TOTAL** | **12 min** | **45 min** |

---

## 💰 Costos

| Item | Costo |
|------|-------|
| VPS (2GB RAM) | $5-10/mes |
| Dominio | $10-15/año |
| SSL (Let's Encrypt) | **Gratis** |
| **Total mensual** | **$5-10** |

---

## 📚 Documentación Completa

- **Técnica**: `docs/DEPLOY_VPS.md`
- **SSL**: `docs/SSL_HTTPS_CONFIGURACION.md`
- **Script**: `deploy-vps.sh`

---

## ✨ Resultado Final

```
Antes:
❌ No accesible
❌ Sin HTTPS
❌ Sin configurar

Después:
✅ https://ventasagil.com (VentaPOS)
✅ https://seccionk.ventasagil.com (SeccionK)
✅ SSL activo
✅ Firewall configurado
✅ Backups automáticos SSL
✅ Listo para producción
```

---

**Última actualización:** 2026-10-11  
**Tiempo total:** 10-15 minutos (automático)  
**Dificultad:** ⭐⭐☆☆☆ (Fácil con script)
