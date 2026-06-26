# Subir VentaPOS a GitHub (usuario ingenioalex)
# Uso:  cd D:\Proyectos\VentaPOS_build
#       .\deploy\git-push-github.ps1
#       .\deploy\git-push-github.ps1 -Mensaje "Mi mensaje de commit"

param(
    [string]$Mensaje = "",
    [string]$RamaRemota = "main",
    [string]$Usuario = "ingenioalex",
    [string]$Repositorio = "ingenioalex/VentaPOS"
)

$ErrorActionPreference = "Stop"
$raiz = Resolve-Path (Join-Path $PSScriptRoot "..")
Set-Location $raiz

Write-Host "=== VentaPOS -> GitHub ($Usuario) ===" -ForegroundColor Cyan
Write-Host "Carpeta: $raiz`n"

# Identidad solo en ESTE repositorio (no global)
$emailActual = git config --local user.email 2>$null
if (-not $emailActual) {
    $email = Read-Host "Correo de GitHub (Enter = ${Usuario}@users.noreply.github.com)"
    if ([string]::IsNullOrWhiteSpace($email)) {
        $email = "${Usuario}@users.noreply.github.com"
    }
    git config --local user.email $email
}
git config --local user.name $Usuario

Write-Host "Git: $(git config --local user.name) <$(git config --local user.email)>`n"

# Token PAT (no se guarda en el script)
Write-Host "Necesitas un Personal Access Token de GitHub:" -ForegroundColor Yellow
Write-Host "  GitHub -> Settings -> Developer settings -> Personal access tokens"
Write-Host "  Permisos: al menos 'repo' (acceso a repositorios privados)`n"

$tokenSeguro = Read-Host "Pega tu token (no se mostrara)" -AsSecureString
$ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($tokenSeguro)
try {
    $token = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
} finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
}

if ([string]::IsNullOrWhiteSpace($token)) {
    Write-Error "Token vacio. Cancelado."
}

$remoteHttps = "https://${Usuario}:$token@github.com/${Repositorio}.git"
$remoteLimpio = "https://github.com/${Repositorio}.git"

try {
    git remote set-url origin $remoteHttps

    $cambios = git status --porcelain
    if ($cambios) {
        if ([string]::IsNullOrWhiteSpace($Mensaje)) {
            $Mensaje = Read-Host "Mensaje del commit"
        }
        if ([string]::IsNullOrWhiteSpace($Mensaje)) {
            $Mensaje = "Actualizacion VentaPOS $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
        }

        git add -A
        git commit -m $Mensaje
        Write-Host "`nCommit creado." -ForegroundColor Green
    } else {
        Write-Host "Sin cambios locales; solo push." -ForegroundColor DarkYellow
    }

    $ramaLocal = git branch --show-current
    if ($ramaLocal -eq $RamaRemota) {
        git push -u origin $RamaRemota
    } else {
        Write-Host "Rama local: $ramaLocal -> remota: $RamaRemota" -ForegroundColor DarkYellow
        git push -u origin "${ramaLocal}:${RamaRemota}"
    }

    Write-Host "`nListo. Repositorio actualizado en GitHub." -ForegroundColor Green
    Write-Host "https://github.com/${Repositorio}"
}
finally {
    git remote set-url origin $remoteLimpio
    $token = $null
    $tokenSeguro = $null
}
