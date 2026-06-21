<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\ExtrasEmpresaService;
use App\Services\PermisoService;
use App\Services\PlanLimiteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminContratacionController extends Controller
{
    public function __construct(
        private PermisoService $permisoService,
        private ExtrasEmpresaService $extrasService,
        private PlanLimiteService $limites,
    ) {}

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $empresas = Empresa::withCount('usuarios')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contratacion.index', compact('empresas', 'q'));
    }

    public function show(Empresa $empresa)
    {
        $modulos = $this->permisoService->modulosContratados($empresa);
        $extras = $this->extrasService->contratados($empresa);
        $desglose = $this->limites->desglose($empresa);
        $planesDisponibles = config('planes.planes_disponibles', ['beta', 'basico', 'negocio', 'pro']);
        $planInfo = config('planes.'.$empresa->plan, []);
        $modulosPorPlan = collect($planesDisponibles)
            ->mapWithKeys(fn (string $plan) => [$plan => $this->permisoService->modulosPorPlan($plan)])
            ->all();

        return view('admin.contratacion.show', compact(
            'empresa', 'modulos', 'extras', 'desglose', 'planesDisponibles', 'planInfo', 'modulosPorPlan'
        ));
    }

    public function actualizarPlan(Request $request, Empresa $empresa)
    {
        $planes = config('planes.planes_disponibles', ['beta', 'basico', 'negocio', 'pro']);

        $datos = $request->validate([
            'plan' => ['required', Rule::in($planes)],
            'aplicar_modulos' => 'boolean',
        ]);

        $empresa->update(['plan' => $datos['plan']]);

        if ($request->boolean('aplicar_modulos')) {
            $this->permisoService->guardarModulosContratados(
                $empresa,
                $this->permisoService->modulosPorPlan($datos['plan'])
            );
        }

        $nombre = config('planes.'.$datos['plan'].'.nombre', ucfirst($datos['plan']));
        $msg = "Plan actualizado a {$nombre}.";
        if ($request->boolean('aplicar_modulos')) {
            $msg .= ' Módulos del plan aplicados.';
        }

        return redirect()->route('admin.contratacion.show', $empresa)->with('ok', $msg);
    }
}
