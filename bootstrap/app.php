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
            // SeccionK en ruta /seccionk
            Route::middleware('web')
                ->prefix('seccionk')
                ->name('seccionk.')
                ->group(base_path('routes/seccionk.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\IdentificarEmpresa::class,
        ]);
        // Auth antes de route model binding: FiltraPorEmpresa necesita usuario autenticado.
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->alias([
            'modulo' => \App\Http\Middleware\VerificarModulo::class,
            'empresa' => \App\Http\Middleware\AsegurarEmpresa::class,
            'dueno' => \App\Http\Middleware\SoloDueno::class,
            'super_admin' => \App\Http\Middleware\SoloSuperAdmin::class,
            'onboarding' => \App\Http\Middleware\RequiereOnboarding::class,
            'seccionk.auth' => \App\Http\Middleware\SeccionKAuth::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            $adminPath = trim((string) config('admin.panel_path', 'admin'), '/');

            if ($request->is($adminPath) || $request->is($adminPath.'/*')) {
                return route('admin.login');
            }

            return '/login';
        });
        $middleware->redirectUsersTo(function () {
            $usuario = auth()->user();
            $adminPath = trim((string) config('admin.panel_path', 'admin'), '/');

            if ($usuario?->esSuperAdmin()) {
                return '/'.$adminPath;
            }

            return $usuario?->rutaInicio() ?? '/app';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

