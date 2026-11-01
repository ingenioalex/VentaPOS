<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$empresaId = (int) ($argv[1] ?? 1);
$extrasActivar = array_slice($argv, 2);

if ($extrasActivar === []) {
    $extrasActivar = ['categorias_jerarquicas', 'flejes'];
}

$empresa = App\Models\Empresa::find($empresaId);
if (! $empresa) {
    echo "Empresa {$empresaId} no encontrada\n";
    exit(1);
}

$service = app(App\Services\ExtrasEmpresaService::class);
$permisos = app(App\Services\PermisoService::class);

$actuales = $service->contratados($empresa);
$nuevos = array_values(array_unique(array_merge($actuales, $extrasActivar)));

$service->guardarContratados($empresa, $nuevos, $permisos);

$empresa->refresh();
echo "Empresa {$empresaId} ({$empresa->nombre}):\n";
foreach ($service->todos($empresa) as $clave => $activo) {
    if ($activo) {
        echo "  ✓ {$clave}\n";
    }
}
