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
| Ruta: ventasagil.com/seccionk
|
*/

// Rutas públicas
Route::get('/', [SeccionKPublicoController::class, 'index'])->name('index');
Route::post('/buscar', [SeccionKPublicoController::class, 'buscar'])->name('buscar');
Route::get('/perfil/{id}', [SeccionKPublicoController::class, 'perfil'])->name('perfil');
Route::post('/perfil/{id}/cuota/{evento}/comentario', [SeccionKPublicoController::class, 'guardarComentarioCuota'])
    ->name('perfil.cuota.comentario');
Route::get('/registro', [SeccionKPublicoController::class, 'registroForm'])->name('registro');
Route::post('/registro', [SeccionKPublicoController::class, 'registro'])->name('registro.store');

// Rutas de autenticación admin
Route::get('/admin/login', [SeccionKAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [SeccionKAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [SeccionKAuthController::class, 'logout'])->name('admin.logout');

// Rutas del panel admin (protegidas)
Route::middleware('seccionk.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Gestión de promociones
    Route::resource('promociones', AdminPromocionController::class)
        ->parameters(['promociones' => 'promocion']);
    Route::post('promociones/{promocion}/pagos/{evento}', [AdminPromocionController::class, 'registrarPago'])
        ->name('promociones.pagos.store');
    
    // Gestión de eventos
    Route::resource('eventos', AdminEventoController::class)
        ->parameters(['eventos' => 'evento']);
    Route::get('eventos/{evento}/participantes', [AdminEventoController::class, 'participantes'])
        ->name('eventos.participantes');
    Route::post('eventos/{evento}/participantes', [AdminEventoController::class, 'guardarParticipantes'])
        ->name('eventos.participantes.store');
});
