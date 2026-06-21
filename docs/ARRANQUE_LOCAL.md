# Arranque local — VentaPOS

## Requisitos

- Laragon con PHP **8.3** (ruta: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\`)
- MySQL de Laragon activo

## Primera vez

```powershell
cd D:\Proyectos\VentaPOS_build

# Crear base de datos (en HeidiSQL o terminal MySQL):
# CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php artisan migrate:fresh --seed
```

## Iniciar servidor

```powershell
.\iniciar.ps1
```

Abre: **http://127.0.0.1:8000**

## Usuarios demo

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | admin123 | dueño |
| cajero | cajero123 | cajero |

## URLs

| Ruta | Descripción |
|------|-------------|
| `/` | Landing marketing |
| `/precios` | Planes |
| `/contacto` | Beta + WhatsApp |
| `/login` | Acceso |
| `/app` | Panel |
| `/app/pos` | Punto de venta |

## Nota PATH

Si `php -v` muestra PHP 7.3 (AppServ), usa siempre `iniciar.ps1` o la ruta completa de Laragon.

## WhatsApp configurado

`.env` → `VENTAPOS_WHATSAPP=51973034118`
