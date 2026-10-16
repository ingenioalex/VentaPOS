<?php

namespace App\Support;

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

        $totalPromociones = SeccionKPromocion::activos()->count();
        $partes = SeccionKParticipacion::dePromocionesActivas()
            ->whereIn('evento_id', $ids)
            ->get()
            ->groupBy('evento_id');

        return $eventos->mapWithKeys(function (SeccionKEvento $evento) use ($partes, $totalPromociones) {
            $rows = $partes->get($evento->id) ?? collect();
            $pagos = $rows->where('pago', true);

            return [
                $evento->id => [
                    'participantes' => $rows->where('participo', true)->count(),
                    'aportantes' => $pagos->count(),
                    'recaudado' => (float) $pagos->sum('monto_aportado'),
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

        return [
            'evento' => $evento,
            'filas' => $filas,
            'total_promociones' => $promociones->count(),
            'participantes' => $participantes->count(),
            'aportantes' => $aportantes->count(),
            'recaudado' => (float) $aportantes->sum('monto'),
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
