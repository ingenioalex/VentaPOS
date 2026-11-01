<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\ImpresoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpresoraController extends Controller
{
    public function __construct(private ImpresoraService $impresora) {}

    public function index()
    {
        $usuario = Auth::user();
        $empresa = $usuario->empresa;
        $puedeGestionar = $usuario->puedeGestionar();

        $usuariosEmpresa = $puedeGestionar
            ? Usuario::where('empresa_id', $usuario->empresa_id)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get()
                ->map(fn (Usuario $u) => [
                    'usuario' => $u,
                    'cfg' => $this->impresora->usuario($u),
                    'efectiva' => $this->impresora->paraUsuario($u),
                    'usaEmpresa' => $this->impresora->usaEmpresa($u),
                ])
            : collect();

        return view('app.impresora.index', [
            'empresaCfg' => $this->impresora->empresa($empresa),
            'usuarioCfg' => $this->impresora->usuario($usuario),
            'efectiva' => $this->impresora->paraUsuario($usuario),
            'usaEmpresa' => $this->impresora->usaEmpresa($usuario),
            'usuariosEmpresa' => $usuariosEmpresa,
            'anchos' => config('impresora.anchos', []),
            'tamanos' => config('impresora.tamanos_fuente', []),
            'metodos' => config('impresora.metodos', []),
            'esDueno' => $usuario->esDueno(),
            'puedeGestionar' => $puedeGestionar,
        ]);
    }

    public function guardarUsuario(Request $request)
    {
        return $this->persistirUsuario(Auth::user(), Auth::user(), $request, 'impresora.index');
    }

    public function guardarUsuarioGestionado(Request $request, Usuario $usuario)
    {
        $auth = Auth::user();
        $this->assertGestionUsuario($auth, $usuario);

        return $this->persistirUsuario($auth, $usuario, $request, 'impresora.index', true);
    }

    public function guardarEmpresa(Request $request)
    {
        $usuario = Auth::user();
        if (! $usuario->esDueno()) {
            abort(403);
        }

        $datos = $request->validate($this->impresora->reglasValidacion());
        $datos['auto_imprimir'] = $request->boolean('auto_imprimir');
        $datos['mostrar_logo'] = $request->boolean('mostrar_logo');

        $this->impresora->guardarEmpresa($usuario->empresa, $datos);

        return redirect()->route('impresora.index')->with('ok', 'Configuración de impresora de la empresa actualizada.');
    }

    public function prueba(Request $request)
    {
        return $this->vistaPrueba($request, Auth::user());
    }

    public function pruebaUsuario(Request $request, Usuario $usuario)
    {
        $this->assertGestionUsuario(Auth::user(), $usuario);

        return $this->vistaPrueba($request, $usuario);
    }

    private function persistirUsuario(
        Usuario $auth,
        Usuario $destino,
        Request $request,
        string $redirectRoute,
        bool $gestionAdmin = false,
    ) {
        $usaEmpresa = $request->boolean('usa_empresa');

        if ($usaEmpresa) {
            $this->impresora->guardarUsuario($destino, [], true);

            $msg = $gestionAdmin && $destino->id !== $auth->id
                ? "Impresora de {$destino->nombre}: usará el default de la empresa."
                : 'Usarás la configuración de impresora de la empresa.';

            return redirect()->route($redirectRoute)->with('ok', $msg);
        }

        $datos = $request->validate($this->impresora->reglasValidacion());
        $datos['auto_imprimir'] = $request->boolean('auto_imprimir');
        $datos['mostrar_logo'] = $request->boolean('mostrar_logo');

        $this->impresora->guardarUsuario($destino, $datos, false);

        $msg = $gestionAdmin && $destino->id !== $auth->id
            ? "Impresora de {$destino->nombre} guardada."
            : 'Tu configuración de impresora se guardó.';

        return redirect()->route($redirectRoute)->with('ok', $msg);
    }

    private function vistaPrueba(Request $request, Usuario $usuario)
    {
        $impresora = $this->impresora->paraUsuario($usuario);
        $estilos = $this->impresora->estilosTicket($impresora);
        $autoImprimir = $impresora['auto_imprimir'] || $request->boolean('imprimir');

        return view('app.impresora.prueba', compact('impresora', 'estilos', 'autoImprimir', 'usuario'));
    }

    private function assertGestionUsuario(Usuario $auth, Usuario $destino): void
    {
        if (! $auth->puedeGestionar()) {
            abort(403);
        }

        if ($destino->empresa_id !== $auth->empresa_id || ! $destino->activo) {
            abort(404);
        }
    }
}
