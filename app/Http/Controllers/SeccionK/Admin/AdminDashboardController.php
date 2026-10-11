<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKParticipacion;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalPromociones = SeccionKPromocion::activos()->count();
        $participanCuotas = SeccionKPromocion::activos()->where('participa_cuotas', true)->count();
        $totalEventos = SeccionKEvento::eventos()->activos()->count();
        $totalCuotas = SeccionKEvento::cuotasMensuales()->activos()->count();

        $pagosCuotas = SeccionKParticipacion::query()
            ->where('pago', true)
            ->whereHas('evento', function ($evento) {
                $evento->where('tipo', 'cuota_mensual')->where('activo', true);
            });

        $cuotasPagadasCount = (clone $pagosCuotas)->count();
        $montoRecaudadoCuotas = (clone $pagosCuotas)->sum('monto_aportado');
        $promocionesQuePagaron = (clone $pagosCuotas)->distinct('promocion_id')->count('promocion_id');
        $esperadosCuotas = $participanCuotas * $totalCuotas;

        $eventosRecientes = SeccionKEvento::eventos()
            ->activos()
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();

        $resumenMeses = SeccionKEvento::cuotasMensuales()
            ->activos()
            ->orderBy('fecha')
            ->get()
            ->map(function ($cuota) use ($participanCuotas) {
                $pagos = SeccionKParticipacion::where('evento_id', $cuota->id)->where('pago', true);
                $pagaron = (clone $pagos)->count();

                return [
                    'evento' => $cuota,
                    'pagaron' => $pagaron,
                    'faltan' => max($participanCuotas - $pagaron, 0),
                    'recaudado' => (clone $pagos)->sum('monto_aportado'),
                ];
            });

        return view('seccionk.admin.dashboard', compact(
            'totalPromociones',
            'participanCuotas',
            'totalEventos',
            'totalCuotas',
            'cuotasPagadasCount',
            'montoRecaudadoCuotas',
            'promocionesQuePagaron',
            'esperadosCuotas',
            'eventosRecientes',
            'resumenMeses'
        ));
    }

    public function resumenCuotas()
    {
        $cuotas = SeccionKEvento::cuotasMensuales()->activos()->orderBy('fecha')->get();
        $promociones = SeccionKPromocion::activos()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get();

        $pagos = SeccionKParticipacion::query()
            ->whereIn('evento_id', $cuotas->pluck('id'))
            ->where('pago', true)
            ->get()
            ->groupBy('promocion_id');

        $filas = $promociones->map(function ($promo) use ($cuotas, $pagos) {
            $pagosPromo = ($pagos->get($promo->id) ?? collect())->keyBy('evento_id');
            $celdas = $cuotas->mapWithKeys(function ($cuota) use ($pagosPromo) {
                $pago = $pagosPromo->get($cuota->id);

                return [
                    $cuota->id => [
                        'pago' => (bool) $pago,
                        'monto' => $pago?->monto_aportado ?? 0,
                        'fecha' => $pago?->fecha_pago,
                        'transaccion' => $pago?->numero_transaccion,
                    ],
                ];
            });

            return [
                'promocion' => $promo,
                'celdas' => $celdas,
                'pagadas' => $celdas->where('pago', true)->count(),
                'recaudado' => $celdas->where('pago', true)->sum('monto'),
            ];
        });

        $totalesMes = $cuotas->mapWithKeys(function ($cuota) use ($filas) {
            $pagaron = $filas->filter(fn ($fila) => $fila['celdas'][$cuota->id]['pago'] ?? false);

            return [
                $cuota->id => [
                    'pagaron' => $pagaron->count(),
                    'recaudado' => $pagaron->sum(fn ($fila) => $fila['celdas'][$cuota->id]['monto']),
                ],
            ];
        });

        return view('seccionk.admin.resumen-cuotas', compact('cuotas', 'filas', 'totalesMes'));
    }
}
