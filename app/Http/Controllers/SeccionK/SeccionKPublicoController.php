<?php

namespace App\Http\Controllers\SeccionK;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeccionKPublicoController extends Controller
{
    public function index()
    {
        return view('seccionk.index');
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'dni' => 'required|digits:8',
            'apellido' => 'required|string|min:3',
        ]);

        $promocion = SeccionKPromocion::where('dni', $request->dni)
            ->where('apellido_paterno', 'like', $request->apellido . '%')
            ->where('activo', true)
            ->first();

        if (! $promocion) {
            return back()->withErrors(['busqueda' => 'No se encontró ninguna promoción con esos datos.']);
        }

        return redirect()->route('seccionk.perfil', $promocion->id);
    }

    public function perfil($id)
    {
        $promocion = SeccionKPromocion::with(['participaciones.evento'])
            ->findOrFail($id);

        // Cuotas mensuales ordenadas por fecha
        $cuotasMensuales = SeccionKEvento::cuotasMensuales()
            ->activos()
            ->orderBy('fecha')
            ->get();

        // Eventos (no cuotas)
        $eventos = SeccionKEvento::eventos()
            ->activos()
            ->orderBy('fecha', 'desc')
            ->get();

        // Obtener participaciones indexadas por evento_id
        $participacionesMap = $promocion->participaciones->keyBy('evento_id');

        // Preparar datos de cuotas mensuales con su estado
        $cuotasData = $cuotasMensuales->map(function ($cuota) use ($participacionesMap) {
            $part = $participacionesMap->get($cuota->id);
            
            return [
                'evento' => $cuota,
                'pago' => $part?->pago ?? false,
                'monto_aportado' => $part?->monto_aportado ?? 0,
                'fecha_pago' => $part?->fecha_pago,
            ];
        });

        // Preparar datos de eventos con su estado
        $eventosData = $eventos->map(function ($evento) use ($participacionesMap) {
            $part = $participacionesMap->get($evento->id);
            
            return [
                'evento' => $evento,
                'participo' => $part?->participo ?? false,
                'pago' => $part?->pago ?? false,
                'monto_aportado' => $part?->monto_aportado ?? 0,
            ];
        });

        // Estadísticas
        $totalCuotas = $cuotasMensuales->count();
        $cuotasPagadas = $cuotasData->where('pago', true)->count();
        $totalAportadoCuotas = $cuotasData->sum('monto_aportado');

        return view('seccionk.perfil', compact(
            'promocion',
            'cuotasData',
            'eventosData',
            'totalCuotas',
            'cuotasPagadas',
            'totalAportadoCuotas'
        ));
    }

    public function registroForm()
    {
        return view('seccionk.registro');
    }

    public function registro(Request $request)
    {
        $request->validate([
            'dni' => 'required|digits:8|unique:seccionk_promociones,dni',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:15',
            'participa_cuotas' => 'boolean',
            'referencias' => 'nullable|string',
        ]);

        $promocion = SeccionKPromocion::create($request->all());

        return redirect()->route('seccionk.perfil', $promocion->id)
            ->with('success', '¡Registro exitoso! Bienvenido a la Sección K - Promoción 2001.');
    }
}
