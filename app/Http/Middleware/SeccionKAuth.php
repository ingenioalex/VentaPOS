<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SeccionKAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('seccionk')->check()) {
            return redirect()->route('seccionk.admin.login');
        }

        return $next($request);
    }
}
