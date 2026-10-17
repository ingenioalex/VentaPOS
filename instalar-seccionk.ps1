# Script de Instalación Automática de SeccionK
# Ejecutar desde la raíz de VentaPOS: D:\Proyectos\VentaPOS_build

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  Instalación SeccionK en VentaPOS" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# Verificar que estamos en la raíz del proyecto
if (-not (Test-Path "artisan")) {
    Write-Host "[X] Error: No se encontró el archivo 'artisan'" -ForegroundColor Red
    Write-Host "    Ejecuta este script desde la raíz de VentaPOS" -ForegroundColor Yellow
    Write-Host "    Ejemplo: D:\Proyectos\VentaPOS_build" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "[OK] Proyecto VentaPOS detectado" -ForegroundColor Green
Write-Host ""

# Descargar archivos desde GitHub
Write-Host "[1/5] Descargando archivos de SeccionK desde GitHub..." -ForegroundColor Yellow

$branch = "cursor/seccionk-module-0c7c"
$baseUrl = "https://raw.githubusercontent.com/ingenioalex/VentaPOS/$branch"

# Crear directorios necesarios
$directories = @(
    "app\Http\Controllers\SeccionK",
    "app\Http\Controllers\SeccionK\Admin",
    "app\Models",
    "app\Http\Middleware",
    "resources\views\seccionk",
    "resources\views\seccionk\admin",
    "resources\views\seccionk\admin\promociones",
    "routes",
    "database\migrations",
    "database\seeders"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}

Write-Host "  [OK] Directorios creados" -ForegroundColor Green

# Lista de archivos a descargar
$files = @{
    # Controladores
    "app/Http/Controllers/SeccionK/SeccionKPublicoController.php" = "app\Http\Controllers\SeccionK\SeccionKPublicoController.php"
    "app/Http/Controllers/SeccionK/SeccionKAuthController.php" = "app\Http\Controllers\SeccionK\SeccionKAuthController.php"
    "app/Http/Controllers/SeccionK/Admin/AdminDashboardController.php" = "app\Http\Controllers\SeccionK\Admin\AdminDashboardController.php"
    "app/Http/Controllers/SeccionK/Admin/AdminPromocionController.php" = "app\Http\Controllers\SeccionK\Admin\AdminPromocionController.php"
    "app/Http/Controllers/SeccionK/Admin/AdminEventoController.php" = "app\Http\Controllers\SeccionK\Admin\AdminEventoController.php"
    
    # Modelos
    "app/Models/SeccionKPromocion.php" = "app\Models\SeccionKPromocion.php"
    "app/Models/SeccionKEvento.php" = "app\Models\SeccionKEvento.php"
    "app/Models/SeccionKParticipacion.php" = "app\Models\SeccionKParticipacion.php"
    "app/Models/SeccionKUsuario.php" = "app\Models\SeccionKUsuario.php"
    
    # Middleware
    "app/Http/Middleware/SeccionKAuth.php" = "app\Http\Middleware\SeccionKAuth.php"
    
    # Vistas
    "resources/views/seccionk/layout.blade.php" = "resources\views\seccionk\layout.blade.php"
    "resources/views/seccionk/index.blade.php" = "resources\views\seccionk\index.blade.php"
    "resources/views/seccionk/perfil.blade.php" = "resources\views\seccionk\perfil.blade.php"
    "resources/views/seccionk/registro.blade.php" = "resources\views\seccionk\registro.blade.php"
    "resources/views/seccionk/admin/layout.blade.php" = "resources\views\seccionk\admin\layout.blade.php"
    "resources/views/seccionk/admin/login.blade.php" = "resources\views\seccionk\admin\login.blade.php"
    "resources/views/seccionk/admin/dashboard.blade.php" = "resources\views\seccionk\admin\dashboard.blade.php"
    "resources/views/seccionk/admin/promociones/index.blade.php" = "resources\views\seccionk\admin\promociones\index.blade.php"
    
    # Rutas
    "routes/seccionk.php" = "routes\seccionk.php"
    
    # Base de datos
    "database/migrations/2026_10_11_000001_crear_tablas_seccionk.php" = "database\migrations\2026_10_11_000001_crear_tablas_seccionk.php"
    "database/seeders/SeccionKSeeder.php" = "database\seeders\SeccionKSeeder.php"
}

$downloaded = 0
$total = $files.Count

foreach ($file in $files.GetEnumerator()) {
    $url = "$baseUrl/$($file.Key)"
    $destination = $file.Value
    
    try {
        Invoke-WebRequest -Uri $url -OutFile $destination -UseBasicParsing
        $downloaded++
        Write-Progress -Activity "Descargando archivos" -Status "$downloaded de $total" -PercentComplete (($downloaded / $total) * 100)
    }
    catch {
        Write-Host "  [!] Error descargando: $($file.Key)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "  [OK] $downloaded archivos descargados" -ForegroundColor Green
Write-Host ""

# Modificar bootstrap/app.php
Write-Host "[2/5] Configurando rutas en bootstrap/app.php..." -ForegroundColor Yellow

$bootstrapFile = "bootstrap\app.php"
$bootstrapContent = Get-Content $bootstrapFile -Raw

if ($bootstrapContent -notmatch "seccionk\.php") {
    # Buscar donde agregar las rutas
    $routesSection = @"
            
            // SeccionK en ruta /seccionk
            Route::middleware('web')
                ->prefix('seccionk')
                ->name('seccionk.')
                ->group(base_path('routes/seccionk.php'));
"@
    
    $bootstrapContent = $bootstrapContent -replace "(Route::middleware\('web'\)\s+->group\(base_path\('routes/admin\.php'\)\);)", "`$1$routesSection"
    
    # Agregar middleware alias
    if ($bootstrapContent -notmatch "seccionk\.auth") {
        $middlewareAlias = "'seccionk.auth' => \App\Http\Middleware\SeccionKAuth::class,"
        $bootstrapContent = $bootstrapContent -replace "('onboarding' => \\App\\Http\\Middleware\\RequiereOnboarding::class,)", "`$1`n            $middlewareAlias"
    }
    
    Set-Content -Path $bootstrapFile -Value $bootstrapContent -Encoding UTF8
    Write-Host "  [OK] Rutas configuradas" -ForegroundColor Green
}
else {
    Write-Host "  [OK] Rutas ya configuradas" -ForegroundColor Green
}

Write-Host ""

# Modificar config/auth.php
Write-Host "[3/5] Configurando autenticación en config/auth.php..." -ForegroundColor Yellow

$authFile = "config\auth.php"
$authContent = Get-Content $authFile -Raw

if ($authContent -notmatch "'seccionk' =>") {
    # Agregar guard
    $guardSection = @"
        
        'seccionk' => [
            'driver' => 'session',
            'provider' => 'seccionk',
        ],
"@
    
    $authContent = $authContent -replace "('web' => \[\s+'driver' => 'session',\s+'provider' => 'users',\s+\],)", "`$1$guardSection"
    
    # Agregar provider
    $providerSection = @"
        
        'seccionk' => [
            'driver' => 'eloquent',
            'model' => App\Models\SeccionKUsuario::class,
        ],
"@
    
    $authContent = $authContent -replace "('users' => \[\s+'driver' => 'eloquent',\s+'model' => [^\]]+\],)", "`$1$providerSection"
    
    Set-Content -Path $authFile -Value $authContent -Encoding UTF8
    Write-Host "  [OK] Autenticación configurada" -ForegroundColor Green
}
else {
    Write-Host "  [OK] Autenticación ya configurada" -ForegroundColor Green
}

Write-Host ""

# Modificar DatabaseSeeder.php
Write-Host "[4/5] Configurando seeder..." -ForegroundColor Yellow

$seederFile = "database\seeders\DatabaseSeeder.php"
if (Test-Path $seederFile) {
    $seederContent = Get-Content $seederFile -Raw
    
    if ($seederContent -notmatch "SeccionKSeeder") {
        $seederContent = $seederContent -replace "(public function run\(\): void\s+\{)", "`$1`n        `$this->call(SeccionKSeeder::class);"
        Set-Content -Path $seederFile -Value $seederContent -Encoding UTF8
        Write-Host "  [OK] Seeder configurado" -ForegroundColor Green
    }
    else {
        Write-Host "  [OK] Seeder ya configurado" -ForegroundColor Green
    }
}
else {
    Write-Host "  [!] DatabaseSeeder.php no encontrado" -ForegroundColor Yellow
}

Write-Host ""

# Ejecutar migraciones
Write-Host "[5/5] Ejecutando migraciones y seeders..." -ForegroundColor Yellow

Write-Host "  Ejecutando: php artisan migrate" -ForegroundColor Gray
php artisan migrate --force

Write-Host "  Ejecutando: php artisan db:seed --class=SeccionKSeeder" -ForegroundColor Gray
php artisan db:seed --class=SeccionKSeeder

Write-Host "  Limpiando caché..." -ForegroundColor Gray
php artisan config:clear | Out-Null
php artisan route:clear | Out-Null
php artisan view:clear | Out-Null

Write-Host "  Generando caché..." -ForegroundColor Gray
php artisan config:cache | Out-Null
php artisan route:cache | Out-Null

Write-Host ""
Write-Host "  [OK] Base de datos configurada" -ForegroundColor Green

Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "  [OK] Instalación Completada!" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "URLs de acceso:" -ForegroundColor Cyan
Write-Host "  SeccionK Público:  " -NoNewline; Write-Host "http://localhost:8000/seccionk" -ForegroundColor Yellow
Write-Host "  SeccionK Admin:    " -NoNewline; Write-Host "http://localhost:8000/seccionk/admin/login" -ForegroundColor Yellow
Write-Host ""
Write-Host "Credenciales Admin:" -ForegroundColor Cyan
Write-Host "  Usuario:    " -NoNewline; Write-Host "admin" -ForegroundColor Yellow
Write-Host "  Contraseña: " -NoNewline; Write-Host "seccionk2026" -ForegroundColor Yellow
Write-Host ""
Write-Host "Datos de prueba (búsqueda):" -ForegroundColor Cyan
Write-Host "  DNI:      " -NoNewline; Write-Host "12345678" -ForegroundColor Yellow
Write-Host "  Apellido: " -NoNewline; Write-Host "García" -ForegroundColor Yellow
Write-Host ""
Write-Host "Para iniciar el servidor:" -ForegroundColor Cyan
Write-Host "  php artisan serve" -ForegroundColor Yellow
Write-Host ""
pause
