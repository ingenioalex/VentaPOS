@echo off
REM Recupera el arranque local de VentaPOS (php.exe colgados + web.php vacio + Laragon + iniciar.ps1).
REM Pida privilegios de administrador para poder matar php.exe bloqueados.
cd /d "%~dp0"

net session >nul 2>&1
if %errorlevel% NEQ 0 (
    echo Solicitando permisos de administrador...
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

echo.
echo VentaPOS - recuperacion local (administrador)
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\recuperar-local.ps1"
echo.
pause
