<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Producto;
use App\Models\ProductoLote;
use Illuminate\Support\Collection;
use RuntimeException;

class LoteService
{
    public function __construct(private ExtrasEmpresaService $extras) {}

    public function habilitado(Empresa $empresa): bool
    {
        return $this->extras->activo($empresa, 'lotes_vencimiento');
    }

    public function registrarEntrada(
        Producto $producto,
        float $cantidad,
        ?string $lote = null,
        ?string $fechaVencimiento = null,
        ?string $referencia = null,
    ): ?ProductoLote {
        if (! $producto->control_lote || $cantidad <= 0) {
            return null;
        }

        return ProductoLote::create([
            'empresa_id' => $producto->empresa_id,
            'producto_id' => $producto->id,
            'lote' => $lote ?: null,
            'fecha_vencimiento' => $fechaVencimiento ?: null,
            'cantidad' => $cantidad,
            'referencia' => $referencia,
        ]);
    }

    public function descontarVenta(Producto $producto, float $cantidad, ?string $referencia = null): void
    {
        if (! $producto->control_lote || $cantidad <= 0) {
            return;
        }

        $restante = $cantidad;
        $lotes = ProductoLote::where('producto_id', $producto->id)
            ->where('cantidad', '>', 0)
            ->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento ASC')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($restante <= 0) {
                break;
            }

            $disponible = (float) $lote->cantidad;
            $usar = min($disponible, $restante);
            $lote->update(['cantidad' => round($disponible - $usar, 4)]);
            $restante = round($restante - $usar, 4);
        }

        if ($restante > 0.0001) {
            throw new RuntimeException("Stock en lotes insuficiente para {$producto->nombre}. Falta: {$restante}");
        }
    }

    /** @return Collection<int, ProductoLote> */
    public function proximosVencer(int $empresaId, int $dias = 30, int $limite = 15): Collection
    {
        return ProductoLote::with('producto:id,nombre,sku')
            ->where('empresa_id', $empresaId)
            ->where('cantidad', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($dias))
            ->orderBy('fecha_vencimiento')
            ->limit($limite)
            ->get();
    }

    public function contarAlertas(int $empresaId, int $dias = 30): int
    {
        return ProductoLote::where('empresa_id', $empresaId)
            ->where('cantidad', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($dias))
            ->count();
    }
}
