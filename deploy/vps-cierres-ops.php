<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CierreCaja;
use App\Models\Usuario;
use App\Services\CierreCajaService;
use Carbon\Carbon;

$empresaId = 1;
$accion = 'list';
$fecha = null;

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === 'delete-x-hoy') {
        $accion = 'delete-x-hoy';
    } elseif ($arg === 'try-close') {
        $accion = 'try-close';
    } elseif (ctype_digit($arg)) {
        $empresaId = (int) $arg;
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $arg)) {
        $fecha = $arg;
    }
}

$tz = config('app.timezone', 'America/Lima');
$hoy = Carbon::now($tz)->startOfDay();

if ($accion === 'delete-x-hoy') {
    $cierres = CierreCaja::query()
        ->where('empresa_id', $empresaId)
        ->where('tipo_cierre', 'X')
        ->where('fecha_fin', '>=', $hoy)
        ->get();
    echo "eliminando_x_hoy={$cierres->count()}\n";
    foreach ($cierres as $c) {
        echo "  delete id={$c->id} fin={$c->fecha_fin}\n";
        $c->delete();
    }
    exit(0);
}

if ($accion === 'try-close' && $fecha) {
    $supervisor = Usuario::where('empresa_id', $empresaId)->whereIn('rol', ['dueno', 'supervisor', 'super_admin'])->first();
    if (! $supervisor) {
        echo "no supervisor\n";
        exit(1);
    }
    try {
        $z = app(CierreCajaService::class)->cerrarDiaPendiente($supervisor, $fecha, 'Prueba script');
        echo "ok Z id={$z->id} fin={$z->fecha_fin}\n";
    } catch (Throwable $e) {
        echo 'ERROR: '.$e->getMessage()."\n";
        exit(1);
    }
    exit(0);
}

$xHoy = CierreCaja::query()
    ->where('empresa_id', $empresaId)
    ->where('tipo_cierre', 'X')
    ->where('fecha_fin', '>=', $hoy)
    ->orderBy('fecha_fin')
    ->get();

echo "x_hoy={$xHoy->count()}\n";
foreach ($xHoy as $x) {
    echo "  id={$x->id} usr={$x->usuario_id} fin={$x->fecha_fin} tk={$x->cantidad_ventas}\n";
}

$s = app(CierreCajaService::class);
$r = $s->resumenZCierre($empresaId);
echo 'puede_z='.($r['puede_cerrar_z_hoy'] ? 'yes' : 'no').' motivo='.$r['motivo_no_z']."\n";
