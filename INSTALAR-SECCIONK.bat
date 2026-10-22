@echo off
REM Instalador Simple de SeccionK para VentaPOS
REM Copiar este archivo a D:\Proyectos\VentaPOS_build y ejecutar

echo.
echo ============================================================
echo   Instalacion SeccionK en VentaPOS
echo ============================================================
echo.

REM Verificar que estamos en la raiz del proyecto
if not exist artisan (
    echo [X] Error: No se encontro el archivo 'artisan'
    echo     Ejecuta este script desde la raiz de VentaPOS
    echo     Ejemplo: D:\Proyectos\VentaPOS_build
    pause
    exit /b 1
)

echo [OK] Proyecto VentaPOS detectado
echo.

echo Este script descargara e instalara el modulo SeccionK automaticamente.
echo.
echo Presiona cualquier tecla para continuar o Ctrl+C para cancelar...
pause >nul

echo.
echo Ejecutando instalador de PowerShell...
echo.

powershell -ExecutionPolicy Bypass -File instalar-seccionk.ps1

if %errorlevel% neq 0 (
    echo.
    echo [X] Error en la instalacion
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   [OK] Instalacion Completada!
echo ============================================================
echo.
pause
