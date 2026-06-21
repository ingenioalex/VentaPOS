<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloDueno
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->rol !== 'dueno') {
            abort(403, 'Solo el administrador de la empresa puede acceder a esta sección.');
        }

        return $next($request);
    }
}
