<?php

namespace App\Http\Controllers;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Services\ExtrasEmpresaService;
use App\Services\InventarioService;
use App\Services\LoteService;
use App\Services\ExportExcelService;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventarioController extends Controller
{
    public function __construct(
        private InventarioService $inventario,
        private ReporteService $reportes,
        private ExportExcelService $excel,
        private LoteService $lotes,
        private ExtrasEmpresaService $extras,
    ) {}

    public function index(Request $request)
    {
        $tipo = $request->get('tipo', 'todos');
        $desde = $request->get('desde', today()->subDays(30)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $productoId = $request->integer('producto_id') ?: null;
        $q = trim($request->get('q', ''));

        $resumen = $this->inventario->resumen(Auth::user()->empresa_id);

        $movimientos = InventarioMovimiento::with(['producto:id,nombre,sku', 'usuario:id,nombre'])
            ->when($tipo !== 'todos', fn ($query) => $query->where('tipo_movimiento', $tipo))
            ->when($productoId, fn ($query) => $query->where('producto_id', $productoId))
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('producto', function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->whereDate('creado_en', '>=', $desde)
            ->whereDate('creado_en', '<=', $hasta)
            ->orderByDesc('creado_en')
            ->paginate(30)
            ->withQueryString();

        $productos = Producto::orderBy('nombre')->get(['id', 'nombre', 'sku']);

        return view('app.inventario.index', compact(
            'movimientos', 'tipo', 'desde', 'hasta', 'productoId', 'q', 'resumen', 'productos'
        ));
    }

    public function stock(Request $request)
    {
        $q = trim($request->get('q', ''));
        $soloBajo = $request->boolean('stock_bajo');

        $items = $this->reportes->stockValorizado(Auth::user()->empresa_id, $q, $soloBajo);
        $totales = [
            'unidades' => round($items->sum('stock'), 2),
            'valor_costo' => round($items->sum('valor_costo'), 2),
            'valor_venta' => round($items->sum('valor_venta'), 2),
        ];

        return view('app.inventario.stock', compact('items', 'q', 'soloBajo', 'totales'));
    }

    public function kardex(Producto $producto, Request $request)
    {
        $desde = $request->get('desde', today()->subMonths(3)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $movimientos = InventarioMovimiento::with('usuario:id,nombre')
            ->where('producto_id', $producto->id)
            ->whereDate('creado_en', '>=', $desde)
            ->whereDate('creado_en', '<=', $hasta)
            ->orderByDesc('creado_en')
            ->paginate(40)
            ->withQueryString();

        return view('app.inventario.kardex', compact('producto', 'movimientos', 'desde', 'hasta'));
    }

    public function entrada()
    {
        $empresa = Auth::user()->empresa;
        $lotesActivo = $empresa && $this->extras->activo($empresa, 'lotes_vencimiento');
        $productos = Producto::where('activo', true)->orderBy('nombre')
            ->get(['id', 'nombre', 'sku', 'stock_disponible', 'control_lote']);

        return view('app.inventario.entrada', compact('productos', 'lotesActivo'));
    }

    public function guardarEntrada(Request $request)
    {
        $empresa = Auth::user()->empresa;
        $lotesActivo = $empresa && $this->extras->activo($empresa, 'lotes_vencimiento');

        $reglas = [
            'producto_id' => 'required|integer',
            'cantidad' => 'required|numeric|min:0.01',
            'referencia' => 'nullable|string|max:100',
        ];

        if ($lotesActivo) {
            $reglas['lote'] = 'nullable|string|max:60';
            $reglas['fecha_vencimiento'] = 'nullable|date';
        }

        $datos = $request->validate($reglas);

        try {
            $producto = Producto::where('id', $datos['producto_id'])->where('activo', true)->firstOrFail();

            $this->inventario->registrarEntrada(
                Auth::user(),
                (int) $datos['producto_id'],
                (float) $datos['cantidad'],
                $datos['referencia'] ?? null,
            );

            if ($lotesActivo && $producto->control_lote) {
                $this->lotes->registrarEntrada(
                    $producto,
                    (float) $datos['cantidad'],
                    $datos['lote'] ?? null,
                    $datos['fecha_vencimiento'] ?? null,
                    $datos['referencia'] ?? null,
                );
            }
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['cantidad' => $e->getMessage()]);
        }

        return redirect()->route('inventario.index')->with('ok', 'Entrada de stock registrada.');
    }

    public function vencimientos()
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa && $this->extras->activo($empresa, 'lotes_vencimiento'), 404);

        $dias = (int) request()->get('dias', 30);
        $items = $this->lotes->proximosVencer(Auth::user()->empresa_id, $dias, 100);

        return view('app.inventario.vencimientos', compact('items', 'dias'));
    }

    public function salida()
    {
        $productos = Producto::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock_disponible']);

        return view('app.inventario.salida', compact('productos'));
    }

    public function guardarSalida(Request $request)
    {
        $datos = $request->validate([
            'producto_id' => 'required|integer',
            'cantidad' => 'required|numeric|min:0.01',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $this->inventario->registrarSalida(
                Auth::user(),
                (int) $datos['producto_id'],
                (float) $datos['cantidad'],
                $datos['referencia'] ?? null,
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['cantidad' => $e->getMessage()]);
        }

        return redirect()->route('inventario.index')->with('ok', 'Salida de stock registrada.');
    }

    public function ajuste()
    {
        $productos = Producto::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock_disponible']);

        return view('app.inventario.ajuste', compact('productos'));
    }

    public function guardarAjuste(Request $request)
    {
        $datos = $request->validate([
            'producto_id' => 'required|integer',
            'stock_nuevo' => 'required|numeric|min:0',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $this->inventario->registrarAjuste(
                Auth::user(),
                (int) $datos['producto_id'],
                (float) $datos['stock_nuevo'],
                $datos['referencia'] ?? null,
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['stock_nuevo' => $e->getMessage()]);
        }

        return redirect()->route('inventario.index')->with('ok', 'Ajuste de inventario registrado.');
    }

    public function exportarStock(): StreamedResponse
    {
        return $this->csvStock(Auth::user()->empresa_id);
    }

    public function exportarStockExcel(): StreamedResponse
    {
        $items = $this->reportes->stockValorizado(Auth::user()->empresa_id);
        $filas = $items->map(fn ($i) => [
            $i->sku,
            $i->nombre,
            $i->categoria,
            $i->stock,
            $i->stock_minimo,
            $i->costo,
            $i->valor_costo,
            $i->precio,
            $i->valor_venta,
        ])->all();

        return $this->excel->descargar(
            'stock_'.today()->format('Y-m-d').'.xls',
            ['SKU', 'Nombre', 'Categoria', 'Stock', 'Minimo', 'Costo', 'Valor costo', 'Precio', 'Valor venta'],
            $filas,
            'Stock'
        );
    }

    public function exportarMovimientosExcel(Request $request): StreamedResponse
    {
        $desde = $request->get('desde', today()->subDays(30)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $tipo = $request->get('tipo', 'todos');

        $movimientos = InventarioMovimiento::with(['producto:id,nombre,sku', 'usuario:id,nombre'])
            ->when($tipo !== 'todos', fn ($q) => $q->where('tipo_movimiento', $tipo))
            ->whereDate('creado_en', '>=', $desde)
            ->whereDate('creado_en', '<=', $hasta)
            ->orderBy('creado_en')
            ->get();

        $filas = $movimientos->map(fn ($m) => [
            $m->creado_en?->format('Y-m-d H:i'),
            $m->producto?->nombre,
            $m->producto?->sku,
            $m->tipo_movimiento,
            $m->cantidad,
            $m->stock_anterior,
            $m->stock_nuevo,
            $m->usuario?->nombre,
            $m->referencia,
        ])->all();

        return $this->excel->descargar(
            "movimientos_{$desde}_{$hasta}.xls",
            ['Fecha', 'Producto', 'SKU', 'Tipo', 'Cantidad', 'Stock ant.', 'Stock nuevo', 'Usuario', 'Referencia'],
            $filas,
            'Movimientos'
        );
    }

    public function exportarMovimientos(Request $request): StreamedResponse
    {
        $desde = $request->get('desde', today()->subDays(30)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $tipo = $request->get('tipo', 'todos');

        $movimientos = InventarioMovimiento::with(['producto:id,nombre,sku', 'usuario:id,nombre'])
            ->when($tipo !== 'todos', fn ($q) => $q->where('tipo_movimiento', $tipo))
            ->whereDate('creado_en', '>=', $desde)
            ->whereDate('creado_en', '<=', $hasta)
            ->orderBy('creado_en')
            ->get();

        $nombre = "movimientos_{$desde}_{$hasta}.csv";

        return response()->streamDownload(function () use ($movimientos) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Fecha', 'Producto', 'SKU', 'Tipo', 'Cantidad', 'Stock ant.', 'Stock nuevo', 'Usuario', 'Referencia'], ';');

            foreach ($movimientos as $m) {
                fputcsv($out, [
                    $m->creado_en?->format('Y-m-d H:i'),
                    $m->producto?->nombre,
                    $m->producto?->sku,
                    $m->tipo_movimiento,
                    $m->cantidad,
                    $m->stock_anterior,
                    $m->stock_nuevo,
                    $m->usuario?->nombre,
                    $m->referencia,
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvStock(int $empresaId): StreamedResponse
    {
        $productos = Producto::with('categoria:id,nombre')
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get();

        return response()->streamDownload(function () use ($productos) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['SKU', 'Codigo barra', 'Nombre', 'Categoria', 'Stock', 'Minimo', 'Costo', 'Valor costo', 'Precio', 'Valor venta', 'Activo'], ';');

            foreach ($productos as $p) {
                $stock = (float) $p->stock_disponible;
                fputcsv($out, [
                    $p->sku,
                    $p->codigo_barra,
                    $p->nombre,
                    $p->categoria?->nombre,
                    $stock,
                    $p->stock_minimo,
                    $p->costo_compra,
                    round($stock * (float) $p->costo_compra, 2),
                    $p->precio_venta,
                    round($stock * (float) $p->precio_venta, 2),
                    $p->activo ? 'si' : 'no',
                ], ';');
            }
            fclose($out);
        }, 'stock_'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
