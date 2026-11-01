<?php

namespace App\Providers;

use App\Models\Producto;
use App\Services\BackupConfigService;
use App\Services\LoteService;
use App\Services\TarifaService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.ventapos');

        RateLimiter::for('admin-login', function (Request $request) {
            $clave = $request->ip().'|'.strtolower((string) $request->input('usuario'));

            return Limit::perMinute(5)->by($clave)->response(function () {
                return back()->withErrors([
                    'usuario' => 'Demasiados intentos. Espere un minuto e intente de nuevo.',
                ])->onlyInput('usuario');
            });
        });

        RateLimiter::for('beta-form', function (Request $request) {
            return Limit::perHour(8)->by($request->ip());
        });

        RateLimiter::for('admin-recuperar', function (Request $request) {
            return Limit::perHour(30)->by($request->ip())->response(function () {
                return back()->withErrors([
                    'usuario' => 'Demasiados intentos de recuperación. Espere unos minutos e intente de nuevo.',
                ]);
            });
        });

        $this->app->booted(function () {
            app(TarifaService::class)->aplicarEnConfig();
            app(BackupConfigService::class)->aplicarEnConfig();
        });

        View::composer('layouts.guest', function ($view) {
            $map = [
                'inicio' => 'inicio',
                'precios' => 'precios',
                'contacto' => 'contacto',
                'faq' => 'faq',
            ];
            $routeName = request()->route()?->getName();
            $pages = config('seo.pages', []);
            $key = $routeName ? ($map[$routeName] ?? null) : null;
            $view->with('seoConfig', $key && isset($pages[$key]) ? $pages[$key] : []);
        });

        View::composer('layouts.app', function ($view) {
            $usuario = Auth::user();
            if (! $usuario) {
                $view->with('stockBajoNav', 0);
                $view->with('vencimientosNav', 0);

                return;
            }

            $view->with('stockBajoNav', $usuario->tieneModulo('productos')
                ? Producto::stockBajo()->count()
                : 0);

            $empresa = $usuario->empresa;
            $view->with('vencimientosNav', ($empresa && $empresa->tieneExtra('lotes_vencimiento') && $usuario->tieneModulo('inventario'))
                ? app(LoteService::class)->contarAlertas($empresa->id)
                : 0);
        });
    }
}
