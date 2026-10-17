#!/bin/bash

# VentaPOS - Arranque local con verificación completa (Linux/Mac)
# Este script verifica que todo esté configurado correctamente antes de iniciar

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Buscar PHP
if command -v php &> /dev/null; then
    PHP=$(which php)
else
    echo -e "${RED}❌ ERROR: PHP no encontrado en el sistema${NC}"
    echo "   Instala PHP 8.1 o superior"
    exit 1
fi

echo -e "\n${CYAN}=====================================${NC}"
echo -e "${CYAN}  VentaPOS - Verificación e Inicio${NC}"
echo -e "${CYAN}=====================================${NC}\n"

# 1. Verificar PHP
echo -e "${YELLOW}[1/7] Verificando PHP...${NC}"
PHP_VERSION=$($PHP -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
if [[ "$PHP_VERSION" == "8.3" ]] || [[ "$PHP_VERSION" == "8.2" ]] || [[ "$PHP_VERSION" == "8.1" ]]; then
    echo -e "${GREEN}✓ PHP $PHP_VERSION encontrado${NC}"
else
    echo -e "${YELLOW}⚠ Advertencia: PHP $PHP_VERSION puede no ser compatible (se recomienda 8.3)${NC}"
fi

# 2. Verificar archivo .env
echo -e "\n${YELLOW}[2/7] Verificando archivo .env...${NC}"
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠ Archivo .env no encontrado. Creando desde .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ Archivo .env creado. Generando APP_KEY...${NC}"
    $PHP artisan key:generate --force
else
    echo -e "${GREEN}✓ Archivo .env encontrado${NC}"
fi

# 3. Verificar APP_KEY
echo -e "\n${YELLOW}[3/7] Verificando APP_KEY...${NC}"
if grep -q "APP_KEY=base64:" .env; then
    echo -e "${GREEN}✓ APP_KEY configurada${NC}"
else
    echo -e "${YELLOW}⚠ APP_KEY vacía. Generando...${NC}"
    $PHP artisan key:generate --force
    echo -e "${GREEN}✓ APP_KEY generada${NC}"
fi

# 4. Verificar base de datos
echo -e "\n${YELLOW}[4/7] Verificando base de datos...${NC}"
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d "=" -f 2)
DB_CONNECTION=${DB_CONNECTION:-sqlite}

if [ "$DB_CONNECTION" == "sqlite" ]; then
    if [ ! -f database/database.sqlite ]; then
        echo -e "${YELLOW}⚠ Base de datos SQLite no encontrada. Creando...${NC}"
        touch database/database.sqlite
        echo -e "${GREEN}✓ Archivo database.sqlite creado${NC}"
    else
        echo -e "${GREEN}✓ Base de datos SQLite encontrada${NC}"
    fi
else
    echo -e "${GREEN}✓ Usando base de datos: $DB_CONNECTION${NC}"
fi

# 5. Verificar si las tablas existen
echo -e "\n${YELLOW}[5/7] Verificando tablas de la base de datos...${NC}"
TABLES_EXIST=$($PHP artisan tinker --execute="echo Schema::hasTable('usuarios') ? 'SI' : 'NO';" 2>/dev/null | grep -o "SI\|NO" || echo "NO")

if [ "$TABLES_EXIST" == "NO" ]; then
    echo -e "${YELLOW}⚠ Tablas no encontradas. Ejecutando migraciones...${NC}"
    
    echo -n "¿Desea crear la base de datos con datos de ejemplo? (S/N) [S]: "
    read -r respuesta
    respuesta=${respuesta:-S}
    
    if [[ "$respuesta" =~ ^[Ss]$ ]]; then
        echo -e "${CYAN}   Ejecutando migrate:fresh --seed (esto borrará datos existentes)...${NC}"
        $PHP artisan migrate:fresh --seed --force
        echo -e "${GREEN}✓ Base de datos creada con datos de ejemplo${NC}"
    else
        echo -e "${CYAN}   Ejecutando migrate...${NC}"
        $PHP artisan migrate --force
        echo -e "${GREEN}✓ Migraciones ejecutadas${NC}"
    fi
else
    echo -e "${GREEN}✓ Tablas encontradas${NC}"
fi

# 6. Verificar permisos de storage
echo -e "\n${YELLOW}[6/7] Verificando permisos de directorios...${NC}"
if [ -d storage ]; then
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    echo -e "${GREEN}✓ Permisos de storage configurados${NC}"
else
    echo -e "${YELLOW}⚠ Advertencia: storage no encontrado${NC}"
fi

# 7. Limpiar caché
echo -e "\n${YELLOW}[7/7] Limpiando caché...${NC}"
$PHP artisan config:clear 2>&1 > /dev/null
$PHP artisan cache:clear 2>&1 > /dev/null
$PHP artisan route:clear 2>&1 > /dev/null
$PHP artisan view:clear 2>&1 > /dev/null
echo -e "${GREEN}✓ Caché limpiada${NC}"

# Resumen
echo -e "\n${CYAN}=====================================${NC}"
echo -e "${GREEN}  ✓ VERIFICACIÓN COMPLETA${NC}"
echo -e "${CYAN}=====================================${NC}\n"

echo -e "${NC}Usuarios de prueba:${NC}"
echo -e "${CYAN}  • admin / admin123 (Administrador)${NC}"
echo -e "${CYAN}  • cajero / cajero123 (Cajero)${NC}"

echo -e "\n${GREEN}Iniciando servidor en http://127.0.0.1:8000 ...\n${NC}"

# Intentar abrir el navegador
if command -v xdg-open &> /dev/null; then
    xdg-open "http://127.0.0.1:8000" 2>/dev/null &
elif command -v open &> /dev/null; then
    open "http://127.0.0.1:8000" 2>/dev/null &
fi

# Iniciar servidor
$PHP artisan serve --host=127.0.0.1 --port=8000
