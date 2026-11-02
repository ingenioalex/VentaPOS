# Arranque local — VentaPOS

## Requisitos

- Laragon con PHP **8.3** (cualquier carpeta `C:\laragon\bin\php\php-8.3*`; `iniciar.ps1` elige la más reciente)
- MySQL de Laragon activo

## Primera vez

```powershell
cd E:\Proyectos\VentaPOS_build

# Crear base de datos (en HeidiSQL o terminal MySQL):
# CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

.\iniciar.ps1
# Primera vez, en otra terminal, con el PHP de Laragon:
# php artisan migrate --seed
```

## Iniciar servidor

```powershell
.\iniciar.ps1
```

Abre: **http://127.0.0.1:8000** (no uses `http://localhost` en el puerto 80: ahí está AppServ con PHP 7.3).

## Si no carga (php.exe colgado o `web.php` vacío)

Doble clic en **`recuperar-local.bat`** (acepta el aviso de administrador). Ese script:

1. Mata `php.exe` colgados que ocupan el puerto 8000
2. Restaura `routes/web.php` si está vacío o corrupto (copia en `scripts/restore/web.php`)
3. Abre Laragon (MySQL)
4. Lanza `iniciar.ps1` en una consola elevada

Si añades rutas nuevas a `web.php`, actualiza también `scripts/restore/web.php` para que el backup siga siendo válido.

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
| `/admin/login` | Panel super admin (solo local; en producción ver `ADMIN_PANEL_PATH`) |
| `/app` | Panel |
| `/app/pos` | Punto de venta |

## Nota PATH

Si `php -v` muestra PHP 7.3 (AppServ), usa siempre `iniciar.ps1` o la ruta completa de Laragon.

## WhatsApp configurado

`.env` → `VENTAPOS_WHATSAPP=51973034118`
