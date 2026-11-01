# Puesta en marcha — Producción

Checklist para llevar VentaPOS de desarrollo local a un entorno productivo. Detalle de hosting en [HOSTING_DOMINIO_Y_DESPLIEGUE.md](HOSTING_DOMINIO_Y_DESPLIEGUE.md).

## 1. Requisitos del servidor

| Recurso | Mínimo | Recomendado (10–30 empresas) |
|---------|--------|------------------------------|
| CPU | 1 vCPU | 2 vCPU |
| RAM | 2 GB | 4 GB |
| Disco | 40 GB SSD | 80 GB SSD |
| SO | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |
| PHP | 8.3 + extensiones: mbstring, pdo_mysql, gd, xml, curl, zip | Igual |
| MySQL | 8.0 | 8.0 |
| Web | Nginx o Apache | Nginx + PHP-FPM |

## 2. Checklist pre-deploy

- [ ] Repositorio clonado en servidor (`/var/www/ventapos`)
- [ ] `.env` producción (nunca commitear)
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` generado (`php artisan key:generate`)
- [ ] Base de datos creada (utf8mb4)
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link`
- [ ] Permisos `storage/` y `bootstrap/cache/` escribibles por www-data
- [ ] SSL (Let's Encrypt) en dominio **y subdominios** (`*.ventasagil.com` o expand por empresa)
- [x] Cron `* * * * * php artisan schedule:run` (usuario `www-data`, ver `deploy/vps-cron-backup.sh`)
- [x] Backup MySQL automático diario (`ventapos:backup-db` a las 03:00 America/Lima)

## 3. Variables `.env` críticas

```env
APP_URL=https://tudominio.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ventapos
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
# o redis en escala mayor

VENTAPOS_WHATSAPP=51999999999
# Perfil LinkedIn del fundador (página /contacto)
VENTAPOS_LINKEDIN_FUNDADOR=https://www.linkedin.com/in/yuri-alexander-vargas-l-1774142b9/
```

## 4. SSL y subdominios multi-empresa

Cada empresa usa `codigo.ventasagil.com`. El certificado inicial de Certbot solo cubre `ventasagil.com` y `www`; los subdominios provocan **NET::ERR_CERT_COMMON_NAME_INVALID** si no se amplía.

**Solución inmediata (sin API):** incluir cada subdominio activo en el certificado:

```bash
bash deploy/ventasagil-ssl-wildcard.sh expand
```

Ejecutar de nuevo al **dar de alta una empresa nueva** en producción.

**Solución definitiva (wildcard `*.ventasagil.com`):** token API Hostinger (hPanel → Perfil → API):

```bash
export HOSTINGER_API_TOKEN="tu-token"
bash deploy/ventasagil-ssl-wildcard.sh wildcard
```

Credenciales en el VPS: `/root/.secrets/hostinger.ini` (chmod 600). Certbot DNS en `/opt/certbot-dns/`. La renovación usa el plugin `dns-hostinger` automáticamente.

Si el navegador muestra **NET::ERR_CERT_DATE_INVALID**, el certificado Let's Encrypt caducó. Causa habitual: el token API de Hostinger devolvió **401** y no pudo crear el TXT `_acme-challenge`. El DNS A/`*` puede estar bien y aun así el candado sale rojo.

**Recuperación inmediata (dominio + www + subdominios ya dados de alta), sin token:**

```bash
bash /var/www/ventapos/deploy/vps-renovar-ssl-ahora.sh
```

Eso usa HTTP-01 (`certbot --webroot`). El `certbot.timer` del sistema lo renovará solo ~30 días antes de vencer.

**Para volver al wildcard `*.ventasagil.com` (empresas nuevas sin tocar el cert):** hPanel → Perfil → API → generar token **nuevo** con permiso DNS, guardarlo en el VPS y:

```bash
export HOSTINGER_API_TOKEN="token-nuevo"
bash /var/www/ventapos/deploy/ventasagil-ssl-wildcard.sh wildcard
```

No pulse «Restablecer registros DNS» en Hostinger: borra A/`*` y rompe el sitio.

## 5. Cron y backups automáticos

En el VPS:

```bash
bash deploy/vps-cron-backup.sh
```

Instala `/etc/cron.d/ventapos` (cada minuto `schedule:run` como `www-data`).

| Hora (Lima) | Tarea |
|-------------|--------|
| **03:00** | Backup MySQL → `storage/app/backups/` |
| **20:00** | Resumen WhatsApp (si está configurado) |

- Copias manuales: **Admin → Backups** (`/admin/backups`)
- Retención: 30 días (configurable en admin)
- Log scheduler: `storage/logs/scheduler.log`
- Requiere `APP_TIMEZONE=America/Lima` en `.env`

## 5.1 Cola de trabajos (SUNAT / POS)

Con `QUEUE_CONNECTION=database`, la emisión SUNAT al cobrar va a la cola. Sin worker activo, los CPE quedan en `pendiente`.

```bash
cp deploy/ventapos-queue.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable --now ventapos-queue
systemctl status ventapos-queue
```

Tras cada deploy que toque jobs: `systemctl restart ventapos-queue`.

## 6. Comandos de despliegue

```bash
cd /var/www/ventapos
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Reiniciar PHP-FPM
sudo systemctl reload php8.3-fpm
```

## 7. Post-deploy funcional

| Verificación | Cómo |
|--------------|------|
| Login empresa demo | Código + usuario |
| POS cobrar boleta | Stock descuenta, ticket imprime |
| SUNAT simulación | Activar en `/app/sunat`, cobrar, ver QR nítido |
| Super admin | `/{ADMIN_PANEL_PATH}/login` (ver [SEGURIDAD.md](SEGURIDAD.md); no usar `/admin` en producción) |
| SEO | `sitemap.xml`, `robots.txt`, `/preguntas-frecuentes` — despliegue: `bash deploy/seo-deploy.sh` |
| HTTPS | Sin mixed content en ticket/logo |

## 8. SUNAT en producción

1. Contratar OSE/PSE homologado.
2. Configurar `ose_url`, usuario y clave SOL.
3. Subir certificado digital (cuando firma esté implementada).
4. Cambiar modo a **producción** solo tras prueba en ambiente de certificación SUNAT.
5. Verificar almacenamiento de XML y CDR en `storage/app/sunat/`.

Ver [SUNAT.md](SUNAT.md).

## 9. Monitoreo y mantenimiento

- Logs: `storage/logs/laravel.log`
- Panel SUNAT: comprobantes con error/pendiente
- Actualizar PHP y dependencias con ventana de mantenimiento
- Probar migraciones primero en staging

## 10. Desarrollo local en paralelo

Mantener Laragon en Windows para desarrollo; producción en VPS. No compartir `.env` entre entornos.

Guía local: [ARRANQUE_LOCAL.md](ARRANQUE_LOCAL.md).

## 11. Rollback

1. `git checkout` tag/commit anterior
2. `composer install`
3. Revertir migración solo si es seguro (`migrate:rollback` — usar con precaución en producción)
4. Restaurar backup BD si hubo cambio de esquema incompatible

## Referencias

- [METODOLOGIA.md](METODOLOGIA.md)
- [ARQUITECTURA.md](ARQUITECTURA.md)
- [HOSTING_DOMINIO_Y_DESPLIEGUE.md](HOSTING_DOMINIO_Y_DESPLIEGUE.md)
