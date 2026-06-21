<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminContratacionController;
use App\Http\Controllers\Admin\AdminEmpresaLimiteUsuarioController;
use App\Http\Controllers\Admin\AdminEmpresaController;
use App\Http\Controllers\Admin\AdminEmpresaUsuarioController;
use App\Http\Controllers\Admin\AdminSuperUsuarioController;
use App\Http\Controllers\Admin\AdminTarifaController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth', 'super_admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::redirect('/', '/admin/empresas')->name('inicio');

        Route::get('/empresas', [AdminEmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/nueva', [AdminEmpresaController::class, 'crear'])->name('empresas.crear');
        Route::post('/empresas', [AdminEmpresaController::class, 'guardar'])->name('empresas.guardar');
        Route::get('/empresas/{empresa}/editar', [AdminEmpresaController::class, 'editar'])->name('empresas.editar');
        Route::put('/empresas/{empresa}', [AdminEmpresaController::class, 'actualizar'])->name('empresas.actualizar');
        Route::post('/empresas/{empresa}/reiniciar-onboarding', [AdminEmpresaController::class, 'reiniciarOnboarding'])->name('empresas.reiniciar-onboarding');

        Route::get('/empresas/{empresa}/usuarios', [AdminEmpresaUsuarioController::class, 'index'])
            ->name('empresas.usuarios.index');
        Route::get('/empresas/{empresa}/usuarios/nuevo', [AdminEmpresaUsuarioController::class, 'crear'])
            ->name('empresas.usuarios.crear');
        Route::post('/empresas/{empresa}/usuarios', [AdminEmpresaUsuarioController::class, 'guardar'])
            ->name('empresas.usuarios.guardar');
        Route::get('/empresas/{empresa}/usuarios/{usuario}/editar', [AdminEmpresaUsuarioController::class, 'editar'])
            ->name('empresas.usuarios.editar');
        Route::put('/empresas/{empresa}/usuarios/{usuario}', [AdminEmpresaUsuarioController::class, 'actualizar'])
            ->name('empresas.usuarios.actualizar');
        Route::delete('/empresas/{empresa}/usuarios/{usuario}', [AdminEmpresaUsuarioController::class, 'eliminar'])
            ->name('empresas.usuarios.eliminar');
        Route::post('/empresas/{empresa}/usuarios/{usuario}/cerrar-sesion', [AdminEmpresaUsuarioController::class, 'cerrarSesion'])
            ->name('empresas.usuarios.cerrar-sesion');

        Route::prefix('contratacion')->name('contratacion.')->group(function () {
            Route::get('/', [AdminContratacionController::class, 'index'])->name('index');
            Route::get('/{empresa}', [AdminContratacionController::class, 'show'])->name('show');
            Route::put('/{empresa}/plan', [AdminContratacionController::class, 'actualizarPlan'])->name('plan');

            Route::get('/{empresa}/modulos', [AdminEmpresaController::class, 'modulos'])->name('modulos');
            Route::put('/{empresa}/modulos', [AdminEmpresaController::class, 'actualizarModulos'])->name('modulos.actualizar');
            Route::post('/{empresa}/modulos/aplicar-plan', [AdminEmpresaController::class, 'aplicarModulosPlan'])->name('modulos.aplicar-plan');

            Route::get('/{empresa}/extras', [AdminEmpresaController::class, 'extras'])->name('extras');
            Route::put('/{empresa}/extras', [AdminEmpresaController::class, 'actualizarExtras'])->name('extras.actualizar');
            Route::post('/{empresa}/extras/aplicar-tipo', [AdminEmpresaController::class, 'aplicarExtrasTipo'])->name('extras.aplicar-tipo');

            Route::get('/{empresa}/cupos', [AdminEmpresaLimiteUsuarioController::class, 'editar'])->name('cupos');
            Route::put('/{empresa}/cupos', [AdminEmpresaLimiteUsuarioController::class, 'actualizar'])->name('cupos.actualizar');
        });

        Route::get('/tarifas', [AdminTarifaController::class, 'index'])->name('tarifas.index');
        Route::put('/tarifas', [AdminTarifaController::class, 'actualizar'])->name('tarifas.actualizar');
        Route::post('/tarifas/restaurar', [AdminTarifaController::class, 'restaurar'])->name('tarifas.restaurar');

        Route::get('/super-usuarios', [AdminSuperUsuarioController::class, 'index'])->name('super-usuarios.index');
        Route::get('/super-usuarios/nuevo', [AdminSuperUsuarioController::class, 'crear'])->name('super-usuarios.crear');
        Route::post('/super-usuarios', [AdminSuperUsuarioController::class, 'guardar'])->name('super-usuarios.guardar');
        Route::get('/super-usuarios/{superUsuario}/editar', [AdminSuperUsuarioController::class, 'editar'])
            ->name('super-usuarios.editar');
        Route::put('/super-usuarios/{superUsuario}', [AdminSuperUsuarioController::class, 'actualizar'])
            ->name('super-usuarios.actualizar');

        Route::get('/backups', [AdminBackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [AdminBackupController::class, 'crear'])->name('backups.crear');
        Route::put('/backups/config', [AdminBackupController::class, 'actualizarConfig'])->name('backups.config');
        Route::get('/backups/{archivo}/descargar', [AdminBackupController::class, 'descargar'])
            ->where('archivo', 'ventapos_.+\.gz')
            ->name('backups.descargar');
        Route::delete('/backups/{archivo}', [AdminBackupController::class, 'eliminar'])
            ->where('archivo', 'ventapos_.+\.gz')
            ->name('backups.eliminar');
    });
});
