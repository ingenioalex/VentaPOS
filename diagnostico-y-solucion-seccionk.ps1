# Script de Diagnóstico y Solución para SeccionK
# Ejecutar: .\diagnostico-y-solucion-seccionk.ps1

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  DIAGNÓSTICO Y SOLUCIÓN - MÓDULO SECCIONK            ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$problemas = @()
$soluciones = @()

# 1. Verificar archivo hosts
Write-Host "[1/8] Verificando archivo HOSTS..." -ForegroundColor Yellow
$hostsPath = "C:\Windows\System32\drivers\etc\hosts"
$hosts = Get-Content $hostsPath -Raw

if ($hosts -match "seccionk\.ventasagil\.com") {
    Write-Host "   ✓ seccionk.ventasagil.com encontrado en hosts" -ForegroundColor Green
} else {
    Write-Host "   ✗ seccionk.ventasagil.com NO está en hosts" -ForegroundColor Red
    $problemas += "hosts"
    $soluciones += "Agregar 'seccionk.ventasagil.com' al archivo hosts"
}

# 2. Verificar rama Git
Write-Host "`n[2/8] Verificando rama Git..." -ForegroundColor Yellow
try {
    $branch = git branch --show-current 2>$null
    Write-Host "   Rama actual: $branch" -ForegroundColor Cyan
    
    if ($branch -ne "cursor/seccionk-module-0c7c") {
        Write-Host "   ⚠ No estás en la rama correcta" -ForegroundColor Red
        $problemas += "rama"
        $soluciones += "Cambiar a rama cursor/seccionk-module-0c7c"
    } else {
        Write-Host "   ✓ Rama correcta" -ForegroundColor Green
    }
} catch {
    Write-Host "   ✗ Error al verificar Git" -ForegroundColor Red
}

# 3. Verificar archivo de rutas
Write-Host "`n[3/8] Verificando routes/seccionk.php..." -ForegroundColor Yellow
if (Test-Path "routes\seccionk.php") {
    Write-Host "   ✓ Archivo routes/seccionk.php existe" -ForegroundColor Green
    $rutasSize = (Get-Item "routes\seccionk.php").Length
    Write-Host "   Tamaño: $rutasSize bytes" -ForegroundColor Cyan
} else {
    Write-Host "   ✗ Archivo routes/seccionk.php NO existe" -ForegroundColor Red
    $problemas += "rutas"
    $soluciones += "Asegurarse de estar en la rama cursor/seccionk-module-0c7c"
}

# 4. Verificar bootstrap/app.php
Write-Host "`n[4/8] Verificando bootstrap/app.php..." -ForegroundColor Yellow
if (Test-Path "bootstrap\app.php") {
    $bootstrap = Get-Content "bootstrap\app.php" -Raw
    if ($bootstrap -match "seccionk\.ventasagil\.com") {
        Write-Host "   ✓ Configuración de dominio SeccionK encontrada" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Configuración de dominio SeccionK NO encontrada" -ForegroundColor Red
        $problemas += "bootstrap"
        $soluciones += "Verificar configuración en bootstrap/app.php"
    }
} else {
    Write-Host "   ✗ bootstrap/app.php NO existe" -ForegroundColor Red
}

# 5. Verificar .env
Write-Host "`n[5/8] Verificando .env..." -ForegroundColor Yellow
if (Test-Path ".env") {
    Write-Host "   ✓ Archivo .env existe" -ForegroundColor Green
    
    $env = Get-Content ".env" -Raw
    if ($env -match "DB_CONNECTION") {
        Write-Host "   ✓ Configuración de base de datos presente" -ForegroundColor Green
    }
} else {
    Write-Host "   ✗ Archivo .env NO existe" -ForegroundColor Red
    $problemas += "env"
    $soluciones += "Copiar .env.example a .env"
}

# 6. Verificar vendor
Write-Host "`n[6/8] Verificando dependencias..." -ForegroundColor Yellow
if (Test-Path "vendor") {
    Write-Host "   ✓ Directorio vendor existe" -ForegroundColor Green
} else {
    Write-Host "   ✗ Directorio vendor NO existe" -ForegroundColor Red
    $problemas += "vendor"
    $soluciones += "Ejecutar: composer install"
}

# 7. Verificar base de datos
Write-Host "`n[7/8] Verificando tablas de base de datos..." -ForegroundColor Yellow
try {
    $tables = & php artisan db:table:list 2>&1 | Out-String
    if ($tables -match "seccionk") {
        Write-Host "   ✓ Tablas SeccionK encontradas" -ForegroundColor Green
    } else {
        Write-Host "   ⚠ Tablas SeccionK NO encontradas" -ForegroundColor Yellow
        $problemas += "migraciones"
        $soluciones += "Ejecutar migraciones y seeder"
    }
} catch {
    Write-Host "   ⚠ No se pudo verificar base de datos" -ForegroundColor Yellow
}

