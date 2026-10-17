# ✅ Cambios Completados - SeccionK en ventasagil.com/seccionk

## 🎯 ¿Qué se Hizo?

Se cambió la configuración de SeccionK de **subdominio** (`seccionk.ventasagil.com`) a **ruta** (`ventasagil.com/seccionk`).

### Ventajas de este cambio:

✅ **No necesitas configurar DNS** para un subdominio  
✅ **Un solo dominio** - todo en `ventasagil.com`  
✅ **Más fácil de desplegar** - una sola configuración de Nginx  
✅ **SSL automático** - un solo certificado para todo  
✅ **VentaPOS sigue funcionando** igual en la raíz del dominio  

---

## 📂 Archivos Modificados

| Archivo | Cambio |
|---------|--------|
| `bootstrap/app.php` | Cambio de `Route::domain()` a `Route::prefix('seccionk')` |
| `routes/seccionk.php` | Ajuste de nombres de rutas |
| `deploy-vps-ruta.sh` | ⭐ Nuevo script de despliegue automatizado |
| `docs/DEPLOY_VPS_RUTA.md` | ⭐ Documentación completa de despliegue |
| `DESPLEGAR_AQUI.md` | ⭐ Guía rápida para ti |

---

## 🚀 Cómo Desplegar en tu VPS

### Opción 1: Automático (Recomendado) ⚡

```bash
# 1. Conectar a tu VPS
ssh root@TU_IP_DEL_VPS

# 2. Descargar script
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh

# 3. Ejecutar
chmod +x deploy.sh
./deploy.sh
```

**Te preguntará:**
- Dominio: `ventasagil.com`
- Email: tu@email.com (para SSL)
- Contraseña MySQL: elige una segura

⏱️ Toma 5-10 minutos. ¡Y listo!

---

### Opción 2: Manual 📝

Si prefieres hacerlo paso a paso, lee:
- **Documentación completa:** [`docs/DEPLOY_VPS_RUTA.md`](docs/DEPLOY_VPS_RUTA.md)

---

## 🌐 URLs después del despliegue

| Sistema | URL |
|---------|-----|
| **VentaPOS Principal** | `https://ventasagil.com` |
| **VentaPOS Login** | `https://ventasagil.com/login` |
| **SeccionK Público** | `https://ventasagil.com/seccionk` |
| **SeccionK Admin** | `https://ventasagil.com/seccionk/admin/login` |

---

## 🔐 Credenciales

### VentaPOS (ya existente)
```
Usuario:    admin
Contraseña: admin123
```

### SeccionK Admin (nuevo)
```
Usuario:    admin
Contraseña: seccionk2026
```

### SeccionK - Búsqueda Pública (datos de prueba)
```
DNI:      12345678
Apellido: García
```

---

## ✨ Características SeccionK

### Para el Público:
- 🔍 Buscar por DNI + Apellido
- 📊 Ver perfil con timeline de cuotas (Oct 2026 - Dic 2027)
- 📅 Ver eventos y participaciones
- 📝 Registro de nuevos miembros

### Para Administradores:
- 👥 Gestionar miembros de la promoción
- 🎉 Crear y gestionar eventos
- 💰 Registrar pagos y participaciones
- 📈 Dashboard con estadísticas
- 🖼️ Subir comprobantes de pago (opcional)

---

## 🎨 Diseño

- ✅ Responsive (móvil, tablet, desktop)
- ✅ Colores corporativos (gradiente púrpura)
- ✅ Timeline interactivo de cuotas
- ✅ Iconos Font Awesome 6.4
- ✅ Tailwind CSS 4.0
- ✅ Profesional y moderno

---

## 🔄 ¿Qué pasa con VentaPOS?

**¡Nada!** VentaPOS sigue funcionando exactamente igual:
- Misma base de datos
- Mismas funcionalidades
- Mismo login
- Todo separado de SeccionK

SeccionK tiene:
- ✅ Su propia base de datos
- ✅ Sus propias tablas
- ✅ Su propio sistema de login
- ✅ Cero conflictos con VentaPOS

---

## 📦 Estructura en el VPS

Después del despliegue:

```
/var/www/ventasagil/
├── public/
│   └── index.php          # Punto de entrada único
├── routes/
│   ├── web.php            # Rutas VentaPOS
│   └── seccionk.php       # Rutas SeccionK
├── app/
│   └── Http/Controllers/
│       └── SeccionK/      # Controladores SeccionK
├── resources/views/
│   └── seccionk/          # Vistas SeccionK
└── database/
    ├── migrations/
    │   └── 2026_10_11_000001_crear_tablas_seccionk.php
    └── seeders/
        └── SeccionKSeeder.php
```

---

## 🐛 Si Algo Sale Mal

### Ver logs de Laravel
```bash
tail -f /var/www/ventasagil/storage/logs/laravel.log
```

### Ver logs de Nginx
```bash
tail -f /var/log/nginx/ventasagil-error.log
```

### Reiniciar servicios
```bash
systemctl restart nginx php8.3-fpm mysql
```

### Limpiar caché
```bash
cd /var/www/ventasagil
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Error 404 en SeccionK
```bash
cd /var/www/ventasagil
php artisan route:list | grep seccionk
php artisan route:clear
systemctl restart nginx
```

---

## 📞 Soporte

Si tienes problemas:
1. Lee los logs (comandos arriba)
2. Copia el error exacto
3. Comparte el contenido de los logs

---

## 🎉 ¡Siguiente Paso!

**Ejecuta el script de despliegue en tu VPS:**

```bash
ssh root@TU_IP_VPS
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh
chmod +x deploy.sh
./deploy.sh
```

En 10 minutos tendrás todo funcionando en:
- **`https://ventasagil.com/seccionk`**

---

## 📄 Pull Request

Ver cambios completos en: [PR #4](https://github.com/ingenioalex/VentaPOS/pull/4)

---

**Última actualización:** Octubre 2026  
**Branch:** `cursor/seccionk-module-0c7c`  
**Estado:** ✅ Listo para producción
