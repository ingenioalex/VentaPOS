<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Comprobante;
use App\Models\DetalleComprobante;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    /** @return object{tickets: int, total: float, igv: float, descuentos: float, ticket_promedio: float} */
    public function resumenVentas(int $empresaId, string $desde, string $hasta): object
    {
        return Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->selectRaw('
                COUNT(*) as tickets,
                COALESCE(SUM(total),0) as total,
                COALESCE(SUM(valor_igv),0) as igv,
                COALESCE(SUM(descuento),0) as descuentos,
                COALESCE(AVG(total),0) as ticket_promedio
            ')
            ->first();
    }

    public function ventasPorMedio(int $empresaId, string $desde, string $hasta): Collection
    {
        return Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->selectRaw('medio_pago, COUNT(*) as tickets, SUM(total) as monto')
            ->groupBy('medio_pago')
            ->orderByDesc('monto')
            ->get();
    }

    public function ventasPorCajero(int $empresaId, string $desde, string $hasta): Collection
    {
        return Comprobante::where('comprobantes.empresa_id', $empresaId)
            ->where('comprobantes.estado', 'emitido')
            ->whereDate('comprobantes.emitido_en', '>=', $desde)
            ->whereDate('comprobantes.emitido_en', '<=', $hasta)
            ->join('usuarios', 'usuarios.id', '=', 'comprobantes.usuario_id')
            ->selectRaw('usuarios.nombre as cajero, COUNT(*) as tickets, SUM(comprobantes.total) as monto')
            ->groupBy('usuarios.id', 'usuarios.nombre')
            ->orderByDesc('monto')
            ->get();
    }

    public function ventasPorDia(int $empresaId, string $desde, string $hasta): Collection
    {
        return Comprobante::where('empresa_id', $empresaId)
            ->where('estado', 'emitido')
            ->whereDate('emitido_en', '>=', $desde)
            ->whereDate('emitido_en', '<=', $hasta)
            ->selectRaw('DATE(emitido_en) as fecha, COUNT(*) as tickets, SUM(total) as monto')
            ->groupBy(DB::raw('DATE(emitido_en)'))
            ->orderBy('fecha')
            ->get();
    }

    public function topProductos(int $empresaId, string $desde, string $hasta, int $limite = 10): Collection
    {
        return DetalleComprobante::query()
            ->join('comprobantes', 'comprobantes.id', '=', 'detalle_comprobante.comprobante_id')
            ->join('productos', 'productos.id', '=', 'detalle_comprobante.producto_id')
            ->where('comprobantes.empresa_id', $empresaId)
            ->where('comprobantes.estado', 'emitido')
            ->whereDate('comprobantes.emitido_en', '>=', $desde)
            ->whereDate('comprobantes.emitido_en', '<=', $hasta)
            ->selectRaw('productos.nombre, productos.sku, SUM(detalle_comprobante.cantidad) as unidades, SUM(detalle_comprobante.subtotal) as monto')
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku')
            ->orderByDesc('unidades')
            ->limit($limite)
            ->get();
    }

    /** @return array<string, mixed> */
    public function resumenInventario(int $empresaId): array
    {
        $base = Producto::where('empresa_id', $empresaId)->where('activo', true);

        $totales = (clone $base)->selectRaw('
            COUNT(*) as productos,
            COALESCE(SUM(stock_disponible), 0) as unidades,
            COALESCE(SUM(stock_disponible * costo_compra), 0) as valor_costo,
            COALESCE(SUM(stock_disponible * precio_venta), 0) as valor_venta
        ')->first();

        $inicioMes = today()->startOfMonth()->toDateString();

        $movimientosMes = InventarioMovimiento::where('empresa_id', $empresaId)
            ->whereDate('creado_en', '>=', $inicioMes)
            ->selectRaw("
                SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad ELSE 0 END) as salidas,
                SUM(CASE WHEN tipo_movimiento = 'venta' THEN cantidad ELSE 0 END) as ventas,
                SUM(CASE WHEN tipo_movimiento = 'ajuste' THEN 1 ELSE 0 END) as ajustes
            ")
            ->first();

        return [
            'productos' => (int) $totales->productos,
            'unidades' => round((float) $totales->unidades, 2),
            'valor_costo' => round((float) $totales->valor_costo, 2),
            'valor_venta' => round((float) $totales->valor_venta, 2),
            'stock_bajo' => Producto::stockBajo()->count(),
            'sin_stock' => (clone $base)->where('stock_disponible', '<=', 0)->count(),
            'entradas_mes' => round((float) ($movimientosMes->entradas ?? 0), 2),
            'salidas_mes' => round((float) ($movimientosMes->salidas ?? 0), 2),
            'ventas_mes' => round((float) ($movimientosMes->ventas ?? 0), 2),
            'ajustes_mes' => (int) ($movimientosMes->ajustes ?? 0),
        ];
    }

    public function stockValorizado(int $empresaId, ?string $q = null, bool $soloBajo = false): Collection
    {
        return Producto::with('categoria:id,nombre')
            ->when($soloBajo, fn ($query) => $query->stockBajo())
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('codigo_barra', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $p) {
                $stock = (float) $p->stock_disponible;

                return (object) [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'nombre' => $p->nombre,
                    'categoria' => $p->categoria?->nombre,
                    'stock' => $stock,
                    'stock_minimo' => (float) $p->stock_minimo,
                    'costo' => (float) $p->costo_compra,
                    'precio' => (float) $p->precio_venta,
                    'valor_costo' => round($stock * (float) $p->costo_compra, 2),
                    'valor_venta' => round($stock * (float) $p->precio_venta, 2),
                    'bajo' => $p->tieneStockBajo(),
                ];
            });
    }

    public function movimientosPorTipo(int $empresaId, string $desde, string $hasta): Collection
    {
        return InventarioMovimiento::where('empresa_id', $empresaId)
            ->whereDate('creado_en', '>=', $desde)
            ->whereDate('creado_en', '<=', $hasta)
            ->selectRaw('tipo_movimiento, COUNT(*) as movimientos, SUM(cantidad) as unidades')
            ->groupBy('tipo_movimiento')
            ->orderBy('tipo_movimiento')
            ->get();
    }

    /** @return object{documentos: int, total: float, igv: float, subtotal: float} */
    public function resumenCompras(int $empresaId, string $desde, string $hasta, string $estado = 'recibido'): object
    {
        return $this->queryCompras($empresaId, $desde, $hasta, $estado)
            ->selectRaw('
                COUNT(*) as documentos,
                COALESCE(SUM(total),0) as total,
                COALESCE(SUM(valor_igv),0) as igv,
                COALESCE(SUM(subtotal),0) as subtotal
            ')
            ->first();
    }

    public function comprasPorProveedor(int $empresaId, string $desde, string $hasta, string $estado = 'recibido'): Collection
    {
        return $this->queryCompras($empresaId, $desde, $hasta, $estado)
            ->join('proveedores', 'proveedores.id', '=', 'compras.proveedor_id')
            ->selectRaw('proveedores.razon_social as proveedor, COUNT(*) as documentos, SUM(compras.total) as monto')
            ->groupBy('proveedores.id', 'proveedores.razon_social')
            ->orderByDesc('monto')
            ->get();
    }

    public function comprasPorTipo(int $empresaId, string $desde, string $hasta, string $estado = 'recibido'): Collection
    {
        return $this->queryCompras($empresaId, $desde, $hasta, $estado)
            ->selectRaw('tipo_documento, COUNT(*) as documentos, SUM(total) as monto')
            ->groupBy('tipo_documento')
            ->orderByDesc('monto')
            ->get();
    }

    public function topProductosComprados(int $empresaId, string $desde, string $hasta, int $limite = 10, string $estado = 'recibido'): Collection
    {
        return CompraDetalle::query()
            ->join('compras', 'compras.id', '=', 'compra_detalle.compra_id')
            ->join('productos', 'productos.id', '=', 'compra_detalle.producto_id')
            ->where('compras.empresa_id', $empresaId)
            ->when($estado !== 'todos', fn ($q) => $q->where('compras.estado', $estado))
            ->whereDate('compras.fecha_emision', '>=', $desde)
            ->whereDate('compras.fecha_emision', '<=', $hasta)
            ->selectRaw('productos.nombre, productos.sku, SUM(compra_detalle.cantidad) as unidades, SUM(compra_detalle.subtotal) as monto')
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku')
            ->orderByDesc('unidades')
            ->limit($limite)
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Compra> */
    private function queryCompras(int $empresaId, string $desde, string $hasta, string $estado = 'recibido')
    {
        return Compra::where('compras.empresa_id', $empresaId)
            ->when($estado !== 'todos', fn ($q) => $q->where('compras.estado', $estado))
            ->whereDate('compras.fecha_emision', '>=', $desde)
            ->whereDate('compras.fecha_emision', '<=', $hasta);
    }
}
