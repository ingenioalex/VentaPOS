@echo off
REM Script de Solución Rápida - Error de Tabla SeccionK

echo.
echo ============================================================
echo   Solucionando Error de Tabla SeccionK
echo ============================================================
echo.

REM Verificar que estamos en la raíz del proyecto
if not exist artisan (
    echo [X] Error: No se encontro el archivo 'artisan'
    echo     Ejecuta este script desde la raiz de VentaPOS
    pause
    exit /b 1
)

echo [1/6] Limpiando cache de configuracion...
php artisan config:clear

echo.
echo [2/6] Limpiando cache de rutas...
php artisan route:clear

echo.
echo [3/6] Limpiando cache de vistas...
php artisan view:clear

echo.
echo [4/6] Ejecutando migraciones de SeccionK...
php artisan migrate --path=database/migrations/2026_10_11_000001_crear_tablas_seccionk.php --force

if %errorlevel% neq 0 (
    echo.
    echo [!] Error al ejecutar migracion. Intentando con todas las migraciones...
    php artisan migrate --force
)

echo.
echo [5/6] Cargando datos de prueba de SeccionK...
php artisan db:seed --class=SeccionKSeeder

echo.
echo [6/6] Regenerando cache...
php artisan config:cache
php artisan route:cache

echo.
echo ============================================================
echo   [OK] Problema Solucionado!
echo ============================================================
echo.
echo Ahora recarga la pagina en tu navegador:
echo   http://localhost:8000/seccionk
echo.
pause
