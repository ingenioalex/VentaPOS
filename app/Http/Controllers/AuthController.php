<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Services\OnboardingService;
use App\Services\SesionCajeroService;
use App\Services\TerminalSesionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private SesionCajeroService $sesionCajero,
        private TerminalSesionService $terminalSesion,
        private OnboardingService $onboarding,
    ) {}

    public function mostrarLogin()
    {
        $empresa = app()->bound('empresaActual') ? app('empresaActual') : null;

        return view('auth.login', compact('empresa'));
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
            'empresa_codigo' => 'nullable|string|max:80',
        ]);

        $porSubdominio = app()->bound('empresaActual');
        $codigoIngresado = trim($request->input('empresa_codigo', ''));
        $empresa = $this->resolverEmpresa($request);

        if (! $porSubdominio) {
            if ($codigoIngresado === '') {
                return back()->withErrors([
                    'empresa_codigo' => 'Ingrese el código de su empresa.',
                ])->onlyInput('usuario', 'empresa_codigo');
            }

            if (! $empresa) {
                return back()->withErrors([
                    'empresa_codigo' => 'Empresa no encontrada. Use el código exacto (ej: bodega-el-sol, minimarket-luna).',
                ])->onlyInput('usuario', 'empresa_codigo');
            }
        }

        if (! $empresa) {
            return back()->withErrors([
                'usuario' => 'No se pudo identificar la empresa de acceso.',
            ])->onlyInput('usuario', 'empresa_codigo');
        }

        $usuario = Usuario::where('usuario', $credenciales['usuario'])
            ->where('activo', true)
            ->where('empresa_id', $empresa->id)
            ->first();

        if (! $usuario || ! Hash::check($credenciales['password'], $usuario->password)) {
            return back()->withErrors([
                'usuario' => 'Usuario, empresa o contraseña incorrectos.',
            ])->onlyInput('usuario', 'empresa_codigo');
        }

        if ($usuario->empresa_id !== $empresa->id) {
            return back()->withErrors([
                'usuario' => 'Este usuario no pertenece a la empresa indicada.',
            ])->onlyInput('usuario', 'empresa_codigo');
        }

        if ($usuario->rol === 'cajero' && $usuario->en_sesion) {
            return back()->withErrors([
                'usuario' => 'Cajero ya en sesión. Solicite al supervisor el cierre de turno.',
            ])->onlyInput('usuario', 'empresa_codigo');
        }

        if ($usuario->rol !== 'dueno' && ! $this->onboarding->completado($empresa)) {
            return back()->withErrors([
                'usuario' => 'La empresa aún no completó la configuración inicial. El administrador debe finalizar el asistente de bienvenida.',
            ])->onlyInput('usuario', 'empresa_codigo');
        }

        Auth::login($usuario, $request->boolean('recordar'));
        $request->session()->regenerate();

        session([
            'tenant_empresa_id' => $empresa->id,
            'tenant_empresa_codigo' => $empresa->codigo,
        ]);

        try {
            $this->sesionCajero->abrirSesion($usuario, $request);
            $this->terminalSesion->inicializarSesion($usuario);

            if ($usuario->rol === 'cajero' && $this->terminalSesion->terminalesDisponibles($usuario)->isEmpty()) {
                throw new \RuntimeException('No tiene terminales asignadas. Contacte al administrador.');
            }
        } catch (\Throwable $e) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['usuario' => $e->getMessage()])->onlyInput('usuario', 'empresa_codigo');
        }

        return redirect()->intended($usuario->rutaInicio());
    }

    public function logout(Request $request)
    {
        $usuario = Auth::user();

        if ($usuario?->rol === 'cajero' && $usuario->en_sesion && ! session('cierre_x_completado')) {
            return back()->withErrors([
                'usuario' => 'El supervisor debe realizar el Cierre X y cerrar su sesión en Cierre de caja.',
            ]);
        }

        if ($usuario) {
            $this->sesionCajero->cerrarSesion($usuario);
            $this->terminalSesion->limpiarSesion();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }

    private function resolverEmpresa(Request $request): ?Empresa
    {
        if (app()->bound('empresaActual')) {
            return app('empresaActual');
        }

        $codigo = trim($request->input('empresa_codigo', ''));

        if ($codigo === '') {
            return null;
        }

        return Empresa::where('codigo', $codigo)->where('activo', true)->first();
    }
}
