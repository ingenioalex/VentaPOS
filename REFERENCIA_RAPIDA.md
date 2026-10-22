# VentaPOS - Referencia Rápida de Problemas Comunes

## 🚀 Inicio Rápido

### Nunca configurado antes
```powershell
# Windows
.\iniciar-con-verificacion.ps1

# Linux/Mac
./iniciar-con-verificacion.sh
```

### Ya configurado
```powershell
.\iniciar.ps1
```

## ❌ Problemas Comunes y Soluciones

### 1. "No tiene permiso para acceder a este módulo"

**Causa:** Usuario sin módulo POS habilitado  
**Solución rápida:**
- Usar: `admin / admin123` (tiene todos los permisos)
- O ir a `/app/permisos` y habilitar "POS Venta"

### 2. Página en blanco o "Sin terminal activa"

**Causa:** Cajero sin terminal asignada  
**Solución rápida:**
- Usar: `admin / admin123` (no necesita terminal)
- O como admin: `/app/terminales` → crear terminal → `/app/usuario-terminales` → asignar

### 3. "Database does not exist" o errores SQL

**Causa:** Base de datos no configurada  
**Solución:**
```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

# Opción 1: Con datos de ejemplo (recomendado)
& $php artisan migrate:fresh --seed

# Opción 2: Vacía
& $php artisan migrate
```

### 4. POS sin productos para vender

**Causa:** Base de datos vacía  
**Solución:**
- Ir a `/app/productos/importar`
- Descargar plantilla CSV
- Importar productos

### 5. "No such file or directory" al iniciar

**Causa:** No existe `.env`  
**Solución:**
```powershell
Copy-Item .env.example .env
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php artisan key:generate
```

### 6. "Class not found" o errores raros

**Causa:** Caché corrupta  
**Solución:**
```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php artisan config:clear
& $php artisan cache:clear
& $php artisan route:clear
& $php artisan view:clear
```

## 👥 Usuarios de Prueba

| Usuario | Contraseña | Rol | Acceso POS |
|---------|------------|-----|------------|
| `admin` | `admin123` | Administrador | ✅ Completo |
| `cajero` | `cajero123` | Cajero | ✅ Con terminal |

## 🔗 Rutas Importantes

| URL | Descripción |
|-----|-------------|
| `http://127.0.0.1:8000/login` | Login |
| `http://127.0.0.1:8000/app/pos` | Punto de Venta |
| `http://127.0.0.1:8000/app/productos` | Productos |
| `http://127.0.0.1:8000/app/ventas` | Historial |
| `http://127.0.0.1:8000/app/permisos` | Permisos (admin) |
| `http://127.0.0.1:8000/app/terminales` | Terminales (admin) |

## 🛠️ Comandos Útiles

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

# Ver versión de PHP
& $php -v

# Recrear base de datos con ejemplos
& $php artisan migrate:fresh --seed

# Solo migraciones (mantiene datos)
& $php artisan migrate

# Limpiar todo el caché
& $php artisan optimize:clear

# Ver rutas disponibles
& $php artisan route:list

# Ver logs
Get-Content storage/logs/laravel.log -Tail 50
```

## 📝 Configuración Base de Datos

### SQLite (más simple)
```env
DB_CONNECTION=sqlite
```
Crear: `New-Item -ItemType File -Path database/database.sqlite -Force`

### MySQL
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventapos
DB_USERNAME=root
DB_PASSWORD=
```
Crear BD: `CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`

## 🔍 Verificar Estado

```powershell
# Verificar archivo .env existe
Test-Path .env

# Verificar APP_KEY configurada
Select-String "APP_KEY=base64:" .env

# Verificar base de datos SQLite
Test-Path database/database.sqlite

# Verificar PHP correcto
& $php -v
```

## 📚 Documentación Completa

Ver: **[docs/SOLUCION_POS_LOCAL.md](docs/SOLUCION_POS_LOCAL.md)**

## 🆘 ¿Nada funciona?

### Reset completo:

```powershell
# 1. Limpiar
Remove-Item .env -ErrorAction SilentlyContinue
Remove-Item database/database.sqlite -ErrorAction SilentlyContinue

# 2. Reconfigurar
Copy-Item .env.example .env
New-Item -ItemType File -Path database/database.sqlite -Force

$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

# 3. Configurar
& $php artisan key:generate
& $php artisan migrate:fresh --seed

# 4. Iniciar
.\iniciar.ps1
```

---

**Última actualización:** 2026-10-10
