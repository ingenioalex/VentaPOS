# VentaPOS - Arranque local con verificación completa
# Este script verifica que todo esté configurado correctamente antes de iniciar

$ErrorActionPreference = "Stop"

$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
$artisan = Join-Path $PSScriptRoot "artisan"

Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "  VentaPOS - Verificación e Inicio" -ForegroundColor Cyan
Write-Host "=====================================`n" -ForegroundColor Cyan

# 1. Verificar PHP
Write-Host "[1/7] Verificando PHP..." -ForegroundColor Yellow
if (-not (Test-Path $php)) {
    Write-Host "❌ ERROR: No se encuentra PHP en $php" -ForegroundColor Red
    Write-Host "   Ajusta la ruta en iniciar-con-verificacion.ps1" -ForegroundColor Red
    Read-Host "Presiona Enter para salir"
    exit 1
}

$phpVersion = & $php -v 2>&1 | Select-String "PHP (\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value }
if ($phpVersion -like "8.3*" -or $phpVersion -like "8.2*" -or $phpVersion -like "8.1*") {
    Write-Host "✓ PHP $phpVersion encontrado" -ForegroundColor Green
} else {
    Write-Host "⚠ Advertencia: PHP $phpVersion puede no ser compatible (se recomienda 8.3)" -ForegroundColor Yellow
}

# 2. Verificar archivo .env
Write-Host "`n[2/7] Verificando archivo .env..." -ForegroundColor Yellow
$envPath = Join-Path $PSScriptRoot ".env"
if (-not (Test-Path $envPath)) {
    Write-Host "⚠ Archivo .env no encontrado. Creando desde .env.example..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "✓ Archivo .env creado. Generando APP_KEY..." -ForegroundColor Green
    & $php $artisan key:generate --force
} else {
    Write-Host "✓ Archivo .env encontrado" -ForegroundColor Green
}

# 3. Verificar APP_KEY
Write-Host "`n[3/7] Verificando APP_KEY..." -ForegroundColor Yellow
$envContent = Get-Content $envPath -Raw
if ($envContent -match "APP_KEY=base64:[A-Za-z0-9+/=]+") {
    Write-Host "✓ APP_KEY configurada" -ForegroundColor Green
} else {
    Write-Host "⚠ APP_KEY vacía. Generando..." -ForegroundColor Yellow
    & $php $artisan key:generate --force
    Write-Host "✓ APP_KEY generada" -ForegroundColor Green
}

# 4. Verificar base de datos
Write-Host "`n[4/7] Verificando base de datos..." -ForegroundColor Yellow
$dbConnection = ($envContent -match "DB_CONNECTION=(\w+)") ? $Matches[1] : "sqlite"

if ($dbConnection -eq "sqlite") {
    $dbPath = Join-Path $PSScriptRoot "database\database.sqlite"
    if (-not (Test-Path $dbPath)) {
        Write-Host "⚠ Base de datos SQLite no encontrada. Creando..." -ForegroundColor Yellow
        New-Item -ItemType File -Path $dbPath -Force | Out-Null
        Write-Host "✓ Archivo database.sqlite creado" -ForegroundColor Green
    } else {
        Write-Host "✓ Base de datos SQLite encontrada" -ForegroundColor Green
    }
} else {
    Write-Host "✓ Usando base de datos: $dbConnection" -ForegroundColor Green
}

# 5. Verificar si las tablas existen
Write-Host "`n[5/7] Verificando tablas de la base de datos..." -ForegroundColor Yellow
try {
    $tablesCheck = & $php $artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('usuarios') ? 'SI' : 'NO';" 2>&1
    
    if ($tablesCheck -match "NO" -or $LASTEXITCODE -ne 0) {
        Write-Host "⚠ Tablas no encontradas. Ejecutando migraciones..." -ForegroundColor Yellow
        
        $respuesta = Read-Host "¿Desea crear la base de datos con datos de ejemplo? (S/N) [S]"
        if ($respuesta -eq "" -or $respuesta -eq "S" -or $respuesta -eq "s") {
            Write-Host "   Ejecutando migrate:fresh --seed (esto borrará datos existentes)..." -ForegroundColor Cyan
            & $php $artisan migrate:fresh --seed --force
            Write-Host "✓ Base de datos creada con datos de ejemplo" -ForegroundColor Green
        } else {
            Write-Host "   Ejecutando migrate..." -ForegroundColor Cyan
            & $php $artisan migrate --force
            Write-Host "✓ Migraciones ejecutadas" -ForegroundColor Green
        }
    } else {
        Write-Host "✓ Tablas encontradas" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠ No se pudo verificar tablas. Intentando ejecutar migraciones..." -ForegroundColor Yellow
    & $php $artisan migrate --force
}

# 6. Verificar permisos de storage
Write-Host "`n[6/7] Verificando permisos de directorios..." -ForegroundColor Yellow
$storagePath = Join-Path $PSScriptRoot "storage"
$bootstrapCache = Join-Path $PSScriptRoot "bootstrap\cache"

if (Test-Path $storagePath) {
    Write-Host "✓ Directorio storage encontrado" -ForegroundColor Green
} else {
    Write-Host "⚠ Advertencia: storage no encontrado" -ForegroundColor Yellow
}

# 7. Limpiar caché
Write-Host "`n[7/7] Limpiando caché..." -ForegroundColor Yellow
& $php $artisan config:clear 2>&1 | Out-Null
& $php $artisan cache:clear 2>&1 | Out-Null
& $php $artisan route:clear 2>&1 | Out-Null
& $php $artisan view:clear 2>&1 | Out-Null
Write-Host "✓ Caché limpiada" -ForegroundColor Green

# Resumen
Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "  ✓ VERIFICACIÓN COMPLETA" -ForegroundColor Green
Write-Host "=====================================`n" -ForegroundColor Cyan

Write-Host "Usuarios de prueba:" -ForegroundColor White
Write-Host "  • admin / admin123 (Administrador)" -ForegroundColor Cyan
Write-Host "  • cajero / cajero123 (Cajero)" -ForegroundColor Cyan

Write-Host "`nIniciando servidor en http://127.0.0.1:8000 ...`n" -ForegroundColor Green

# Intentar abrir el navegador automáticamente
Start-Process "http://127.0.0.1:8000" -ErrorAction SilentlyContinue

# Iniciar servidor
& $php $artisan serve --host=127.0.0.1 --port=8000
