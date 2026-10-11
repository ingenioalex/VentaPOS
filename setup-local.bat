@echo off
REM Script de Setup Local para Windows
REM Ejecutar en tu máquina local con PHP instalado

echo.
echo ============================================================
echo   Setup Local - VentaPOS + SeccionK (Windows)
echo ============================================================
echo.

REM Verificar PHP
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [X] PHP no esta instalado
    echo Instala PHP 8.2+ desde: https://www.php.net/downloads
    echo O usa Laragon: https://laragon.org/download/
    pause
    exit /b 1
)

REM Verificar Composer
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo [X] Composer no esta instalado
    echo Instala Composer desde: https://getcomposer.org/download/
    pause
    exit /b 1
)

echo [OK] PHP detectado
echo [OK] Composer detectado
echo.

echo [1/10] Instalando dependencias de PHP...
composer install --no-interaction

echo.
echo [2/10] Configurando .env...
if not exist .env (
    copy .env.example .env
    echo [OK] .env creado
) else (
    echo [OK] .env ya existe
)

echo.
echo [3/10] Generando application key...
php artisan key:generate

echo.
echo [4/10] Configurando base de datos SQLite...
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo [OK] database\database.sqlite creado
) else (
    echo [OK] database.sqlite ya existe
)

echo.
echo [5/10] Ejecutando migraciones...
php artisan migrate --force

echo.
echo [6/10] Cargando datos de prueba...
php artisan db:seed --force

echo.
echo [7/10] Instalando dependencias de Node...
where npm >nul 2>nul
if %errorlevel% equ 0 (
    npm install
    echo.
    echo [8/10] Construyendo assets...
    npm run build
) else (
    echo [!] NPM no detectado, saltando build de assets
    echo     Los assets se cargaran desde CDN
)

echo.
echo [9/10] Optimizando...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

echo.
echo [10/10] Creando acceso directo...
echo @echo off > INICIAR-SERVIDOR.bat
echo echo. >> INICIAR-SERVIDOR.bat
echo echo ============================================================ >> INICIAR-SERVIDOR.bat
echo echo   Servidor Local - VentaPOS + SeccionK >> INICIAR-SERVIDOR.bat
echo echo ============================================================ >> INICIAR-SERVIDOR.bat
echo echo. >> INICIAR-SERVIDOR.bat
echo echo Iniciando servidor en http://localhost:8000 >> INICIAR-SERVIDOR.bat
echo echo. >> INICIAR-SERVIDOR.bat
echo echo URLs de acceso: >> INICIAR-SERVIDOR.bat
echo echo   - VentaPOS:         http://localhost:8000 >> INICIAR-SERVIDOR.bat
echo echo   - SeccionK Publico: http://localhost:8000/seccionk >> INICIAR-SERVIDOR.bat
echo echo   - SeccionK Admin:   http://localhost:8000/seccionk/admin/login >> INICIAR-SERVIDOR.bat
echo echo. >> INICIAR-SERVIDOR.bat
echo echo Presiona Ctrl+C para detener el servidor >> INICIAR-SERVIDOR.bat
echo echo. >> INICIAR-SERVIDOR.bat
echo php artisan serve >> INICIAR-SERVIDOR.bat

echo.
echo ============================================================
echo   [OK] Setup Completado
echo ============================================================
echo.
echo Para iniciar el servidor local, ejecuta:
echo.
echo    INICIAR-SERVIDOR.bat
echo.
echo O manualmente:
echo.
echo    php artisan serve
echo.
echo URLs de acceso:
echo.
echo    VentaPOS:         http://localhost:8000
echo    SeccionK Publico: http://localhost:8000/seccionk
echo    SeccionK Admin:   http://localhost:8000/seccionk/admin/login
echo.
echo Credenciales SeccionK Admin:
echo.
echo    Usuario:    admin
echo    Contraseña: seccionk2026
echo.
echo Datos de prueba (busqueda publica):
echo.
echo    DNI:      12345678
echo    Apellido: Garcia
echo.
echo Para ver rutas de SeccionK:
echo.
echo    php artisan route:list --path=seccionk
echo.
pause
