# Solución: No se puede utilizar el sistema POS de forma local

## Problema

El sistema de venta POS no está accesible localmente debido a uno o varios de estos motivos:

1. **Base de datos no configurada**: Sin `.env` o base de datos sin crear
2. **Migraciones no ejecutadas**: Tablas no existen
3. **Usuarios sin permisos**: El módulo POS no está habilitado para el usuario
4. **Empresa sin configurar**: Onboarding incompleto
5. **Sesión de cajero**: Problemas con terminales o sesiones

## Diagnóstico Rápido

### 1. Verificar archivo `.env`

```powershell
# Debe existir el archivo .env
Get-Item .env

# Si no existe, copiarlo desde .env.example
Copy-Item .env.example .env
```

### 2. Configurar base de datos en `.env`

Para **MySQL** (Laragon):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventapos
DB_USERNAME=root
DB_PASSWORD=
```

Para **SQLite** (más simple para desarrollo):
```env
DB_CONNECTION=sqlite
# DB_HOST, DB_PORT, etc. no son necesarios
```

### 3. Crear base de datos

**Opción A - MySQL en Laragon:**
```sql
-- En HeidiSQL o terminal MySQL
CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Opción B - SQLite:**
```powershell
# Crear archivo de base de datos vacío
New-Item -ItemType File -Path database/database.sqlite -Force
```

### 4. Generar clave de aplicación

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php artisan key:generate
```

### 5. Ejecutar migraciones y seeders

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php artisan migrate:fresh --seed
```

Este comando:
- Crea todas las tablas necesarias
- Crea empresas de ejemplo
- Crea usuarios: `admin/admin123` y `cajero/cajero123`
- Asigna permisos por defecto

## Verificación de Permisos

### Usuario debe tener módulo POS habilitado

Los usuarios se crean con permisos según su rol:

| Rol | Módulos por defecto |
|-----|---------------------|
| **dueno** (admin) | Todos los módulos incluido POS |
| **cajero** | Solo POS y POS Móvil |
| **supervisor** | POS, ventas, cierres, reportes |

### Verificar permisos de un usuario

1. Iniciar sesión con cuenta de administrador
2. Ir a `/app/permisos`
3. Verificar que el rol del usuario tenga marcado "POS Venta"

### Problema común: Empresa sin módulos contratados

Si la empresa está en plan "beta", debería tener todos los módulos:

```php
// En config/permisos.php línea 79:
'modulos_por_plan' => [
    'beta' => ['pos', 'pos_movil', 'ventas', 'cierres', 'productos', 
               'categorias', 'clientes', 'inventario', 'proveedores', 
               'compras', 'reportes', 'terminales', 'usuario_terminales', 'sunat'],
    // ...
],
```

## Verificación de Onboarding

El sistema requiere completar el onboarding al primer uso:

1. Login con usuario `admin` / `admin123`
2. Si redirige a `/app/onboarding`, completar:
   - Datos de empresa
   - Configuración de terminal
   - Productos iniciales (opcional)
   - Usuario cajero (opcional)

## Acceso al POS

Una vez configurado correctamente:

### Usuario Administrador (dueno)
- URL: `http://127.0.0.1:8000/app/pos`
- Login: `admin` / `admin123`
- Acceso directo sin restricciones

### Usuario Cajero
- URL: `http://127.0.0.1:8000/app/pos`
- Login: `cajero` / `cajero123`
- Requiere:
  - Terminal asignada
  - Sesión iniciada (automático al entrar)

## Solución Rápida Completa

Si nada funciona, empezar desde cero:

```powershell
# 1. Copiar configuración
Copy-Item .env.example .env

# 2. Editar .env - opción simple SQLite
# Cambiar DB_CONNECTION=sqlite

# 3. Crear archivo SQLite
New-Item -ItemType File -Path database/database.sqlite -Force

# 4. Configurar PHP
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

# 5. Generar clave
& $php artisan key:generate

# 6. Crear base de datos con datos de ejemplo
& $php artisan migrate:fresh --seed

# 7. Iniciar servidor
.\iniciar.ps1

# 8. Abrir navegador
# http://127.0.0.1:8000/login
# Usuario: admin
# Contraseña: admin123
```

## Problemas Específicos

### Error: "No tiene permiso para acceder a este módulo"

**Causa**: El usuario no tiene el módulo POS habilitado.

**Solución**:
1. Login como administrador
2. Ir a `/app/permisos`
3. Habilitar "POS Venta" para el rol del usuario
4. O usar directamente el usuario `admin` que tiene todos los permisos

### Error: "Sin terminal activa" o pantalla en blanco

**Causa**: Usuario cajero sin terminal asignada.

**Solución**:
1. Login como administrador
2. Ir a `/app/terminales` - crear una terminal si no existe
3. Ir a `/app/usuario-terminales` - asignar terminal al cajero
4. O usar directamente el usuario `admin` que no requiere terminal

### Error: Base de datos no existe

**Causa**: No se ejecutó `migrate:fresh --seed`.

**Solución**:
```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php artisan migrate:fresh --seed
```

### POS carga pero no aparecen productos

**Causa**: No hay productos en la base de datos.

**Solución**:
1. Ir a `/app/productos`
2. Importar CSV desde `/app/productos/importar`
3. O crear productos manualmente

## Rutas del Sistema

| Ruta | Descripción | Requiere |
|------|-------------|----------|
| `/login` | Página de login | - |
| `/app` | Dashboard principal | Login |
| `/app/onboarding` | Configuración inicial | Login como dueño |
| `/app/pos` | Punto de venta escritorio | Módulo POS |
| `/app/pos/movil` | Punto de venta móvil | Módulo POS Móvil |
| `/app/productos` | Gestión productos | Módulo Productos |
| `/app/ventas` | Historial ventas | Módulo Ventas |
| `/app/cierres` | Cierres de caja | Módulo Cierres |
| `/app/permisos` | Configurar permisos | Admin |
| `/app/terminales` | Configurar terminales | Admin |

## Logs y Depuración

Ver errores detallados:

```powershell
# Ver logs de Laravel
Get-Content storage/logs/laravel.log -Tail 50

# Limpiar caché
& $php artisan cache:clear
& $php artisan config:clear
& $php artisan route:clear
& $php artisan view:clear
```

## Contacto y Soporte

Si ninguna solución funciona:

1. Verificar que PHP sea 8.3 (`php -v`)
2. Verificar extensiones de PHP necesarias (ver `composer.json`)
3. Revisar logs en `storage/logs/laravel.log`
4. Contactar al equipo de desarrollo con:
   - Mensaje de error exacto
   - Captura de pantalla
   - Contenido del log
