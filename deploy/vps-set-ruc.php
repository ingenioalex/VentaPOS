<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ruc = $argv[1] ?? '';
$nombre = $argv[2] ?? '';
$empresaId = (int) ($argv[3] ?? 1);

if (strlen($ruc) !== 11 || $nombre === '') {
    fwrite(STDERR, "Uso: php deploy/vps-set-ruc.php RUC11 \"RAZON SOCIAL\" [empresa_id]\n");
    exit(1);
}

$empresa = App\Models\Empresa::find($empresaId);
if (! $empresa) {
    fwrite(STDERR, "Empresa {$empresaId} no encontrada.\n");
    exit(1);
}

$empresa->update([
    'ruc' => $ruc,
    'nombre' => $nombre,
]);

echo "OK empresa {$empresa->id}: {$empresa->nombre} RUC {$empresa->ruc}\n";
