<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            
            Route::domain('seccionk.ventasagil.com')
                ->middleware('web')
                ->group(base_path('routes/seccionk.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\IdentificarEmpresa::class,
        ]);
        $middleware->alias([
            'modulo' => \App\Http\Middleware\VerificarModulo::class,
            'empresa' => \App\Http\Middleware\AsegurarEmpresa::class,
            'dueno' => \App\Http\Middleware\SoloDueno::class,
            'super_admin' => \App\Http\Middleware\SoloSuperAdmin::class,
            'onboarding' => \App\Http\Middleware\RequiereOnboarding::class,
            'seccionk.auth' => \App\Http\Middleware\SeccionKAuth::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(function () {
            $usuario = auth()->user();

            if ($usuario?->esSuperAdmin()) {
                return '/admin';
            }

            return $usuario?->rutaInicio() ?? '/app';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
