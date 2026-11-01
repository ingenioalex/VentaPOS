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
        $categoriaId = $request->integer('categoria_id') ?: null;
        $empresa = Auth::user()->empresa;
        $jerarquicas = $empresa && $this->extras->activo($empresa, 'categorias_jerarquicas');
        $orden = $this->normalizarOrdenProductos($request->get('orden', 'categoria'), $jerarquicas);

        $productos = Producto::with(['categoria.parent'])
            ->when($soloStockBajo, fn ($query) => $query->stockBajo())
            ->when($categoriaId, fn ($query) => $query->where('categoria_id', $categoriaId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('productos.nombre', 'like', "%{$q}%")
                        ->orWhere('productos.codigo_barra', 'like', "%{$q}%")
                        ->orWhere('productos.sku', 'like', "%{$q}%");
                });
            })
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->when($jerarquicas, fn ($query) => $query->leftJoin('categorias as cat_padre', 'cat_padre.id', '=', 'categorias.parent_id'))
            ->tap(fn ($query) => $this->aplicarOrdenProductos($query, $orden, $jerarquicas))
            ->select('productos.*')
            ->paginate(25)
            ->withQueryString();

        $stockBajoCount = Producto::stockBajo()->count();
        $categoriasFiltro = $jerarquicas
            ? Categoria::with('parent')->orderBy('orden')->orderBy('nombre')->get()
            : collect();
        $resumen = [
            'total' => Producto::count(),
            'activos' => Producto::where('activo', true)->count(),
            'stock_bajo' => $stockBajoCount,
        ];

        return view('app.productos.index', compact(
            'productos', 'q', 'soloStockBajo', 'stockBajoCount', 'jerarquicas', 'categoriasFiltro', 'categoriaId', 'orden', 'resumen'
        ));
    }

    private function normalizarOrdenProductos(?string $orden, bool $jerarquicas): string
    {
        $validos = [
            'categoria',
            'nombre_asc', 'nombre_desc',
            'sku_asc', 'sku_desc',
            'barra_asc', 'barra_desc',
            'categoria_asc', 'categoria_desc',
            'precio_asc', 'precio_desc',
            'costo_asc', 'costo_desc',
            'stock_asc', 'stock_desc',
            'um_asc', 'um_desc',
            'estado_asc', 'estado_desc',
        ];

        if ($jerarquicas) {
            $validos[] = 'subcategoria_asc';
            $validos[] = 'subcategoria_desc';
        }

        return in_array($orden, $validos, true) ? $orden : 'categoria';
    }

    private function aplicarOrdenProductos($query, string $orden, bool $jerarquicas): void
    {
        match ($orden) {
            'nombre_asc' => $query->orderBy('productos.nombre'),
            'nombre_desc' => $query->orderByDesc('productos.nombre'),
            'sku_asc' => $query->orderBy('productos.sku')->orderBy('productos.nombre'),
            'sku_desc' => $query->orderByDesc('productos.sku')->orderBy('productos.nombre'),
            'barra_asc' => $query->orderByRaw('COALESCE(productos.codigo_barra, \'\')')->orderBy('productos.nombre'),
            'barra_desc' => $query->orderByRaw('COALESCE(productos.codigo_barra, \'\') DESC')->orderBy('productos.nombre'),
            'categoria_asc' => $jerarquicas
                ? $query->orderByRaw('COALESCE(cat_padre.nombre, categorias.nombre)')
                    ->orderBy('categorias.nombre')
                    ->orderBy('productos.nombre')
                : $query->orderByRaw('COALESCE(categorias.nombre, \'\')')->orderBy('productos.nombre'),
            'categoria_desc' => $jerarquicas
                ? $query->orderByRaw('COALESCE(cat_padre.nombre, categorias.nombre) DESC')
                    ->orderByDesc('categorias.nombre')
                    ->orderBy('productos.nombre')
                : $query->orderByRaw('COALESCE(categorias.nombre, \'\') DESC')->orderBy('productos.nombre'),
            'subcategoria_asc' => $query->orderByRaw('CASE WHEN categorias.parent_id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('categorias.nombre')
                ->orderBy('productos.nombre'),
            'subcategoria_desc' => $query->orderByRaw('CASE WHEN categorias.parent_id IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('categorias.nombre')
                ->orderBy('productos.nombre'),
            'precio_asc' => $query->orderBy('productos.precio_venta')->orderBy('productos.nombre'),
            'precio_desc' => $query->orderByDesc('productos.precio_venta')->orderBy('productos.nombre'),
            'costo_asc' => $query->orderBy('productos.costo_compra')->orderBy('productos.nombre'),
            'costo_desc' => $query->orderByDesc('productos.costo_compra')->orderBy('productos.nombre'),
            'stock_asc' => $query->orderBy('productos.stock_disponible')->orderBy('productos.nombre'),
            'stock_desc' => $query->orderByDesc('productos.stock_disponible')->orderBy('productos.nombre'),
            'um_asc' => $query->orderBy('productos.unidad_medida')->orderBy('productos.nombre'),
            'um_desc' => $query->orderByDesc('productos.unidad_medida')->orderBy('productos.nombre'),
            'estado_asc' => $query->orderBy('productos.activo')->orderBy('productos.nombre'),
            'estado_desc' => $query->orderByDesc('productos.activo')->orderBy('productos.nombre'),
            default => $jerarquicas
                ? $query->orderByRaw('COALESCE(cat_padre.orden, categorias.orden, 999)')
                    ->orderByRaw('COALESCE(cat_padre.nombre, categorias.nombre)')
                    ->orderBy('categorias.nombre')
                    ->orderBy('productos.nombre')
                : $query->orderByRaw('COALESCE(categorias.orden, 999)')
                    ->orderBy('categorias.nombre')
                    ->orderBy('productos.nombre'),
        };
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
