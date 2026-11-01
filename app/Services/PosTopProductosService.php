<?php

namespace App\Services;

use App\Models\DetalleComprobante;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PosTopProductosService
{
    private const DIAS_RANKING = 90;

    private const CACHE_MINUTOS = 15;

    /** @return list<array<string, mixed>> */
    public function topMasVendidos(int $empresaId, string|int|null $categoriaId = 'todos', int $limite = 5): array
    {
        $catKey = $this->claveCategoria($categoriaId);
        $cacheKey = $this->cacheKey($empresaId, $catKey);

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTOS), function () use ($empresaId, $categoriaId, $limite) {
            return $this->consultarTop($empresaId, $categoriaId, $limite);
        });
    }

    public function invalidarCacheEmpresa(int $empresaId): void
    {
        $version = (int) Cache::get($this->versionKey($empresaId), 1);
        Cache::forever($this->versionKey($empresaId), $version + 1);
    }

    /** @return list<array<string, mixed>> */
    private function consultarTop(int $empresaId, string|int|null $categoriaId, int $limite): array
    {
        $campos = [
            'productos.id', 'productos.nombre', 'productos.codigo_barra', 'productos.sku',
            'productos.precio_venta', 'productos.stock_disponible', 'productos.stock_minimo',
            'productos.unidad_medida', 'productos.afecto_igv',
        ];

        $desde = Carbon::now(config('app.timezone', 'America/Lima'))
            ->subDays(self::DIAS_RANKING)
            ->startOfDay();

        $ranking = DetalleComprobante::query()
            ->join('comprobantes', 'comprobantes.id', '=', 'detalle_comprobante.comprobante_id')
            ->join('productos', 'productos.id', '=', 'detalle_comprobante.producto_id')
            ->where('comprobantes.empresa_id', $empresaId)
            ->where('comprobantes.estado', 'emitido')
            ->where('comprobantes.emitido_en', '>=', $desde)
            ->where('productos.activo', true);

        if ($categoriaId && $categoriaId !== 'todos') {
            $ranking->where('productos.categoria_id', (int) $categoriaId);
        }

        $top = $ranking
            ->select('detalle_comprobante.producto_id', DB::raw('SUM(detalle_comprobante.cantidad) as total_vendido'))
            ->groupBy('detalle_comprobante.producto_id')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();

        if ($top->isEmpty()) {
            return Producto::query()
                ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
                ->where('productos.empresa_id', $empresaId)
                ->where('productos.activo', true)
                ->when($categoriaId && $categoriaId !== 'todos', fn ($q) => $q->where('productos.categoria_id', (int) $categoriaId))
                ->orderByRaw('COALESCE(categorias.orden, 999)')
                ->orderBy('categorias.nombre')
                ->orderBy('productos.nombre')
                ->limit($limite)
                ->get($campos)
                ->map(fn ($p) => array_merge($p->toArray(), ['total_vendido' => 0]))
                ->values()
                ->all();
        }

        $vendidos = $top->keyBy('producto_id');
        $productos = Producto::whereIn('id', $top->pluck('producto_id'))->get($campos);

        return $top->pluck('producto_id')->map(function ($id) use ($productos, $vendidos) {
            $producto = $productos->firstWhere('id', $id);
            if (! $producto) {
                return null;
            }

            return array_merge($producto->toArray(), [
                'total_vendido' => (float) $vendidos[$id]->total_vendido,
            ]);
        })->filter()->values()->all();
    }

    private function claveCategoria(string|int|null $categoriaId): string
    {
        if ($categoriaId === null || $categoriaId === '' || $categoriaId === 'todos') {
            return 'todos';
        }

        return (string) (int) $categoriaId;
    }

    private function versionKey(int $empresaId): string
    {
        return "ventapos:pos:top5:ver:{$empresaId}";
    }

    private function cacheKey(int $empresaId, string $catKey): string
    {
        $version = (int) Cache::get($this->versionKey($empresaId), 1);

        return "ventapos:pos:top5:{$empresaId}:{$version}:{$catKey}";
    }
}
