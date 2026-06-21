<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AsegurarEmpresa
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return $next($request);
        }

        if ($usuario->esSuperAdmin() && ($request->is('app') || $request->is('app/*'))) {
            return redirect()->route('admin.inicio');
        }

        if (! $usuario->empresa_id && $usuario->rol !== 'super_admin') {
            abort(403, 'Su usuario no tiene empresa asignada.');
        }

        if ($usuario->rol === 'cajero' && ! $usuario->en_sesion) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['usuario' => 'Su sesión fue cerrada por el supervisor.']);
        }

        if ($usuario->empresa_id) {
            $empresaTenant = $request->attributes->get('empresa')
                ?? (app()->bound('empresaActual') ? app('empresaActual') : null);

            $sessionEmpresaId = (int) session('tenant_empresa_id', 0);

            if ($sessionEmpresaId && $sessionEmpresaId !== $usuario->empresa_id) {
                return $this->cerrarPorEmpresaInvalida($request);
            }

            if ($empresaTenant && $usuario->empresa_id !== $empresaTenant->id) {
                return $this->cerrarPorEmpresaInvalida($request);
            }

            if ($empresaTenant && $sessionEmpresaId && $sessionEmpresaId !== $empresaTenant->id) {
                return $this->cerrarPorEmpresaInvalida($request);
            }

            if (! $empresaTenant && ! $sessionEmpresaId) {
                return $this->cerrarPorEmpresaInvalida($request);
            }

            if (! app()->bound('empresaActual')) {
                app()->instance('empresaActual', $usuario->empresa);
            }
        }

        return $next($request);
    }

    private function cerrarPorEmpresaInvalida(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['usuario' => 'Este usuario no pertenece a la empresa de este acceso.']);
    }
}
