<?php

namespace App\Support;

use App\Models\SeccionKDeclaracion;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use Illuminate\Support\Collection;

class SeccionKEstadisticas
{
    public static function comunidad(): array
    {
        $totalPromociones = SeccionKPromocion::activos()->count();
        $participandoCuotas = SeccionKPromocion::activos()->where('participa_cuotas', true)->count();

        $pagosCuotas = self::pagosDeTipo('cuota_mensual');
        $pagosEventos = self::pagosDeTipo('evento');
        $participacionesEventos = self::participacionesDeTipo('evento');

        $idsParticipandoEventos = (clone $participacionesEventos)->pluck('promocion_id')->unique();

        $participando = SeccionKPromocion::activos()
            ->where(function ($query) use ($idsParticipandoEventos) {
                $query->where('participa_cuotas', true)
                    ->orWhereIn('id', $idsParticipandoEventos);
            })
            ->count();

        return [
            'total_promociones' => $totalPromociones,
            'participando' => $participando,
            'participando_cuotas' => $participandoCuotas,
            'participando_eventos' => $idsParticipandoEventos->count(),
            'aportando' => self::contarPromocionesQuePagaron(),
            'aportando_cuotas' => (clone $pagosCuotas)->select('promocion_id')->distinct()->count('promocion_id'),
            'aportando_eventos' => (clone $pagosEventos)->select('promocion_id')->distinct()->count('promocion_id'),
            'monto_cuotas' => (float) (clone $pagosCuotas)->sum('monto_aportado'),
            'monto_eventos' => (float) (clone $pagosEventos)->sum('monto_aportado'),
            'total_eventos' => SeccionKEvento::eventos()->activos()->count(),
            'total_cuotas' => SeccionKEvento::cuotasMensuales()->activos()->count(),
        ];
    }

    public static function resumenEventos(int $totalPromociones): Collection
    {
        $eventos = SeccionKEvento::eventos()->activos()->orderByDesc('fecha')->orderByDesc('id')->get();
        if ($eventos->isEmpty()) {
            return collect();
        }

        $partes = SeccionKParticipacion::dePromocionesActivas()
            ->whereIn('evento_id', $eventos->pluck('id'))
            ->get()
            ->groupBy('evento_id');

        return $eventos->map(function (SeccionKEvento $evento) use ($partes, $totalPromociones) {
            $rows = $partes->get($evento->id) ?? collect();
            $pagos = $rows->where('pago', true);

            return [
                'evento' => $evento,
                'participantes' => $rows->where('participo', true)->count(),
                'aportantes' => $pagos->count(),
                'recaudado' => (float) $pagos->sum('monto_aportado'),
                'total_promociones' => $totalPromociones,
            ];
        });
    }

    public static function resumenPorEventos(Collection $eventos): Collection
    {
        $ids = $eventos->pluck('id')->filter();
        if ($ids->isEmpty()) {
            return collect();
        }

        $declaraciones = SeccionKDeclaracion::whereIn('evento_id', $ids)->get()->keyBy('evento_id');
        $eventos->each(function ($evento) use ($declaraciones) {
            if ($evento instanceof SeccionKEvento) {
                $evento->setRelation('declaracion', $declaraciones->get($evento->id));
            }
        });
        $totalPromociones = SeccionKPromocion::activos()->count();
        $partes = SeccionKParticipacion::dePromocionesActivas()
            ->whereIn('evento_id', $ids)
            ->get()
            ->groupBy('evento_id');

        return $eventos->mapWithKeys(function (SeccionKEvento $evento) use ($partes, $totalPromociones) {
            $rows = $partes->get($evento->id) ?? collect();
            $pagos = $rows->where('pago', true);

            $recaudado = (float) $pagos->sum('monto_aportado');
            $utilizado = $evento->montoUtilizado();

            return [
                $evento->id => [
                    'participantes' => $rows->where('participo', true)->count(),
                    'aportantes' => $pagos->count(),
                    'recaudado' => $recaudado,
                    'utilizado' => $utilizado,
                    'saldo' => $recaudado - $utilizado,
                    'cerrado' => $evento->estaCerrado(),
                    'total_promociones' => $totalPromociones,
                ],
            ];
        });
    }

