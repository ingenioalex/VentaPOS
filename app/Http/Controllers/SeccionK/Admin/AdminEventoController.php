<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKDeclaracion;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Support\SeccionKEsquema;
use App\Support\SeccionKEstadisticas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminEventoController extends Controller
{
    public function index(Request $request)
    {
        SeccionKEsquema::asegurarDeclaraciones();

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

        $eventos = $query->with('declaracion')->orderBy('fecha', 'desc')->paginate(20);
        $resumenPorEvento = SeccionKEstadisticas::resumenPorEventos(collect($eventos->items()));

        return view('seccionk.admin.eventos.index', compact('eventos', 'resumenPorEvento'));
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
        ]);

        if ($error = $this->validarMesUnico($data)) {
            return back()->withInput()->withErrors($error);
        }

        $data['mes_cuota'] = $data['tipo'] === 'cuota_mensual'
            ? SeccionKEvento::etiquetaDesdeFecha($data['fecha'])
            : null;

        SeccionKEvento::create($data);

        return redirect()->route('seccionk.admin.eventos.index')
            ->with('success', 'Evento creado exitosamente.');
    }

    public function edit(SeccionKEvento $evento)
    {
        return view('seccionk.admin.eventos.form', compact('evento'));
    }

    public function show(SeccionKEvento $evento)
    {
        return redirect()->route('seccionk.admin.eventos.participantes', $evento);
    }

    public function update(Request $request, SeccionKEvento $evento)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'tipo' => 'required|in:evento,cuota_mensual',
            'activo' => 'boolean',
        ]);

        if ($error = $this->validarMesUnico($data, $evento)) {
            return back()->withInput()->withErrors($error);
        }

        $data['mes_cuota'] = $data['tipo'] === 'cuota_mensual'
            ? SeccionKEvento::etiquetaDesdeFecha($data['fecha'])
            : null;

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

    public function cerrar(Request $request, SeccionKEvento $evento)
    {
        SeccionKEsquema::asegurarDeclaraciones();

        $data = $request->validate([
            'monto_utilizado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        SeccionKDeclaracion::registrarCierre(
            $evento,
            (float) $data['monto_utilizado'],
            $data['observaciones'] ?? null
        );

        $destino = $request->input('volver') === 'declaracion'
            ? route('seccionk.admin.declaracion')
            : route('seccionk.admin.eventos.detalle', $evento);

        $tipo = $evento->esCuotaMensual() ? 'cuota' : 'evento';

        return redirect($destino)
            ->with('success', ucfirst($tipo).' cerrado. Se registró el monto utilizado sin cambiar los aportes.');
    }

    public function detalle(SeccionKEvento $evento)
    {
        SeccionKEsquema::asegurarDeclaraciones();
        $evento->load('declaracion');

        return view('seccionk.admin.eventos.detalle', SeccionKEstadisticas::detalleEvento($evento));
    }

    public function participantes(SeccionKEvento $evento)
    {
        $detalle = SeccionKEstadisticas::detalleEvento($evento);
        $promociones = $detalle['filas']->pluck('promocion');
        $participaciones = SeccionKParticipacion::query()
            ->dePromocionesActivas()
            ->where('evento_id', $evento->id)
            ->get()
            ->keyBy('promocion_id');

        return view('seccionk.admin.eventos.participantes', array_merge($detalle, [
            'promociones' => $promociones,
            'participaciones' => $participaciones,
        ]));
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
            'participantes.*.imagen_sustento' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        DB::transaction(function () use ($evento, $data, $request) {
            foreach ($data['participantes'] as $promocionId => $participanteData) {
                $imagenSustento = null;

                if ($request->hasFile("participantes.{$promocionId}.imagen_sustento")) {
                    $directorio = public_path('uploads/seccionk');
                    File::ensureDirectoryExists($directorio);
                    $imagen = $request->file("participantes.{$promocionId}.imagen_sustento");
                    $nombre = $promocionId.'_'.$evento->id.'_'.Str::random(8).'.'.$imagen->getClientOriginalExtension();
                    $imagen->move($directorio, $nombre);
                    $imagenSustento = $nombre;
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

    private function validarMesUnico(array $data, ?SeccionKEvento $evento = null): ?array
    {
        if (($data['tipo'] ?? '') !== 'cuota_mensual') {
            return null;
        }

        $mes = SeccionKEvento::etiquetaDesdeFecha($data['fecha']);
        $existe = SeccionKEvento::cuotasMensuales()
            ->where('mes_cuota', $mes)
            ->when($evento, fn ($query) => $query->where('id', '!=', $evento->id))
            ->exists();

        if ($existe) {
            return ['fecha' => 'Ya existe la cuota de '.$mes.'. No se puede repetir el mes.'];
        }

        return null;
    }
}
