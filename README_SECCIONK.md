# 🎉 SeccionK - Listo para Desplegar en ventasagil.com/seccionk

## 📌 Resumen de Cambios

He configurado el módulo **SeccionK** para que funcione en tu VPS con el dominio `ventasagil.com` en la ruta `/seccionk`, **sin necesidad de configurar un subdominio**.

---

## 🚀 Para Desplegar AHORA

### 1️⃣ Conecta a tu VPS
```bash
ssh root@TU_IP_DEL_VPS
```

### 2️⃣ Ejecuta este comando
```bash
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh && chmod +x deploy.sh && ./deploy.sh
```

### 3️⃣ Responde las preguntas
- **Dominio:** `ventasagil.com`
- **Email:** tu email (para SSL)
- **Contraseña MySQL:** elige una segura

### ⏱️ Espera 5-10 minutos

### 4️⃣ ¡Listo!
Abre tu navegador en: **https://ventasagil.com/seccionk**

---

## 🌐 URLs después del despliegue

| Sistema | URL |
|---------|-----|
| **SeccionK Público** | `https://ventasagil.com/seccionk` |
| **SeccionK Admin** | `https://ventasagil.com/seccionk/admin/login` |
| VentaPOS | `https://ventasagil.com` (sigue igual) |

---

## 🔐 Credenciales

### Admin SeccionK
```
URL:        https://ventasagil.com/seccionk/admin/login
Usuario:    admin
Contraseña: seccionk2026
```

### Búsqueda Pública (datos de prueba)
```
URL:      https://ventasagil.com/seccionk
DNI:      12345678
Apellido: García
```

---

## ✨ Características

- ✅ Búsqueda por DNI + apellido
- ✅ Perfil con timeline de cuotas (Oct 2026 - Dic 2027)
- ✅ Gestión de eventos deportivos
- ✅ Panel administrativo completo
- ✅ Sistema de pagos con comprobantes
- ✅ Base de datos separada de VentaPOS
- ✅ SSL (HTTPS) automático
- ✅ Diseño responsive y profesional

---

## 📚 Documentación Completa

Si necesitas más detalles, lee estos archivos:

1. **`DESPLEGAR_AQUI.md`** - Guía rápida de despliegue
2. **`INSTRUCCIONES_DESPLIEGUE.md`** - Instrucciones detalladas
3. **`VERIFICACION_PRE_DEPLOY.md`** - Lista de verificación
4. **`ESTRUCTURA_PROYECTO.txt`** - Diagrama visual del proyecto
5. **`docs/DEPLOY_VPS_RUTA.md`** - Documentación técnica
6. **`docs/SECCIONK_README.md`** - Documentación del módulo

---

## 🔧 Si Algo Sale Mal

### Ver logs
```bash
# Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Nginx
tail -f /var/log/nginx/ventasagil-error.log
```

### Reiniciar servicios
```bash
systemctl restart nginx php8.3-fpm
```

---

## ❓ Preguntas Frecuentes

### ¿VentaPOS seguirá funcionando?
**Sí**, completamente. SeccionK usa su propia base de datos y rutas. Cero conflictos.

### ¿Necesito configurar DNS?
**No**, si ya tienes `ventasagil.com` apuntando a tu VPS, no necesitas hacer nada más.

### ¿El SSL se configura automáticamente?
**Sí**, el script instala y configura Let's Encrypt automáticamente.

### ¿Puedo personalizar los datos?
**Sí**, después del despliegue entra al panel admin en `/seccionk/admin/login` y modifica todo.

### ¿Cuánto cuesta?
**Gratis**. Solo necesitas tu VPS. El SSL es gratis con Let's Encrypt.

---

## 🎯 Siguiente Paso

**Ejecuta el comando de despliegue en tu VPS ahora:**

```bash
ssh root@TU_IP_VPS
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh
chmod +x deploy.sh
./deploy.sh
```

---

## 📦 Pull Request

Ver todos los cambios en: **[PR #4](https://github.com/ingenioalex/VentaPOS/pull/4)**

---

## ✅ Estado

- ✅ Código completo
- ✅ Rutas configuradas para `/seccionk`
- ✅ Base de datos independiente
- ✅ Script de despliegue listo
- ✅ Documentación completa
- ✅ **LISTO PARA PRODUCCIÓN**

---

**Branch:** `cursor/seccionk-module-0c7c`  
**Última actualización:** Octubre 2026  

---

## 🎉 ¡Disfruta tu nuevo módulo SeccionK!
