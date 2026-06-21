<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentificarEmpresa
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $partes = explode('.', $host);

        if (count($partes) >= 3) {
            $subdominio = $partes[0];
            if (! in_array($subdominio, ['www', 'app', 'api'], true)) {
                $empresa = Empresa::where('codigo', $subdominio)->where('activo', true)->first();
                if ($empresa) {
                    app()->instance('empresaActual', $empresa);
                    $request->attributes->set('empresa', $empresa);
                }
            }
        }

        return $next($request);
    }
}
