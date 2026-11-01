<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    public function mostrarLogin()
    {
        if (Auth::check() && Auth::user()->esSuperAdmin()) {
            return redirect()->route('admin.inicio');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'usuario' => 'required|string|max:60',
            'password' => 'required|string|max:255',
        ]);

        $usuario = Usuario::where('usuario', $credenciales['usuario'])
            ->where('rol', 'super_admin')
            ->whereNull('empresa_id')
            ->where('activo', true)
            ->first();

        if (! $usuario || ! Hash::check($credenciales['password'], $usuario->password)) {
            Log::warning('Intento fallido panel admin', [
                'usuario' => $credenciales['usuario'],
                'ip' => $request->ip(),
                'agent' => $request->userAgent(),
            ]);

            return back()->withErrors([
                'usuario' => 'Credenciales incorrectas.',
            ])->onlyInput('usuario');
        }

        Auth::login($usuario, $request->boolean('recordar'));
        $request->session()->regenerate();
        $request->session()->forget(['tenant_empresa_id', 'tenant_empresa_codigo', 'cierre_x_completado']);

        return redirect()->intended(route('admin.inicio'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
