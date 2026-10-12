<?php

namespace App\Http\Controllers\SeccionK\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeccionKEvento;
use App\Models\SeccionKParticipacion;
use App\Models\SeccionKPromocion;
use App\Support\SeccionKExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminPromocionController extends Controller
{
    public function index(Request $request)
    {
        $query = SeccionKPromocion::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('dni', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('participa_cuotas')) {
            $query->where('participa_cuotas', $request->participa_cuotas === '1');
        }

        if (!$request->has('mostrar_inactivos')) {
            $query->where('activo', true);
        }

        $promociones = $this->aplicarFiltrosPromociones($query)
            ->paginate(20);

        $totalCuotas = SeccionKEvento::cuotasMensuales()->activos()->count();
        $totalEventos = SeccionKEvento::eventos()->activos()->count();

        return view('seccionk.admin.promociones.index', compact('promociones', 'totalCuotas', 'totalEventos'));
    }

    public function exportar(Request $request)
    {
        $query = SeccionKPromocion::query();
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('dni', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%");
            });
        }
        if ($request->filled('participa_cuotas')) {
            $query->where('participa_cuotas', $request->participa_cuotas === '1');
        }
        if (! $request->has('mostrar_inactivos')) {
            $query->where('activo', true);
        }

        $promociones = $this->aplicarFiltrosPromociones($query)->get();
        $totalCuotas = SeccionKEvento::cuotasMensuales()->activos()->count();
        $totalEventos = SeccionKEvento::eventos()->activos()->count();

        $filasExcel = $promociones->map(function ($promo) use ($totalCuotas, $totalEventos) {
            $cuotasPagadas = (int) $promo->cuotas_pagadas;
            $eventosPagados = (int) $promo->eventos_pagados;

            return [
                $promo->dni,
                $promo->nombreCompleto(),
                $promo->celular ?? '',
                $promo->participa_cuotas ? 'Sí' : 'No',
                $cuotasPagadas,
                max($totalCuotas - $cuotasPagadas, 0),
                $eventosPagados,
                max($totalEventos - $eventosPagados, 0),
                $promo->activo ? 'Activo' : 'Inactivo',
            ];
        })->all();

        if ($request->get('formato') === 'excel') {
            return SeccionKExport::excel(
                'Promociones',
                ['DNI', 'Nombre', 'Celular', 'Participa cuotas', 'Cuotas pagadas', 'Cuotas por pagar', 'Eventos pagados', 'Eventos por pagar', 'Estado'],
                $filasExcel,
                'seccionk-promociones'
            );
        }

        $lineas = ["📋 *SECCIÓN K 2001 - PROMOCIONES*", 'Fecha: '.now()->format('d/m/Y H:i'), ''];
        foreach ($promociones as $promo) {
            $cuotasPagadas = (int) $promo->cuotas_pagadas;
            $cuotasFaltan = max($totalCuotas - $cuotasPagadas, 0);
            $eventosPagados = (int) $promo->eventos_pagados;
            $eventosFaltan = max($totalEventos - $eventosPagados, 0);
            $lineas[] = '👤 *'.$promo->nombreCompleto().'*';
            $lineas[] = 'DNI: '.$promo->dni;
            if ($promo->celular) {
                $lineas[] = 'Cel: '.$promo->celular;
            }
            $lineas[] = 'Cuotas: '.$cuotasPagadas.'/'.$totalCuotas.' pagadas · '.$cuotasFaltan.' por pagar';
            $lineas[] = 'Eventos: '.$eventosPagados.'/'.$totalEventos.' pagados · '.$eventosFaltan.' por pagar';
            $lineas[] = '------------------------------';
        }
        $texto = implode("\n", $lineas);

        if ($request->get('formato') === 'descargar') {
            return SeccionKExport::texto($texto, 'seccionk-promociones-whatsapp');
        }

        return view('seccionk.admin.exportar-whatsapp', [
            'titulo' => 'Promociones para WhatsApp',
            'texto' => $texto,
            'volver' => route('seccionk.admin.promociones.index', $request->query()),
            'descargar' => route('seccionk.admin.promociones.exportar', array_merge($request->query(), ['formato' => 'descargar'])),
        ]);
    }

    private function aplicarFiltrosPromociones($query)
    {
        return $query
            ->withCount([
                'participaciones as cuotas_pagadas' => function ($q) {
                    $q->where('pago', true)
                        ->whereHas('evento', function ($evento) {
                            $evento->where('tipo', 'cuota_mensual')->where('activo', true);
                        });
                },
                'participaciones as eventos_pagados' => function ($q) {
                    $q->where('pago', true)
                        ->whereHas('evento', function ($evento) {
                            $evento->where('tipo', 'evento')->where('activo', true);
                        });
                },
            ])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno');
    }

    public function create()
    {
        return view('seccionk.admin.promociones.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dni' => 'required|digits:8|unique:seccionk_promociones,dni',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:15',
            'participa_cuotas' => 'boolean',
            'referencias' => 'nullable|string',
        ]);

        SeccionKPromocion::create($data);

        return redirect()->route('seccionk.admin.promociones.index')
            ->with('success', 'Promoción registrada exitosamente.');
    }

    public function edit(SeccionKPromocion $promocion)
    {
        return view('seccionk.admin.promociones.form', compact('promocion'));
    }

    public function show(SeccionKPromocion $promocion)
    {
        $promocion->load('participaciones.evento');
        $participaciones = $promocion->participaciones->keyBy('evento_id');

        $cuotas = SeccionKEvento::cuotasMensuales()->activos()->orderBy('fecha')->get()->map(function ($cuota) use ($participaciones) {
            $part = $participaciones->get($cuota->id);

            return [
                'evento' => $cuota,
                'pago' => $part?->pago ?? false,
                'monto_aportado' => $part?->monto_aportado ?? 0,
                'fecha_pago' => $part?->fecha_pago,
                'comentario' => $part?->observaciones,
                'imagen' => $part?->imagen_sustento,
                'numero_transaccion' => $part?->numero_transaccion,
            ];
        });

        $eventos = SeccionKEvento::eventos()->activos()->orderBy('fecha', 'desc')->get()->map(function ($evento) use ($participaciones) {
            $part = $participaciones->get($evento->id);

            return [
                'evento' => $evento,
                'participo' => $part?->participo ?? false,
                'pago' => $part?->pago ?? false,
                'monto_aportado' => $part?->monto_aportado ?? 0,
                'fecha_pago' => $part?->fecha_pago,
                'comentario' => $part?->observaciones,
                'imagen' => $part?->imagen_sustento,
                'numero_transaccion' => $part?->numero_transaccion,
            ];
        });

        $cuotasPagadas = $cuotas->where('pago', true)->count();
        $eventosPagados = $eventos->where('pago', true)->count();

        return view('seccionk.admin.promociones.pagos', compact(
            'promocion',
            'cuotas',
            'eventos',
            'cuotasPagadas',
            'eventosPagados'
        ));
    }

    public function update(Request $request, SeccionKPromocion $promocion)
    {
        $data = $request->validate([
            'dni' => 'required|digits:8|unique:seccionk_promociones,dni,' . $promocion->id,
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'celular' => 'nullable|string|max:15',
            'participa_cuotas' => 'boolean',
            'referencias' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $promocion->update($data);

        return redirect()->route('seccionk.admin.promociones.index')
            ->with('success', 'Promoción actualizada exitosamente.');
    }

    public function registrarPago(Request $request, SeccionKPromocion $promocion, SeccionKEvento $evento)
    {
        $data = $request->validate([
            'monto_aportado' => 'required|numeric|min:0',
            'numero_transaccion' => 'nullable|string|max:100',
            'fecha_pago' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $participacion = SeccionKParticipacion::firstOrNew([
            'promocion_id' => $promocion->id,
            'evento_id' => $evento->id,
        ]);

        $participacion->pago = true;
        $participacion->participo = true;
        $participacion->monto_aportado = $data['monto_aportado'];
        $participacion->numero_transaccion = $data['numero_transaccion'] ?? $participacion->numero_transaccion;
        $participacion->fecha_pago = $data['fecha_pago'] ?? now()->toDateString();
        $participacion->observaciones = $data['observaciones'] ?? $participacion->observaciones;

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
            ->route('seccionk.admin.promociones.show', $promocion)
            ->with('success', 'Pago registrado en '.$evento->nombre.'.');
    }

    public function destroy(SeccionKPromocion $promocion)
    {
        $promocion->update(['activo' => false]);

        return redirect()->route('seccionk.admin.promociones.index')
            ->with('success', 'Promoción ocultada exitosamente.');
    }

    public function referencias(Request $request)
    {
        $promociones = $this->consultaReferencias($request)->paginate(20);
        $conReferencia = SeccionKPromocion::activos()
            ->whereNotNull('referencias')
            ->where('referencias', '!=', '')
            ->count();

        return view('seccionk.admin.referencias', compact('promociones', 'conReferencia'));
    }

    public function exportarReferencias(Request $request)
    {
        $promociones = $this->consultaReferencias($request)->get();

        if ($request->get('formato') === 'excel') {
            return SeccionKExport::excel(
                'Referencias',
                ['Quién indicó', 'DNI', 'Celular', 'Referencias'],
                $promociones->map(fn ($promo) => [
                    $promo->nombreCompleto(),
                    $promo->dni,
                    $promo->celular ?? '',
                    $promo->referencias ?? '',
                ])->all(),
                'seccionk-referencias'
            );
        }

        $lineas = [
            '📒 *SECCIÓN K 2001 - REFERENCIAS*',
            'Fecha: '.now()->format('d/m/Y H:i'),
            '',
        ];
        foreach ($promociones as $promo) {
            $lineas[] = '👤 *'.$promo->nombreCompleto().'*';
            $lineas[] = 'DNI '.$promo->dni.($promo->celular ? ' · Cel '.$promo->celular : '');
            $lineas[] = 'Indicó: '.($promo->referencias ?: 'Sin referencia');
            $lineas[] = '------------------------------';
        }
        $texto = implode("\n", $lineas);

        if ($request->get('formato') === 'descargar') {
            return SeccionKExport::texto($texto, 'seccionk-referencias-whatsapp');
        }

        return view('seccionk.admin.exportar-whatsapp', [
            'titulo' => 'Referencias para WhatsApp',
            'texto' => $texto,
            'volver' => route('seccionk.admin.referencias', $request->query()),
            'descargar' => route('seccionk.admin.referencias.exportar', array_merge($request->query(), ['formato' => 'descargar'])),
        ]);
    }

    private function consultaReferencias(Request $request)
    {
        $query = SeccionKPromocion::query()->orderBy('apellido_paterno')->orderBy('nombre');

        if (! $request->boolean('mostrar_inactivos')) {
            $query->where('activo', true);
        }

        if ($request->get('filtro') !== 'todas') {
            $query->whereNotNull('referencias')->where('referencias', '!=', '');
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('dni', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                    ->orWhere('celular', 'like', "%{$buscar}%")
                    ->orWhere('referencias', 'like', "%{$buscar}%");
            });
        }

        return $query;
    }
}
