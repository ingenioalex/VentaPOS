<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Services\SunatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $config = $this->sunat->config($empresa);

        $datos = $request->validate([
            'activo' => 'sometimes|boolean',
            'modo' => 'required|in:simulacion,produccion',
            'proveedor_ose' => 'required|in:generico,nubefact',
            'usuario_sol' => 'nullable|string|max:60',
            'clave_sol' => 'nullable|string|max:120',
            'certificado' => 'nullable|string|max:255',
            'clave_certificado' => 'nullable|string|max:120',
            'ose_url' => 'nullable|url|max:255',
            'nubefact_ruta' => 'nullable|string|max:120',
            'nubefact_token' => 'nullable|string|max:200',
        ]);

        $datos['activo'] = $request->boolean('activo');

        if ($datos['activo'] && empty($empresa->ruc)) {
            return back()->withErrors(['activo' => 'Registre el RUC de la empresa en Configuración antes de activar SUNAT.']);
        }

        if ($datos['activo'] && $datos['modo'] === 'produccion') {
            if ($datos['proveedor_ose'] === 'nubefact') {
                $tieneRuta = ($datos['nubefact_ruta'] ?? '') !== '' || ($config['nubefact_ruta'] ?? '') !== '';
                $tieneToken = ($datos['nubefact_token'] ?? '') !== '' || ($this->tieneTokenNubefactGuardado($empresa));
                if (! $tieneRuta || ! $tieneToken) {
                    return back()->withErrors(['nubefact_ruta' => 'En producción con Nubefact indique ruta y token API.']);
                }
            } elseif (empty($datos['ose_url']) && empty($config['ose_url'])) {
                return back()->withErrors(['ose_url' => 'En producción indique la URL del OSE/PSE o elija Nubefact.']);
            }
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

        try {
            $xml = $this->sunat->obtenerORegenerarXml($comprobante);
        } catch (\Throwable $e) {
            abort(404, $e->getMessage());
        }

        $nombre = sprintf('%s-%08d.xml', $comprobante->serie, $comprobante->numero);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
        ]);
    }

    public function descargarCdr(Comprobante $comprobante)
    {
        $this->autorizar($comprobante);

        if (! $comprobante->sunat_cdr) {
            abort(404, 'CDR no disponible para este comprobante.');
        }

        if (! Storage::disk('local')->exists($comprobante->sunat_cdr)) {
            abort(404, 'El archivo CDR no está en el servidor. Reenvíe el comprobante a SUNAT en producción.');
        }

        $nombre = basename($comprobante->sunat_cdr);

        return response(Storage::disk('local')->get($comprobante->sunat_cdr), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
        ]);
    }

    private function autorizar(Comprobante $comprobante): void
    {
        if ($comprobante->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }
    }

    private function tieneTokenNubefactGuardado(\App\Models\Empresa $empresa): bool
    {
        $raw = ($empresa->configuracion['sunat']['nubefact_token'] ?? '');

        return is_string($raw) && $raw !== '';
    }
}
