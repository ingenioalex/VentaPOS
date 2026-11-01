<?php

/**
 * Elimina comprobantes emitidos desde una hora/fecha (solo hoy calendario).
 * Uso: php deploy/vps-delete-ventas-desde.php [empresa_id] [--dry-run] [--desde=YYYY-MM-DD HH:MM:SS]
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$empresaId = 1;
$dryRun = false;
$desdeManual = null;

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
    } elseif (str_starts_with($arg, '--desde=')) {
        $desdeManual = substr($arg, 8);
    } elseif (ctype_digit($arg)) {
        $empresaId = (int) $arg;
    }
}

$tz = config('app.timezone', 'America/Lima');
$ahora = Carbon::now($tz);
$inicioHoy = $ahora->copy()->startOfDay();
$cutoff = $desdeManual
    ? Carbon::parse($desdeManual, $tz)
    : $ahora->copy()->startOfHour();

echo "empresa={$empresaId}\n";
echo "cutoff={$cutoff->toDateTimeString()} ({$tz})\n";
echo 'modo='.($dryRun ? 'DRY-RUN' : 'EJECUTAR')."\n\n";

$comprobantes = Comprobante::query()
    ->where('empresa_id', $empresaId)
    ->where('estado', 'emitido')
    ->where('emitido_en', '>=', $inicioHoy)
    ->where('emitido_en', '<', $inicioHoy->copy()->addDay())
    ->where('emitido_en', '>=', $cutoff)
    ->with('detalles')
    ->orderBy('emitido_en')
    ->get();

echo "comprobantes_a_eliminar={$comprobantes->count()}\n";
$total = 0.0;
foreach ($comprobantes as $c) {
    $t = $c->emitido_en->timezone($tz);
    $total += (float) $c->total;
    echo "  id={$c->id} {$t->format('H:i:s')} {$c->numero_interno} S/{$c->total}\n";
}
echo 'total_S/='.number_format($total, 2)."\n\n";

if ($comprobantes->isEmpty()) {
    exit(0);
}

if ($dryRun) {
    echo "Dry-run: no se eliminó nada.\n";
    exit(0);
}

$eliminados = 0;

DB::transaction(function () use ($comprobantes, &$eliminados) {
    foreach ($comprobantes as $comprobante) {
        foreach ($comprobante->detalles as $detalle) {
            $producto = Producto::where('empresa_id', $comprobante->empresa_id)
                ->where('id', $detalle->producto_id)
                ->lockForUpdate()
                ->first();

            if ($producto) {
                $stockAnterior = (float) $producto->stock_disponible;
                $stockNuevo = $stockAnterior + (float) $detalle->cantidad;
                $producto->update(['stock_disponible' => $stockNuevo]);
            }
        }

        InventarioMovimiento::query()
            ->where('empresa_id', $comprobante->empresa_id)
            ->where('referencia', $comprobante->numero_interno)
            ->delete();

        if ($comprobante->sunat_xml && Storage::disk('local')->exists($comprobante->sunat_xml)) {
            Storage::disk('local')->delete($comprobante->sunat_xml);
        }
        if ($comprobante->sunat_cdr && Storage::disk('local')->exists($comprobante->sunat_cdr)) {
            Storage::disk('local')->delete($comprobante->sunat_cdr);
        }

        DetalleComprobante::where('comprobante_id', $comprobante->id)->delete();
        $comprobante->delete();
        $eliminados++;
    }
});

echo "eliminados={$eliminados}\n";
