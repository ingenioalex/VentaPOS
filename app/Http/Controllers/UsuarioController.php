<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\PlanLimiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    public function __construct(private PlanLimiteService $limites) {}

    /** @return list<string> */
    private function rolesPermitidos(): array
    {
        return config('permisos.roles_gestionables', []);
    }

    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $q = trim($request->get('q', ''));

        $usuarios = Usuario::where('empresa_id', $empresaId)
            ->whereIn('rol', $this->rolesPermitidos())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('usuario', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $empresa = Auth::user()->empresa;
        $limiteUsuarios = $empresa ? $this->limites->limiteUsuarios($empresa) : 0;
        $usuariosActivos = $empresa ? $this->limites->usuariosActivos($empresa) : 0;
        $puedeCrear = $empresa ? $this->limites->puedeCrearUsuario($empresa) : false;
        $desglose = $empresa ? $this->limites->desglose($empresa) : null;

        return view('app.usuarios.index', compact('usuarios', 'q', 'empresa', 'limiteUsuarios', 'usuariosActivos', 'puedeCrear', 'desglose'));
    }

    public function crear()
    {
        $empresa = Auth::user()->empresa;
        if ($empresa && ! $this->limites->puedeCrearUsuario($empresa)) {
            return redirect()->route('usuarios.index')
                ->withErrors(['limite' => $this->limites->mensajeLimiteAlcanzado($empresa)]);
        }

        return view('app.usuarios.form', [
            'usuarioEdit' => new Usuario,
            'roles' => $this->rolesPermitidos(),
        ]);
    }

    public function guardar(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $empresa = Auth::user()->empresa;

        if ($empresa && ! $this->limites->puedeCrearUsuario($empresa)) {
            throw ValidationException::withMessages([
                'usuario' => $this->limites->mensajeLimiteAlcanzado($empresa),
            ]);
        }

        try {
            $datos = $this->validar($request);
        } catch (ValidationException $e) {
            if (isset($e->errors()['usuario'])) {
                $existente = Usuario::where('empresa_id', $empresaId)
                    ->where('usuario', $request->input('usuario'))
                    ->first();

                if ($existente) {
                    return redirect()->route('usuarios.index')
                        ->with('ok', "El usuario «{$existente->usuario}» ya fue registrado. Revíselo en el listado.");
                }
            }

            throw $e;
        }

        Usuario::create(array_merge($datos, [
            'empresa_id' => $empresaId,
            'activo' => true,
        ]));

        return redirect()->route('usuarios.index')->with('ok', 'Usuario creado correctamente.');
    }

    public function editar(Usuario $usuario)
    {
        $this->autorizar($usuario);

        return view('app.usuarios.form', [
            'usuarioEdit' => $usuario,
            'roles' => $this->rolesPermitidos(),
        ]);
    }

    public function actualizar(Request $request, Usuario $usuario)
    {
        $this->autorizar($usuario);

        $datos = $this->validar($request, $usuario);

        $activara = array_key_exists('activo', $datos) && $datos['activo'] && ! $usuario->activo;
        $empresa = Auth::user()->empresa;
        if ($activara && $empresa && ! $this->limites->puedeActivarUsuario($empresa, $usuario->activo)) {
            throw ValidationException::withMessages([
                'activo' => $this->limites->mensajeLimiteAlcanzado($empresa),
            ]);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        $empresaId = Auth::user()->empresa_id;

        $reglas = [
            'nombre' => 'required|string|max:120',
            'usuario' => [
                'required',
                'string',
                'max:60',
                'alpha_dash',
                Rule::unique('usuarios', 'usuario')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($usuario?->id),
            ],
            'email' => 'nullable|email|max:150',
            'rol' => ['required', Rule::in($this->rolesPermitidos())],
            'password' => $usuario
                ? 'nullable|string|min:6|confirmed'
                : 'required|string|min:6|confirmed',
        ];

        $datos = $request->validate($reglas, [
            'usuario.unique' => 'El usuario de acceso ya está registrado en esta empresa.',
        ]);

        if ($usuario) {
            $datos['activo'] = $request->boolean('activo');
        }

        if ($usuario && empty($datos['password'])) {
            unset($datos['password']);
        }

        if ($usuario && $usuario->id === Auth::id() && ! $datos['activo']) {
            throw ValidationException::withMessages([
                'activo' => 'No puede desactivar su propio usuario.',
            ]);
        }

        if (! $usuario) {
            $datos['activo'] = true;
        }

        return $datos;
    }

    private function autorizar(Usuario $usuario): void
    {
        if ($usuario->empresa_id !== Auth::user()->empresa_id) {
            abort(404);
        }

        if (! in_array($usuario->rol, $this->rolesPermitidos(), true)) {
            abort(403, 'No puede gestionar este usuario.');
        }
    }
}
