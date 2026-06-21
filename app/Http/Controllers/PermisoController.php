<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisoController extends Controller
{
    public function __construct(private PermisoService $permisoService) {}

    public function index()
    {
        $empresa = Auth::user()->empresa;
        $modulos = $this->permisoService->modulosDisponibles();
        $roles = $this->permisoService->rolesConfigurables();
        $permisos = $this->permisoService->permisosEmpresa($empresa);
        $habilitados = $this->permisoService->modulosHabilitadosEmpresa($empresa);

        $modulosConfigurables = collect($modulos)
            ->except(['usuarios', 'permisos', 'configuracion'])
            ->only($habilitados)
            ->all();

        return view('app.permisos.index', compact('modulosConfigurables', 'roles', 'permisos', 'habilitados'));
    }

    public function actualizar(Request $request)
    {
        $empresa = Auth::user()->empresa;
        $modulosValidos = array_values(array_intersect(
            array_keys(
                collect($this->permisoService->modulosDisponibles())
                    ->except(['usuarios', 'permisos', 'configuracion'])
                    ->all()
            ),
            $this->permisoService->modulosHabilitadosEmpresa($empresa)
        ));

        $datos = $request->validate([
            'permisos' => 'required|array',
        ]);

        $permisos = [];
        foreach ($this->permisoService->rolesConfigurables() as $rol) {
            $modulos = $datos['permisos'][$rol] ?? [];
            $permisos[$rol] = is_array($modulos)
                ? array_values(array_intersect($modulos, $modulosValidos))
                : [];
        }

        $this->permisoService->guardarPermisos($empresa, $permisos);

        return redirect()->route('permisos.index')->with('ok', 'Permisos por rol actualizados.');
    }
}
