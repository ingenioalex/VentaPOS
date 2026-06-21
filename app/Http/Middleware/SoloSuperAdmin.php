<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->esSuperAdmin()) {
            abort(403, 'Acceso restringido a super administradores.');
        }

        return $next($request);
    }
}
