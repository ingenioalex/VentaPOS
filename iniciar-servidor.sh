#!/bin/bash

# Acceso directo para iniciar servidor local
# Crear este archivo en la raíz del proyecto

echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║  Servidor Local - VentaPOS + SeccionK                  ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Verificar si ya se hizo setup
if [ ! -d "vendor" ]; then
    echo "[!] Primera vez: Ejecutando setup..."
    echo ""
    ./setup-local.sh
    echo ""
fi

# Verificar si existe la base de datos
if [ ! -f "database/database.sqlite" ]; then
    echo "[!] Base de datos no existe, ejecutando migraciones..."
    echo ""
    php artisan migrate --force
    php artisan db:seed --force
    echo ""
fi

echo "Iniciando servidor en http://localhost:8000"
echo ""
echo "╔════════════════════════════════════════════════════════╗"
echo "║  URLs de Acceso:                                       ║"
echo "╠════════════════════════════════════════════════════════╣"
echo "║  VentaPOS:         http://localhost:8000              ║"
echo "║  SeccionK Público: http://localhost:8000/seccionk     ║"
echo "║  SeccionK Admin:   http://localhost:8000/seccionk/admin/login ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
echo "Credenciales SeccionK Admin:"
echo "  Usuario:    admin"
echo "  Contraseña: seccionk2026"
echo ""
echo "Datos de prueba (búsqueda pública):"
echo "  DNI:      12345678"
echo "  Apellido: García"
echo ""
echo "Presiona Ctrl+C para detener el servidor"
echo ""
echo "════════════════════════════════════════════════════════"
echo ""

# Abrir navegador automáticamente en SeccionK (funciona en Mac/Linux con escritorio)
sleep 2
if command -v xdg-open &> /dev/null; then
    xdg-open http://localhost:8000/seccionk &
elif command -v open &> /dev/null; then
    open http://localhost:8000/seccionk &
fi

# Iniciar servidor
php artisan serve
