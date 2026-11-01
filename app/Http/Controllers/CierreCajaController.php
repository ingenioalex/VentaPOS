<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Usuario;
use App\Services\AnulacionService;
use App\Services\CierreCajaService;
use App\Services\SesionCajeroService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CierreCajaController extends Controller
{
    private const HISTORIAL_DIAS = 5;

    public function __construct(
        private CierreCajaService $cierreService,
        private AnulacionService $anulacionService,
        private SesionCajeroService $sesionCajero,
    ) {}

    public function index(Request $request)
    {
        $usuario = Auth::user();
        $this->cierreService->prepararModuloCierres($usuario->empresa_id);

        $resumen = $this->cierreService->resumenActual($usuario);
        $resumenZ = $usuario->puedeGestionar()
            ? $this->cierreService->resumenZCierre($usuario->empresa_id)
            : null;

        $resumenTurno = $usuario->rol === 'cajero'
            ? array_merge($resumen, [
                'titulo' => 'Mi turno actual',
                'subtitulo' => $resumen['es_hoy']
                    ? 'Ventas de hoy desde las '.$resumen['periodo_desde']->format('H:i')
                    : 'Ventas del '.$resumen['fecha_desde'].' al '.$resumen['fecha_hasta'],
            ])
            : null;

        [$historialPorDia] = $this->historialPorDia($usuario->empresa_id);

        $cajerosConectados = $usuario->puedeGestionar()
            ? $this->sesionCajero->cajerosConectados($usuario->empresa_id)
            : collect();

        $jornadasPendientesPag = null;
        if ($usuario->puedeGestionar()) {
            $todas = $this->cierreService->jornadasPendientes($usuario->empresa_id);
            $perPage = 5;
            $total = count($todas);
            $page = max(1, min((int) $request->query('jp', 1), max(1, (int) ceil($total / $perPage))));
            $jornadasPendientesPag = [
                'items' => array_slice($todas, ($page - 1) * $perPage, $perPage),
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'proximo' => $todas[0]['fecha'] ?? null,
            ];
        }

        return view('app.cierres.index', compact(
            'resumen',
            'resumenTurno',
            'resumenZ',
            'historialPorDia',
            'usuario',
            'cajerosConectados',
            'jornadasPendientesPag',
        ));
    }

    /** @return array{0: Collection} */
    private function historialPorDia(int $empresaId): array
    {
        $tz = config('app.timezone', 'America/Lima');
        $desde = now($tz)->subDays(self::HISTORIAL_DIAS - 1)->startOfDay();

        $registros = CierreCaja::where('empresa_id', $empresaId)
            ->where('fecha_fin', '>=', $desde)
            ->with(['usuario:id,nombre,usuario', 'supervisor:id,nombre'])
            ->orderBy('fecha_fin')
            ->orderBy('id')
            ->get();

        $porDia = $registros
            ->groupBy(fn (CierreCaja $c) => $c->fecha_fin->timezone($tz)->toDateString())
            ->sortKeysDesc()
            ->take(self::HISTORIAL_DIAS)
            ->map(function (Collection $cierres, string $dia) use ($tz) {
                $ordenados = $cierres->sortBy([
                    fn (CierreCaja $c) => $c->fecha_fin->timestamp,
                    fn (CierreCaja $c) => $c->id,
                ])->values();
                $fecha = Carbon::parse($dia, $tz)->locale('es');

                return [
                    'clave' => $dia,
                    'etiqueta' => ucfirst($fecha->isoFormat('dddd D [de] MMMM [de] YYYY')),
                    'es_hoy' => $dia === now($tz)->toDateString(),
                    'cierres' => $ordenados,
                    'totales' => [
                        'registros' => $ordenados->count(),
                        'tickets' => (int) $ordenados->sum('cantidad_ventas'),
                        'efectivo' => round((float) $ordenados->sum('total_efectivo'), 2),
                        'tarjeta' => round((float) $ordenados->sum('total_tarjeta'), 2),
                        'otros' => round((float) $ordenados->sum('total_otros'), 2),
                        'contado' => round((float) $ordenados->sum('monto_efectivo_contado'), 2),
                        'diferencia' => round(
                            (float) $ordenados->sum('monto_efectivo_contado') - (float) $ordenados->sum('total_efectivo'),
                            2
                        ),
                    ],
                ];
            });

        return [$porDia];
    }

    public function cierreXCajero(Request $request, Usuario $cajero)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        if ($cajero->empresa_id !== Auth::user()->empresa_id || $cajero->rol !== 'cajero') {
            abort(404);
        }

        $datos = $request->validate([
            'monto_efectivo_contado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $cierre = $this->sesionCajero->cierreSupervisorYCerrarSesion(
                $cajero,
                Auth::user(),
                (float) $datos['monto_efectivo_contado'],
                $datos['observaciones'] ?? null,
            );

            return redirect()->route('cierres.index')
                ->with('ok', "Cierre X de {$cajero->nombre} registrado y sesión cerrada.")
                ->with('ticket_url', route('cierres.ticket', $cierre))
                ->with('ultimo_cierre_id', $cierre->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['cierre_x_cajero' => $e->getMessage()]);
        }
    }

    public function cerrarSesionCajero(Usuario $cajero)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        if ($cajero->empresa_id !== Auth::user()->empresa_id || $cajero->rol !== 'cajero') {
            abort(404);
        }

        try {
            $this->sesionCajero->cerrarSesionForzada($cajero);

            return back()->with('ok', "Sesión de {$cajero->nombre} cerrada.");
        } catch (\Throwable $e) {
            return back()->withErrors(['cerrar_sesion' => $e->getMessage()]);
        }
    }

    public function cerrarX(Request $request)
    {
        $datos = $request->validate([
            'monto_efectivo_contado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $usuario = Auth::user();
            $cierre = $this->cierreService->cerrarX(
                $usuario,
                (float) $datos['monto_efectivo_contado'],
                $datos['observaciones'] ?? null,
            );

            $this->sesionCajero->cerrarSesion($usuario);

            return redirect()->route('cierres.ticket', $cierre)
                ->with('ok', 'Cierre X registrado y sesión cerrada. Puede imprimir el ticket si lo desea.');
        } catch (\Throwable $e) {
            return back()->withErrors(['monto_efectivo_contado' => $e->getMessage()]);
        }
    }

    public function cerrarZ(Request $request)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $rect = $this->datosRectificacionArqueo($request);
        $datos = $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $cierre = $this->cierreService->cerrarZ(
                Auth::user(),
                $datos['observaciones'] ?? null,
                $rect['rectificar'],
                $rect['motivo'],
            );

            return redirect()->route('cierres.index')
                ->with('ok', 'Cierre Z registrado.')
                ->with('ticket_url', route('cierres.ticket', $cierre))
                ->with('ultimo_cierre_id', $cierre->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['cierre_z' => $e->getMessage()])->withInput();
        }
    }

    public function rectificarArqueo(Request $request)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $rect = $this->datosRectificacionArqueo($request);

        if ($rect['rectificar'] === []) {
            return back()->withErrors(['rectificar_arqueo' => 'Indique al menos un monto a rectificar.']);
        }

        try {
            $n = $this->cierreService->aplicarRectificacionesArqueo(
                Auth::user(),
                $rect['rectificar'],
                $rect['motivo'] ?: 'Rectificación manual antes del Cierre Z',
            );

            $msg = $n > 0
                ? "Se rectificaron {$n} arqueo(s) de Cierre X."
                : 'No hubo cambios en los montos indicados.';

            return redirect()->route('cierres.index')->with('ok', $msg);
        } catch (\Throwable $e) {
            return back()->withErrors(['rectificar_arqueo' => $e->getMessage()])->withInput();
        }
    }

    /** @return array{rectificar: array<int, float>, motivo: ?string} */
    private function datosRectificacionArqueo(Request $request): array
    {
        $datos = $request->validate([
            'rectificar' => 'nullable|array',
            'rectificar.*' => 'nullable|numeric|min:0',
            'motivo_rectificacion' => 'nullable|string|max:500',
        ]);

        $rectificar = [];
        foreach ($datos['rectificar'] ?? [] as $id => $monto) {
            if ($monto === '' || $monto === null) {
                continue;
            }
            $rectificar[(int) $id] = (float) $monto;
        }

        return [
            'rectificar' => $rectificar,
            'motivo' => $datos['motivo_rectificacion'] ?? null,
        ];
    }

    public function ticket(Request $request, CierreCaja $cierre)
    {
        if ($cierre->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }

        $cierre->load(['usuario', 'supervisor', 'empresa']);

        $porCajero = $cierre->tipo_cierre === 'Z'
            ? $this->cierreService->cierresXEnPeriodo($cierre)
                ->groupBy('usuario_id')
                ->map(fn ($grupo) => [
                    'cajero' => $grupo->first()->usuario,
                    'cantidad_cierres' => $grupo->count(),
                    'cantidad_ventas' => $grupo->sum('cantidad_ventas'),
                    'total_ventas' => round((float) $grupo->sum('total_ventas'), 2),
                    'total_efectivo' => round((float) $grupo->sum('total_efectivo'), 2),
                    'monto_contado' => round((float) $grupo->sum('monto_efectivo_contado'), 2),
                    'diferencia' => round((float) $grupo->sum('diferencia_efectivo'), 2),
                ])
                ->values()
            : collect();

        return view('app.cierres.ticket', [
            'cierre' => $cierre,
            'porCajero' => $porCajero,
            'autoImprimir' => $request->boolean('imprimir'),
        ]);
    }

    public function jornadaPendiente(string $fecha)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        try {
            $resumen = $this->cierreService->resumenJornadaPendiente(Auth::user()->empresa_id, $fecha);
        } catch (\Throwable $e) {
            return redirect()->route('cierres.index')->withErrors(['jornada' => $e->getMessage()]);
        }

        return view('app.cierres.jornada-pendiente', [
            'resumen' => $resumen,
            'usuario' => Auth::user(),
            'esSiguiente' => $this->cierreService->proximoDiaPendiente(Auth::user()->empresa_id) === $fecha,
        ]);
    }

    public function cerrarZExcepcional(Request $request, string $fecha)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $rect = $this->datosRectificacionArqueo($request);
        $datos = $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $cierre = $this->cierreService->cerrarZExcepcional(
                Auth::user(),
                $fecha,
                $datos['observaciones'] ?? null,
                $rect['rectificar'],
                $rect['motivo'],
            );

            return redirect()->route('cierres.index')
                ->with('ok', 'Cierre Z excepcional del '.$fecha.' registrado.')
                ->with('ticket_url', route('cierres.ticket', $cierre))
                ->with('ultimo_cierre_id', $cierre->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['cierre_z' => $e->getMessage()])->withInput();
        }
    }

    public function cerrarXExcepcional(Request $request, string $fecha, Usuario $cajero)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        if ($cajero->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }

        $datos = $request->validate([
            'monto_efectivo_contado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $cierre = $this->cierreService->cerrarXExcepcional(
                $cajero,
                Auth::user(),
                $fecha,
                (float) $datos['monto_efectivo_contado'],
                $datos['observaciones'] ?? null,
            );

            return redirect()->route('cierres.jornada', $fecha)
                ->with('ok', "Cierre X excepcional de {$cajero->nombre} registrado.")
                ->with('ticket_url', route('cierres.ticket', $cierre))
                ->with('ultimo_cierre_id', $cierre->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['cierre_x' => $e->getMessage()]);
        }
    }

    public function cerrarJornadaDirecta(Request $request, string $fecha)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $datos = $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $cierre = $this->cierreService->cerrarDiaPendiente(
                Auth::user(),
                $fecha,
                $datos['observaciones'] ?? null,
            );

            return redirect()->route('cierres.index', ['jp' => $request->input('jp', 1)])
                ->with('ok', 'Día '.$fecha.' cerrado correctamente (X + Z).')
                ->with('ticket_url', route('cierres.ticket', $cierre))
                ->with('ultimo_cierre_id', $cierre->id)
                ->withFragment('jornadas-pendientes');
        } catch (\Throwable $e) {
            return redirect()->route('cierres.index', ['jp' => $request->input('jp', 1)])
                ->withFragment('jornadas-pendientes')
                ->withErrors(['jornada_directa' => $e->getMessage()]);
        }
    }
}
