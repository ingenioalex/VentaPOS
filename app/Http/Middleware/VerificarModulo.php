<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarModulo
{
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->tieneModulo($modulo)) {
            abort(403, 'No tiene permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