# 8. Verificar rutas registradas
Write-Host "`n[8/8] Verificando rutas registradas..." -ForegroundColor Yellow
try {
    $rutas = & php artisan route:list --domain=seccionk 2>&1
    if ($rutas -match "seccionk") {
        Write-Host "   ✓ Rutas de SeccionK registradas" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Rutas de SeccionK NO registradas" -ForegroundColor Red
        $problemas += "cache"
        $soluciones += "Limpiar cache de Laravel"
    }
} catch {
    Write-Host "   ✗ Error al verificar rutas" -ForegroundColor Red
}

# Resumen
Write-Host ""
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  RESUMEN DEL DIAGNÓSTICO" -ForegroundColor Cyan
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

if ($problemas.Count -eq 0) {
    Write-Host "✅ No se encontraron problemas evidentes" -ForegroundColor Green
    Write-Host ""
    Write-Host "Intenta:" -ForegroundColor Yellow
    Write-Host "1. Limpiar cache del navegador (Ctrl+Shift+Delete)" -ForegroundColor White
    Write-Host "2. Usar modo incógnito (Ctrl+Shift+N)" -ForegroundColor White
    Write-Host "3. Reiniciar el servidor PHP" -ForegroundColor White
} else {
    Write-Host "⚠ Problemas encontrados: $($problemas.Count)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Soluciones sugeridas:" -ForegroundColor Yellow
    $i = 1
    foreach ($solucion in $soluciones) {
        Write-Host "$i. $solucion" -ForegroundColor White
        $i++
    }
}

Write-Host ""
Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Preguntar si desea aplicar soluciones
if ($problemas.Count -gt 0) {
    $respuesta = Read-Host "¿Desea aplicar las soluciones automáticamente? (S/N)"
    
    if ($respuesta -eq "S" -or $respuesta -eq "s") {
        Write-Host ""
        Write-Host "Aplicando soluciones..." -ForegroundColor Cyan
        Write-Host ""
        
        # Solución 1: Hosts (requiere permisos de admin)
        if ($problemas -contains "hosts") {
            Write-Host "[→] Configurando archivo hosts..." -ForegroundColor Yellow
            Write-Host "   ⚠ REQUIERE PERMISOS DE ADMINISTRADOR" -ForegroundColor Red
            Write-Host "   Por favor, agrega manualmente esta línea al archivo:" -ForegroundColor Yellow
            Write-Host "   C:\Windows\System32\drivers\etc\hosts" -ForegroundColor Cyan
            Write-Host ""
            Write-Host "   127.0.0.1   seccionk.ventasagil.com" -ForegroundColor White
            Write-Host ""
            Read-Host "Presiona Enter cuando hayas agregado la línea"
        }
        
        # Solución 2: Cambiar rama
        if ($problemas -contains "rama") {
            Write-Host "[→] Cambiando a rama cursor/seccionk-module-0c7c..." -ForegroundColor Yellow
            git checkout cursor/seccionk-module-0c7c
            Write-Host "   ✓ Rama cambiada" -ForegroundColor Green
        }
        
        # Solución 3: Instalar dependencias
        if ($problemas -contains "vendor") {
            Write-Host "[→] Instalando dependencias de Composer..." -ForegroundColor Yellow
            composer install --no-dev
            Write-Host "   ✓ Dependencias instaladas" -ForegroundColor Green
        }
        
        # Solución 4: Crear .env
        if ($problemas -contains "env") {
            Write-Host "[→] Creando archivo .env..." -ForegroundColor Yellow
            Copy-Item .env.example .env
            & php artisan key:generate
            Write-Host "   ✓ Archivo .env creado" -ForegroundColor Green
        }
        
        # Solución 5: Migraciones
        if ($problemas -contains "migraciones") {
            Write-Host "[→] Ejecutando migraciones..." -ForegroundColor Yellow
            & php artisan migrate --force
            & php artisan db:seed --class=SeccionKSeeder --force
            Write-Host "   ✓ Migraciones ejecutadas" -ForegroundColor Green
        }
        
        # Solución 6: Limpiar cache
        if ($problemas -contains "cache" -or $problemas.Count -gt 0) {
            Write-Host "[→] Limpiando cache..." -ForegroundColor Yellow
            & php artisan config:clear
            & php artisan route:clear
            & php artisan cache:clear
            & php artisan view:clear
            Write-Host "   ✓ Cache limpiado" -ForegroundColor Green
        }
        
        Write-Host ""
        Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
        Write-Host "  ✅ SOLUCIONES APLICADAS" -ForegroundColor Green
        Write-Host "════════════════════════════════════════════════════════" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Próximos pasos:" -ForegroundColor Yellow
        Write-Host "1. Reinicia el servidor:" -ForegroundColor White
        Write-Host "   php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "2. Abre en el navegador (modo incógnito):" -ForegroundColor White
        Write-Host "   http://seccionk.ventasagil.com:8000" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "3. Credenciales de prueba:" -ForegroundColor White
        Write-Host "   Usuario: admin" -ForegroundColor Cyan
        Write-Host "   Contraseña: seccionk2026" -ForegroundColor Cyan
        Write-Host ""
    }
}

Write-Host "Diagnóstico completado." -ForegroundColor Green
Write-Host ""
