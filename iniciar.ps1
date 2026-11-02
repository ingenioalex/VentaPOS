# VentaPOS - Arranque local con Laragon PHP 8.3
$phpDir = Get-ChildItem "C:\laragon\bin\php" -Directory -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -like "php-8.3*" } |
    Sort-Object Name -Descending |
    Select-Object -First 1
$php = if ($phpDir) { Join-Path $phpDir.FullName "php.exe" } else { $null }
$artisan = Join-Path $PSScriptRoot "artisan"

if (-not $php -or -not (Test-Path $php)) {
    Write-Host "ERROR: No se encuentra PHP 8.3 de Laragon en C:\laragon\bin\php" -ForegroundColor Red
    exit 1
}

Write-Host "VentaPOS - http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "Login demo: admin - admin123  o  cajero - cajero123"
# Varios workers: sin esto, una petición lenta (o un bot) deja el login colgado.
$env:PHP_CLI_SERVER_WORKERS = "4"
& $php $artisan serve --host=127.0.0.1 --port=8000 --no-reload
