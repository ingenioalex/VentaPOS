# VentaPOS - Configurar conexion MySQL (Laragon)
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
$mysql = "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"
$envFile = Join-Path $PSScriptRoot ".env"

if (-not (Test-Path $php)) {
    Write-Host "ERROR: No se encuentra PHP de Laragon." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $mysql)) {
    $mysql = (Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter "mysql.exe" -ErrorAction SilentlyContinue |
        Select-Object -First 1 -ExpandProperty FullName)
}

Write-Host "MySQL de Laragon requiere la contraseña de root." -ForegroundColor Yellow
Write-Host "Si no la recuerdas: Laragon > Menu > MySQL > Reset root password (deja vacia)." -ForegroundColor Yellow
Write-Host ""
$password = Read-Host "Contrasena de root (Enter si esta vacia)"

$mysqlArgs = @("-u", "root", "-h", "127.0.0.1", "-e", "SELECT 1;")
if ($password) {
    $mysqlArgs = @("-u", "root", "-p$password", "-h", "127.0.0.1", "-e", "SELECT 1;")
}

& $mysql @mysqlArgs 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: No se pudo conectar con esas credenciales." -ForegroundColor Red
    exit 1
}

Write-Host "Conexion OK." -ForegroundColor Green

$content = Get-Content $envFile -Raw
if ($content -match "(?m)^DB_PASSWORD=.*$") {
    $content = $content -replace "(?m)^DB_PASSWORD=.*$", "DB_PASSWORD=$password"
} else {
    $content += "`nDB_PASSWORD=$password"
}
Set-Content -Path $envFile -Value $content -NoNewline

$createDb = "CREATE DATABASE IF NOT EXISTS ventapos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
$createArgs = @("-u", "root", "-h", "127.0.0.1", "-e", $createDb)
if ($password) {
    $createArgs = @("-u", "root", "-p$password", "-h", "127.0.0.1", "-e", $createDb)
}
& $mysql @createArgs

Set-Location $PSScriptRoot
& $php artisan config:clear | Out-Null
Write-Host "Ejecutando migraciones y datos demo..." -ForegroundColor Cyan
& $php artisan migrate:fresh --seed

if ($LASTEXITCODE -eq 0) {
    Write-Host "Listo. Ejecuta .\iniciar.ps1" -ForegroundColor Green
} else {
    Write-Host "ERROR en migrate:fresh --seed. Revisa el mensaje anterior." -ForegroundColor Red
    exit 1
}
