# 🚀 Guía Rápida de Despliegue - SeccionK en ventasagil.com/seccionk

## ✅ Tu Configuración

- **Dominio:** `ventasagil.com` (ya tienes VPS)
- **SeccionK estará en:** `https://ventasagil.com/seccionk`
- **Base de datos:** Separada de VentaPOS (sin afectar tu sistema actual)

---

## 📋 Requisitos Previos

1. **VPS con Ubuntu 22.04** (o Debian 11+)
2. **Acceso root SSH**
3. **Dominio apuntando al VPS** (ventasagil.com → IP del VPS)

---

## 🎯 Despliegue en 2 Pasos

### Paso 1: Conectar al VPS

```bash
ssh root@TU_IP_VPS
```

### Paso 2: Ejecutar Script Automático

```bash
# Descargar script
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh

# Dar permisos
chmod +x deploy.sh

# Ejecutar
./deploy.sh
```

El script te preguntará:

1. **Dominio:** Escribe `ventasagil.com`
2. **Email para SSL:** Tu correo (para certificado HTTPS)
3. **Contraseña MySQL:** Elige una contraseña segura (guárdala)

⏱️ El proceso toma 5-10 minutos.

---

## ✨ Al Finalizar

### URLs de Acceso

| Sistema | URL |
|---------|-----|
| **SeccionK Público** | `https://ventasagil.com/seccionk` |
| **SeccionK Admin** | `https://ventasagil.com/seccionk/admin/login` |
| VentaPOS | `https://ventasagil.com` |

### Credenciales SeccionK Admin

```
Usuario:    admin
Contraseña: seccionk2026
```

### Datos de Prueba (Búsqueda Pública)

```
DNI:      12345678
Apellido: García
```

---

## 🔧 Si Algo Sale Mal

### Ver Logs

```bash
# Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Nginx
tail -f /var/log/nginx/ventasagil-error.log
```

### Reiniciar Servicios

```bash
systemctl restart nginx php8.3-fpm
```

### Limpiar Caché Laravel

```bash
cd /var/www/ventasagil
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📞 Soporte

Si encuentras algún problema:
1. Copia el error exacto
2. Ejecuta: `tail -50 /var/www/ventasagil/storage/logs/laravel.log`
3. Comparte el resultado

---

## 🎉 ¡Listo!

Después del despliegue, podrás:

✅ Acceder a SeccionK en `ventasagil.com/seccionk`  
✅ Gestionar miembros de la promoción  
✅ Crear eventos y registrar participaciones  
✅ Ver timeline de cuotas mensuales  
✅ Todo con SSL (HTTPS) configurado  

**VentaPOS sigue funcionando normal en `ventasagil.com`**
