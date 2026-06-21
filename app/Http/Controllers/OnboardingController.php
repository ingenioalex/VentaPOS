<?php

namespace App\Http\Controllers;

use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function __construct(private OnboardingService $onboarding) {}

    public function index()
    {
        $empresa = Auth::user()->empresa;

        if ($this->onboarding->completado($empresa)) {
            return redirect()->route('app.inicio');
        }

        $ctx = $this->onboarding->contexto($empresa);

        return view('app.onboarding.index', array_merge(['empresa' => $empresa], $ctx));
    }

    public function pasoEmpresa(Request $request)
    {
        $empresa = Auth::user()->empresa;

        $datos = $request->validate([
            'nombre' => 'required|string|max:150',
            'ruc' => 'nullable|string|size:11',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'tipo_negocio' => 'required|in:bodega,ferreteria,farmacia,otro',
            'serie_boleta' => 'required|string|max:10',
            'serie_factura' => 'required|string|max:10',
        ]);

        $this->onboarding->guardarPasoEmpresa($empresa, $datos);

        return redirect()->route('onboarding.index')->with('ok', 'Datos del negocio guardados.');
    }

    public function pasoTerminal(Request $request)
    {
        $empresa = Auth::user()->empresa;
        $empresaId = $empresa->id;
        $config = $empresa->configuracion ?? [];
        $terminalId = (int) ($config['onboarding_terminal_id'] ?? 0);

        if ($terminalId <= 0) {
            $terminalId = (int) \App\Models\Terminal::where('empresa_id', $empresaId)->value('id');
        }

        $datos = $request->validate([
            'codigo' => [
                'required', 'string', 'max:10',
                Rule::unique('terminales', 'codigo')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($terminalId),
            ],
            'nombre' => 'required|string|max:100',
            'local' => 'nullable|string|max:100',
            'caja' => 'required|string|max:10',
        ]);

        $this->onboarding->guardarPasoTerminal($empresa, $datos);

        return redirect()->route('onboarding.index')->with('ok', 'Terminal configurada.');
    }

    public function pasoProductos()
    {
        $empresa = Auth::user()->empresa;
        $this->onboarding->avanzarPasoProductos($empresa);

        return redirect()->route('onboarding.index')->with('ok', 'Paso de productos listo.');
    }

    public function pasoCajero(Request $request)
    {
        $empresa = Auth::user()->empresa;
        $empresaId = $empresa->id;

        $datos = $request->validate([
            'nombre' => 'required|string|max:120',
            'usuario' => [
                'required', 'string', 'max:60', 'alpha_dash',
                Rule::unique('usuarios', 'usuario')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'email' => 'nullable|email|max:150',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $this->onboarding->guardarPasoCajero($empresa, $datos);

        return redirect()->route('app.inicio')
            ->with('ok', '¡Configuración inicial completada! Ya puede usar VentaPOS.');
    }

    public function omitirCajero()
    {
        $this->onboarding->omitirCajero(Auth::user()->empresa);

        return redirect()->route('app.inicio')
            ->with('ok', 'Configuración inicial completada. Puede crear cajeros después en Usuarios.');
    }
}
