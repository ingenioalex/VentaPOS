<?php

use App\Http\Controllers\SeccionK\SeccionKPublicoController;
use App\Http\Controllers\SeccionK\SeccionKAuthController;
use App\Http\Controllers\SeccionK\Admin\AdminDashboardController;
use App\Http\Controllers\SeccionK\Admin\AdminPromocionController;
use App\Http\Controllers\SeccionK\Admin\AdminEventoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SecciónK Routes
|--------------------------------------------------------------------------
|
| Rutas para el módulo de gestión de la Promoción 2001 - Sección K
| Subdominio: seccionk.ventasagil.com
|
*/

// Rutas públicas
Route::get('/', [SeccionKPublicoController::class, 'index'])->name('seccionk.index');
Route::post('/buscar', [SeccionKPublicoController::class, 'buscar'])->name('seccionk.buscar');
Route::get('/perfil/{id}', [SeccionKPublicoController::class, 'perfil'])->name('seccionk.perfil');
Route::get('/registro', [SeccionKPublicoController::class, 'registroForm'])->name('seccionk.registro');
Route::post('/registro', [SeccionKPublicoController::class, 'registro'])->name('seccionk.registro.store');

// Rutas de autenticación admin
Route::get('/admin/login', [SeccionKAuthController::class, 'showLogin'])->name('seccionk.admin.login');
Route::post('/admin/login', [SeccionKAuthController::class, 'login'])->name('seccionk.admin.login.post');
Route::post('/admin/logout', [SeccionKAuthController::class, 'logout'])->name('seccionk.admin.logout');

// Rutas del panel admin (protegidas)
Route::middleware('seccionk.auth')->prefix('admin')->name('seccionk.admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gestión de promociones
    Route::resource('promociones', AdminPromocionController::class);
    
    // Gestión de eventos
    Route::resource('eventos', AdminEventoController::class);
    Route::get('eventos/{evento}/participantes', [AdminEventoController::class, 'participantes'])
        ->name('eventos.participantes');
    Route::post('eventos/{evento}/participantes', [AdminEventoController::class, 'guardarParticipantes'])
        ->name('eventos.participantes.store');
});
