<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Usuario;
use App\Services\AnulacionService;
use App\Services\CierreCajaService;
use App\Services\SesionCajeroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CierreCajaController extends Controller
{
    public function __construct(
        private CierreCajaService $cierreService,
        private AnulacionService $anulacionService,
        private SesionCajeroService $sesionCajero,
    ) {}

    public function index()
    {
        $usuario = Auth::user();
        $resumen = $this->cierreService->resumenActual($usuario);
        $resumenZ = $usuario->puedeGestionar()
            ? $this->cierreService->resumenZCierre($usuario->empresa_id)
            : null;

        $historial = CierreCaja::where('empresa_id', $usuario->empresa_id)
            ->with(['usuario:id,nombre', 'supervisor:id,nombre'])
            ->orderByDesc('fecha_fin')
            ->limit(15)
            ->get();

        $cajerosConectados = $usuario->puedeGestionar()
            ? $this->sesionCajero->cajerosConectados($usuario->empresa_id)
            : collect();

        return view('app.cierres.index', compact('resumen', 'resumenZ', 'historial', 'usuario', 'cajerosConectados'));
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
            $cierre = $this->sesionCajero->cierreXDesdePos(
                $cajero,
                Auth::user(),
                (float) $datos['monto_efectivo_contado'],
                $datos['observaciones'] ?? null,
            );

            return redirect()->route('cierres.index')
                ->with('ok', "Cierre X de {$cajero->nombre} registrado.")
                ->with('ticket_url', route('cierres.ticket', $cierre));
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
            $cierre = $this->cierreService->cerrarX(
                Auth::user(),
                (float) $datos['monto_efectivo_contado'],
                $datos['observaciones'] ?? null,
            );

            return redirect()->route('cierres.index')
                ->with('ok', 'Cierre X registrado.')
                ->with('ticket_url', route('cierres.ticket', $cierre));
        } catch (\Throwable $e) {
            return back()->withErrors(['monto_efectivo_contado' => $e->getMessage()]);
        }
    }

    public function cerrarZ(Request $request)
    {
        if (! Auth::user()->puedeGestionar()) {
            abort(403);
        }

        $datos = $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $cierre = $this->cierreService->cerrarZ(
                Auth::user(),
                $datos['observaciones'] ?? null,
            );

            return redirect()->route('cierres.index')
                ->with('ok', 'Cierre Z registrado.')
                ->with('ticket_url', route('cierres.ticket', $cierre));
        } catch (\Throwable $e) {
            return back()->withErrors(['cierre_z' => $e->getMessage()]);
        }
    }

    public function ticket(CierreCaja $cierre)
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

        return view('app.cierres.ticket', compact('cierre', 'porCajero'));
    }
}
