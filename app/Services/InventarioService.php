<?php

namespace App\Services;

use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventarioService
{
    public function registrarEntrada(Usuario $usuario, int $productoId, float $cantidad, ?string $referencia = null, ?int $compraId = null): InventarioMovimiento
    {
        if ($cantidad <= 0) {
            throw new RuntimeException('La cantidad debe ser mayor a cero.');
        }

        return $this->aplicarMovimiento($usuario, $productoId, $cantidad, 'entrada', $referencia, $compraId);
    }

    public function registrarAjuste(Usuario $usuario, int $productoId, float $stockNuevo, ?string $referencia = null): InventarioMovimiento
    {
        if ($stockNuevo < 0) {
            throw new RuntimeException('El stock no puede ser negativo.');
        }

        return DB::transaction(function () use ($usuario, $productoId, $stockNuevo, $referencia) {
            $producto = $this->bloquearProducto($usuario->empresa_id, $productoId);
            $stockAnterior = (float) $producto->stock_disponible;
            $diferencia = round($stockNuevo - $stockAnterior, 4);

            if (abs($diferencia) < 0.0001) {
                throw new RuntimeException('El stock indicado es igual al actual.');
            }

            $producto->update(['stock_disponible' => $stockNuevo]);

            return InventarioMovimiento::create([
                'empresa_id' => $usuario->empresa_id,
                'producto_id' => $producto->id,
                'usuario_id' => $usuario->id,
                'tipo_movimiento' => 'ajuste',
                'cantidad' => abs($diferencia),
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia' => $referencia ?: 'Ajuste manual',
            ]);
        });
    }

    public function registrarSalida(Usuario $usuario, int $productoId, float $cantidad, ?string $referencia = null): InventarioMovimiento
    {
        if ($cantidad <= 0) {
            throw new RuntimeException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($usuario, $productoId, $cantidad, $referencia) {
            $producto = $this->bloquearProducto($usuario->empresa_id, $productoId);
            $stockAnterior = (float) $producto->stock_disponible;

            if ($stockAnterior < $cantidad) {
                throw new RuntimeException("Stock insuficiente. Disponible: {$stockAnterior}");
            }

            $stockNuevo = round($stockAnterior - $cantidad, 4);
            $producto->update(['stock_disponible' => $stockNuevo]);

            return InventarioMovimiento::create([
                'empresa_id' => $usuario->empresa_id,
                'producto_id' => $producto->id,
                'usuario_id' => $usuario->id,
                'tipo_movimiento' => 'salida',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia' => $referencia ?: 'Salida manual',
            ]);
        });
    }

    /** @return array<string, mixed> */
    public function resumen(int $empresaId): array
    {
        return app(ReporteService::class)->resumenInventario($empresaId);
    }

    private function aplicarMovimiento(
        Usuario $usuario,
        int $productoId,
        float $cantidad,
        string $tipo,
        ?string $referencia,
        ?int $compraId = null,
    ): InventarioMovimiento {
        return DB::transaction(function () use ($usuario, $productoId, $cantidad, $tipo, $referencia, $compraId) {
            $producto = $this->bloquearProducto($usuario->empresa_id, $productoId);
            $stockAnterior = (float) $producto->stock_disponible;
            $stockNuevo = round($stockAnterior + $cantidad, 4);

            $producto->update(['stock_disponible' => $stockNuevo]);

            return InventarioMovimiento::create([
                'empresa_id' => $usuario->empresa_id,
                'producto_id' => $producto->id,
                'usuario_id' => $usuario->id,
                'tipo_movimiento' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia' => $referencia,
                'compra_id' => $compraId,
            ]);
        });
    }

    private function bloquearProducto(int $empresaId, int $productoId): Producto
    {
        return Producto::where('empresa_id', $empresaId)
            ->where('id', $productoId)
            ->where('activo', true)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
