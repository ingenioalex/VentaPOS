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
- [ ] SSL (Let's Encrypt) en dominio
- [ ] Cron para scheduler Laravel (si se usa colas)
- [ ] Backup automático MySQL diario

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
```

## 4. Comandos de despliegue

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

## 5. Post-deploy funcional

| Verificación | Cómo |
|--------------|------|
| Login empresa demo | Código + usuario |
| POS cobrar boleta | Stock descuenta, ticket imprime |
| SUNAT simulación | Activar en `/app/sunat`, cobrar, ver QR nítido |
| Super admin | `/admin/login` |
| HTTPS | Sin mixed content en ticket/logo |

## 6. SUNAT en producción

1. Contratar OSE/PSE homologado.
2. Configurar `ose_url`, usuario y clave SOL.
3. Subir certificado digital (cuando firma esté implementada).
4. Cambiar modo a **producción** solo tras prueba en ambiente de certificación SUNAT.
5. Verificar almacenamiento de XML y CDR en `storage/app/sunat/`.

Ver [SUNAT.md](SUNAT.md).

## 7. Monitoreo y mantenimiento

- Logs: `storage/logs/laravel.log`
- Panel SUNAT: comprobantes con error/pendiente
- Actualizar PHP y dependencias con ventana de mantenimiento
- Probar migraciones primero en staging

## 8. Desarrollo local en paralelo

Mantener Laragon en Windows para desarrollo; producción en VPS. No compartir `.env` entre entornos.

Guía local: [ARRANQUE_LOCAL.md](ARRANQUE_LOCAL.md).

## 9. Rollback

1. `git checkout` tag/commit anterior
2. `composer install`
3. Revertir migración solo si es seguro (`migrate:rollback` — usar con precaución en producción)
4. Restaurar backup BD si hubo cambio de esquema incompatible

## Referencias

- [METODOLOGIA.md](METODOLOGIA.md)
- [ARQUITECTURA.md](ARQUITECTURA.md)
- [HOSTING_DOMINIO_Y_DESPLIEGUE.md](HOSTING_DOMINIO_Y_DESPLIEGUE.md)
