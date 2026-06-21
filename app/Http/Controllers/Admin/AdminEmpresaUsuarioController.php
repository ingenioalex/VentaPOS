<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Services\PlanLimiteService;
use App\Services\SesionCajeroService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminEmpresaUsuarioController extends Controller
{
    public function __construct(
        private SesionCajeroService $sesionCajero,
        private PlanLimiteService $limites,
    ) {}

    /** @return list<string> */
    private function rolesPermitidos(): array
    {
        return ['dueno', 'supervisor', 'cajero', 'digitador'];
    }

    public function index(Request $request, Empresa $empresa)
    {
        $q = trim($request->get('q', ''));

        $usuarios = Usuario::where('empresa_id', $empresa->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('usuario', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        $limiteUsuarios = $this->limites->limiteUsuarios($empresa);
        $usuariosActivos = $this->limites->usuariosActivos($empresa);
        $desglose = $this->limites->desglose($empresa);

        return view('admin.empresas.usuarios.index', compact(
            'empresa', 'usuarios', 'q', 'limiteUsuarios', 'usuariosActivos', 'desglose'
        ));
    }

    public function crear(Empresa $empresa)
    {
        if (! $this->limites->puedeCrearUsuario($empresa)) {
            return redirect()->route('admin.contratacion.cupos', $empresa)
                ->withErrors(['limite' => $this->limites->mensajeLimiteAlcanzado($empresa)]);
        }

        return view('admin.empresas.usuarios.form', [
            'empresa' => $empresa,
            'usuarioEdit' => new Usuario(['empresa_id' => $empresa->id]),
            'roles' => $this->rolesPermitidos(),
        ]);
    }

    public function guardar(Request $request, Empresa $empresa)
    {
        if (! $this->limites->puedeCrearUsuario($empresa)) {
            throw ValidationException::withMessages([
                'usuario' => $this->limites->mensajeLimiteAlcanzado($empresa),
            ]);
        }

        try {
            $datos = $this->validar($request, $empresa);
        } catch (ValidationException $e) {
            if (isset($e->errors()['usuario'])) {
                $existente = Usuario::where('empresa_id', $empresa->id)
                    ->where('usuario', $request->input('usuario'))
                    ->first();

                if ($existente) {
                    return redirect()->route('admin.empresas.usuarios.index', $empresa)
                        ->with('ok', "El usuario «{$existente->usuario}» ya fue registrado. Revíselo en el listado.");
                }
            }

            throw $e;
        }

        Usuario::create(array_merge($datos, [
            'empresa_id' => $empresa->id,
            'activo' => true,
        ]));

        return redirect()->route('admin.empresas.usuarios.index', $empresa)
            ->with('ok', 'Usuario creado correctamente.');
    }

    public function editar(Empresa $empresa, Usuario $usuario)
    {
        $this->autorizar($empresa, $usuario);

        return view('admin.empresas.usuarios.form', [
            'empresa' => $empresa,
            'usuarioEdit' => $usuario,
            'roles' => $this->rolesPermitidos(),
        ]);
    }

    public function actualizar(Request $request, Empresa $empresa, Usuario $usuario)
    {
        $this->autorizar($empresa, $usuario);

        $datos = $this->validar($request, $empresa, $usuario);

        $activara = array_key_exists('activo', $datos) && $datos['activo'] && ! $usuario->activo;
        if ($activara && ! $this->limites->puedeActivarUsuario($empresa, $usuario->activo)) {
            throw ValidationException::withMessages([
                'activo' => $this->limites->mensajeLimiteAlcanzado($empresa),
            ]);
        }

        if ($usuario->en_sesion && array_key_exists('activo', $datos) && ! $datos['activo']) {
            $this->sesionCajero->cerrarSesionForzada($usuario);
        }

        $usuario->update($datos);

        return redirect()->route('admin.empresas.usuarios.index', $empresa)
            ->with('ok', 'Usuario actualizado.');
    }

    public function eliminar(Empresa $empresa, Usuario $usuario)
    {
        $this->autorizar($empresa, $usuario);
        $this->validarEliminacion($empresa, $usuario);

        if ($usuario->en_sesion) {
            $this->sesionCajero->cerrarSesionForzada($usuario);
        }

        $usuario->delete();

        return redirect()->route('admin.empresas.usuarios.index', $empresa)
            ->with('ok', 'Usuario eliminado.');
    }

    public function cerrarSesion(Empresa $empresa, Usuario $usuario)
    {
        $this->autorizar($empresa, $usuario);

        if (! $usuario->en_sesion) {
            return back()->withErrors(['sesion' => 'El usuario no tiene sesión activa.']);
        }

        $this->sesionCajero->cerrarSesionForzada($usuario);

        return back()->with('ok', "Sesión de {$usuario->nombre} cerrada.");
    }

    private function validar(Request $request, Empresa $empresa, ?Usuario $usuario = null): array
    {
        $reglas = [
            'nombre' => 'required|string|max:120',
            'usuario' => [
                'required',
                'string',
                'max:60',
                'alpha_dash',
                Rule::unique('usuarios', 'usuario')
                    ->where(fn ($q) => $q->where('empresa_id', $empresa->id))
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

            if ($usuario->rol === 'dueno' && ! $datos['activo']) {
                $this->validarUltimoDuenoActivo($empresa, $usuario);
            }
        }

        if ($usuario && empty($datos['password'])) {
            unset($datos['password']);
        }

        if (! $usuario) {
            $datos['activo'] = true;
        }

        return $datos;
    }

    private function autorizar(Empresa $empresa, Usuario $usuario): void
    {
        if ($usuario->empresa_id !== $empresa->id || $usuario->rol === 'super_admin') {
            abort(404);
        }
    }

    private function validarEliminacion(Empresa $empresa, Usuario $usuario): void
    {
        if ($usuario->rol === 'dueno') {
            $duenosActivos = Usuario::where('empresa_id', $empresa->id)
                ->where('rol', 'dueno')
                ->where('activo', true)
                ->count();

            if ($duenosActivos <= 1 && $usuario->activo) {
                throw ValidationException::withMessages([
                    'usuario' => 'No puede eliminar el único administrador activo de la empresa.',
                ]);
            }
        }
    }

    private function validarUltimoDuenoActivo(Empresa $empresa, Usuario $usuario): void
    {
        $otrosDuenos = Usuario::where('empresa_id', $empresa->id)
            ->where('rol', 'dueno')
            ->where('activo', true)
            ->where('id', '!=', $usuario->id)
            ->exists();

        if (! $otrosDuenos) {
            throw ValidationException::withMessages([
                'activo' => 'Debe haber al menos un administrador activo en la empresa.',
            ]);
        }
    }
}
