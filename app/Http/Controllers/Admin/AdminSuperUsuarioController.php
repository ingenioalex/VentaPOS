<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prospecto;
use App\Rules\ContrasenaSuperAdmin;
use App\Models\Usuario;
use App\Services\AdminRecuperacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminSuperUsuarioController extends Controller
{
    public function __construct(private AdminRecuperacionService $recuperacion) {}

    public function index()
    {
        $superUsuarios = Usuario::where('rol', 'super_admin')
            ->whereNull('empresa_id')
            ->orderBy('nombre')
            ->get();

        return view('admin.super-usuarios.index', compact('superUsuarios'));
    }

    public function crear()
    {
        return view('admin.super-usuarios.form', ['superUsuario' => new Usuario]);
    }

    public function guardar(Request $request)
    {
        $datos = $this->validar($request);

        Usuario::create(array_merge($datos, [
            'empresa_id' => null,
            'rol' => 'super_admin',
            'activo' => true,
        ]));

        return redirect()->route('admin.super-usuarios.index')
            ->with('ok', 'Super usuario creado.');
    }

    public function editar(Usuario $superUsuario)
    {
        $this->autorizar($superUsuario);

        return view('admin.super-usuarios.form', ['superUsuario' => $superUsuario]);
    }

    public function actualizar(Request $request, Usuario $superUsuario)
    {
        $this->autorizar($superUsuario);

        $datos = $this->validar($request, $superUsuario);
        $superUsuario->update($datos);

        return redirect()->route('admin.super-usuarios.index')
            ->with('ok', 'Super usuario actualizado.');
    }

    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        $reglas = [
            'nombre' => 'required|string|max:120',
            'usuario' => [
                'required',
                'string',
                'max:60',
                'alpha_dash',
                Rule::unique('usuarios', 'usuario')
                    ->where(fn ($q) => $q->whereNull('empresa_id')->where('rol', 'super_admin'))
                    ->ignore($usuario?->id),
            ],
            'email' => 'nullable|email|max:150',
            'email_recuperacion' => 'nullable|email|max:150',
            'pregunta_secreta' => 'nullable|string|max:255',
            'respuesta_secreta' => 'nullable|string|max:255|confirmed',
        ];

        $quiereRecuperacion = $request->filled('email_recuperacion')
            || $request->filled('pregunta_secreta')
            || $request->filled('respuesta_secreta')
            || ($usuario && $usuario->tieneRecuperacionActiva());

        if ($quiereRecuperacion) {
            $reglas['email_recuperacion'] = 'required|email|max:150';
            $reglas['pregunta_secreta'] = 'required|string|max:255';
            if ($request->filled('respuesta_secreta') || ! $usuario?->respuesta_secreta) {
                $reglas['respuesta_secreta'] = 'required|string|max:255|confirmed';
            }
        }

        if ($request->filled('password') || ! $usuario) {
            $reglas['password'] = ['required', 'string', 'confirmed', new ContrasenaSuperAdmin];
        }

        $datos = $request->validate($reglas);

        if ($usuario) {
            $datos['activo'] = $request->boolean('activo');

            if ($usuario->id === Auth::id() && ! $datos['activo']) {
                throw ValidationException::withMessages([
                    'activo' => 'No puede desactivar su propio usuario.',
                ]);
            }

            if (empty($datos['password'])) {
                unset($datos['password']);
            }
        }

        if ($request->filled('respuesta_secreta')) {
            $datos['respuesta_secreta'] = $this->recuperacion->hashRespuesta($datos['respuesta_secreta']);
        } else {
            unset($datos['respuesta_secreta']);
        }

        unset($datos['respuesta_secreta_confirmation']);

        if (! $quiereRecuperacion && $usuario) {
            unset($datos['email_recuperacion'], $datos['pregunta_secreta']);
        }

        return $datos;
    }

    private function autorizar(Usuario $usuario): void
    {
        if ($usuario->rol !== 'super_admin' || $usuario->empresa_id !== null) {
            abort(404);
        }
    }
}
