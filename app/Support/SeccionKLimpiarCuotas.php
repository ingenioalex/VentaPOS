<?php

namespace App\Support;

use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeccionKLimpiarCuotas
{
    public static function ejecutar(): array
    {
        $resultado = [
            'meses' => 0,
            'eliminados' => 0,
            'pagos_movidos' => 0,
        ];

        DB::transaction(function () use (&$resultado) {
            $cuotas = SeccionKEvento::cuotasMensuales()
                ->with('participaciones')
                ->orderBy('id')
                ->get()
                ->groupBy(fn (SeccionKEvento $cuota) => $cuota->claveMes())
                ->filter(fn ($grupo) => $grupo->count() > 1 && filled($grupo->first()->claveMes()));

            $resultado['meses'] = $cuotas->count();

            foreach ($cuotas as $grupo) {
                $conservada = self::elegirConservada($grupo);
                $conservada->mes_cuota = self::tituloMes($conservada->claveMes())
                    ?: SeccionKEvento::etiquetaDesdeFecha($conservada->fecha);
                $conservada->activo = true;
                $conservada->save();

                foreach ($grupo->where('id', '!=', $conservada->id) as $duplicada) {
                    $resultado['pagos_movidos'] += self::moverPagos($conservada, $duplicada);
                    $duplicada->participaciones()->delete();
                    $duplicada->delete();
                    $resultado['eliminados']++;
                }
            }
        });

        self::asegurarIndiceUnico();

        return $resultado;
    }

    private static function tituloMes(string $clave): ?string
    {
        if (! preg_match('/^(\p{L}+)\s+(\d{4})$/u', trim($clave), $partes)) {
            return null;
        }

        foreach (SeccionKEvento::mesesEspanol() as $nombre) {
            if (mb_strtolower($nombre) === mb_strtolower($partes[1])) {
                return $nombre.' '.$partes[2];
            }
        }

        return mb_convert_case($partes[1], MB_CASE_TITLE, 'UTF-8').' '.$partes[2];
    }

    private static function elegirConservada($grupo): SeccionKEvento
    {
        return $grupo->sort(function (SeccionKEvento $a, SeccionKEvento $b) {
            $pagosA = $a->participaciones->where('pago', true)->count();
            $pagosB = $b->participaciones->where('pago', true)->count();
            if ($pagosA !== $pagosB) {
                return $pagosB <=> $pagosA;
            }

            if ((float) $a->monto !== (float) $b->monto) {
                return (float) $b->monto <=> (float) $a->monto;
            }

            return $a->id <=> $b->id;
        })->first();
    }

    private static function moverPagos(SeccionKEvento $conservada, SeccionKEvento $duplicada): int
    {
        $movidos = 0;
        $existentes = $conservada->participaciones->keyBy('promocion_id');

        foreach ($duplicada->participaciones as $pago) {
            $actual = $existentes->get($pago->promocion_id);
            if (! $actual) {
                $pago->evento_id = $conservada->id;
                $pago->save();
                $existentes->put($pago->promocion_id, $pago);
                $conservada->setRelation('participaciones', $existentes->values());
                $movidos++;
                continue;
            }

            $cambios = [];
            if ($pago->pago && ! $actual->pago) {
                $cambios = [
                    'participo' => $pago->participo || $actual->participo,
                    'pago' => true,
                    'monto_aportado' => $pago->monto_aportado,
                    'numero_transaccion' => $pago->numero_transaccion ?: $actual->numero_transaccion,
                    'imagen_sustento' => $pago->imagen_sustento ?: $actual->imagen_sustento,
                    'fecha_pago' => $pago->fecha_pago ?: $actual->fecha_pago,
                    'observaciones' => $pago->observaciones ?: $actual->observaciones,
                ];
            } elseif ($pago->pago && $actual->pago) {
                if ((float) $pago->monto_aportado > (float) $actual->monto_aportado) {
                    $cambios['monto_aportado'] = $pago->monto_aportado;
                }
                if (! $actual->numero_transaccion && $pago->numero_transaccion) {
                    $cambios['numero_transaccion'] = $pago->numero_transaccion;
                }
                if (! $actual->imagen_sustento && $pago->imagen_sustento) {
                    $cambios['imagen_sustento'] = $pago->imagen_sustento;
                }
                if (! $actual->observaciones && $pago->observaciones) {
                    $cambios['observaciones'] = $pago->observaciones;
                }
            } elseif ($pago->observaciones && ! $actual->observaciones) {
                $cambios['observaciones'] = $pago->observaciones;
            }

            if ($cambios) {
                $actual->update($cambios);
                $movidos++;
            }

            $pago->delete();
        }

        return $movidos;
    }

    private static function asegurarIndiceUnico(): void
    {
        try {
            if (Schema::hasIndex('seccionk_eventos', 'seccionk_eventos_tipo_mes_cuota_unique')) {
                return;
            }
        } catch (\Throwable) {
        }

        try {
            Schema::table('seccionk_eventos', function (Blueprint $table) {
                $table->unique(['tipo', 'mes_cuota'], 'seccionk_eventos_tipo_mes_cuota_unique');
            });
        } catch (\Throwable) {
        }
    }
}
