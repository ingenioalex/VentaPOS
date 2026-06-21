<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\CompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{
    public function __construct(private CompraService $compras) {}

    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->subDays(30)->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $estado = $request->get('estado', 'todos');
        $tipo = $request->get('tipo', 'todos');
        $proveedorId = $request->integer('proveedor_id') ?: null;

        $compras = Compra::where('empresa_id', $empresaId)
            ->with(['proveedor:id,razon_social,numero_documento', 'usuario:id,nombre'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->when($tipo !== 'todos', fn ($q) => $q->where('tipo_documento', $tipo))
            ->when($proveedorId, fn ($q) => $q->where('proveedor_id', $proveedorId))
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $totales = Compra::where('empresa_id', $empresaId)
            ->where('estado', 'recibido')
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->selectRaw('COUNT(*) as documentos, COALESCE(SUM(total),0) as monto')
            ->first();

        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get(['id', 'razon_social']);

        return view('app.compras.index', compact(
            'compras', 'desde', 'hasta', 'estado', 'tipo', 'proveedorId', 'totales', 'proveedores'
        ));
    }

    public function crear(Request $request)
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        $puedeCrearProducto = Auth::user()->tieneModulo('productos');

        $productosRestaurar = collect();
        if (old('lineas')) {
            $ids = collect(old('lineas'))->pluck('producto_id')->filter()->unique();
            if ($ids->isNotEmpty()) {
                $productosRestaurar = Producto::whereIn('id', $ids)
                    ->get(['id', 'nombre', 'sku', 'codigo_barra', 'costo_compra', 'afecto_igv'])
                    ->keyBy('id');
            }
        }

        $productoNuevo = null;
        if ($request->filled('nuevo')) {
            $productoNuevo = Producto::where('id', $request->integer('nuevo'))
                ->where('activo', true)
                ->first(['id', 'nombre', 'sku', 'codigo_barra', 'costo_compra', 'afecto_igv']);
        }

        return view('app.compras.form', compact(
            'proveedores', 'puedeCrearProducto', 'productosRestaurar', 'productoNuevo'
        ));
    }

    public function guardar(Request $request)
    {
        $datos = $this->validarCabecera($request);
        $lineas = $this->validarLineas($request);
        $recibir = $request->boolean('recibir');

        try {
            $compra = $this->compras->registrar(Auth::user(), $datos, $lineas, $recibir);

            $msg = $recibir
                ? 'Compra registrada y mercadería ingresada al inventario.'
                : 'Compra guardada en borrador.';

            return redirect()->route('compras.detalle', $compra)->with('ok', $msg);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['lineas' => $e->getMessage()]);
        }
    }

    public function detalle(Compra $compra)
    {
        $this->autorizar($compra);
        $compra->load(['detalles.producto', 'proveedor', 'usuario', 'anuladoPor']);

        return view('app.compras.detalle', compact('compra'));
    }

    public function recibir(Compra $compra)
    {
        $this->autorizar($compra);

        try {
            $this->compras->recibir($compra, Auth::user());

            return redirect()->route('compras.detalle', $compra)
                ->with('ok', 'Compra recibida. Stock actualizado.');
        } catch (\Throwable $e) {
            return back()->withErrors(['recibir' => $e->getMessage()]);
        }
    }

    public function anular(Request $request, Compra $compra)
    {
        $this->autorizar($compra);

        $datos = $request->validate([
            'motivo' => 'required|string|max:255',
        ]);

        try {
            $this->compras->anular($compra, Auth::user(), $datos['motivo']);

            return redirect()->route('compras.detalle', $compra)
                ->with('ok', 'Compra anulada.');
        } catch (\Throwable $e) {
            return back()->withErrors(['motivo' => $e->getMessage()]);
        }
    }

    public function buscarProductos(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $productos = Producto::where('activo', true)
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('codigo_barra', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id', 'nombre', 'sku', 'codigo_barra', 'costo_compra', 'afecto_igv']);

        return response()->json($productos);
    }

    public function buscarExactoProducto(Request $request)
    {
        $q = trim($request->get('q', ''));
        if ($q === '') {
            return response()->json(null);
        }

        $producto = Producto::where('activo', true)
            ->where(function ($query) use ($q) {
                $query->where('codigo_barra', $q)->orWhere('sku', $q);
            })
            ->first(['id', 'nombre', 'sku', 'codigo_barra', 'costo_compra', 'afecto_igv']);

        return response()->json($producto);
    }

    /** @return array<string, mixed> */
    private function validarCabecera(Request $request): array
    {
        return $request->validate([
            'proveedor_id' => 'required|integer|exists:proveedores,id',
            'tipo_documento' => 'required|in:orden,guia,factura',
            'serie' => 'nullable|string|max:10',
            'numero_documento' => 'required|string|max:20',
            'fecha_emision' => 'required|date',
            'fecha_recepcion' => 'nullable|date',
            'observaciones' => 'nullable|string|max:500',
        ]);
    }

    /** @return list<array{producto_id: int, cantidad: float, costo_unitario: float}> */
    private function validarLineas(Request $request): array
    {
        $request->validate([
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|integer|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.01',
            'lineas.*.costo_unitario' => 'required|numeric|min:0',
        ]);

        return collect($request->input('lineas', []))
            ->map(fn ($l) => [
                'producto_id' => (int) $l['producto_id'],
                'cantidad' => (float) $l['cantidad'],
                'costo_unitario' => (float) $l['costo_unitario'],
            ])
            ->values()
            ->all();
    }

    private function autorizar(Compra $compra): void
    {
        if ($compra->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }
}
