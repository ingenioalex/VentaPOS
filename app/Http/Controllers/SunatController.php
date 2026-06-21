<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Services\SunatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SunatController extends Controller
{
    public function __construct(private SunatService $sunat) {}

    public function index()
    {
        $empresa = Auth::user()->empresa;
        $config = $this->sunat->config($empresa);

        $comprobantesPendientes = Comprobante::where('empresa_id', $empresa->id)
            ->whereIn('sunat_estado', ['rechazado', 'error', 'pendiente'])
            ->orderByDesc('emitido_en')
            ->limit(15)
            ->get();

        return view('app.sunat.index', compact('empresa', 'config', 'comprobantesPendientes'));
    }

    public function actualizar(Request $request)
    {
        $empresa = Auth::user()->empresa;

        $datos = $request->validate([
            'activo' => 'sometimes|boolean',
            'modo' => 'required|in:simulacion,produccion',
            'usuario_sol' => 'nullable|string|max:60',
            'clave_sol' => 'nullable|string|max:120',
            'certificado' => 'nullable|string|max:255',
            'ose_url' => 'nullable|url|max:255',
        ]);

        $datos['activo'] = $request->boolean('activo');

        if ($datos['activo'] && empty($empresa->ruc)) {
            return back()->withErrors(['activo' => 'Registre el RUC de la empresa en Configuración antes de activar SUNAT.']);
        }

        $this->sunat->guardarConfig($empresa, $datos);

        return redirect()->route('sunat.index')->with('ok', 'Configuración SUNAT guardada.');
    }

    public function reenviar(Comprobante $comprobante)
    {
        $this->autorizar($comprobante);

        try {
            $this->sunat->reenviar($comprobante);

            return back()->with('ok', 'Comprobante reenviado a SUNAT.');
        } catch (\Throwable $e) {
            return back()->withErrors(['sunat' => $e->getMessage()]);
        }
    }

    public function descargarXml(Comprobante $comprobante)
    {
        $this->autorizar($comprobante);

        if (! $comprobante->sunat_xml) {
            abort(404);
        }

        $path = storage_path('app/'.$comprobante->sunat_xml);
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path, basename($path), ['Content-Type' => 'application/xml']);
    }

    public function descargarCdr(Comprobante $comprobante)
    {
        $this->autorizar($comprobante);

        if (! $comprobante->sunat_cdr) {
            abort(404);
        }

        $path = storage_path('app/'.$comprobante->sunat_cdr);
        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path, basename($path), ['Content-Type' => 'application/xml']);
    }

    private function autorizar(Comprobante $comprobante): void
    {
        if ($comprobante->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }
}
