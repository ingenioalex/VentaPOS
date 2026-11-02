<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKPromocion;
use App\Models\SeccionKParticipacion;
use App\Support\SeccionKEsquema;
use App\Support\SeccionKEstadisticas;
use App\Support\SeccionKExport;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        SeccionKEsquema::asegurarDeclaraciones();

        $totalPromociones = SeccionKPromocion::activos()->count();
        $participanCuotas = SeccionKPromocion::activos()->where('participa_cuotas', true)->count();
        $totalEventos = SeccionKEvento::eventos()->activos()->count();
        $totalCuotas = SeccionKEvento::cuotasMensuales()->activos()->count();

        $pagosCuotas = SeccionKParticipacion::query()
            ->dePromocionesActivas()
            ->where('pago', true)
            ->whereHas('evento', function ($evento) {
                $evento->where('tipo', 'cuota_mensual')->where('activo', true);
            });

        $cuotasPagadasCount = (clone $pagosCuotas)->count();
        $montoRecaudadoCuotas = (clone $pagosCuotas)->sum('monto_aportado');
        $promocionesQuePagaron = (clone $pagosCuotas)->select('promocion_id')->distinct()->count('promocion_id');
        $esperadosCuotas = $participanCuotas * $totalCuotas;

        $resumenEventos = SeccionKEstadisticas::resumenEventos($totalPromociones);
        $montoRecaudadoEventos = $resumenEventos->sum('recaudado');
        $montoUtilizadoEventos = $resumenEventos->sum('utilizado');
        $montoSaldoEventos = $montoRecaudadoEventos - $montoUtilizadoEventos;
        $participantesEventos = $resumenEventos->sum('participantes');
        $aportantesEventos = SeccionKEstadisticas::comunidad()['aportando_eventos'];

        $resumenMeses = SeccionKEvento::cuotasMensuales()
            ->activos()
            ->orderBy('fecha')
            ->get()
            ->map(function ($cuota) use ($participanCuotas) {
                $pagos = SeccionKParticipacion::dePromocionesActivas()
                    ->where('evento_id', $cuota->id)
                    ->where('pago', true);
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
            'montoRecaudadoEventos',
            'montoUtilizadoEventos',
            'montoSaldoEventos',
            'participantesEventos',
            'aportantesEventos',
            'promocionesQuePagaron',
            'esperadosCuotas',
            'resumenEventos',
            'resumenMeses'
        ));
    }

    public function exportarDashboard(Request $request)
    {
        SeccionKEsquema::asegurarDeclaraciones();

        $orden = $request->get('orden', 'fecha') === 'nombre' ? 'nombre' : 'fecha';
        $datos = $this->datosExportarDashboard($orden);
        $params = ['orden' => $orden, 'formato' => 'descargar'];

        if ($request->get('formato') === 'excel') {
            return $this->excelDashboard($datos);
        }

        $texto = $this->textoDashboard($datos);

        if ($request->get('formato') === 'descargar') {
            return SeccionKExport::texto($texto, 'seccionk-dashboard-whatsapp');
        }

        return view('seccionk.admin.exportar-whatsapp', [
            'titulo' => 'Dashboard Sección K para WhatsApp',
            'texto' => $texto,
            'volver' => route('seccionk.admin.dashboard'),
            'descargar' => route('seccionk.admin.dashboard.exportar', $params),
        ]);
    }

    public function resumenCuotas()
    {
        $datos = $this->datosResumenCuotas();

        return view('seccionk.admin.resumen-cuotas', $datos);
    }

    public function exportarResumen(Request $request)
    {
        $datos = $this->datosResumenCuotas();
        $todas = $datos['cuotas'];
        $filas = $datos['filas'];
        $totalesMes = $datos['totalesMes'];
        $orden = $request->get('orden', 'mes') === 'promocion' ? 'promocion' : 'mes';

        $ids = collect($request->input('meses', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $cuotas = $todas->whereIn('id', $ids)->values();

        if ($ids->isEmpty() || $cuotas->isEmpty()) {
            return redirect()
                ->route('seccionk.admin.resumen.cuotas')
                ->with('error', 'Selecciona al menos un mes para exportar.');
        }

        $params = [
            'formato' => 'descargar',
            'orden' => $orden,
            'meses' => $cuotas->pluck('id')->all(),
        ];

        if ($request->get('formato') === 'excel') {
            return $orden === 'mes'
                ? $this->excelPorMes($cuotas, $filas, $totalesMes)
                : $this->excelPorPromocion($cuotas, $filas, $totalesMes);
        }

        $texto = $orden === 'mes'
            ? $this->textoPorMes($cuotas, $filas, $totalesMes)
            : $this->textoPorPromocion($cuotas, $filas, $totalesMes);

        if ($request->get('formato') === 'descargar') {
            return SeccionKExport::texto($texto, 'seccionk-resumen-cuotas-whatsapp');
        }

        return view('seccionk.admin.exportar-whatsapp', [
            'titulo' => 'Resumen de cuotas para WhatsApp',
            'texto' => $texto,
            'volver' => route('seccionk.admin.resumen.cuotas'),
            'descargar' => route('seccionk.admin.resumen.cuotas.exportar', $params),
        ]);
    }

    private function excelPorMes($cuotas, $filas, $totalesMes)
    {
        $filasExcel = [];
        foreach ($cuotas as $cuota) {
            foreach ($filas as $fila) {
                $celda = $fila['celdas'][$cuota->id];
                $filasExcel[] = [
                    $cuota->mesCorto(),
                    $fila['promocion']->nombreCompleto(),
                    $fila['promocion']->dni,
                    $celda['pago'] ? 'Pagó' : 'Pendiente',
                    $celda['pago'] ? number_format($celda['monto'], 2) : '0.00',
                ];
            }
            $filasExcel[] = [
                $cuota->mesCorto().' TOTAL',
                $totalesMes[$cuota->id]['pagaron'].' pagaron',
                '',
                '',
                number_format($totalesMes[$cuota->id]['recaudado'], 2),
            ];
        }

        return SeccionKExport::excel(
            'Por mes',
            ['Mes', 'Promoción', 'DNI', 'Estado', 'Monto S/'],
            $filasExcel,
            'seccionk-cuotas-por-mes'
        );
    }

    private function excelPorPromocion($cuotas, $filas, $totalesMes)
    {
        $encabezados = array_merge(
            ['Promoción', 'DNI'],
            $cuotas->map(fn ($cuota) => $cuota->mesCorto())->all(),
            ['Cuotas pagadas', 'Total S/']
        );

        $filasExcel = $filas->map(function ($fila) use ($cuotas) {
            $celdas = [
                $fila['promocion']->nombreCompleto(),
                $fila['promocion']->dni,
            ];
            $pagadas = 0;
            $recaudado = 0;
            foreach ($cuotas as $cuota) {
                $celda = $fila['celdas'][$cuota->id];
                $celdas[] = $celda['pago'] ? 'Pagó S/ '.number_format($celda['monto'], 2) : 'Pendiente';
                if ($celda['pago']) {
                    $pagadas++;
                    $recaudado += $celda['monto'];
                }
            }
            $celdas[] = $pagadas.'/'.$cuotas->count();
            $celdas[] = number_format($recaudado, 2);

            return $celdas;
        })->all();

        $totalFila = ['Total del mes', ''];
        foreach ($cuotas as $cuota) {
            $totalFila[] = $totalesMes[$cuota->id]['pagaron'].' pagaron / S/ '.number_format($totalesMes[$cuota->id]['recaudado'], 2);
        }
        $totalFila[] = '';
        $totalFila[] = number_format($filas->sum(function ($fila) use ($cuotas) {
            return $cuotas->sum(fn ($cuota) => $fila['celdas'][$cuota->id]['pago'] ? $fila['celdas'][$cuota->id]['monto'] : 0);
        }), 2);
        $filasExcel[] = $totalFila;

        return SeccionKExport::excel('Por promoción', $encabezados, $filasExcel, 'seccionk-cuotas-por-promocion');
    }

    private function textoPorMes($cuotas, $filas, $totalesMes): string
    {
        $lineas = [
            '📊 *SECCIÓN K 2001 - CUOTAS POR MES*',
            'Fecha: '.now()->format('d/m/Y H:i'),
            'Meses: '.$cuotas->map->mesCorto()->implode(', '),
            '',
        ];

        foreach ($cuotas as $cuota) {
            $lineas[] = '📅 *'.$cuota->mesCorto().'* · Sugerido S/ '.number_format($cuota->monto, 2);
            $lineas[] = $totalesMes[$cuota->id]['pagaron'].' pagaron · S/ '.number_format($totalesMes[$cuota->id]['recaudado'], 2);
            foreach ($filas as $fila) {
                $celda = $fila['celdas'][$cuota->id];
                $lineas[] = $celda['pago']
                    ? '✅ '.$fila['promocion']->nombreCompleto().' — S/ '.number_format($celda['monto'], 2)
                    : '⏳ '.$fila['promocion']->nombreCompleto().' — Pendiente';
            }
            $lineas[] = '------------------------------';
        }

        $total = $cuotas->sum(fn ($cuota) => $totalesMes[$cuota->id]['recaudado']);
        $lineas[] = '*Total recaudado: S/ '.number_format($total, 2).'*';

        return implode("\n", $lineas);
    }

    private function textoPorPromocion($cuotas, $filas, $totalesMes): string
    {
        $lineas = [
            '📊 *SECCIÓN K 2001 - CUOTAS POR PROMOCIÓN*',
            'Fecha: '.now()->format('d/m/Y H:i'),
            'Meses: '.$cuotas->map->mesCorto()->implode(', '),
            '',
        ];

        foreach ($filas as $fila) {
            $pagadas = $cuotas->filter(fn ($cuota) => $fila['celdas'][$cuota->id]['pago'])->count();
            $recaudado = $cuotas->sum(fn ($cuota) => $fila['celdas'][$cuota->id]['pago'] ? $fila['celdas'][$cuota->id]['monto'] : 0);
            $lineas[] = '👤 *'.$fila['promocion']->nombreCompleto().'*';
            $lineas[] = 'DNI '.$fila['promocion']->dni.' · '.$pagadas.'/'.$cuotas->count().' · S/ '.number_format($recaudado, 2);
            foreach ($cuotas as $cuota) {
                $celda = $fila['celdas'][$cuota->id];
                $lineas[] = $cuota->mesCorto().': '.($celda['pago'] ? '✅ Pagó S/ '.number_format($celda['monto'], 2) : '⏳ Pendiente');
            }
            $lineas[] = '------------------------------';
        }

        $total = $cuotas->sum(fn ($cuota) => $totalesMes[$cuota->id]['recaudado']);
        $lineas[] = '*Total recaudado: S/ '.number_format($total, 2).'*';

        return implode("\n", $lineas);
    }

    private function datosExportarDashboard(string $orden): array
    {
        $participanCuotas = SeccionKPromocion::activos()->where('participa_cuotas', true)->count();
        $totalPromociones = SeccionKPromocion::activos()->count();

        $meses = SeccionKEvento::cuotasMensuales()
            ->activos()
            ->orderBy('fecha')
            ->get()
            ->map(function ($cuota) use ($participanCuotas) {
                $pagos = SeccionKParticipacion::dePromocionesActivas()
                    ->where('evento_id', $cuota->id)
                    ->where('pago', true);
                $pagaron = (clone $pagos)->count();

                return [
                    'nombre' => $cuota->mes_cuota ?: $cuota->nombre,
                    'fecha' => $cuota->fecha,
                    'pagaron' => $pagaron,
                    'faltan' => max($participanCuotas - $pagaron, 0),
                    'recaudado' => (float) (clone $pagos)->sum('monto_aportado'),
                ];
            });

        $eventos = SeccionKEstadisticas::resumenEventos($totalPromociones);
        $eventos = $orden === 'nombre'
            ? $eventos->sortBy(fn ($fila) => mb_strtolower($fila['evento']->nombre), SORT_NATURAL)->values()
            : $eventos->sortBy(fn ($fila) => $fila['evento']->fecha->timestamp)->values();

        return [
            'orden' => $orden,
            'meses' => $meses,
            'eventos' => $eventos,
            'total_cuotas' => (float) $meses->sum('recaudado'),
            'total_recaudado_eventos' => (float) $eventos->sum('recaudado'),
            'total_utilizado_eventos' => (float) $eventos->sum('utilizado'),
            'total_saldo_eventos' => (float) $eventos->sum('saldo'),
        ];
    }

    private function excelDashboard(array $datos)
    {
        $filas = [
            ['CUOTAS POR MES', '', '', '', '', '', ''],
            ['Mes', 'Fecha', 'Pagaron', 'Faltan', 'Recaudado S/', '', ''],
        ];
        foreach ($datos['meses'] as $mes) {
            $filas[] = [
                $mes['nombre'],
                $mes['fecha']->format('d/m/Y'),
                $mes['pagaron'],
                $mes['faltan'],
                number_format($mes['recaudado'], 2, '.', ''),
                '',
                '',
            ];
        }
        $filas[] = ['TOTAL CUOTAS', '', '', '', number_format($datos['total_cuotas'], 2, '.', ''), '', ''];
        $filas[] = ['', '', '', '', '', '', ''];
        $filas[] = ['EVENTOS (recaudado − declaración)', '', '', '', '', '', ''];
        $filas[] = ['Evento', 'Fecha', 'Participantes', 'Aportes', 'Recaudado S/', 'Declarado S/', 'Saldo S/'];
        foreach ($datos['eventos'] as $fila) {
            $filas[] = [
                $fila['evento']->nombre,
                $fila['evento']->fecha->format('d/m/Y'),
                $fila['participantes'].'/'.$fila['total_promociones'],
                $fila['aportantes'],
                number_format($fila['recaudado'], 2, '.', ''),
                number_format($fila['utilizado'], 2, '.', ''),
                number_format($fila['saldo'], 2, '.', ''),
            ];
        }
        $filas[] = [
            'TOTAL EVENTOS',
            '',
            '',
            '',
            number_format($datos['total_recaudado_eventos'], 2, '.', ''),
            number_format($datos['total_utilizado_eventos'], 2, '.', ''),
            number_format($datos['total_saldo_eventos'], 2, '.', ''),
        ];

        return SeccionKExport::excel(
            'Dashboard',
            ['Sección K 2001', 'Orden: '.($datos['orden'] === 'nombre' ? 'nombre' : 'fecha'), '', '', '', '', ''],
            $filas,
            'seccionk-dashboard'
        );
    }

    private function textoDashboard(array $datos): string
    {
        $lineas = [
            '📊 *SECCIÓN K 2001 - DASHBOARD*',
            'Fecha: '.now()->format('d/m/Y H:i'),
            'Eventos ordenados por '.($datos['orden'] === 'nombre' ? 'nombre' : 'fecha'),
            '',
            '📅 *CUOTAS POR MES*',
        ];
        foreach ($datos['meses'] as $mes) {
            $lineas[] = $mes['fecha']->format('d/m/Y').' · *'.$mes['nombre'].'* · '.$mes['pagaron'].' pagaron · S/ '.number_format($mes['recaudado'], 2);
        }
        $lineas[] = '*Total cuotas: S/ '.number_format($datos['total_cuotas'], 2).'*';
        $lineas[] = '';
        $lineas[] = '⭐ *EVENTOS (recaudado − declaración)*';
        foreach ($datos['eventos'] as $fila) {
            $lineas[] = $fila['evento']->fecha->format('d/m/Y').' · *'.$fila['evento']->nombre.'*';
            $lineas[] = $fila['participantes'].'/'.$fila['total_promociones'].' participaron · Recaudado S/ '.number_format($fila['recaudado'], 2)
                .' · Declarado S/ '.number_format($fila['utilizado'], 2)
                .' · Saldo S/ '.number_format($fila['saldo'], 2);
        }
        $lineas[] = '*Total eventos: Recaudado S/ '.number_format($datos['total_recaudado_eventos'], 2)
            .' − Declarado S/ '.number_format($datos['total_utilizado_eventos'], 2)
            .' = Saldo S/ '.number_format($datos['total_saldo_eventos'], 2).'*';

        return implode("\n", $lineas);
    }

    private function datosResumenCuotas(): array
    {
        $cuotas = SeccionKEvento::cuotasMensuales()->activos()->orderBy('fecha')->get();
        $promociones = SeccionKPromocion::activos()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get();

        $pagos = SeccionKParticipacion::query()
            ->dePromocionesActivas()
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
