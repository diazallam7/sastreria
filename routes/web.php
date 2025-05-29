<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\GastoVarioController;
use App\Http\Controllers\ProductoVentaController;

Route::get('/',[homeController::class,'index'])->name('panel');
route::resource('profiles',profileController::class);
route::resource('users',userController::class);
route::resource('roles',roleController::class);



Route::prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index'); // Listar clientes
    Route::get('/crear', [ClienteController::class, 'create'])->name('create'); // Crear cliente
    Route::post('/', [ClienteController::class, 'store'])->name('store'); // Guardar cliente
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show'); // Ver cliente
    Route::get('/{cliente}/editar', [ClienteController::class, 'edit'])->name('edit'); // Editar cliente
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update'); // Actualizar cliente
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy'); // Eliminar cliente
    Route::patch('/clientes/{id}/estado', [ClienteController::class, 'updateEstado'])->name('clientes.updateEstado');
    Route::get('clientes/{cliente}/historial', [ClienteController::class, 'historial'])->name('clientes.historial');
});


Route::get('configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
Route::patch('configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');



Route::prefix('alquileres')->name('alquileres.')->group(function () {
    Route::get('/', [AlquilerController::class, 'index'])->name('index'); // Listar alquileres
    Route::get('/crear', [AlquilerController::class, 'create'])->name('create'); // Crear alquiler
    Route::post('/', [AlquilerController::class, 'store'])->name('store'); // Guardar alquiler
    Route::delete('/{alquiler}', [AlquilerController::class, 'destroy'])->name('destroy'); // Eliminar alquiler

Route::patch('/alquileres/{alquiler}/devolver', [AlquilerController::class, 'devolver'])->name('alquileres.devolver');
});

Route::prefix('compras')->name('compras.')->group(function () {
    Route::get('/', [CompraController::class, 'index'])->name('index');
    Route::get('/create', [CompraController::class, 'create'])->name('create');
    Route::post('/', [CompraController::class, 'store'])->name('store');
    Route::get('/{compra}', [CompraController::class, 'show'])->name('show');
    Route::get('/{compra}/edit', [CompraController::class, 'edit'])->name('edit');
    Route::put('/{compra}', [CompraController::class, 'update'])->name('update');
    Route::delete('/{compra}', [CompraController::class, 'destroy'])->name('destroy');
    Route::patch('/{compra}/activar', [CompraController::class, 'activarParaVenta'])->name('activar');
    Route::patch('/{compra}/desactivar', [CompraController::class, 'desactivarParaVenta'])->name('desactivar');
});

// Rutas para productos de venta manuales
Route::prefix('productos-venta')->name('productos-venta.')->group(function () {
    Route::get('/', [ProductoVentaController::class, 'index'])->name('index');
    Route::get('/create', [ProductoVentaController::class, 'create'])->name('create');
    Route::post('/', [ProductoVentaController::class, 'store'])->name('store');
    Route::get('/{producto}', [ProductoVentaController::class, 'show'])->name('show');
    Route::get('/{producto}/edit', [ProductoVentaController::class, 'edit'])->name('edit');
    Route::put('/{producto}', [ProductoVentaController::class, 'update'])->name('update');
    Route::delete('/{producto}', [ProductoVentaController::class, 'destroy'])->name('destroy');
});
Route::prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/', [VentaController::class, 'index'])->name('index');
    Route::get('/crear', [VentaController::class, 'create'])->name('create');
    Route::post('/', [VentaController::class, 'store'])->name('store');
    Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
    Route::get('/{venta}/edit', [VentaController::class, 'edit'])->name('edit');
    Route::put('/{venta}', [VentaController::class, 'update'])->name('update');
    Route::delete('/{venta}', [VentaController::class, 'destroy'])->name('destroy');
    
    // Rutas AJAX para obtener datos dinámicos
    Route::post('/obtener-talles', [VentaController::class, 'obtenerTalles'])->name('obtenerTalles');
    Route::post('/obtener-precio', [VentaController::class, 'obtenerPrecio'])->name('obtenerPrecio');
});

Route::resource('gastos-varios', GastoVarioController::class)->names([
    'index' => 'gastos-varios.index',
    'create' => 'gastos-varios.create',
    'store' => 'gastos-varios.store',
    'edit' => 'gastos-varios.edit',
    'update' => 'gastos-varios.update',
    'destroy' => 'gastos-varios.destroy'
]);


Route::prefix('devoluciones')->name('devoluciones.')->group(function () {
    Route::get('/', [DevolucionController::class, 'index'])->name('index');
    Route::get('/calcular-multas/{id}', [DevolucionController::class, 'calcularMultas'])->name('calcular-multas');
    Route::post('/procesar/{id}', [DevolucionController::class, 'procesarDevolucion'])->name('procesar');
    Route::get('/comprobante/{id}', [DevolucionController::class, 'comprobante'])->name('comprobante');
    Route::get('/historial', [DevolucionController::class, 'historial'])->name('historial');
    Route::post('/actualizar-estado/{id}', [DevolucionController::class, 'actualizarEstado'])->name('actualizar-estado');
});


use App\Http\Controllers\ReporteController;
use App\Http\Controllers\StockAlquilerController;

Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    Route::get('/alquileres', [ReporteController::class, 'alquileres'])->name('alquileres');
    Route::get('/ventas', [ReporteController::class, 'ventas'])->name('ventas');
});

// Rutas para el stock de alquiler
Route::prefix('stock')->name('stock.')->group(function () {
    Route::prefix('alquiler')->name('alquiler.')->group(function () {
        Route::get('/', [StockAlquilerController::class, 'index'])->name('index');
        Route::get('/create', [StockAlquilerController::class, 'create'])->name('create');
        Route::post('/', [StockAlquilerController::class, 'store'])->name('store');
        Route::get('/{item}', [StockAlquilerController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [StockAlquilerController::class, 'edit'])->name('edit');
        Route::put('/{item}', [StockAlquilerController::class, 'update'])->name('update');
        Route::delete('/{item}', [StockAlquilerController::class, 'destroy'])->name('destroy');
    });
});


use App\Http\Controllers\FacturaController;

Route::get('factura/alquiler/{alquiler}', [FacturaController::class, 'alquiler'])->name('factura.alquiler');
Route::get('factura/venta/{venta}', [FacturaController::class, 'venta'])->name('factura.venta');


Route::get('/forbidden', [loginController::class, 'index'])->name('login');



Route::get('/login', [loginController::class, 'index'])->name('login');
Route::post('/login', [loginController::class, 'login']);
Route::get('/logout', [logoutController::class, 'logout'])->name('logout');

Route::get('/401', function () {
   return view('pages.401');
});

Route::get('/404', function () {
    return view('pages.404');
});

Route::get('/500', function () {
    return view('pages.500');
});