    public static function detalleEvento(SeccionKEvento $evento): array
    {
        $promociones = SeccionKPromocion::activos()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $participaciones = SeccionKParticipacion::query()
            ->dePromocionesActivas()
            ->where('evento_id', $evento->id)
            ->get()
            ->keyBy('promocion_id');

        $filas = $promociones->map(function (SeccionKPromocion $promo) use ($participaciones) {
            $part = $participaciones->get($promo->id);

            return [
                'promocion' => $promo,
                'participo' => (bool) ($part?->participo),
                'pago' => (bool) ($part?->pago),
                'monto' => (float) ($part?->monto_aportado ?? 0),
                'fecha_pago' => $part?->fecha_pago,
                'transaccion' => $part?->numero_transaccion,
                'observaciones' => $part?->observaciones,
            ];
        });

        $participantes = $filas->where('participo', true);
        $aportantes = $filas->where('pago', true);
        $recaudado = (float) $aportantes->sum('monto');
        $evento->loadMissing('declaracion');
        $utilizado = $evento->montoUtilizado();

        return [
            'evento' => $evento,
            'filas' => $filas,
            'total_promociones' => $promociones->count(),
            'participantes' => $participantes->count(),
            'aportantes' => $aportantes->count(),
            'recaudado' => $recaudado,
            'utilizado' => $utilizado,
            'saldo' => $recaudado - $utilizado,
            'cerrado' => $evento->estaCerrado(),
        ];
    }

    public static function declaracionFondos(): array
    {
        $cuotas = SeccionKEvento::cuotasMensuales()->activos()->with('declaracion')->orderBy('fecha')->get();
        $eventos = SeccionKEvento::eventos()->activos()->with('declaracion')->orderByDesc('fecha')->get();
        $resumenCuotas = self::resumenPorEventos($cuotas);
        $resumenEventos = self::resumenPorEventos($eventos);

        $filasCuotas = $cuotas->map(fn (SeccionKEvento $item) => self::filaDeclaracion($item, $resumenCuotas->get($item->id)));
        $filasEventos = $eventos->map(fn (SeccionKEvento $item) => self::filaDeclaracion($item, $resumenEventos->get($item->id)));

        $totalesCuotas = self::totalesDeclaracion($filasCuotas);
        $totalesEventos = self::totalesDeclaracion($filasEventos);

        return [
            'filas_cuotas' => $filasCuotas,
            'filas_eventos' => $filasEventos,
            'cuotas' => $totalesCuotas,
            'eventos' => $totalesEventos,
            'total' => [
                'recaudado' => $totalesCuotas['recaudado'] + $totalesEventos['recaudado'],
                'utilizado' => $totalesCuotas['utilizado'] + $totalesEventos['utilizado'],
                'saldo' => $totalesCuotas['saldo'] + $totalesEventos['saldo'],
                'cerrados' => $totalesCuotas['cerrados'] + $totalesEventos['cerrados'],
                'abiertos' => $totalesCuotas['abiertos'] + $totalesEventos['abiertos'],
            ],
        ];
    }

    private static function filaDeclaracion(SeccionKEvento $evento, ?array $resumen): array
    {
        $resumen = $resumen ?? ['recaudado' => 0, 'utilizado' => 0, 'saldo' => 0, 'cerrado' => false, 'aportantes' => 0];

        return [
            'evento' => $evento,
            'aportantes' => $resumen['aportantes'] ?? 0,
            'recaudado' => (float) ($resumen['recaudado'] ?? 0),
            'utilizado' => (float) ($resumen['utilizado'] ?? 0),
            'saldo' => (float) ($resumen['saldo'] ?? 0),
            'cerrado' => (bool) ($resumen['cerrado'] ?? false),
            'fecha_cierre' => $evento->declaracion?->fecha_cierre,
            'observaciones' => $evento->declaracion?->observaciones,
        ];
    }

    private static function totalesDeclaracion(Collection $filas): array
    {
        return [
            'recaudado' => (float) $filas->sum('recaudado'),
            'utilizado' => (float) $filas->sum('utilizado'),
            'saldo' => (float) $filas->sum('saldo'),
            'cerrados' => $filas->where('cerrado', true)->count(),
            'abiertos' => $filas->where('cerrado', false)->count(),
        ];
    }

    private static function pagosDeTipo(string $tipo)
    {
        return SeccionKParticipacion::query()
            ->dePromocionesActivas()
            ->where('pago', true)
            ->whereHas('evento', function ($evento) use ($tipo) {
                $evento->where('tipo', $tipo)->where('activo', true);
            });
    }

    private static function participacionesDeTipo(string $tipo)
    {
        return SeccionKParticipacion::query()
            ->dePromocionesActivas()
            ->where('participo', true)
            ->whereHas('evento', function ($evento) use ($tipo) {
                $evento->where('tipo', $tipo)->where('activo', true);
            });
    }

    private static function contarPromocionesQuePagaron(): int
    {
        return SeccionKParticipacion::query()
            ->dePromocionesActivas()
            ->where('pago', true)
            ->whereHas('evento', function ($evento) {
                $evento->where('activo', true);
            })
            ->select('promocion_id')
            ->distinct()
            ->count('promocion_id');
    }
}
