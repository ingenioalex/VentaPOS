# ✅ Lista de Verificación Pre-Despliegue

## Antes de ejecutar el script en el VPS, verifica:

### 🖥️ VPS
- [ ] Tienes acceso SSH: `ssh root@TU_IP`
- [ ] Sistema operativo: Ubuntu 22.04 o Debian 11+
- [ ] Recursos mínimos:
  - [ ] 1 vCPU
  - [ ] 2 GB RAM
  - [ ] 20 GB almacenamiento
  - [ ] Conexión a internet

### 🌐 Dominio
- [ ] `ventasagil.com` apunta a la IP de tu VPS
- [ ] Registro DNS tipo A configurado
- [ ] Propagación DNS completa (espera 5-15 min después de configurar)
- [ ] Verificar con: `ping ventasagil.com` (debe mostrar tu IP)

### 📧 Email para SSL
- [ ] Tienes un email válido para registrar el certificado SSL

### 🔐 Contraseña MySQL
- [ ] Has pensado una contraseña segura para la base de datos
- [ ] Anótala en un lugar seguro (la necesitarás si algo sale mal)

---

## Durante el Despliegue

El script te preguntará:

```
Dominio (sin www): ventasagil.com
Email para SSL: tu@email.com
Contraseña MySQL para ventapos_user: ********
```

**IMPORTANTE:**
- No uses `www.ventasagil.com`, solo `ventasagil.com`
- La contraseña no se mostrará mientras escribes (es normal)
- El proceso toma 5-10 minutos, no lo interrumpas

---

## Qué Hace el Script

1. ✅ Actualiza el sistema Ubuntu
2. ✅ Instala Nginx, MySQL, PHP 8.3, Node.js, Composer
3. ✅ Crea base de datos `ventapos` y usuario
4. ✅ Clona repositorio en `/var/www/ventasagil`
5. ✅ Cambia a branch `cursor/seccionk-module-0c7c`
6. ✅ Configura archivo `.env` de Laravel
7. ✅ Instala dependencias PHP (composer) y JS (npm)
8. ✅ Construye assets frontend
9. ✅ Genera key de Laravel
10. ✅ Configura permisos de archivos
11. ✅ Ejecuta migraciones de base de datos
12. ✅ Carga datos de prueba (seeders)
13. ✅ Optimiza caché de Laravel
14. ✅ Configura Nginx con virtual host
15. ✅ Instala certificado SSL (Let's Encrypt)
16. ✅ Configura firewall (UFW)
17. ✅ Reinicia servicios

---

## Después del Despliegue

### ✅ Verificar que Funciona

1. **VentaPOS Principal**
   ```
   Abre: https://ventasagil.com
   Debe cargar la página principal de VentaPOS
   ```

2. **SeccionK Público**
   ```
   Abre: https://ventasagil.com/seccionk
   Debe cargar la página de búsqueda
   ```

3. **SeccionK Admin**
   ```
   Abre: https://ventasagil.com/seccionk/admin/login
   Login con:
     Usuario: admin
     Contraseña: seccionk2026
   ```

4. **SSL (HTTPS)**
   ```
   Verifica que el navegador muestre el candado 🔒
   La URL debe empezar con https://
   ```

### ✅ Prueba Funcional

1. **Búsqueda Pública**
   - Ve a: `https://ventasagil.com/seccionk`
   - DNI: `12345678`
   - Apellido: `García`
   - Click "Buscar"
   - Debe aparecer el perfil de Juan García

2. **Panel Admin**
   - Login en: `https://ventasagil.com/seccionk/admin/login`
   - Ve al dashboard
   - Verifica estadísticas
   - Entra a "Promociones" y ve la lista
   - Entra a "Eventos" y ve la lista

---

## Si Algo Sale Mal

### Error: "Connection refused" al conectar SSH
```bash
# Verifica que el puerto SSH esté abierto
# Contacta a tu proveedor de VPS
```

### Error: DNS no resuelve
```bash
# Verificar DNS
nslookup ventasagil.com

# Si no muestra tu IP, espera más tiempo o revisa configuración DNS
```

### Error: "Permission denied" en MySQL
```bash
# Reconectar SSH y verificar
sudo mysql

# Dentro de MySQL:
SHOW DATABASES;
SELECT user FROM mysql.user WHERE user='ventapos_user';
```

### Error: 500 Internal Server Error
```bash
# Ver logs
tail -50 /var/www/ventasagil/storage/logs/laravel.log

# Verificar permisos
sudo chown -R www-data:www-data /var/www/ventasagil
sudo chmod -R 775 /var/www/ventasagil/storage
```

### Error: 404 en SeccionK
```bash
# Verificar rutas
cd /var/www/ventasagil
php artisan route:list | grep seccionk

# Limpiar caché
php artisan route:clear
php artisan config:clear
sudo systemctl restart nginx
```

---

## Comandos Útiles Post-Despliegue

### Ver Estado de Servicios
```bash
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mysql
```

### Ver Logs en Tiempo Real
```bash
# Laravel
tail -f /var/www/ventasagil/storage/logs/laravel.log

# Nginx error
tail -f /var/log/nginx/ventasagil-error.log

# Nginx access
tail -f /var/log/nginx/ventasagil-access.log
```

### Reiniciar Todo
```bash
sudo systemctl restart nginx php8.3-fpm mysql
```

### Actualizar Código (después de cambios)
```bash
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

## 🎯 Listo para Desplegar

Si todo está verificado, ejecuta:

```bash
ssh root@TU_IP_VPS

curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh

chmod +x deploy.sh

./deploy.sh
```

---

## 📞 Soporte

Si necesitas ayuda:
1. Lee los logs (comandos arriba)
2. Busca el error exacto
3. Verifica esta lista de verificación nuevamente

---

**Última actualización:** Octubre 2026  
**Versión del script:** 1.0  
**Branch:** cursor/seccionk-module-0c7c
