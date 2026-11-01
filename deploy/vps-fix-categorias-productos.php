<?php

/**
 * Corrige categoría de productos según nombre y reordena SKU globalmente.
 * Uso: php deploy/vps-fix-categorias-productos.php [empresa_id]
 */
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

$empresaId = (int) ($argv[1] ?? 1);

$categorias = Categoria::withoutGlobalScopes()
    ->where('empresa_id', $empresaId)
    ->get()
    ->keyBy(fn ($c) => mb_strtoupper(trim($c->nombre)));

if ($categorias->isEmpty()) {
    fwrite(STDERR, "Sin categorías\n");
    exit(1);
}

/** Palabras clave → nombre exacto de categoría en BD */
$reglas = [
    'PAPEL HIGIENICO' => ['PAPEL HIGI', 'PAÑAL', 'TOALLAS PAPEL', 'SERVILLETAS'],
    'JABONES' => ['JABON', 'JABÓN'],
    'HIGIENE' => ['SHAMPOO', 'ACONDICIONADOR', 'DESODORANTE', 'PASTA DENTAL', 'CEPILLO DENTAL', 'CREMA CORPORAL', 'RASURADORA'],
    'CHOCOLATES' => ['CHOCOLATE', 'CARAMELO'],
    'GALLETAS' => ['GALLETA', 'GALLETAS'],
    'SNACKS' => ['PAPAS', 'LAYS', 'MANI', 'MAÍZ', 'CHIZITO', 'BARra CEREAL', 'CEREAL HOJUELAS'],
    'YOGURT' => ['YOGURT', 'YOGUR'],
    'LECHES' => ['LECHE ENTERA', 'LECHE DESLACTOSADA', 'LECHE GLORIA', 'LECHE IDEAL'],
    'LÁCTEOS' => ['MANTEQUILLA', 'QUESO', 'CREMA CHANTILLY'],
    'ENLATADOS' => ['ATUN', 'ATÚN', 'SARDINA', 'CONSERVA', 'LECHE CONDENSADA', 'DURAZNO'],
    'GASEOSAS' => ['COCA COLA', 'INCA KOLA', 'SPRITE', 'GASEOSA', 'REFRESCO', 'KR ', 'PILSEN', 'ENERGIZANTE'],
    'JUGOS' => ['JUGO ', 'JUGO CONCENTRADO'],
    'AGUA' => ['AGUA '],
    'ARROZ Y GRANOS' => ['ARROZ', 'MENESTRA', 'LENTEJA', 'ARVEJA', 'AVENA', 'AZUCAR', 'AZÚCAR'],
    'PASTAS' => ['FIDEO', 'SPAGHETTI', 'MACARRON'],
    'HARINAS' => ['HARINA'],
    'ABARROTES' => ['ACEITE', 'SAL DE MESA', 'SAL ', 'CAFE', 'CAFÉ', 'TE ', 'TÉ ', 'VINAGRE', 'SILLAO', 'KETCHUP', 'MAYONESA', 'MOSTAZA', 'SAZONADOR', 'PIMIENTA', 'COMINO', 'OREGANO', 'ORÉGANO', 'AJO', 'AJÍ', 'AJI', 'CALDO', 'SOPA', 'SAZONADOR', 'HUEVOS'],
    'DETERGENTES' => ['DETERGENTE'],
    'DESINFECTANTES' => ['CLORO', 'ALCOHOL GEL', 'LIMPIAVIDRIOS', 'DESINFECTANTE'],
    'LIMPIEZA' => ['BOLSA BASURA', 'ESPONJA', 'TRAPEADOR', 'ESCOBA', 'LINTERNA', 'PILAS', 'FOSFOROS', 'FÓSFOROS', 'VELAS'],
    'Golosinas' => ['GELATINA', 'FLAN', 'MERMELADA', 'MIEL', 'PANETON', 'PANETÓN'],
];

function resolverCategoria(string $nombre, $categorias, array $reglas): ?int
{
    $upper = mb_strtoupper($nombre);

    foreach ($reglas as $catNombre => $claves) {
        $catKey = mb_strtoupper($catNombre);
        if (! $categorias->has($catKey)) {
            continue;
        }
        foreach ($claves as $clave) {
            if (str_contains($upper, mb_strtoupper($clave))) {
                return $categorias->get($catKey)->id;
            }
        }
    }

    // Fallback: categoría padre "GENERAL" o primera que contenga palabra
    foreach ($categorias as $cat) {
        if (str_contains($upper, mb_strtoupper($cat->nombre))) {
            return $cat->id;
        }
    }

    return null;
}

DB::transaction(function () use ($empresaId, $categorias, $reglas) {
    $productos = Producto::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('activo', true)
        ->get();

    $corregidos = 0;
    foreach ($productos as $p) {
        $catId = resolverCategoria($p->nombre, $categorias, $reglas);
        if ($catId && $p->categoria_id !== $catId) {
            $p->categoria_id = $catId;
            $p->save();
            $corregidos++;
        }
    }

    // SKU global único: categoría (orden) + nombre
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
        if ($p->sku !== $sku) {
            Producto::withoutGlobalScopes()->where('id', $p->id)->update(['sku' => $sku]);
        }
        $i++;
    }

    echo "Productos corregidos de categoría: {$corregidos}\n";
    echo "SKU global reasignado: {$ordenados->count()} productos\n";
});

$cats = Categoria::withoutGlobalScopes()
    ->where('empresa_id', $empresaId)
    ->orderBy('orden')
    ->orderBy('nombre')
    ->get();

foreach ($cats as $cat) {
    $lista = Producto::withoutGlobalScopes()
        ->where('empresa_id', $empresaId)
        ->where('categoria_id', $cat->id)
        ->orderBy('nombre')
        ->get(['sku', 'nombre', 'precio_venta']);

    if ($lista->isEmpty()) {
        continue;
    }

    echo "\n=== {$cat->nombre} ({$lista->count()}) ===\n";
    foreach ($lista->take(8) as $p) {
        echo sprintf("  %s | S/ %s | %s\n", $p->sku, number_format((float) $p->precio_venta, 2), $p->nombre);
    }
    if ($lista->count() > 8) {
        echo "  ... +".($lista->count() - 8)." más\n";
    }
}
