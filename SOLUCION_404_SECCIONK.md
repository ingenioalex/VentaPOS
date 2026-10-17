# 🔧 Solución: Error 404 en seccionk.ventasagil.com

## ❌ Problema

```
404 Not Found
URL: https://seccionk.ventasagil.com/admin/login
```

El servidor responde pero Laravel no encuentra la ruta.

## 🎯 Causas Posibles

1. **Hosts no configurado** - El dominio no apunta a localhost
2. **Rutas no registradas** - Las rutas de SeccionK no están cargando
3. **Dominio no detectado** - Laravel no reconoce el subdominio
4. **Cache de rutas** - Cache antiguo sin las nuevas rutas

---

## ✅ Solución Completa

### Paso 1: Verificar archivo HOSTS

**Windows** - Editar `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1   ventasagil.com
127.0.0.1   www.ventasagil.com
127.0.0.1   seccionk.ventasagil.com
```

**Linux/Mac** - Editar `/etc/hosts`:

```bash
sudo nano /etc/hosts
```

Agregar:
```
127.0.0.1   ventasagil.com
127.0.0.1   www.ventasagil.com
127.0.0.1   seccionk.ventasagil.com
```

### Paso 2: Limpiar Cache de Laravel

```bash
cd D:\Proyectos\VentaPOS_build

# Limpiar todo el cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Verificar que las rutas están registradas
php artisan route:list | findstr seccionk
```

**Debería mostrar rutas como:**
```
GET|HEAD  seccionk.ventasagil.com/  ........................ seccionk.index
GET|HEAD  seccionk.ventasagil.com/admin/login ............. seccionk.admin.login
POST      seccionk.ventasagil.com/admin/login ............. seccionk.admin.login.post
...
```

Si NO muestra rutas, continúa al Paso 3.

### Paso 3: Verificar Configuración de Rutas

Verificar que el archivo `bootstrap/app.php` tiene las rutas de SeccionK:

```bash
# Ver contenido del archivo
type bootstrap\app.php | findstr seccionk
```

**Debe contener algo como:**
```php
Route::domain('seccionk.ventasagil.com')
    ->middleware('web')
    ->group(base_path('routes/seccionk.php'));
```

Si NO está, añadirlo manualmente.

### Paso 4: Verificar que existe routes/seccionk.php

```bash
# Verificar que el archivo existe
dir routes\seccionk.php
```

Si NO existe, el módulo SeccionK no está en esta rama.

### Paso 5: Cambiar a la Rama Correcta

```bash
# Ver rama actual
git branch

# Cambiar a la rama con SeccionK
git checkout cursor/seccionk-module-0c7c

# Verificar que ahora existe
dir routes\seccionk.php
```

### Paso 6: Instalar Dependencias (si cambió de rama)

```bash
# Instalar dependencias de PHP
composer install

# Instalar dependencias de Node
npm install
npm run build
```

### Paso 7: Ejecutar Migraciones de SeccionK

```bash
# Verificar conexión a base de datos
php artisan db:show

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeder de SeccionK
php artisan db:seed --class=SeccionKSeeder
```

### Paso 8: Limpiar Cache Nuevamente

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Cachear configuración
php artisan config:cache
php artisan route:cache
```

### Paso 9: Reiniciar Servidor

```bash
# Detener servidor si está corriendo (Ctrl+C)

# Iniciar de nuevo
php artisan serve --host=0.0.0.0 --port=8000
```

### Paso 10: Limpiar Cache del Navegador

1. Presionar `Ctrl + Shift + Delete`
2. Seleccionar "Todo el tiempo"
3. Marcar "Imágenes y archivos en caché"
4. Click en "Borrar datos"

O simplemente abrir ventana de incógnito: `Ctrl + Shift + N`

---

## 🧪 Verificación

### 1. Verificar DNS Local

```bash
ping seccionk.ventasagil.com
```

**Debe responder:**
```
Haciendo ping a seccionk.ventasagil.com [127.0.0.1]
```

### 2. Verificar Rutas Registradas

```bash
php artisan route:list --domain=seccionk
```

**Debe mostrar las rutas del módulo SeccionK**

### 3. Probar URLs

Abrir en navegador (modo incógnito):

✅ http://seccionk.ventasagil.com:8000  
✅ http://seccionk.ventasagil.com:8000/admin/login  
✅ http://seccionk.ventasagil.com:8000/registro  

---

## 🔍 Diagnóstico Rápido

### Comando de Diagnóstico

Ejecutar este script de PowerShell:

```powershell
Write-Host "=== DIAGNÓSTICO SECCIONK ===" -ForegroundColor Cyan

# 1. Verificar hosts
Write-Host "`n1. Verificar HOSTS:" -ForegroundColor Yellow
$hosts = Get-Content C:\Windows\System32\drivers\etc\hosts
if ($hosts -match "seccionk.ventasagil.com") {
    Write-Host "   ✓ seccionk.ventasagil.com está en hosts" -ForegroundColor Green
} else {
    Write-Host "   ✗ seccionk.ventasagil.com NO está en hosts" -ForegroundColor Red
}

