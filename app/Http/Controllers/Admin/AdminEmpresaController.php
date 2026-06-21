<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Terminal;
use App\Models\Usuario;
use App\Services\OnboardingService;
use App\Services\PermisoService;
use App\Services\ExtrasEmpresaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminEmpresaController extends Controller
{
    public function __construct(
        private PermisoService $permisoService,
        private OnboardingService $onboarding,
        private ExtrasEmpresaService $extrasService,
    ) {}

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $empresas = Empresa::withCount('usuarios')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%")
                        ->orWhere('ruc', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('admin.empresas.index', compact('empresas', 'q'));
    }

    public function crear()
    {
        return view('admin.empresas.form', ['empresa' => new Empresa]);
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo' => ['required', 'string', 'max:80', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:empresas,codigo'],
            'tipo_negocio' => 'required|in:bodega,ferreteria,farmacia,otro',
            'ruc' => 'nullable|string|size:11',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'plan' => 'required|in:beta,basico,negocio,pro',
            'prueba_hasta' => 'nullable|date',
            'activo' => 'boolean',
            'dueno_nombre' => 'required|string|max:120',
            'dueno_usuario' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'dueno_email' => 'nullable|email|max:150',
            'dueno_password' => 'required|string|min:6|confirmed',
        ], [
            'codigo.regex' => 'El código de acceso solo puede usar letras, números, guiones y guiones bajos (ej: bodega-el-sol).',
            'dueno_usuario.regex' => 'El usuario del administrador solo puede usar letras, números, guiones y guiones bajos, sin espacios.',
        ]);

        $datos['codigo'] = strtolower($datos['codigo']);
        $datos['dueno_usuario'] = strtolower($datos['dueno_usuario']);

        DB::transaction(function () use ($datos, $request) {
            $empresa = Empresa::create([
                'nombre' => $datos['nombre'],
                'codigo' => $datos['codigo'],
                'tipo_negocio' => $datos['tipo_negocio'],
                'ruc' => $datos['ruc'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'plan' => $datos['plan'],
                'prueba_hasta' => $datos['prueba_hasta'] ?? null,
                'activo' => $request->boolean('activo', true),
                'configuracion' => [
                    'serie_boleta' => 'B001',
                    'serie_factura' => 'F001',
                    'onboarding_completado' => false,
                    'onboarding_paso' => 1,
                    'modulos_contratados' => $this->permisoService->modulosPorPlan($datos['plan']),
                    'extras' => $this->extrasService->defaultsParaTipo($datos['tipo_negocio']),
                    'cupos_usuarios' => [
                        'bonificados' => 0,
                        'extras_pago' => 0,
                        'nota_interna' => null,
                    ],
                ],
            ]);

            Terminal::create([
                'empresa_id' => $empresa->id,
                'codigo' => '01',
                'nombre' => 'Caja principal',
                'local' => $datos['direccion'] ?? null,
                'caja' => '01',
                'activo' => true,
            ]);

            Usuario::create([
                'empresa_id' => $empresa->id,
                'nombre' => $datos['dueno_nombre'],
                'usuario' => $datos['dueno_usuario'],
                'email' => $datos['dueno_email'] ?? null,
                'password' => $datos['dueno_password'],
                'rol' => 'dueno',
                'activo' => true,
            ]);
        });

        return redirect()->route('admin.empresas.index')
            ->with('ok', 'Empresa y administrador creados correctamente.');
    }

    public function editar(Empresa $empresa)
    {
        return view('admin.empresas.form', compact('empresa'));
    }

    public function actualizar(Request $request, Empresa $empresa)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('empresas', 'codigo')->ignore($empresa->id),
            ],
            'tipo_negocio' => 'required|in:bodega,ferreteria,farmacia,otro',
            'ruc' => 'nullable|string|size:11',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'prueba_hasta' => 'nullable|date',
            'activo' => 'boolean',
        ], [
            'codigo.regex' => 'El código de acceso solo puede usar letras, números, guiones y guiones bajos (ej: bodega-el-sol).',
        ]);

        $datos['codigo'] = strtolower($datos['codigo']);

        $empresa->update([
            'nombre' => $datos['nombre'],
            'codigo' => $datos['codigo'],
            'tipo_negocio' => $datos['tipo_negocio'],
            'ruc' => $datos['ruc'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'prueba_hasta' => $datos['prueba_hasta'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()->route('admin.empresas.index')->with('ok', 'Empresa actualizada.');
    }

    public function modulos(Empresa $empresa)
    {
        $contratables = $this->permisoService->modulosContratables();
        $contratados = $this->permisoService->modulosContratados($empresa);
        $totalMensual = $this->permisoService->totalMensualContrato($contratados);
        $porGrupo = collect($contratables)
            ->map(fn ($info, $clave) => array_merge($info, ['clave' => $clave]))
            ->groupBy('grupo');

        return view('admin.empresas.modulos', compact(
            'empresa', 'contratables', 'contratados', 'totalMensual', 'porGrupo'
        ));
    }

    public function actualizarModulos(Request $request, Empresa $empresa)
    {
        $validos = array_keys($this->permisoService->modulosContratables());

        $datos = $request->validate([
            'modulos' => 'nullable|array',
            'modulos.*' => Rule::in($validos),
        ]);

        $this->permisoService->guardarModulosContratados($empresa, $datos['modulos'] ?? []);

        return redirect()->route('admin.contratacion.modulos', $empresa)
            ->with('ok', 'Módulos contratados actualizados.');
    }

    public function aplicarModulosPlan(Request $request, Empresa $empresa)
    {
        $plan = $request->validate([
            'plan' => 'nullable|in:beta,basico,negocio,pro',
        ])['plan'] ?? $empresa->plan ?? 'basico';

        $this->permisoService->guardarModulosContratados(
            $empresa,
            $this->permisoService->modulosPorPlan($plan)
        );

        $nombrePlan = config('planes.'.$plan.'.nombre', ucfirst($plan));

        return redirect()->route('admin.contratacion.modulos', $empresa)
            ->with('ok', "Módulos del plan {$nombrePlan} aplicados y guardados.");
    }

    public function extras(Empresa $empresa)
    {
        $contratables = $this->extrasService->contratables();
        $contratados = $this->extrasService->contratados($empresa);
        $totalMensual = $this->extrasService->totalMensual($contratados);
        $modulosContratados = $this->permisoService->modulosContratados($empresa);
        $porGrupo = collect($contratables)
            ->map(fn ($info, $clave) => array_merge($info, ['clave' => $clave]))
            ->groupBy('grupo');

        return view('admin.empresas.extras', compact(
            'empresa', 'contratables', 'contratados', 'totalMensual', 'porGrupo', 'modulosContratados'
        ));
    }

    public function actualizarExtras(Request $request, Empresa $empresa)
    {
        $validos = array_keys($this->extrasService->contratables());

        $datos = $request->validate([
            'extras' => 'nullable|array',
            'extras.*' => Rule::in($validos),
        ]);

        try {
            $this->extrasService->guardarContratados(
                $empresa,
                $datos['extras'] ?? [],
                $this->permisoService
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('admin.contratacion.extras', $empresa)
            ->with('ok', 'Extras contratados actualizados.');
    }

    public function aplicarExtrasTipo(Empresa $empresa)
    {
        try {
            $this->extrasService->aplicarSugeridosPorTipo($empresa, $this->permisoService);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.contratacion.extras', $empresa)
            ->with('ok', 'Se aplicaron los extras sugeridos para '.ucfirst($empresa->tipo_negocio).'.');
    }

    public function reiniciarOnboarding(Empresa $empresa)
    {
        $this->onboarding->reiniciar($empresa);

        return redirect()->route('admin.empresas.editar', $empresa)
            ->with('ok', 'Asistente de configuración reiniciado. El administrador verá el wizard al ingresar.');
    }
}
