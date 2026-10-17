@echo off
REM Solución: Descargar archivos faltantes de SeccionK

echo.
echo ============================================================
echo   Descargando Archivos Faltantes de SeccionK
echo ============================================================
echo.

REM Verificar PowerShell
where powershell >nul 2>nul
if %errorlevel% neq 0 (
    echo [X] PowerShell no encontrado
    pause
    exit /b 1
)

echo [1/3] Descargando SeccionKSeeder.php...
powershell -Command "Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/database/seeders/SeccionKSeeder.php' -OutFile 'database\seeders\SeccionKSeeder.php'"

echo.
echo [2/3] Descargando migración...
powershell -Command "Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/database/migrations/2026_10_11_000001_crear_tablas_seccionk.php' -OutFile 'database\migrations\2026_10_11_000001_crear_tablas_seccionk.php'"

echo.
echo [3/3] Limpiando caché...
php artisan config:clear
php artisan cache:clear

echo.
echo ============================================================
echo   [OK] Archivos Descargados
echo ============================================================
echo.
echo Ahora ejecuta:
echo   php artisan migrate
echo   php artisan db:seed --class=SeccionKSeeder
echo.
pause
