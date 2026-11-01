<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CierreCaja;
use App\Models\Comprobante;
use App\Services\CierreCajaService;

$empresaId = (int) ($argv[1] ?? 1);
$s = app(CierreCajaService::class);

echo "empresa={$empresaId}\n";
echo 'dias_pendientes='.$s->cantidadDiasPendientes($empresaId)."\n";
echo 'proximo='.($s->proximoDiaPendiente($empresaId) ?? 'null')."\n";

$r = $s->resumenZCierre($empresaId);
echo 'puede_z_hoy='.($r['puede_cerrar_z_hoy'] ? 'yes' : 'no')."\n";
echo 'motivo='.$r['motivo_no_z']."\n";
echo 'cierres_x='.$r['cantidad_cierres_x']."\n";
echo 'z_hoy='.($r['z_hoy_cerrado'] ? 'yes' : 'no')."\n";

$j = $s->jornadasPendientes($empresaId);
echo 'jornadas_list='.count($j)."\n";
foreach (array_slice($j, 0, 5) as $item) {
    echo '  - '.$item['fecha'].' ventas='.$item['ventas_dia_calendario']['cantidad_ventas']."\n";
}

$tz = config('app.timezone', 'America/Lima');
$hoy = now($tz)->startOfDay();
$sqlDias = Comprobante::query()
    ->where('empresa_id', $empresaId)
    ->where('estado', 'emitido')
    ->where('emitido_en', '<', $hoy)
    ->selectRaw('DATE(emitido_en) as dia')
    ->groupByRaw('DATE(emitido_en)')
    ->orderBy('dia')
    ->pluck('dia');
echo "dias_sql_DATE(emitido_en) sin Z:\n";
foreach ($sqlDias as $dia) {
    $ymd = is_string($dia) ? substr($dia, 0, 10) : $dia;
    if (! $s->diaTieneZCerrado($empresaId, $ymd)) {
        echo "  pendiente_sql: {$ymd}\n";
    }
}

echo "Z recientes:\n";
foreach (CierreCaja::where('empresa_id', $empresaId)->where('tipo_cierre', 'Z')->orderByDesc('fecha_fin')->limit(3)->get() as $z) {
    echo "  id={$z->id} fin={$z->fecha_fin}\n";
}
