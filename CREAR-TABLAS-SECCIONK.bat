@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"

echo.
echo ============================================================
echo   Crear tablas SeccionK (sin artisan / sin PHP 8.3)
echo ============================================================
echo.

if not exist "database\sql\seccionk_instalar.sql" (
    echo [X] No se encontro database\sql\seccionk_instalar.sql
    echo     Copia ese archivo desde GitHub a esta carpeta.
    pause
    exit /b 1
)

set "DB_HOST=127.0.0.1"
set "DB_PORT=3306"
set "DB_DATABASE=ventapos"
set "DB_USERNAME=root"
set "DB_PASSWORD="

if exist ".env" (
    for /f "usebackq tokens=1,* delims==" %%A in (".env") do (
        if /I "%%A"=="DB_HOST" set "DB_HOST=%%B"
        if /I "%%A"=="DB_PORT" set "DB_PORT=%%B"
        if /I "%%A"=="DB_DATABASE" set "DB_DATABASE=%%B"
        if /I "%%A"=="DB_USERNAME" set "DB_USERNAME=%%B"
        if /I "%%A"=="DB_PASSWORD" set "DB_PASSWORD=%%B"
    )
)

echo Base de datos: !DB_DATABASE!
echo Usuario:       !DB_USERNAME!
echo Host:          !DB_HOST!:!DB_PORT!
echo.

set "MYSQL_EXE="

where mysql >nul 2>nul
if %errorlevel%==0 set "MYSQL_EXE=mysql"

if not defined MYSQL_EXE if exist "C:\xampp\mysql\bin\mysql.exe" set "MYSQL_EXE=C:\xampp\mysql\bin\mysql.exe"
if not defined MYSQL_EXE if exist "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe" set "MYSQL_EXE=C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe"
if not defined MYSQL_EXE if exist "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" set "MYSQL_EXE=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe"

if not defined MYSQL_EXE (
    for /d %%D in ("C:\laragon\bin\mysql\*") do (
        if exist "%%D\bin\mysql.exe" set "MYSQL_EXE=%%D\bin\mysql.exe"
    )
)
if not defined MYSQL_EXE (
    for /d %%D in ("C:\wamp64\bin\mysql\*") do (
        if exist "%%D\bin\mysql.exe" set "MYSQL_EXE=%%D\bin\mysql.exe"
    )
)

if not defined MYSQL_EXE (
    echo [!] No se encontro mysql.exe
    echo.
    echo Usa phpMyAdmin:
    echo   1. Abre http://localhost/phpmyadmin
    echo   2. Selecciona la base "!DB_DATABASE!"
    echo   3. Pestana Importar
    echo   4. Elige: database\sql\seccionk_instalar.sql
    echo   5. Click Continuar
    echo.
    start "" "database\sql\seccionk_instalar.sql"
    pause
    exit /b 1
)

echo Usando: !MYSQL_EXE!
echo.

if "!DB_PASSWORD!"=="" (
    "!MYSQL_EXE!" -h!DB_HOST! -P!DB_PORT! -u!DB_USERNAME! !DB_DATABASE! < "database\sql\seccionk_instalar.sql"
) else (
    "!MYSQL_EXE!" -h!DB_HOST! -P!DB_PORT! -u!DB_USERNAME! -p!DB_PASSWORD! !DB_DATABASE! < "database\sql\seccionk_instalar.sql"
)

if %errorlevel% neq 0 (
    echo.
    echo [X] No se pudo importar el SQL.
    echo     Abre phpMyAdmin y importa database\sql\seccionk_instalar.sql
    echo     en la base !DB_DATABASE!
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   [OK] Tablas y datos de SeccionK creados
echo ============================================================
echo.
echo Recarga: http://localhost:8000/seccionk
echo Busca DNI 12345678  Apellido Garcia
echo Admin:   http://localhost:8000/seccionk/admin/login
echo Usuario admin  /  seccionk2026
echo.
pause
