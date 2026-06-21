<?php

namespace App\Http\Middleware;

use App\Services\OnboardingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiereOnboarding
{
    public function __construct(private OnboardingService $onboarding) {}

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->empresa) {
            return $next($request);
        }

        if ($this->onboarding->completado($usuario->empresa)) {
            return $next($request);
        }

        if (! $usuario->esDueno()) {
            abort(403, 'La empresa aún no completó la configuración inicial. Contacte al administrador.');
        }

        if ($this->onboarding->pasoActual($usuario->empresa) >= 3
            && ($request->routeIs('productos.*') || $request->routeIs('categorias.*'))) {
            return $next($request);
        }

        return redirect()->route('onboarding.index');
    }
}
