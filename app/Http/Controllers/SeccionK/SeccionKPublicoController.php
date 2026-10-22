<?php

namespace App\Http\Controllers\SeccionK;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Support\SeccionKEstadisticas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

        $this->recordarIdentidad($promocion);

        return redirect()->route('seccionk.perfil', $promocion->id);
    }

    public function perfil($id)
    {
        $promocion = SeccionKPromocion::activos()
            ->with(['participaciones.evento'])
            ->findOrFail($id);

        if (! $this->estaIdentificado($promocion)) {
            return redirect()
                ->route('seccionk.index')
                ->withErrors(['busqueda' => 'Identifícate con tu DNI y apellido para ver tu información.']);
        }

        $stats = SeccionKEstadisticas::comunidad();

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
                'comentario' => $part?->observaciones,
                'imagen' => $part?->imagen_sustento,
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
        $totalAportadoCuotas = $cuotasData->where('pago', true)->sum('monto_aportado');
        $totalAportadoEventos = $eventosData->where('pago', true)->sum('monto_aportado');

        return view('seccionk.perfil', compact(
            'promocion',
            'cuotasData',
            'eventosData',
            'totalCuotas',
            'cuotasPagadas',
            'totalAportadoCuotas',
            'totalAportadoEventos',
            'stats'
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
        $this->recordarIdentidad($promocion);

        return redirect()->route('seccionk.perfil', $promocion->id)
            ->with('success', '¡Registro exitoso! Bienvenido a la Sección K - Promoción 2001.');
    }

    public function avisoComentarioIncompleto($id)
    {
        return redirect()
            ->route('seccionk.perfil', $id)
            ->with('error', 'No se pudo guardar. Si subiste una foto del celular, vuelve a intentar: la imagen se reduce sola y es opcional.');
    }

    public function errorSubida(Request $request)
    {
        $volver = $request->headers->get('referer') ?: route('seccionk.index');
        if (str_contains((string) $volver, '/admin')) {
            $volver = route('seccionk.admin.promociones.index');
        }

        return response()
            ->view('seccionk.error-subida', ['volver' => $volver])
            ->header('Cache-Control', 'no-store');
    }

    public function guardarComentarioCuota(Request $request, $id, $eventoId)
    {
        $promocion = SeccionKPromocion::where('activo', true)->findOrFail($id);

        if (! $this->estaIdentificado($promocion)) {
            return redirect()
                ->route('seccionk.index')
                ->withErrors(['busqueda' => 'Identifícate con tu DNI y apellido para ver tu información.']);
        }

        $evento = SeccionKEvento::cuotasMensuales()
            ->activos()
            ->findOrFail($eventoId);

        $request->validate([
            'comentario' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        if (! $request->filled('comentario') && ! $request->hasFile('imagen')) {
            return redirect()
                ->route('seccionk.perfil', $promocion->id)
                ->withErrors(['comentario_'.$evento->id => 'Escribe un comentario o carga una imagen.'])
                ->withFragment('cuota-'.$evento->id);
        }

        $participacion = SeccionKParticipacion::firstOrNew([
            'promocion_id' => $promocion->id,
            'evento_id' => $evento->id,
        ]);

        if ($request->filled('comentario')) {
            $participacion->observaciones = $request->comentario;
        }

        if ($request->hasFile('imagen')) {
            $directorio = public_path('uploads/seccionk');
            File::ensureDirectoryExists($directorio);

            $archivo = $request->file('imagen');
            $nombre = $promocion->id.'_'.$evento->id.'_'.Str::random(8).'.'.$archivo->getClientOriginalExtension();
            $archivo->move($directorio, $nombre);

            if ($participacion->imagen_sustento) {
                $anterior = public_path('uploads/seccionk/'.$participacion->imagen_sustento);
                if (File::exists($anterior)) {
                    File::delete($anterior);
                }
            }

            $participacion->imagen_sustento = $nombre;
        }

        $participacion->save();

        return redirect()
            ->route('seccionk.perfil', $promocion->id)
            ->with('success', 'Comentario guardado en la cuota de '.$evento->mes_cuota.'.')
            ->withFragment('cuota-'.$evento->id);
    }

    private function recordarIdentidad(SeccionKPromocion $promocion): void
    {
        session(['seccionk_identificado' => $promocion->id]);
    }

    private function estaIdentificado(SeccionKPromocion $promocion): bool
    {
        if (auth('seccionk')->check()) {
            return true;
        }

        return (int) session('seccionk_identificado') === (int) $promocion->id;
    }
}
