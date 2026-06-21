<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\PlanLimiteService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminEmpresaLimiteUsuarioController extends Controller
{
    public function __construct(private PlanLimiteService $limites) {}

    public function editar(Empresa $empresa)
    {
        $desglose = $this->limites->desglose($empresa);
        $cupos = $this->limites->cuposPersonalizados($empresa);
        $usuarioExtra = config('planes.usuario_extra', []);

        return view('admin.empresas.limites-usuarios', compact(
            'empresa', 'desglose', 'cupos', 'usuarioExtra'
        ));
    }

    public function actualizar(Request $request, Empresa $empresa)
    {
        $datos = $request->validate([
            'bonificados' => 'required|integer|min:0|max:50',
            'extras_pago' => 'required|integer|min:0|max:50',
            'nota_interna' => 'nullable|string|max:500',
        ]);

        $nuevoTotal = $this->limites->limiteBasePlan($empresa)
            + (int) $datos['bonificados']
            + (int) $datos['extras_pago'];

        $activos = $this->limites->usuariosActivos($empresa);
        if ($activos > $nuevoTotal) {
            throw ValidationException::withMessages([
                'bonificados' => "Hay {$activos} usuarios activos. El cupo total no puede ser menor (máximo permitido ahora: {$activos}).",
            ]);
        }

        $this->limites->guardarCupos(
            $empresa,
            (int) $datos['bonificados'],
            (int) $datos['extras_pago'],
            $datos['nota_interna'] ?? null,
        );

        return redirect()->route('admin.contratacion.cupos', $empresa)
            ->with('ok', 'Cupos de usuarios actualizados.');
    }
}
