<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompraService
{
    private const TASA_IGV = 0.18;

    public function __construct(private InventarioService $inventario) {}

    /**
     * @param  array<string, mixed>  $cabecera
     * @param  list<array{producto_id: int, cantidad: float, costo_unitario: float}>  $lineas
     */
    public function registrar(Usuario $usuario, array $cabecera, array $lineas, bool $recibir = false): Compra
    {
        if ($lineas === []) {
            throw new RuntimeException('Agregue al menos un producto a la compra.');
        }

        $proveedor = Proveedor::where('empresa_id', $usuario->empresa_id)
            ->where('id', $cabecera['proveedor_id'])
            ->where('activo', true)
            ->firstOrFail();

        return DB::transaction(function () use ($usuario, $cabecera, $lineas, $recibir, $proveedor) {
            $totales = $this->calcularTotales($usuario->empresa_id, $lineas);

            $compra = Compra::create([
                'empresa_id' => $usuario->empresa_id,
                'proveedor_id' => $proveedor->id,
                'usuario_id' => $usuario->id,
                'tipo_documento' => $cabecera['tipo_documento'],
                'serie' => $cabecera['serie'] ?: null,
                'numero_documento' => $cabecera['numero_documento'],
                'fecha_emision' => $cabecera['fecha_emision'],
                'fecha_recepcion' => $cabecera['fecha_recepcion'] ?? null,
                'subtotal' => $totales['subtotal'],
                'valor_igv' => $totales['valor_igv'],
                'total' => $totales['total'],
                'estado' => 'borrador',
                'observaciones' => $cabecera['observaciones'] ?? null,
            ]);

            foreach ($totales['detalles'] as $detalle) {
                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $detalle['producto_id'],
                    'descripcion' => $detalle['descripcion'],
                    'cantidad' => $detalle['cantidad'],
                    'costo_unitario' => $detalle['costo_unitario'],
                    'afecto_igv' => $detalle['afecto_igv'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }

            if ($recibir) {
                $this->recibirInterno($compra, $usuario);
            }

            return $compra->fresh(['detalles.producto', 'proveedor', 'usuario']);
        });
    }

    public function recibir(Compra $compra, Usuario $usuario): Compra
    {
        return DB::transaction(function () use ($compra, $usuario) {
            $this->recibirInterno($compra, $usuario);

            return $compra->fresh(['detalles.producto', 'proveedor', 'usuario']);
        });
    }

    public function anular(Compra $compra, Usuario $usuario, string $motivo): Compra
    {
        if ($compra->estado === 'anulado') {
            throw new RuntimeException('La compra ya está anulada.');
        }

        return DB::transaction(function () use ($compra, $usuario, $motivo) {
            if ($compra->estado === 'recibido') {
                $this->revertirStock($compra, $usuario);
            }

            $compra->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $motivo,
                'anulado_por_usuario_id' => $usuario->id,
                'anulado_en' => now(),
            ]);

            return $compra->fresh(['detalles.producto', 'proveedor', 'usuario', 'anuladoPor']);
        });
    }

    /**
     * @param  list<array{producto_id: int, cantidad: float, costo_unitario: float}>  $lineas
     * @return array{subtotal: float, valor_igv: float, total: float, detalles: list<array<string, mixed>>}
     */
    public function calcularTotales(int $empresaId, array $lineas): array
    {
        $subtotal = 0;
        $valorIgv = 0;
        $detalles = [];

        foreach ($lineas as $linea) {
            $producto = Producto::where('empresa_id', $empresaId)
                ->where('id', $linea['producto_id'])
                ->where('activo', true)
                ->firstOrFail();

            $cantidad = (float) $linea['cantidad'];
            $costoUnitario = (float) $linea['costo_unitario'];

            if ($cantidad <= 0) {
                throw new RuntimeException("Cantidad inválida para {$producto->nombre}.");
            }
            if ($costoUnitario < 0) {
                throw new RuntimeException("Costo inválido para {$producto->nombre}.");
            }

            $lineaSubtotal = round($cantidad * $costoUnitario, 2);
            $lineaIgv = $producto->afecto_igv
                ? round($lineaSubtotal * self::TASA_IGV, 2)
                : 0;

            $subtotal += $lineaSubtotal;
            $valorIgv += $lineaIgv;

            $detalles[] = [
                'producto_id' => $producto->id,
                'descripcion' => $producto->nombre,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'afecto_igv' => $producto->afecto_igv,
                'subtotal' => $lineaSubtotal,
            ];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'valor_igv' => round($valorIgv, 2),
            'total' => round($subtotal + $valorIgv, 2),
            'detalles' => $detalles,
        ];
    }

    private function recibirInterno(Compra $compra, Usuario $usuario): void
    {
        if ($compra->estado !== 'borrador') {
            throw new RuntimeException('Solo se pueden recibir compras en borrador.');
        }

        $compra->load('detalles.producto', 'proveedor');
        $referencia = $compra->etiquetaDocumento().' — '.$compra->proveedor->razon_social;

        foreach ($compra->detalles as $detalle) {
            $producto = Producto::where('empresa_id', $compra->empresa_id)
                ->where('id', $detalle->producto_id)
                ->lockForUpdate()
                ->firstOrFail();

            $costoAnterior = (float) $producto->costo_compra;
            $costoNuevo = (float) $detalle->costo_unitario;

            $producto->update(['costo_compra' => $costoNuevo]);

            if (abs($costoAnterior - $costoNuevo) > 0.0001) {
                ProductoPrecio::create([
                    'empresa_id' => $compra->empresa_id,
                    'producto_id' => $producto->id,
                    'usuario_id' => $usuario->id,
                    'precio_anterior' => $producto->precio_venta,
                    'precio_nuevo' => $producto->precio_venta,
                    'costo_anterior' => $costoAnterior,
                    'costo_nuevo' => $costoNuevo,
                ]);
            }

            $this->inventario->registrarEntrada(
                $usuario,
                $producto->id,
                (float) $detalle->cantidad,
                $referencia,
                $compra->id
            );
        }

        $compra->update([
            'estado' => 'recibido',
            'fecha_recepcion' => $compra->fecha_recepcion ?? today(),
            'recibido_en' => now(),
        ]);
    }

    private function revertirStock(Compra $compra, Usuario $usuario): void
    {
        $compra->load('detalles');
        $referencia = 'Anulación '.$compra->etiquetaDocumento();

        foreach ($compra->detalles as $detalle) {
            $this->inventario->registrarSalida(
                $usuario,
                $detalle->producto_id,
                (float) $detalle->cantidad,
                $referencia
            );
        }

        InventarioMovimiento::where('compra_id', $compra->id)->update(['compra_id' => null]);
    }
}
