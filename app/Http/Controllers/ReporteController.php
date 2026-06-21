<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Comprobante;
use App\Services\ExportExcelService;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function __construct(
        private ReporteService $reportes,
        private ExportExcelService $excel,
    ) {}

    public function index(Request $request)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $resumen = $this->reportes->resumenVentas($empresaId, $desde, $hasta);
        $porMedio = $this->reportes->ventasPorMedio($empresaId, $desde, $hasta);
        $porCajero = $this->reportes->ventasPorCajero($empresaId, $desde, $hasta);
        $porDia = $this->reportes->ventasPorDia($empresaId, $desde, $hasta);
        $topProductos = $this->reportes->topProductos($empresaId, $desde, $hasta);

        return view('app.reportes.index', compact(
            'desde', 'hasta', 'resumen', 'porMedio', 'porCajero', 'porDia', 'topProductos'
        ));
    }

    public function inventario(Request $request)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $q = trim($request->get('q', ''));

        $resumen = $this->reportes->resumenInventario($empresaId);
        $porTipo = $this->reportes->movimientosPorTipo($empresaId, $desde, $hasta);
        $stock = $this->reportes->stockValorizado($empresaId, $q, $request->boolean('stock_bajo'));

        $totalesStock = [
            'unidades' => round($stock->sum('stock'), 2),
            'valor_costo' => round($stock->sum('valor_costo'), 2),
            'valor_venta' => round($stock->sum('valor_venta'), 2),
        ];

        return view('app.reportes.inventario', compact(
            'desde', 'hasta', 'q', 'resumen', 'porTipo', 'stock', 'totalesStock'
        ));
    }

    public function compras(Request $request)
    {
        if (! Auth::user()->puedeGestionar() || ! Auth::user()->tieneModulo('compras')) {
            abort(403);
        }

        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $estado = $request->get('estado', 'recibido');

        $resumen = $this->reportes->resumenCompras($empresaId, $desde, $hasta, $estado);
        $porProveedor = $this->reportes->comprasPorProveedor($empresaId, $desde, $hasta, $estado);
        $porTipo = $this->reportes->comprasPorTipo($empresaId, $desde, $hasta, $estado);
        $topProductos = $this->reportes->topProductosComprados($empresaId, $desde, $hasta, 10, $estado);

        return view('app.reportes.compras', compact(
            'desde', 'hasta', 'estado', 'resumen', 'porProveedor', 'porTipo', 'topProductos'
        ));
    }

    public function exportarCompras(Request $request): StreamedResponse
    {
        if (! Auth::user()->puedeGestionar() || ! Auth::user()->tieneModulo('compras')) {
            abort(403);
        }

        $compras = $this->comprasParaExportar($request);
        $desde = $request->get('desde', today()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $nombre = "compras_{$desde}_{$hasta}.csv";

        return response()->streamDownload(function () use ($compras) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Documento', 'Fecha', 'Proveedor', 'RUC/DNI', 'Tipo', 'Estado', 'Usuario', 'Subtotal', 'IGV', 'Total'], ';');

            foreach ($compras as $c) {
                fputcsv($out, [
                    $c->etiquetaDocumento(),
                    $c->fecha_emision->format('Y-m-d'),
                    $c->proveedor->razon_social,
                    $c->proveedor->numero_documento,
                    $c->tipo_documento,
                    $c->estado,
                    $c->usuario?->nombre,
                    $c->subtotal,
                    $c->valor_igv,
                    $c->total,
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarComprasExcel(Request $request): StreamedResponse
    {
        if (! Auth::user()->puedeGestionar() || ! Auth::user()->tieneModulo('compras')) {
            abort(403);
        }

        $compras = $this->comprasParaExportar($request);
        $desde = $request->get('desde', today()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $filas = $compras->map(fn ($c) => [
            $c->etiquetaDocumento(),
            $c->fecha_emision->format('Y-m-d'),
            $c->proveedor->razon_social,
            $c->proveedor->numero_documento,
            $c->tipo_documento,
            $c->estado,
            $c->usuario?->nombre,
            $c->subtotal,
            $c->valor_igv,
            $c->total,
        ])->all();

        return $this->excel->descargar(
            "compras_{$desde}_{$hasta}.xls",
            ['Documento', 'Fecha', 'Proveedor', 'RUC/DNI', 'Tipo', 'Estado', 'Usuario', 'Subtotal', 'IGV', 'Total'],
            $filas,
            'Compras'
        );
    }

    /** @return \Illuminate\Support\Collection<int, Compra> */
    private function comprasParaExportar(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $estado = $request->get('estado', 'todos');
        $tipo = $request->get('tipo', 'todos');
        $proveedorId = $request->integer('proveedor_id') ?: null;

        return Compra::where('empresa_id', $empresaId)
            ->with(['proveedor:id,razon_social,numero_documento', 'usuario:id,nombre'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->when($tipo !== 'todos', fn ($q) => $q->where('tipo_documento', $tipo))
            ->when($proveedorId, fn ($q) => $q->where('proveedor_id', $proveedorId))
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->orderBy('fecha_emision')
            ->get();
    }

    public function exportar(Request $request): StreamedResponse
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $ventas = Comprobante::where('empresa_id', $empresaId)
            ->with(['usuario:id,nombre', 'cliente:id,nombre'])
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->orderBy('emitido_en')
            ->get();

        $nombre = "ventas_{$desde}_{$hasta}.csv";

        return response()->streamDownload(function () use ($ventas) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Numero', 'Fecha', 'Tipo', 'Cajero', 'Cliente', 'Estado', 'Medio pago', 'Subtotal', 'IGV', 'Descuento', 'Total'], ';');

            foreach ($ventas as $v) {
                fputcsv($out, [
                    $v->numero_interno,
                    $v->emitido_en->format('Y-m-d H:i'),
                    $v->tipo_documento,
                    $v->usuario?->nombre,
                    $v->cliente?->nombre ?? 'Consumidor final',
                    $v->estado,
                    $v->etiquetaMedioPago(),
                    $v->subtotal,
                    $v->valor_igv,
                    $v->descuento,
                    $v->total,
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarExcel(Request $request): StreamedResponse
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $empresaId = Auth::user()->empresa_id;
        $desde = $request->get('desde', today()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());

        $ventas = Comprobante::where('empresa_id', $empresaId)
            ->with(['usuario:id,nombre', 'cliente:id,nombre'])
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->orderBy('emitido_en')
            ->get();

        $filas = $ventas->map(fn ($v) => [
            $v->numero_interno,
            $v->emitido_en->format('Y-m-d H:i'),
            $v->tipo_documento,
            $v->usuario?->nombre,
            $v->cliente?->nombre ?? 'Consumidor final',
            $v->estado,
            $v->etiquetaMedioPago(),
            $v->subtotal,
            $v->valor_igv,
            $v->descuento,
            $v->total,
        ])->all();

        return $this->excel->descargar(
            "ventas_{$desde}_{$hasta}.xls",
            ['Numero', 'Fecha', 'Tipo', 'Cajero', 'Cliente', 'Estado', 'Medio pago', 'Subtotal', 'IGV', 'Descuento', 'Total'],
            $filas,
            'Ventas'
        );
    }

    public function exportarStock(): StreamedResponse
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $items = $this->reportes->stockValorizado(Auth::user()->empresa_id);
        $nombre = 'stock_valorizado_'.today()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['SKU', 'Nombre', 'Categoria', 'Stock', 'Minimo', 'Costo unit.', 'Valor costo', 'Precio unit.', 'Valor venta'], ';');

            foreach ($items as $i) {
                fputcsv($out, [
                    $i->sku,
                    $i->nombre,
                    $i->categoria,
                    $i->stock,
                    $i->stock_minimo,
                    $i->costo,
                    $i->valor_costo,
                    $i->precio,
                    $i->valor_venta,
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarStockExcel(): StreamedResponse
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

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
            'stock_valorizado_'.today()->format('Y-m-d').'.xls',
            ['SKU', 'Nombre', 'Categoria', 'Stock', 'Minimo', 'Costo unit.', 'Valor costo', 'Precio unit.', 'Valor venta'],
            $filas,
            'Stock'
        );
    }
}
