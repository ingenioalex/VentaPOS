<?php

/**
 * Elimina ventas y cierres posteriores a una fecha (exclusive).
 * Uso: php deploy/vps-purge-despues-de.php [empresa_id] [--dry-run] [--hasta=YYYY-MM-DD]
 * Default: elimina todo con fecha >= 2026-06-29 00:00:00 (posterior al 28/06).
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CierreCaja;
use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$empresaId = 1;
$dryRun = false;
$desdeFecha = '2026-06-29';

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
    } elseif (str_starts_with($arg, '--desde=')) {
        $desdeFecha = substr($arg, 8);
    } elseif (ctype_digit($arg)) {
        $empresaId = (int) $arg;
    }
}

$tz = config('app.timezone', 'America/Lima');
$cutoff = Carbon::parse($desdeFecha, $tz)->startOfDay();

echo "empresa={$empresaId}\n";
echo "eliminar_registros_con_fecha>={$cutoff->toDateString()} ({$tz})\n";
echo 'modo='.($dryRun ? 'DRY-RUN' : 'EJECUTAR')."\n\n";

$comprobantes = Comprobante::query()
    ->where('empresa_id', $empresaId)
    ->where('emitido_en', '>=', $cutoff)
    ->with('detalles')
    ->orderBy('emitido_en')
    ->get();

$cierres = CierreCaja::query()
    ->where('empresa_id', $empresaId)
    ->where('fecha_fin', '>=', $cutoff)
    ->orderBy('fecha_fin')
    ->get();

echo "ventas_a_eliminar={$comprobantes->count()}\n";
foreach ($comprobantes->take(20) as $c) {
    $t = $c->emitido_en->timezone($tz);
    echo "  venta id={$c->id} {$t->format('Y-m-d H:i')} {$c->numero_interno} {$c->estado} S/{$c->total}\n";
}
if ($comprobantes->count() > 20) {
    echo '  ... y '.($comprobantes->count() - 20)." más\n";
}
echo 'ventas_total_S/='.number_format((float) $comprobantes->sum('total'), 2)."\n\n";

echo "cierres_a_eliminar={$cierres->count()}\n";
foreach ($cierres as $x) {
    $t = $x->fecha_fin->timezone($tz);
    echo "  cierre id={$x->id} {$x->tipo_cierre} {$t->format('Y-m-d H:i')} usr={$x->usuario_id}\n";
}

if ($comprobantes->isEmpty() && $cierres->isEmpty()) {
    echo "\nNada que eliminar.\n";
    exit(0);
}

if ($dryRun) {
    echo "\nDry-run: no se eliminó nada.\n";
    exit(0);
}

$ventasElim = 0;
$cierresElim = 0;

DB::transaction(function () use ($comprobantes, $cierres, &$ventasElim, &$cierresElim) {
    foreach ($comprobantes as $comprobante) {
        if ($comprobante->estado === 'emitido') {
            foreach ($comprobante->detalles as $detalle) {
                $producto = Producto::where('empresa_id', $comprobante->empresa_id)
                    ->where('id', $detalle->producto_id)
                    ->lockForUpdate()
                    ->first();

                if ($producto) {
                    $producto->update([
                        'stock_disponible' => (float) $producto->stock_disponible + (float) $detalle->cantidad,
                    ]);
                }
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
        $ventasElim++;
    }

    foreach ($cierres as $cierre) {
        $cierre->delete();
        $cierresElim++;
    }
});

echo "\nventas_eliminadas={$ventasElim}\n";
echo "cierres_eliminados={$cierresElim}\n";
