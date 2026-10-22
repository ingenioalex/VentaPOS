# Sube SeccionK al VPS de ventasagil.com
# Ejecutar en PowerShell. Te pedira usuario y contrasena SSH.

$ErrorActionPreference = "Stop"
$HostName = "ventasagil.com"
$User = Read-Host "Usuario SSH (Enter = root)"
if (-not $User) { $User = "root" }
$Password = Read-Host "Contrasena SSH" -AsSecureString
$BSTR = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Password)
$Plain = [Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

$RemoteScript = @'
curl -fsSL https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy/actualizar-seccionk-vps.sh -o /tmp/actualizar-seccionk-vps.sh
bash /tmp/actualizar-seccionk-vps.sh
'@

$plink = Get-Command plink -ErrorAction SilentlyContinue
if ($plink) {
    echo y | & $plink.Source -ssh "$User@$HostName" -pw $Plain $RemoteScript
} else {
    Write-Host "Conectando con ssh. Escribe la contrasena si la pide."
    ssh "$User@$HostName" $RemoteScript
}

Write-Host ""
Write-Host "Listo. Abre https://ventasagil.com/seccionk"
Write-Host "Admin: https://ventasagil.com/seccionk/admin/login  admin / seccionk2026"
