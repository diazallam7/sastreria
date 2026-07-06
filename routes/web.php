<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\CierreCajaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\GastoVarioController;
use App\Http\Controllers\FacturaController;
use App\Livewire\Dashboard;

/*
|--------------------------------------------------------------------------
| Rutas públicas (invitados)
|--------------------------------------------------------------------------
*/
Route::get('/login', [loginController::class, 'index'])->name('login');
Route::post('/login', [loginController::class, 'login']);
Route::post('/logout', [logoutController::class, 'logout'])->name('logout')->middleware('auth');

Route::view('/401', 'pages.401');
Route::view('/404', 'pages.404');
Route::view('/500', 'pages.500');

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren sesión iniciada)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', Dashboard::class)->name('panel');

    Route::get('/perfil', App\Livewire\Perfil\Index::class)->name('profiles.index');

    // Usuarios (Livewire)
    Route::get('/users', App\Livewire\Usuarios\Index::class)->name('users.index')->middleware('permission:ver-user');
    Route::get('/users/crear', App\Livewire\Usuarios\Form::class)->name('users.create')->middleware('permission:crear-user');
    Route::get('/users/{user}/editar', App\Livewire\Usuarios\Form::class)->name('users.edit')->middleware('permission:editar-user');

    // Roles (Livewire)
    Route::get('/roles', App\Livewire\Roles\Index::class)->name('roles.index')->middleware('permission:ver-role');
    Route::get('/roles/crear', App\Livewire\Roles\Form::class)->name('roles.create')->middleware('permission:crear-role');
    Route::get('/roles/{role}/editar', App\Livewire\Roles\Form::class)->name('roles.edit')->middleware('permission:editar-role');

    // Clientes (Livewire)
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('/', App\Livewire\Clientes\Index::class)->name('index')->middleware('permission:ver-cliente');
        Route::get('/crear', App\Livewire\Clientes\Form::class)->name('create')->middleware('permission:crear-cliente');
        Route::get('/{cliente}/editar', App\Livewire\Clientes\Form::class)->name('edit')->middleware('permission:editar-cliente');
        Route::get('/{cliente}', App\Livewire\Clientes\Show::class)->name('show')->middleware('permission:ver-cliente');
    });

    // Configuraciones
    Route::get('/configuraciones', App\Livewire\Configuraciones\Index::class)->name('configuraciones.index');

    // Alquileres (Livewire)
    Route::prefix('alquileres')->name('alquileres.')->group(function () {
        Route::get('/', App\Livewire\Alquileres\Index::class)->name('index')->middleware('permission:ver-alquiler');
        Route::get('/crear', App\Livewire\Alquileres\Form::class)->name('create')->middleware('permission:crear-alquiler');
        Route::get('/{alquiler}/editar', App\Livewire\Alquileres\Form::class)->name('edit')->middleware('permission:editar-alquiler');
        Route::get('/{alquiler}', App\Livewire\Alquileres\Show::class)->name('show')->middleware('permission:ver-alquiler');
    });

    // Productos (Livewire — unifica comprados + fabricados)
    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/', App\Livewire\Productos\Index::class)->name('index')->middleware('permission:ver-producto');
        Route::get('/crear', App\Livewire\Productos\Form::class)->name('create')->middleware('permission:crear-producto');
        Route::get('/{producto}/editar', App\Livewire\Productos\Form::class)->name('edit')->middleware('permission:editar-producto');
        Route::get('/{producto}', App\Livewire\Productos\Show::class)->name('show')->middleware('permission:ver-producto');
    });

    // Reservas (Livewire)
    Route::prefix('reservas')->name('reservas.')->group(function () {
        Route::get('/', App\Livewire\Reservas\Index::class)->name('index')->middleware('permission:ver-reserva');
        Route::get('/crear', App\Livewire\Reservas\Form::class)->name('create')->middleware('permission:crear-reserva');
        Route::get('/{reserva}/editar', App\Livewire\Reservas\Form::class)->name('edit')->middleware('permission:editar-reserva');
        Route::get('/{reserva}', App\Livewire\Reservas\Show::class)->name('show')->middleware('permission:ver-reserva');
    });

    // Ventas (Livewire)
    Route::prefix('ventas')->name('ventas.')->group(function () {
        Route::get('/', App\Livewire\Ventas\Index::class)->name('index')->middleware('permission:ver-venta');
        Route::get('/crear', App\Livewire\Ventas\Form::class)->name('create')->middleware('permission:crear-venta');
        Route::get('/{venta}/editar', App\Livewire\Ventas\Form::class)->name('edit')->middleware('permission:editar-venta');
        Route::get('/{venta}', App\Livewire\Ventas\Show::class)->name('show')->middleware('permission:ver-venta');
    });

    // Gastos varios (Livewire)
    Route::get('/gastos-varios', App\Livewire\Gastos\Index::class)->name('gastos-varios.index');
    Route::get('/gastos-varios/crear', App\Livewire\Gastos\Form::class)->name('gastos-varios.create');
    Route::get('/gastos-varios/{gasto}/editar', App\Livewire\Gastos\Form::class)->name('gastos-varios.edit');

    // Devoluciones (Livewire)
    Route::prefix('devoluciones')->name('devoluciones.')->group(function () {
        Route::get('/', App\Livewire\Devoluciones\Index::class)->name('index')->middleware('permission:ver-devolucion');
        Route::get('/historial', App\Livewire\Devoluciones\Historial::class)->name('historial')->middleware('permission:ver-devolucion');
        Route::get('/comprobante/{devolucion}', App\Livewire\Devoluciones\Comprobante::class)->name('comprobante')->middleware('permission:ver-devolucion');
    });

    // Cierre de caja (Livewire + PDF por controller)
    Route::prefix('cierre-caja')->name('cierre-caja.')->group(function () {
        Route::get('/', App\Livewire\CierreCaja\Diario::class)->name('index')->middleware('permission:ver-cierre-caja');
        Route::get('/semanal', App\Livewire\CierreCaja\Semanal::class)->name('semanal')->middleware('permission:ver-cierre-caja-semanal');
        Route::get('/mensual', App\Livewire\CierreCaja\Mensual::class)->name('mensual')->middleware('permission:ver-cierre-caja-mensual');
        Route::get('/pdf', [CierreCajaController::class, 'exportarPDF'])->name('pdf');
        Route::get('/semanal/pdf', [CierreCajaController::class, 'exportarPDFSemanal'])->name('semanal.pdf');
        Route::get('/mensual/pdf', [CierreCajaController::class, 'exportarPDFMensual'])->name('mensual.pdf');
    });

    // Reportes (Livewire)
    Route::get('/reportes', App\Livewire\Reportes\Index::class)->name('reportes.index');

    // Stock de alquiler (Livewire)
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::prefix('alquiler')->name('alquiler.')->group(function () {
            Route::get('/', App\Livewire\StockAlquiler\Index::class)->name('index')->middleware('permission:ver-stock-alquiler');
            Route::get('/crear', App\Livewire\StockAlquiler\Form::class)->name('create')->middleware('permission:crear-stock-alquiler');
            Route::get('/{item}/editar', App\Livewire\StockAlquiler\Form::class)->name('edit')->middleware('permission:editar-stock-alquiler');
            Route::get('/{item}', App\Livewire\StockAlquiler\Show::class)->name('show')->middleware('permission:ver-stock-alquiler');
        });
    });

    // Facturas
    Route::get('factura/alquiler/{alquiler}', [FacturaController::class, 'alquiler'])->name('factura.alquiler');
    Route::get('factura/venta/{venta}', [FacturaController::class, 'venta'])->name('factura.venta');
});
