<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CierreCajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionEmpresaController;
use App\Http\Controllers\FlejeController;
use App\Http\Controllers\GuiaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SunatController;
use App\Http\Controllers\TerminalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioTerminalController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'inicio'])->name('inicio');
Route::get('/precios', [LandingController::class, 'precios'])->name('precios');
Route::get('/contacto', [LandingController::class, 'contacto'])->name('contacto');
Route::post('/beta', [ProspectoController::class, 'beta'])->name('beta.store');

Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'empresa'])->prefix('app')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding/paso-empresa', [OnboardingController::class, 'pasoEmpresa'])->name('onboarding.paso-empresa');
    Route::post('/onboarding/paso-terminal', [OnboardingController::class, 'pasoTerminal'])->name('onboarding.paso-terminal');
    Route::post('/onboarding/paso-productos', [OnboardingController::class, 'pasoProductos'])->name('onboarding.paso-productos');
    Route::post('/onboarding/paso-cajero', [OnboardingController::class, 'pasoCajero'])->name('onboarding.paso-cajero');
    Route::post('/onboarding/omitir-cajero', [OnboardingController::class, 'omitirCajero'])->name('onboarding.omitir-cajero');

    Route::middleware('onboarding')->group(function () {
    Route::get('/', [AppController::class, 'inicio'])->middleware('modulo:inicio')->name('app.inicio');
    Route::get('/guia', [GuiaController::class, 'index'])->name('guia.index');

    Route::middleware('modulo:pos')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::middleware('modulo:pos_movil')->group(function () {
            Route::get('/pos/movil', [PosController::class, 'movil'])->name('pos.movil');
        });
        Route::get('/pos/buscar', [PosController::class, 'buscar'])->name('pos.buscar');
        Route::get('/pos/buscar-exacto', [PosController::class, 'buscarExacto'])->name('pos.buscar-exacto');
        Route::get('/pos/mas-vendidos', [PosController::class, 'masVendidos'])->name('pos.mas-vendidos');
        Route::get('/pos/cliente', [PosController::class, 'buscarCliente'])->name('pos.cliente.buscar');
        Route::post('/pos/cliente', [PosController::class, 'guardarCliente'])->name('pos.cliente.guardar');
        Route::post('/pos/cobrar', [PosController::class, 'cobrar'])->name('pos.cobrar');
        Route::post('/pos/suspender', [PosController::class, 'suspender'])->name('pos.suspender');
        Route::post('/pos/recuperar', [PosController::class, 'recuperar'])->name('pos.recuperar');
        Route::get('/pos/suspendidas', [PosController::class, 'listarSuspendidas'])->name('pos.suspendidas');
        Route::get('/pos/resumen-turno', [PosController::class, 'resumenTurno'])->name('pos.resumen-turno');
        Route::post('/pos/cierre-x', [PosController::class, 'cierreX'])->name('pos.cierre-x');
        Route::post('/pos/cerrar-sesion', [PosController::class, 'cerrarSesion'])->name('pos.cerrar-sesion');
        Route::get('/pos/ticket-cierre/{cierre}', [PosController::class, 'ticketCierre'])->name('pos.ticket-cierre');
        Route::get('/pos/ticket/{comprobante}', [PosController::class, 'ticket'])->name('pos.ticket');
        Route::get('/pos/ticket/{comprobante}/qr', [PosController::class, 'qrComprobante'])->name('pos.qr');
        Route::post('/pos/cambiar-terminal', [PosController::class, 'cambiarTerminal'])->name('pos.cambiar-terminal');
    });

    Route::middleware('modulo:productos')->group(function () {
        Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/importar', [ProductoController::class, 'importarForm'])->name('productos.importar');
        Route::get('/productos/plantilla-csv', [ProductoController::class, 'plantillaCsv'])->name('productos.plantilla');
        Route::post('/productos/importar', [ProductoController::class, 'importar'])->name('productos.importar.procesar');
        Route::get('/productos/nuevo', [ProductoController::class, 'crear'])->name('productos.crear');
        Route::post('/productos', [ProductoController::class, 'guardar'])->name('productos.guardar');
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'editar'])->name('productos.editar');
        Route::put('/productos/{producto}', [ProductoController::class, 'actualizar'])->name('productos.actualizar');
        Route::get('/flejes', [FlejeController::class, 'index'])->name('flejes.index');
        Route::post('/flejes/imprimir', [FlejeController::class, 'imprimir'])->name('flejes.imprimir');
    });

    Route::middleware('modulo:categorias')->group(function () {
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::post('/categorias', [CategoriaController::class, 'guardar'])->name('categorias.guardar');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'actualizar'])->name('categorias.actualizar');
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'eliminar'])->name('categorias.eliminar');
    });

    Route::middleware('modulo:ventas')->group(function () {
        Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/{comprobante}', [VentaController::class, 'detalle'])->name('ventas.detalle');
        Route::post('/ventas/{comprobante}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    });

    Route::middleware('modulo:cierres')->group(function () {
        Route::get('/cierres', [CierreCajaController::class, 'index'])->name('cierres.index');
        Route::post('/cierres/x', [CierreCajaController::class, 'cerrarX'])->name('cierres.x');
        Route::post('/cierres/z', [CierreCajaController::class, 'cerrarZ'])->name('cierres.z');
        Route::get('/cierres/{cierre}/ticket', [CierreCajaController::class, 'ticket'])->name('cierres.ticket');
        Route::post('/cierres/cajero/{cajero}/cierre-x', [CierreCajaController::class, 'cierreXCajero'])
            ->name('cierres.cajero.cierre-x');
        Route::post('/cierres/cajero/{cajero}/cerrar-sesion', [CierreCajaController::class, 'cerrarSesionCajero'])
            ->name('cierres.cajero.cerrar-sesion');
    });

    Route::middleware('modulo:reportes')->group(function () {
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
        Route::get('/reportes/compras', [ReporteController::class, 'compras'])->name('reportes.compras');
        Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
        Route::get('/reportes/exportar-excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar-excel');
        Route::get('/reportes/exportar-compras', [ReporteController::class, 'exportarCompras'])->name('reportes.exportar-compras');
        Route::get('/reportes/exportar-compras-excel', [ReporteController::class, 'exportarComprasExcel'])->name('reportes.exportar-compras-excel');
        Route::get('/reportes/exportar-stock', [ReporteController::class, 'exportarStock'])->name('reportes.exportar-stock');
        Route::get('/reportes/exportar-stock-excel', [ReporteController::class, 'exportarStockExcel'])->name('reportes.exportar-stock-excel');
    });

    Route::middleware('modulo:clientes')->group(function () {
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/nuevo', [ClienteController::class, 'crear'])->name('clientes.crear');
        Route::post('/clientes', [ClienteController::class, 'guardar'])->name('clientes.guardar');
        Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'editar'])->name('clientes.editar');
        Route::put('/clientes/{cliente}', [ClienteController::class, 'actualizar'])->name('clientes.actualizar');
    });

    Route::middleware(['dueno', 'modulo:usuarios'])->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/nuevo', [UsuarioController::class, 'crear'])->name('usuarios.crear');
        Route::post('/usuarios', [UsuarioController::class, 'guardar'])->name('usuarios.guardar');
        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'editar'])->name('usuarios.editar');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'actualizar'])->name('usuarios.actualizar');
    });

    Route::middleware(['dueno', 'modulo:permisos'])->group(function () {
        Route::get('/permisos', [PermisoController::class, 'index'])->name('permisos.index');
        Route::put('/permisos', [PermisoController::class, 'actualizar'])->name('permisos.actualizar');
    });

    Route::middleware('modulo:inventario')->group(function () {
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::get('/inventario/stock', [InventarioController::class, 'stock'])->name('inventario.stock');
        Route::get('/inventario/kardex/{producto}', [InventarioController::class, 'kardex'])->name('inventario.kardex');
        Route::get('/inventario/entrada', [InventarioController::class, 'entrada'])->name('inventario.entrada');
        Route::post('/inventario/entrada', [InventarioController::class, 'guardarEntrada'])->name('inventario.entrada.guardar');
        Route::get('/inventario/salida', [InventarioController::class, 'salida'])->name('inventario.salida');
        Route::post('/inventario/salida', [InventarioController::class, 'guardarSalida'])->name('inventario.salida.guardar');
        Route::get('/inventario/vencimientos', [InventarioController::class, 'vencimientos'])->name('inventario.vencimientos');
        Route::get('/inventario/ajuste', [InventarioController::class, 'ajuste'])->name('inventario.ajuste');
        Route::post('/inventario/ajuste', [InventarioController::class, 'guardarAjuste'])->name('inventario.ajuste.guardar');
        Route::get('/inventario/exportar-stock', [InventarioController::class, 'exportarStock'])->name('inventario.exportar');
        Route::get('/inventario/exportar-stock-excel', [InventarioController::class, 'exportarStockExcel'])->name('inventario.exportar-excel');
        Route::get('/inventario/exportar-movimientos', [InventarioController::class, 'exportarMovimientos'])->name('inventario.exportar-movimientos');
        Route::get('/inventario/exportar-movimientos-excel', [InventarioController::class, 'exportarMovimientosExcel'])->name('inventario.exportar-movimientos-excel');
    });

    Route::middleware('modulo:proveedores')->group(function () {
        Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/proveedores/nuevo', [ProveedorController::class, 'crear'])->name('proveedores.crear');
        Route::post('/proveedores', [ProveedorController::class, 'guardar'])->name('proveedores.guardar');
        Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'editar'])->name('proveedores.editar');
        Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'actualizar'])->name('proveedores.actualizar');
    });

    Route::middleware('modulo:compras')->group(function () {
        Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
        Route::get('/compras/nueva', [CompraController::class, 'crear'])->name('compras.crear');
        Route::post('/compras', [CompraController::class, 'guardar'])->name('compras.guardar');
        Route::get('/compras/buscar-productos', [CompraController::class, 'buscarProductos'])->name('compras.buscar-productos');
        Route::get('/compras/buscar-exacto', [CompraController::class, 'buscarExactoProducto'])->name('compras.buscar-exacto');
        Route::get('/compras/{compra}', [CompraController::class, 'detalle'])->name('compras.detalle');
        Route::post('/compras/{compra}/recibir', [CompraController::class, 'recibir'])->name('compras.recibir');
        Route::post('/compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    });

    Route::middleware(['dueno', 'modulo:configuracion'])->group(function () {
        Route::get('/configuracion', [ConfiguracionEmpresaController::class, 'index'])->name('configuracion.index');
        Route::put('/configuracion', [ConfiguracionEmpresaController::class, 'actualizar'])->name('configuracion.actualizar');
    });

    Route::middleware(['dueno', 'modulo:sunat'])->group(function () {
        Route::get('/sunat', [SunatController::class, 'index'])->name('sunat.index');
        Route::put('/sunat', [SunatController::class, 'actualizar'])->name('sunat.actualizar');
        Route::post('/sunat/comprobantes/{comprobante}/reenviar', [SunatController::class, 'reenviar'])->name('sunat.reenviar');
        Route::get('/sunat/comprobantes/{comprobante}/xml', [SunatController::class, 'descargarXml'])->name('sunat.xml');
        Route::get('/sunat/comprobantes/{comprobante}/cdr', [SunatController::class, 'descargarCdr'])->name('sunat.cdr');
    });

    Route::middleware(['dueno', 'modulo:terminales'])->group(function () {
        Route::get('/terminales', [TerminalController::class, 'index'])->name('terminales.index');
        Route::get('/terminales/nuevo', [TerminalController::class, 'crear'])->name('terminales.crear');
        Route::post('/terminales', [TerminalController::class, 'guardar'])->name('terminales.guardar');
        Route::get('/terminales/{terminal}/editar', [TerminalController::class, 'editar'])->name('terminales.editar');
        Route::put('/terminales/{terminal}', [TerminalController::class, 'actualizar'])->name('terminales.actualizar');
    });

    Route::middleware(['dueno', 'modulo:usuario_terminales'])->group(function () {
        Route::get('/usuario-terminales', [UsuarioTerminalController::class, 'index'])->name('usuario-terminales.index');
        Route::get('/usuario-terminales/{usuario}/editar', [UsuarioTerminalController::class, 'editar'])->name('usuario-terminales.editar');
        Route::put('/usuario-terminales/{usuario}', [UsuarioTerminalController::class, 'actualizar'])->name('usuario-terminales.actualizar');
    });
    });
});
