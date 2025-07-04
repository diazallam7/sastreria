<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\CierreCajaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\GastoVarioController;
use App\Http\Controllers\ProductoVentaController;
use App\Http\Controllers\ReservaController;

Route::get('/',[homeController::class,'index'])->name('panel');
route::resource('profiles',profileController::class);
route::resource('users',userController::class);
route::resource('roles',roleController::class);



// Actualiza esta parte de tus rutas
Route::prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index'); // Listar clientes
    Route::get('/crear', [ClienteController::class, 'create'])->name('create'); // Crear cliente
    Route::post('/', [ClienteController::class, 'store'])->name('store'); // Guardar cliente
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show'); // Ver cliente
    Route::get('/{cliente}/editar', [ClienteController::class, 'edit'])->name('edit'); // Editar cliente
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update'); // Actualizar cliente
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy'); // Eliminar cliente
    Route::patch('/{id}/estado', [ClienteController::class, 'updateEstado'])->name('updateEstado');
    Route::get('/{cliente}/historial', [ClienteController::class, 'historial'])->name('historial');
});



Route::get('configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
Route::patch('configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');



Route::prefix('alquileres')->name('alquileres.')->group(function () {
    Route::get('/', [AlquilerController::class, 'index'])->name('index'); // Listar alquileres
    Route::get('/crear', [AlquilerController::class, 'create'])->name('create'); // Crear alquiler
    Route::post('/', [AlquilerController::class, 'store'])->name('store'); // Guardar alquiler
    Route::get('/{alquiler}', [AlquilerController::class, 'show'])->name('show');
    Route::get('/{alquiler}/edit', [AlquilerController::class, 'edit'])->name('edit');
    Route::put('/{alquiler}', [AlquilerController::class, 'update'])->name('update');
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

// Agregar estas rutas al archivo routes/web.php

// Rutas para reservas
Route::prefix('reservas')->name('reservas.')->group(function () {
    Route::get('/', [ReservaController::class, 'index'])->name('index');
    Route::get('/create', [ReservaController::class, 'create'])->name('create');
    Route::post('/', [ReservaController::class, 'store'])->name('store');
    Route::get('/{id}', [ReservaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ReservaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ReservaController::class, 'update'])->name('update');
    Route::delete('/{id}', [ReservaController::class, 'destroy'])->name('destroy');
    Route::match(['get', 'post'], '/{id}/convertir-alquiler', [ReservaController::class, 'convertirAAlquiler'])->name('convertir-alquiler');
    Route::patch('/{id}/cancelar', [ReservaController::class, 'cancelar'])->name('cancelar');
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
    Route::post('/alquileres/{alquiler}/devolver', [AlquilerController::class, 'devolver'])->name('alquileres.devolver');
    // Cambiar a PATCH
    Route::patch('/actualizar-estado/{id}', [DevolucionController::class, 'actualizarEstado'])->name('actualizar-estado');
});

Route::prefix('cierre-caja')->name('cierre-caja.')->group(function () {
    Route::get('/', [CierreCajaController::class, 'index'])->name('index');
    Route::post('/consultar', [CierreCajaController::class, 'consultarFecha'])->name('consultar');
    Route::get('/pdf', [CierreCajaController::class, 'exportarPDF'])->name('pdf');
    Route::get('/semanal', [CierreCajaController::class, 'resumenSemanal'])->name('semanal');
    Route::get('/mensual', [CierreCajaController::class, 'resumenMensual'])->name('mensual');
    Route::get('/cierre-caja/semanal/pdf', [CierreCajaController::class, 'exportarPDFSemanal'])->name('cierre-caja.semanal.pdf');
Route::get('/cierre-caja/mensual/pdf', [CierreCajaController::class, 'exportarPDFMensual'])->name('cierre-caja.mensual.pdf');
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


