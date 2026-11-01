<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CierreCaja;
use App\Models\Comprobante;
use Carbon\Carbon;

$empresaId = (int) ($argv[1] ?? 1);
$tz = config('app.timezone', 'America/Lima');
$ahora = Carbon::now($tz);

echo "ahora={$ahora->toDateTimeString()} ({$tz})\n\n";

$inicioHoy = $ahora->copy()->startOfDay();
$ventas = Comprobante::query()
    ->where('empresa_id', $empresaId)
    ->where('estado', 'emitido')
    ->where('emitido_en', '>=', $inicioHoy)
    ->orderBy('emitido_en')
    ->get(['id', 'numero_interno', 'usuario_id', 'emitido_en', 'total', 'serie', 'numero']);

echo "ventas_hoy={$ventas->count()}\n";
foreach ($ventas as $v) {
    $t = $v->emitido_en->timezone($tz);
    echo "  id={$v->id} {$t->format('H:i:s')} S/{$v->total} {$v->serie}-{$v->numero} usr={$v->usuario_id}\n";
}

$xHoy = CierreCaja::query()
    ->where('empresa_id', $empresaId)
    ->where('tipo_cierre', 'X')
    ->where('fecha_fin', '>=', $inicioHoy)
    ->orderBy('fecha_fin')
    ->get(['id', 'usuario_id', 'fecha_fin', 'cantidad_ventas', 'total_ventas']);

echo "\ncierres_x_hoy={$xHoy->count()}\n";
foreach ($xHoy as $x) {
    $t = $x->fecha_fin->timezone($tz);
    echo "  id={$x->id} usr={$x->usuario_id} fin={$t->format('Y-m-d H:i:s')} tk={$x->cantidad_ventas} S/{$x->total_ventas}\n";
}

$desdeAhora = $ventas->filter(fn ($v) => $v->emitido_en->gte($ahora));
echo "\nventas_desde_ahora={$desdeAhora->count()}\n";
