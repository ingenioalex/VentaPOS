# Recuperacion local VentaPOS: mata php.exe colgados, restaura web.php si esta vacio,
# abre Laragon y lanza iniciar.ps1 (esta ventana ya corre como administrador).
$ErrorActionPreference = 'Continue'
$Root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $Root

$Laragon = 'C:\laragon\laragon.exe'
$Php = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe'
$Web = Join-Path $Root 'routes\web.php'
$WebBak = Join-Path $Root 'scripts\restore\web.php'
$Iniciar = Join-Path $Root 'iniciar.ps1'

function Write-Paso($t) { Write-Host $t -ForegroundColor Cyan }

Write-Host ''
Write-Host '=== VentaPOS — recuperar arranque local ===' -ForegroundColor Green
Write-Host $Root

Write-Paso '[1] Cerrando php.exe (servidores artisan colgados)...'
$antes = @(Get-Process php -ErrorAction SilentlyContinue)
if ($antes.Count -eq 0) {
    Write-Host '    No habia php.exe.'
} else {
    foreach ($p in $antes) {
        Write-Host ("    PID {0}" -f $p.Id)
        & taskkill.exe /F /PID $p.Id 2>$null | Out-Null
    }
    Start-Sleep -Seconds 2
    $quedan = @(Get-Process php -ErrorAction SilentlyContinue)
    if ($quedan.Count -gt 0) {
        Write-Host '    Forzando taskkill /IM php.exe...' -ForegroundColor Yellow
        & taskkill.exe /F /IM php.exe 2>$null | Out-Null
        Start-Sleep -Seconds 1
    }
}
$aun = @(Get-Process php -ErrorAction SilentlyContinue)
if ($aun.Count -gt 0) {
    Write-Host '    AVISO: siguen php.exe vivos. Cierre Antivirus o mate el proceso a mano.' -ForegroundColor Red
} else {
    Write-Host '    Puerto 8000 libre de php.exe.'
}

Write-Paso '[2] Revisando routes\web.php...'
$bytes = 0
if (Test-Path $Web) { $bytes = (Get-Item $Web).Length }
if ($bytes -lt 1000) {
    Write-Host ("    Archivo vacio o corrupto ({0} bytes). Restaurando..." -f $bytes) -ForegroundColor Yellow
    if (Test-Path $WebBak) {
        Copy-Item $WebBak $Web -Force
        Write-Host '    Restaurado desde scripts\restore\web.php'
    } else {
        & git.exe -C $Root checkout -- routes/web.php
        if ($LASTEXITCODE -ne 0) {
            Write-Host '    ERROR: no hay backup ni git para restaurar web.php' -ForegroundColor Red
            exit 1
        }
        Write-Host '    Restaurado desde git HEAD (puede faltar faq/impresora/chatbot).' -ForegroundColor Yellow
    }
} else {
    Write-Host ("    OK ({0} bytes). No se sobrescribe." -f $bytes)
}

Write-Paso '[3] Abriendo Laragon (MySQL)...'
if (Test-Path $Laragon) {
    $ya = Get-Process laragon -ErrorAction SilentlyContinue
    if (-not $ya) {
        Start-Process -FilePath $Laragon
        Write-Host '    Laragon iniciado. Si MySQL no arranca, pulse Start All en Laragon.'
    } else {
        Write-Host '    Laragon ya estaba abierto.'
    }
} else {
    Write-Host "    No se encontro $Laragon" -ForegroundColor Yellow
}

Write-Paso '[4] Esperando MySQL en 127.0.0.1:3306...'
$okDb = $false
for ($i = 1; $i -le 20; $i++) {
    $tcp = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
    if ($tcp) { $okDb = $true; break }
    Start-Sleep -Seconds 1
}
if ($okDb) {
    Write-Host '    MySQL escuchando.'
} else {
    Write-Host '    MySQL no responde aun. En Laragon: Start All y vuelva a intentar.' -ForegroundColor Yellow
}

if (-not (Test-Path $Php)) {
    Write-Host "ERROR: PHP 8.3 no esta en $Php" -ForegroundColor Red
    exit 1
}
if (-not (Test-Path $Iniciar)) {
    Write-Host "ERROR: falta iniciar.ps1" -ForegroundColor Red
    exit 1
}

Write-Paso '[5] Lanzando iniciar.ps1 (administrador) en otra ventana...'
# Ya somos admin (el .bat se autoelevo). RunAs abre otra consola elevada que no se cierra.
Start-Process -FilePath 'powershell.exe' -Verb RunAs -ArgumentList @(
    '-NoExit',
    '-ExecutionPolicy', 'Bypass',
    '-File', "`"$Iniciar`""
)

Write-Host ''
Write-Host 'Listo. Cuando vea "Server running", abra:' -ForegroundColor Green
Write-Host '    http://127.0.0.1:8000' -ForegroundColor Green
Write-Host '    http://127.0.0.1:8000/login'
Write-Host 'Demo: admin / admin123'
Write-Host 'No use http://localhost (puerto 80 = AppServ PHP 7.3).'
Write-Host ''