# 2. Verificar rama Git
Write-Host "`n2. Verificar rama Git:" -ForegroundColor Yellow
$branch = git branch --show-current
Write-Host "   Rama actual: $branch" -ForegroundColor Cyan

# 3. Verificar archivo de rutas
Write-Host "`n3. Verificar rutas/seccionk.php:" -ForegroundColor Yellow
if (Test-Path "routes\seccionk.php") {
    Write-Host "   ✓ Archivo existe" -ForegroundColor Green
} else {
    Write-Host "   ✗ Archivo NO existe" -ForegroundColor Red
}

# 4. Verificar bootstrap/app.php
Write-Host "`n4. Verificar bootstrap/app.php:" -ForegroundColor Yellow
$bootstrap = Get-Content bootstrap\app.php -Raw
if ($bootstrap -match "seccionk.ventasagil.com") {
    Write-Host "   ✓ Configuración de dominio encontrada" -ForegroundColor Green
} else {
    Write-Host "   ✗ Configuración de dominio NO encontrada" -ForegroundColor Red
}

# 5. Verificar base de datos
Write-Host "`n5. Verificar tablas SeccionK:" -ForegroundColor Yellow
try {
    $tables = php artisan db:table:list | Select-String "seccionk"
    if ($tables) {
        Write-Host "   ✓ Tablas SeccionK encontradas" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Tablas SeccionK NO encontradas" -ForegroundColor Red
    }
} catch {
    Write-Host "   ✗ Error al verificar base de datos" -ForegroundColor Red
}

Write-Host "`n=== FIN DIAGNÓSTICO ===" -ForegroundColor Cyan
```

Guardar como `diagnostico-seccionk.ps1` y ejecutar:
```powershell
.\diagnostico-seccionk.ps1
```

---

## 🚀 Solución Rápida (Todo en uno)

Si quieres hacer todo de una vez:

```powershell
# Script de solución rápida
Write-Host "Solucionando SeccionK..." -ForegroundColor Cyan

# 1. Cambiar a rama correcta
git checkout cursor/seccionk-module-0c7c

# 2. Instalar dependencias
composer install
npm install
npm run build

# 3. Migrar base de datos
php artisan migrate --force
php artisan db:seed --class=SeccionKSeeder --force

# 4. Limpiar cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# 5. Verificar rutas
Write-Host "`nRutas registradas:" -ForegroundColor Yellow
php artisan route:list --domain=seccionk

Write-Host "`n✅ Listo! Ahora reinicia el servidor y prueba:" -ForegroundColor Green
Write-Host "   http://seccionk.ventasagil.com:8000" -ForegroundColor Cyan
```

---

## 🆘 Si Nada Funciona

### Opción 1: Servidor Artisan con Host

```bash
# En lugar de:
php artisan serve

# Usar:
php artisan serve --host=0.0.0.0 --port=8000
```

Luego acceder a:
- http://localhost:8000 (VentaPOS)
- http://127.0.0.1:8000 (VentaPOS)

Para SeccionK, necesitas configurar un servidor web real (Apache/Nginx).

### Opción 2: Laragon (Recomendado para Windows)

1. Descargar Laragon: https://laragon.org/download/
2. Instalar Laragon
3. Copiar proyecto a `C:\laragon\www\ventasagil`
4. En Laragon: Click derecho → Apache → Add VirtualHost
   - Domain: `seccionk.ventasagil.com`
   - Path: `C:\laragon\www\ventasagil\public`
5. Reiniciar Laragon
6. Acceder a: http://seccionk.ventasagil.com

### Opción 3: Usar PHP Built-in con Router

Crear archivo `router.php` en la raíz:

```php
<?php

// Router personalizado para desarrollo
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar subdominio
if (strpos($host, 'seccionk') !== false) {
    $_SERVER['HTTP_HOST'] = 'seccionk.ventasagil.com';
}

// Dejar que Laravel maneje el resto
require_once __DIR__.'/public/index.php';
```

Luego ejecutar:
```bash
php -S 0.0.0.0:8000 router.php
```

---

## 📋 Checklist de Solución

```
□ Archivo hosts configurado con seccionk.ventasagil.com
□ Rama Git: cursor/seccionk-module-0c7c
□ Archivo routes/seccionk.php existe
□ bootstrap/app.php tiene configuración de dominio
□ Dependencias instaladas (composer, npm)
□ Migraciones ejecutadas
□ Seeder de SeccionK ejecutado
□ Cache limpiado
□ Servidor reiniciado
□ Cache del navegador limpiado
□ Probado en modo incógnito
```

---

## 🎯 Resultado Esperado

**Antes:**
```
❌ 404 Not Found
```

**Después:**
```
✅ Página de login de SeccionK cargando
✅ Formulario visible con usuario/contraseña
✅ Diseño purple/indigo
```

---

**Última actualización:** 2026-10-11  
**Prioridad:** Alta  
**Tiempo estimado:** 10-15 minutos
