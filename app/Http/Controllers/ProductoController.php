<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Services\ExtrasEmpresaService;
use App\Services\ProductoImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductoController extends Controller
{
    public function __construct(
        private ProductoImportService $importService,
        private ExtrasEmpresaService $extras,
    ) {}

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $soloStockBajo = $request->boolean('stock_bajo');

        $productos = Producto::with('categoria')
            ->when($soloStockBajo, fn ($query) => $query->stockBajo())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barra', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        $stockBajoCount = Producto::stockBajo()->count();

        return view('app.productos.index', compact('productos', 'q', 'soloStockBajo', 'stockBajoCount'));
    }

    public function crear(Request $request)
    {
        $categorias = Categoria::with('parent')->orderBy('orden')->orderBy('nombre')->get();
        $siguienteSku = $this->generarSku();
        $retorno = $request->get('retorno');
        $prefillBarra = $request->get('barra', '');
        $prefillNombre = $request->get('nombre', '');

        return view('app.productos.form', [
            'producto' => new Producto,
            'categorias' => $categorias,
            'siguienteSku' => $siguienteSku,
            'retorno' => $retorno,
            'prefillBarra' => $prefillBarra,
            'prefillNombre' => $prefillNombre,
            'lotesActivo' => Auth::user()->empresa?->tieneExtra('lotes_vencimiento') ?? false,
        ]);
    }

    public function guardar(Request $request)
    {
        $datos = $this->validarProducto($request);
        $empresaId = Auth::user()->empresa_id;

        $producto = Producto::create([
            ...$datos,
            'empresa_id' => $empresaId,
            'sku' => $datos['sku'] ?: $this->generarSku(),
            'activo' => true,
        ]);

        if ($request->get('retorno') === 'compras' && Auth::user()->tieneModulo('compras')) {
            return redirect()->route('compras.crear', ['nuevo' => $producto->id])
                ->with('ok', 'Producto registrado. Se agregó a la compra.');
        }

        return redirect()->route('productos.index')->with('ok', 'Producto registrado.');
    }

    public function editar(Producto $producto)
    {
        $categorias = Categoria::with('parent')->orderBy('orden')->orderBy('nombre')->get();
        $historial = ProductoPrecio::where('producto_id', $producto->id)
            ->where('empresa_id', Auth::user()->empresa_id)
            ->with('usuario:id,nombre')
            ->orderByDesc('creado_en')
            ->limit(10)
            ->get();

        return view('app.productos.form', [
            'producto' => $producto,
            'categorias' => $categorias,
            'historial' => $historial,
            'lotesActivo' => Auth::user()->empresa?->tieneExtra('lotes_vencimiento') ?? false,
            'lotes' => $producto->lotes()->orderBy('fecha_vencimiento')->get(),
        ]);
    }

    public function actualizar(Request $request, Producto $producto)
    {
        $datos = $this->validarProducto($request, $producto);

        $precioAnterior = (float) $producto->precio_venta;
        $costoAnterior = (float) $producto->costo_compra;
        $precioNuevo = (float) $datos['precio_venta'];
        $costoNuevo = (float) $datos['costo_compra'];

        if ($precioAnterior !== $precioNuevo || $costoAnterior !== $costoNuevo) {
            ProductoPrecio::create([
                'empresa_id' => $producto->empresa_id,
                'producto_id' => $producto->id,
                'usuario_id' => Auth::id(),
                'precio_anterior' => $precioAnterior,
                'precio_nuevo' => $precioNuevo,
                'costo_anterior' => $costoAnterior,
                'costo_nuevo' => $costoNuevo,
            ]);
        }

        $producto->update($datos);

        return redirect()->route('productos.index')->with('ok', 'Producto actualizado.');
    }

    public function importarForm()
    {
        return view('app.productos.importar');
    }

    public function plantillaCsv(): StreamedResponse
    {
        $contenido = "\xEF\xBB\xBF".$this->importService->contenidoPlantilla();

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, 'plantilla_productos.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => [
                'required',
                'file',
                'max:5120',
                function (string $attribute, $value, \Closure $fail): void {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, ['csv', 'txt'], true)) {
                        $fail('El archivo debe tener extensión .csv o .txt.');
                    }
                },
            ],
        ]);

        try {
            $resultado = $this->importService->importar(
                Auth::user()->empresa_id,
                $request->file('archivo'),
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => $e->getMessage()]);
        }

        $mensaje = sprintf(
            'Importación completada: %d creados, %d actualizados.',
            $resultado['creados'],
            $resultado['actualizados'],
        );

        if ($resultado['errores'] !== []) {
            $mensaje .= ' '.count($resultado['errores']).' fila(s) con error.';
        }

        return redirect()
            ->route('productos.importar')
            ->with('ok', $mensaje)
            ->with('import_errores', $resultado['errores']);
    }

    private function validarProducto(Request $request, ?Producto $producto = null): array
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:200',
            'codigo_barra' => 'nullable|string|max:64',
            'sku' => 'nullable|string|max:50',
            'categoria_id' => 'nullable|integer',
            'precio_venta' => 'required|numeric|min:0',
            'costo_compra' => 'nullable|numeric|min:0',
            'stock_disponible' => 'nullable|numeric',
            'stock_minimo' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:20',
            'afecto_igv' => 'boolean',
            'campo1' => 'nullable|string|max:100',
            'campo2' => 'nullable|string|max:100',
            'campo3' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ]);

        if (Auth::user()->empresa?->tieneExtra('lotes_vencimiento')) {
            $reglas['control_lote'] = 'boolean';
        }

        if (! empty($datos['categoria_id']) && ! Categoria::where('id', $datos['categoria_id'])->exists()) {
            abort(422, 'La categoría no pertenece a su empresa.');
        }

        return [
            'nombre' => strtoupper(trim($datos['nombre'])),
            'codigo_barra' => $datos['codigo_barra'] ?? null,
            'sku' => $datos['sku'] ?? null,
            'categoria_id' => $datos['categoria_id'] ?? null,
            'precio_venta' => $datos['precio_venta'],
            'costo_compra' => $datos['costo_compra'] ?? 0,
            'stock_disponible' => $datos['stock_disponible'] ?? ($producto?->stock_disponible ?? 0),
            'stock_minimo' => $datos['stock_minimo'] ?? ($producto?->stock_minimo ?? 0),
            'unidad_medida' => $datos['unidad_medida'] ?? 'UND',
            'afecto_igv' => $request->boolean('afecto_igv', true),
            'campo1' => $datos['campo1'] ?? null,
            'campo2' => $datos['campo2'] ?? null,
            'campo3' => $datos['campo3'] ?? 'Mercadería',
            'activo' => $producto ? $request->boolean('activo', true) : true,
            'control_lote' => Auth::user()->empresa?->tieneExtra('lotes_vencimiento')
                ? $request->boolean('control_lote', false)
                : ($producto?->control_lote ?? false),
        ];
    }

    private function generarSku(): string
    {
        $ultimo = Producto::where('empresa_id', Auth::user()->empresa_id)->count();

        return 'SKU'.str_pad((string) ($ultimo + 1), 5, '0', STR_PAD_LEFT);
    }
}
