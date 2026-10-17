#!/bin/bash

# Script de Setup Local para SeccionK
# Ejecutar en tu máquina local con PHP instalado

set -e

echo "╔════════════════════════════════════════════════════════╗"
echo "║  Setup Local - VentaPOS + SeccionK                     ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Verificar requisitos
echo "[1/10] Verificando requisitos..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP no está instalado"
    echo "Instala PHP 8.2+ desde: https://www.php.net/downloads"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "❌ Composer no está instalado"
    echo "Instala Composer desde: https://getcomposer.org/download/"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "✅ PHP $PHP_VERSION detectado"
echo "✅ Composer detectado"

# Instalar dependencias PHP
echo ""
echo "[2/10] Instalando dependencias de PHP..."
composer install --no-interaction 2>&1 | tail -5

# Copiar .env si no existe
echo ""
echo "[3/10] Configurando .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env creado"
else
    echo "✅ .env ya existe"
fi

# Generar key
echo ""
echo "[4/10] Generando application key..."
php artisan key:generate

# Crear base de datos SQLite
echo ""
echo "[5/10] Configurando base de datos SQLite..."
touch database/database.sqlite
echo "✅ database/database.sqlite creado"

# Ejecutar migraciones
echo ""
echo "[6/10] Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders
echo ""
echo "[7/10] Cargando datos de prueba..."
php artisan db:seed --force

# Instalar dependencias Node
echo ""
echo "[8/10] Instalando dependencias de Node..."
if command -v npm &> /dev/null; then
    npm install 2>&1 | tail -5
    echo "[9/10] Construyendo assets..."
    npm run build 2>&1 | tail -5
else
    echo "⚠️  NPM no detectado, saltando build de assets"
    echo "   Los assets se cargarán desde CDN"
fi

# Limpiar caché
echo ""
echo "[10/10] Optimizando..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║  ✅ Setup Completado                                   ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
echo "🚀 Para iniciar el servidor local, ejecuta:"
echo ""
echo "   php artisan serve"
echo ""
echo "🌐 URLs de acceso:"
echo ""
echo "   VentaPOS:         http://localhost:8000"
echo "   SeccionK Público: http://localhost:8000/seccionk"
echo "   SeccionK Admin:   http://localhost:8000/seccionk/admin/login"
echo ""
echo "🔐 Credenciales SeccionK Admin:"
echo ""
echo "   Usuario:    admin"
echo "   Contraseña: seccionk2026"
echo ""
echo "🔍 Datos de prueba (búsqueda pública):"
echo ""
echo "   DNI:      12345678"
echo "   Apellido: García"
echo ""
echo "💡 Para ver rutas de SeccionK:"
echo ""
echo "   php artisan route:list --path=seccionk"
echo ""
