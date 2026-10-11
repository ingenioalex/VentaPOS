@echo off
REM Acceso directo para iniciar servidor local
REM Crear este archivo en la raiz del proyecto

echo.
echo ============================================================
echo   Servidor Local - VentaPOS + SeccionK
echo ============================================================
echo.

REM Verificar si ya se hizo setup
if not exist vendor (
    echo [!] Primera vez: Ejecutando setup...
    echo.
    call setup-local.bat
    echo.
)

REM Verificar si existe la base de datos
if not exist database\database.sqlite (
    echo [!] Base de datos no existe, ejecutando migraciones...
    echo.
    php artisan migrate --force
    php artisan db:seed --force
    echo.
)

echo Iniciando servidor en http://localhost:8000
echo.
echo ╔════════════════════════════════════════════════════════╗
echo ║  URLs de Acceso:                                       ║
echo ╠════════════════════════════════════════════════════════╣
echo ║  VentaPOS:         http://localhost:8000              ║
echo ║  SeccionK Publico: http://localhost:8000/seccionk     ║
echo ║  SeccionK Admin:   http://localhost:8000/seccionk/admin/login ║
echo ╚════════════════════════════════════════════════════════╝
echo.
echo Credenciales SeccionK Admin:
echo   Usuario:    admin
echo   Contraseña: seccionk2026
echo.
echo Datos de prueba (busqueda publica):
echo   DNI:      12345678
echo   Apellido: Garcia
echo.
echo Presiona Ctrl+C para detener el servidor
echo.
echo ════════════════════════════════════════════════════════
echo.

REM Abrir navegador automáticamente en SeccionK
timeout /t 2 /nobreak >nul
start http://localhost:8000/seccionk

REM Iniciar servidor
php artisan serve
