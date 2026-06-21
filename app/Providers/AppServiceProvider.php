<?php

namespace App\Providers;

use App\Models\Producto;
use App\Services\BackupConfigService;
use App\Services\LoteService;
use App\Services\TarifaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        $this->app->booted(function () {
            app(TarifaService::class)->aplicarEnConfig();
            app(BackupConfigService::class)->aplicarEnConfig();
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
