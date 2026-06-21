# VentaPOS - Arranque local con Laragon PHP 8.3
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
$artisan = Join-Path $PSScriptRoot "artisan"

if (-not (Test-Path $php)) {
    Write-Host "ERROR: No se encuentra PHP de Laragon en $php" -ForegroundColor Red
    Write-Host "Ajusta la ruta en iniciar.ps1 segun tu version de PHP."
    exit 1
}

Write-Host "VentaPOS - http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "Login demo: admin - admin123  o  cajero - cajero123"
& $php $artisan serve --host=127.0.0.1 --port=8000
