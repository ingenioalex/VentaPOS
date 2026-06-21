# Configurar entorno Windows para VentaPOS

## Situación actual

| Herramienta | Estado en tu PC | Requerido |
|-------------|-----------------|-----------|
| PHP | 7.3.10 (AppServ) | **8.2+** |
| Composer | No instalado | **Sí** |
| MySQL | AppServ (probable) | 8.0+ |
| Python | 3.14.5 | No necesario para VentaPOS |

---

## Opción A — Laragon (recomendada)

1. Descarga **Laragon Full**: https://laragon.org/download
2. Instala con opciones por defecto
3. Abre Laragon → clic derecho → **PHP** → selecciona **8.2** o **8.3**
4. Clic **Start All** (Apache + MySQL)
5. Abre terminal de Laragon (botón Terminal):

```bash
php -v
composer -V
mysql --version
```

6. Crear base de datos:
```sql
CREATE DATABASE ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. En Laragon, la carpeta de proyectos suele ser `C:\laragon\www\`
   - Opción: copiar/enlazar `d:\Proyectos\VentaPOS` ahí
   - O trabajar directo en `d:\Proyectos\VentaPOS`

---

## Opción B — Mantener AppServ + PHP 8 aparte

1. Descarga PHP 8.2 VC15 x64 Thread Safe: https://windows.php.net/download/
2. Extrae en `C:\php82\`
3. Agrega `C:\php82` al PATH de Windows
4. Descarga Composer: https://getcomposer.org/Composer-Setup.exe
5. En instalador, selecciona `C:\php82\php.exe`

---

## Crear proyecto Laravel (cuando PHP 8.2+ esté listo)

```bash
cd d:\Proyectos\VentaPOS

# Si la carpeta ya tiene docs/, crear Laravel en temp y fusionar:
composer create-project laravel/laravel temp-laravel
# Mover archivos Laravel a raíz (conservar docs/ y database/)

# O si carpeta vacía de vendor:
composer create-project laravel/laravel . --prefer-dist

copy .env.example .env
php artisan key:generate
```

Editar `.env`:
```env
APP_NAME=VentaPOS
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventapos
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate
php artisan serve
```

Abre: http://localhost:8000

---

## Problemas frecuentes

| Error | Solución |
|-------|----------|
| `composer` no reconocido | Reinstalar Composer o usar Laragon terminal |
| PHP 7.3 al hacer `php -v` | PATH apunta a AppServ; priorizar PHP 8 en PATH |
| `ext-pdo_mysql` missing | En `php.ini` descomentar `extension=pdo_mysql` |
| Puerto 8000 ocupado | `php artisan serve --port=8080` |
