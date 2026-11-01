<?php

/**
 * Reduce precios y reordena SKU globalmente (A-Z por categoría).
 * Uso: php deploy/vps-reordenar-productos.php [empresa_id] [descuento_pct]
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

$empresaId = (int) ($argv[1] ?? 1);
$descuentoPct = min(50, max(1, (float) ($argv[2] ?? 10)));

function redondearPrecio(float $precio): float
{
    if ($precio < 1) {
        return round($precio, 2);
    }
    if ($precio < 5) {
        return round($precio * 2) / 2;
    }

    return round($precio * 10) / 10;
}

DB::transaction(function () use ($empresaId, $descuentoPct) {
    $productos = Producto::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('activo', true)
        ->get();

    $rebajados = 0;
    foreach ($productos as $producto) {
        $nuevo = redondearPrecio((float) $producto->precio_venta * (1 - $descuentoPct / 100));
        if ($nuevo > 0 && $nuevo < (float) $producto->precio_venta) {
            $producto->precio_venta = $nuevo;
            if ((float) $producto->costo_compra > $nuevo) {
                $producto->costo_compra = round($nuevo * 0.72, 2);
            }
            $rebajados++;
        }
        $producto->save();
    }

    $ordenados = Producto::withoutGlobalScopes()
        ->where('productos.empresa_id', $empresaId)
        ->where('productos.activo', true)
        ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
        ->orderByRaw('COALESCE(categorias.orden, 999)')
        ->orderBy('categorias.nombre')
        ->orderBy('productos.nombre')
        ->select('productos.*')
        ->get();

    $i = 1;
    foreach ($ordenados as $p) {
        $sku = 'SKU'.str_pad((string) $i, 5, '0', STR_PAD_LEFT);
        Producto::withoutGlobalScopes()->where('id', $p->id)->update(['sku' => $sku]);
        $i++;
    }

    echo "Empresa {$empresaId}: {$productos->count()} productos\n";
    echo "Precios rebajados (~{$descuentoPct}%): {$rebajados}\n";
    echo "Orden alfabético por categoría (SKU global)\n";
});
