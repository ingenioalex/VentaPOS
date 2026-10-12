<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKParticipacion;
use App\Support\SeccionKExport;
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
        $promocionesQuePagaron = (clone $pagosCuotas)->select('promocion_id')->distinct()->count('promocion_id');
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
        $datos = $this->datosResumenCuotas();

        return view('seccionk.admin.resumen-cuotas', $datos);
    }

    public function exportarResumen(Request $request)
    {
        $datos = $this->datosResumenCuotas();
        $cuotas = $datos['cuotas'];
        $filas = $datos['filas'];
        $totalesMes = $datos['totalesMes'];

        $encabezados = array_merge(
            ['Promoción', 'DNI'],
            $cuotas->map(fn ($cuota) => $cuota->mes_cuota)->all(),
            ['Cuotas pagadas', 'Total S/']
        );

        $filasExcel = $filas->map(function ($fila) use ($cuotas) {
            $celdas = [
                $fila['promocion']->nombreCompleto(),
                $fila['promocion']->dni,
            ];
            foreach ($cuotas as $cuota) {
                $celda = $fila['celdas'][$cuota->id];
                $celdas[] = $celda['pago'] ? 'Pagó S/ '.number_format($celda['monto'], 2) : 'Pendiente';
            }
            $celdas[] = $fila['pagadas'].'/'.$cuotas->count();
            $celdas[] = number_format($fila['recaudado'], 2);

            return $celdas;
        })->all();

        $totalFila = ['Total del mes', ''];
        foreach ($cuotas as $cuota) {
            $totalFila[] = $totalesMes[$cuota->id]['pagaron'].' pagaron / S/ '.number_format($totalesMes[$cuota->id]['recaudado'], 2);
        }
        $totalFila[] = '';
        $totalFila[] = number_format($filas->sum('recaudado'), 2);
        $filasExcel[] = $totalFila;

        if ($request->get('formato') === 'excel') {
            return SeccionKExport::excel('Resumen cuotas', $encabezados, $filasExcel, 'seccionk-resumen-cuotas');
        }

        $lineas = ['📊 *SECCIÓN K 2001 - RESUMEN DE CUOTAS*', 'Fecha: '.now()->format('d/m/Y H:i'), ''];
        $lineas[] = '*Por mes:*';
        foreach ($cuotas as $cuota) {
            $lineas[] = '• '.$cuota->mes_cuota.': '.$totalesMes[$cuota->id]['pagaron'].' pagaron · S/ '.number_format($totalesMes[$cuota->id]['recaudado'], 2);
        }
        $lineas[] = '';
        foreach ($filas as $fila) {
            $lineas[] = '👤 *'.$fila['promocion']->nombreCompleto().'*';
            $lineas[] = 'DNI '.$fila['promocion']->dni.' · '.$fila['pagadas'].'/'.$cuotas->count().' cuotas · S/ '.number_format($fila['recaudado'], 2);
            foreach ($cuotas as $cuota) {
                $celda = $fila['celdas'][$cuota->id];
                $estado = $celda['pago'] ? '✅ Pagó S/ '.number_format($celda['monto'], 2) : '⏳ Pendiente';
                $lineas[] = $cuota->mes_cuota.': '.$estado;
            }
            $lineas[] = '------------------------------';
        }
        $lineas[] = '*Total recaudado: S/ '.number_format($filas->sum('recaudado'), 2).'*';
        $texto = implode("\n", $lineas);

        if ($request->get('formato') === 'descargar') {
            return SeccionKExport::texto($texto, 'seccionk-resumen-cuotas-whatsapp');
        }

        return view('seccionk.admin.exportar-whatsapp', [
            'titulo' => 'Resumen de cuotas para WhatsApp',
            'texto' => $texto,
            'volver' => route('seccionk.admin.resumen.cuotas'),
            'descargar' => route('seccionk.admin.resumen.cuotas.exportar', ['formato' => 'descargar']),
        ]);
    }

    private function datosResumenCuotas(): array
    {
        $cuotas = SeccionKEvento::cuotasMensuales()->activos()->orderBy('fecha')->get();
        $promociones = SeccionKPromocion::activos()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get();

        $pagos = SeccionKParticipacion::query()
            ->whereIn('evento_id', $cuotas->pluck('id')->filter())
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

        return compact('cuotas', 'filas', 'totalesMes');
    }
}
