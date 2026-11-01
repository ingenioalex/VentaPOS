<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminEventoController extends Controller
{
    public function index(Request $request)
    {
        $query = SeccionKEvento::query();

        if ($request->filled('tipo')) {
            if ($request->tipo === 'cuota_mensual') {
                $query->cuotasMensuales();
            } elseif ($request->tipo === 'evento') {
                $query->eventos();
            }
        }

        if (!$request->has('mostrar_inactivos')) {
            $query->where('activo', true);
        }

        $eventos = $query->orderBy('fecha', 'desc')->paginate(20);

        return view('seccionk.admin.eventos.index', compact('eventos'));
    }

    public function create()
    {
        return view('seccionk.admin.eventos.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'tipo' => 'required|in:evento,cuota_mensual',
            'mes_cuota' => 'nullable|string|max:20',
        ]);

        SeccionKEvento::create($data);

        return redirect()->route('seccionk.admin.eventos.index')
            ->with('success', 'Evento creado exitosamente.');
    }

    public function edit(SeccionKEvento $evento)
    {
        return view('seccionk.admin.eventos.form', compact('evento'));
    }

    public function update(Request $request, SeccionKEvento $evento)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'tipo' => 'required|in:evento,cuota_mensual',
            'mes_cuota' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ]);

        $evento->update($data);

        return redirect()->route('seccionk.admin.eventos.index')
            ->with('success', 'Evento actualizado exitosamente.');
    }

    public function destroy(SeccionKEvento $evento)
    {
        $evento->update(['activo' => false]);

        return redirect()->route('seccionk.admin.eventos.index')
            ->with('success', 'Evento ocultado exitosamente.');
    }

    public function participantes(SeccionKEvento $evento)
    {
        $promociones = SeccionKPromocion::activos()->orderBy('apellido_paterno')->get();
        
        $participaciones = SeccionKParticipacion::where('evento_id', $evento->id)
            ->get()
            ->keyBy('promocion_id');

        return view('seccionk.admin.eventos.participantes', compact('evento', 'promociones', 'participaciones'));
    }

    public function guardarParticipantes(Request $request, SeccionKEvento $evento)
    {
        $data = $request->validate([
            'participantes' => 'required|array',
            'participantes.*' => 'array',
            'participantes.*.participo' => 'boolean',
            'participantes.*.pago' => 'boolean',
            'participantes.*.monto_aportado' => 'nullable|numeric|min:0',
            'participantes.*.numero_transaccion' => 'nullable|string|max:100',
            'participantes.*.fecha_pago' => 'nullable|date',
            'participantes.*.observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($evento, $data, $request) {
            foreach ($data['participantes'] as $promocionId => $participanteData) {
                $imagenSustento = null;
                
                // Manejar la imagen si fue subida
                if ($request->hasFile("participantes.{$promocionId}.imagen_sustento")) {
                    $imagen = $request->file("participantes.{$promocionId}.imagen_sustento");
                    $imagenSustento = $imagen->store('seccionk/sustentos', 'public');
                }

                $existente = SeccionKParticipacion::where('evento_id', $evento->id)
                    ->where('promocion_id', $promocionId)
                    ->first();

                $dataParticipacion = [
                    'evento_id' => $evento->id,
                    'promocion_id' => $promocionId,
                    'participo' => $participanteData['participo'] ?? false,
                    'pago' => $participanteData['pago'] ?? false,
                    'monto_aportado' => $participanteData['monto_aportado'] ?? 0,
                    'numero_transaccion' => $participanteData['numero_transaccion'] ?? null,
                    'fecha_pago' => $participanteData['fecha_pago'] ?? null,
                    'observaciones' => $participanteData['observaciones'] ?? null,
                ];

                if ($imagenSustento) {
                    $dataParticipacion['imagen_sustento'] = $imagenSustento;
                }

                if ($existente) {
                    $existente->update($dataParticipacion);
                } else {
                    SeccionKParticipacion::create($dataParticipacion);
                }
            }
        });

        return redirect()->route('seccionk.admin.eventos.participantes', $evento)
            ->with('success', 'Participantes actualizados exitosamente.');
    }
}
