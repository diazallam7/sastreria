<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VestidoController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\DevolucionController;

Route::get('/',[homeController::class,'index'])->name('panel');
route::resource('profiles',profileController::class);
route::resource('users',userController::class);
route::resource('roles',roleController::class);


Route::view('/panel','panel.index')->name('panel');

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

Route::prefix('vestidos')->name('vestidos.')->group(function () {
    Route::get('/', [VestidoController::class, 'index'])->name('index'); // Listar vestidos
    Route::get('/crear', [VestidoController::class, 'create'])->name('create'); // Crear vestido
    Route::post('/', [VestidoController::class, 'store'])->name('store'); // Guardar vestido
    Route::get('/{vestido}/editar', [VestidoController::class, 'edit'])->name('edit'); // Editar vestido
    Route::put('/{vestido}', [VestidoController::class, 'update'])->name('update'); // Actualizar vestido
    Route::delete('/{vestido}', [VestidoController::class, 'destroy'])->name('destroy'); // Eliminar vestido

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

Route::prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/', [VentaController::class, 'index'])->name('index'); // Listar ventas
    Route::get('/crear', [VentaController::class, 'create'])->name('create'); // Crear venta
    Route::post('/', [VentaController::class, 'store'])->name('store'); // Guardar venta
    Route::delete('/{venta}', [VentaController::class, 'destroy'])->name('destroy'); // Eliminar venta
});
Route::post('/ventas/obtener-precio', [VentaController::class, 'obtenerPrecio'])->name('ventas.obtenerPrecio');




Route::get('/devoluciones', [DevolucionController::class, 'index'])->name('devoluciones.index');
Route::post('/devoluciones/{id}/procesar', [DevolucionController::class, 'procesar'])->name('devoluciones.procesar');
Route::get('/devoluciones/calcular-multas/{id}', [DevolucionController::class, 'calcularMultas'])->name('devoluciones.calcular-multas');
Route::post('/devoluciones/actualizar-estado/{id}', [DevolucionController::class, 'actualizarEstado'])->name('devoluciones.actualizar-estado');



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


