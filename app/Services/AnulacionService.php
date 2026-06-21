<?php

namespace App\Services;

use App\Models\Comprobante;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AnulacionService
{
    public function __construct(private SunatService $sunat) {}

    public function validarSupervisor(int $empresaId, string $usuario, string $password): ?Usuario
    {
        $supervisor = Usuario::where('empresa_id', $empresaId)
            ->where('usuario', $usuario)
            ->where('activo', true)
            ->whereIn('rol', ['dueno', 'supervisor', 'super_admin'])
            ->first();

        if (! $supervisor || ! Hash::check($password, $supervisor->password)) {
            return null;
        }

        return $supervisor;
    }

    public function anular(Comprobante $comprobante, Usuario $supervisor, string $motivo): Comprobante
    {
        if ($comprobante->estado !== 'emitido') {
            throw new RuntimeException('Solo se pueden anular comprobantes emitidos.');
        }

        if (trim($motivo) === '') {
            throw new RuntimeException('Debe indicar el motivo de anulación.');
        }

        $comprobante = DB::transaction(function () use ($comprobante, $supervisor, $motivo) {
            $comprobante->load('detalles');

            foreach ($comprobante->detalles as $detalle) {
                $producto = Producto::where('empresa_id', $comprobante->empresa_id)
                    ->where('id', $detalle->producto_id)
                    ->lockForUpdate()
                    ->first();

                if (! $producto) {
                    continue;
                }

                $stockAnterior = (float) $producto->stock_disponible;
                $stockNuevo = $stockAnterior + (float) $detalle->cantidad;
                $producto->update(['stock_disponible' => $stockNuevo]);

                InventarioMovimiento::create([
                    'empresa_id' => $comprobante->empresa_id,
                    'producto_id' => $producto->id,
                    'usuario_id' => $supervisor->id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'referencia' => 'ANUL-'.$comprobante->numero_interno,
                ]);
            }

            $comprobante->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $motivo,
                'anulado_por_usuario_id' => $supervisor->id,
                'anulado_en' => now(),
                'observaciones' => trim(($comprobante->observaciones ?? '')."\nAnulado por {$supervisor->nombre}: {$motivo}"),
            ]);

            return $comprobante->fresh();
        });

        $this->sunat->comunicarAnulacion($comprobante);

        return $comprobante;
    }
}
