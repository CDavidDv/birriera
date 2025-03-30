<?php

use App\Events\OrderStatusChangedEvent;
use App\Http\Controllers\CheckInCheckOutController;
use App\Http\Controllers\CocinaController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Models\Pedidos;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// NO AUTH ROUTES 
Route::get('/', [DashboardController::class, 'index']);

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', ])->group(function () {
    // AUTH ROUTES
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name("dashboard");
    Route::get('/home', [DashboardController::class, 'home'])->name("home");

    Route::post('/cancelOrden', [PedidosController::class, 'cancelar']);
    // MESAS ROUTES
    Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
    Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
    Route::put('/mesas/{id}', [MesaController::class, 'update'])->name('mesas.update');
    Route::delete('/mesas/{id}', [MesaController::class, 'destroy'])->name('mesas.destroy');
    Route::post('/mesas/{id}/asignar-personas', [MesaController::class, 'asignarPersonas'])->name('mesas.asignarPersonas');
    Route::put('/mesaUpdate', [MesaController::class, 'updateMesa']);

    
    Route::post('/ventas', [VentaController::class, 'procesarVenta'])->name('ventas.procesar');

    Route::post('/pedidos', [PedidosController::class, 'crearPedido'])->name("crearPedido");
    Route::get('/getPedido/{id}', [PedidosController::class, 'getPedido'])->name("getPedido");

    Route::post('/completarPedido', [PedidosController::class, 'completarEntregar'])->name("completarPedido");
    Route::post('/productosEliminados', [PedidosController::class, 'productosEliminados'])->name("productosEliminados");
    Route::post('/enviaraCaja', [PedidosController::class, 'enviaraCaja'])->name("sendCaja");
    Route::post('/enviaraEntregar', [PedidosController::class, 'enviaraEntregar'])->name("enviaraEntregar");
    Route::post('/terminar_pedido', [PedidosController::class, 'terminar_pedido'])->name("terminar_pedido");

    Route::get('/Entregar', [CorteCajaController::class, 'entregar'])->name("Entregar");
    Route::post('/Entregar', [PedidosController::class, 'completarPedido']);

    Route::get('/inventario', [InventarioController::class, 'inventario'])->name("inventario");
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
    Route::put('/inventario/{inventario}', [InventarioController::class, 'update'])->name('inventario.update');
    Route::delete('/inventario/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');
    Route::post('/images/upload', [ImageController::class, 'upload']);
    Route::post('/api/images', [ImageController::class, 'upload']);

    Route::get('/images/all', [ImageController::class, 'getImages']);


    Route::get('/cocina', [CocinaController::class, 'index'])->name("cocina");
    Route::post('/checkInOut', [CheckInCheckOutController::class, 'checkInOut'])->name('checkInOut');

    Route::post('/checkout/{usuario}/{sucursal}', [CheckInCheckOutController::class, 'checkOut'])->name('checkout');

    Route::get('/personal', [UsuarioController::class, 'index'])->name("personal");
    Route::resource('users', UsuarioController::class);
    Route::resource('sucursales', SucursalController::class)->parameters([
        'sucursales' => 'sucursal',  // Esto asegura que el parámetro sea 'sucursal'
    ]);

    
    // Route::get('/fired', function(){
    //     broadcast(new OrderStatusChangedEvent()); 
    //     return 'fired'; 
    // });
    
    Route::get('/datos', [PedidosController::class, 'datos'])->name('datos.index');

    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    
    Route::get('/corte-caja/filtro', [CorteCajaController::class, 'corte']);
    Route::get('/caja', [CorteCajaController::class, 'caja'])->name('caja');
    Route::get('/corte-caja', [CorteCajaController::class, 'corte'])->name("corte-caja");
    Route::post('/corte-caja/guardar-gasto', [CorteCajaController::class, 'guardarOperacion']);
    Route::get('/corte-caja/obtener-datos', [CorteCajaController::class, 'obtenerDatos'])->name('corte-caja.obtenerDatos');
    
    Route::post('/corte-caja/guardar-inicial', [CorteCajaController::class, 'guardarInicial'])->name('corte-caja.guardar-inicial');
    Route::post('/corte-caja/guardar-final', [CorteCajaController::class, 'guardarFinal'])->name('corte-caja.guardar-final');
    Route::post('/corte-caja/cerrar-corte', [CorteCajaController::class, 'cerrarCorte'])->name('corte-caja.cerrar-corte');
    
    Route::post('/corte-caja/filtro', [CorteCajaController::class, 'filtro'])->name('corte-caja.filtro');


    Route::post('/print-ticket', [PrintController::class, 'printTicket']);
    Route::post('/search-check-ins', [CheckInCheckOutController::class, 'search'])->name('search-check-ins');

   
    Route::get('/empacar', [PedidosController::class, 'empacar'])->name('empacar');
    Route::post('/paraLlevar', [PedidosController::class, 'paraLlevar'])->name('paraLlevar');
});

// Redireccionar al inico si es otra url
Route::fallback(function () {
    return redirect()->route('dashboard');
});