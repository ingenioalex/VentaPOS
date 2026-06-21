<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\VentaSuspendida;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VentaSuspendidaService
{
    public function suspender(Usuario $usuario, array $datos): VentaSuspendida
    {
        if ($usuario->empresa_id === null) {
            throw new RuntimeException('Usuario sin empresa.');
        }

        $lineas = $datos['lineas'] ?? [];
        if (count($lineas) === 0) {
            throw new RuntimeException('No hay productos para suspender.');
        }

        return DB::transaction(function () use ($usuario, $datos, $lineas) {
            $codigo = $this->generarCodigoHold($usuario->empresa_id);

            return VentaSuspendida::create([
                'empresa_id' => $usuario->empresa_id,
                'usuario_id' => $usuario->id,
                'codigo_hold' => $codigo,
                'datos' => $datos,
                'total' => $datos['total'] ?? 0,
            ]);
        });
    }

    public function recuperar(Usuario $usuario, string $codigoHold): array
    {
        $hold = VentaSuspendida::where('empresa_id', $usuario->empresa_id)
            ->where('codigo_hold', $codigoHold)
            ->first();

        if (! $hold) {
            throw new RuntimeException('No se encontró venta con ese código HOLD.');
        }

        $datos = $hold->datos;
        $hold->delete();

        return $datos;
    }

    public function listar(int $empresaId): array
    {
        return VentaSuspendida::where('empresa_id', $empresaId)
            ->orderByDesc('creado_en')
            ->limit(20)
            ->get(['id', 'codigo_hold', 'total', 'creado_en', 'usuario_id'])
            ->toArray();
    }

    private function generarCodigoHold(int $empresaId): string
    {
        do {
            $base = str_pad((string) ($empresaId % 1000), 3, '0', STR_PAD_LEFT);
            $seq = str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            $codigo = '1'.$base.$seq;
        } while (VentaSuspendida::where('codigo_hold', $codigo)->exists());

        return $codigo;
    }
}
