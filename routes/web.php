<?php

use App\Http\Controllers\DetalleOrdenInsumoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MovimientoInsumoController;
use App\Http\Controllers\OrdenProduccionController;
use App\Http\Controllers\ProduccionDiariaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CotizacionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// RUTAS PÚBLICAS (Sin autenticación)
// ============================================
Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/about', [PagesController::class, 'about'])->name('about');
Route::get('/contact', [PagesController::class, 'contact'])->name('contact');
Route::get('/faq', [PagesController::class, 'faq'])->name('faq');
Route::get('/portfolio', [PagesController::class, 'portfolio'])->name('portfolio');

// ============================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ============================================
Route::middleware('auth')->group(function () {

    // Dashboard - Acceso para todos los usuarios autenticados
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================
    // SOLO ADMINISTRADOR
    // ============================================
    Route::middleware('role:Administrador')->group(function () {
        // Usuarios
        Route::resource('users', UserController::class);
        Route::get('users-data', [UserController::class, 'getUsers'])->name('users.data');
        Route::get('users-check-email', [UserController::class, 'checkEmail'])->name('users.check-email');

        // Clientes
        Route::resource('clientes', ClienteController::class);
        Route::get('clientes-data', [ClienteController::class, 'getClientes'])->name('clientes.data');
        Route::get('clientes-check-documento', [ClienteController::class, 'checkDocumento'])->name('clientes.check-documento');
        Route::get('clientes-check-email', [ClienteController::class, 'checkEmail'])->name('clientes.check-email');
        Route::get('clientes-search', [ClienteController::class, 'searchAjax'])->name('clientes.search');
        Route::get('/clientes/reporte/pdf', [ClienteController::class, 'exportarPDF'])->name('clientes.reporte.pdf');

        // Empleados
        Route::resource('empleados', EmpleadoController::class);
        Route::get('empleados-data', [EmpleadoController::class, 'getEmpleados'])->name('empleados.data');
        Route::get('empleados-check-documento', [EmpleadoController::class, 'checkDocumento'])->name('empleados.check-documento');
        Route::get('empleados-check-email', [EmpleadoController::class, 'checkEmail'])->name('empleados.check-email');
        Route::get('empleados-check-codigo', [EmpleadoController::class, 'checkCodigo'])->name('empleados.check-codigo');
        Route::get('/empleados/reporte/pdf', [EmpleadoController::class, 'reportePdf'])->name('empleados.reporte.pdf');
    });

    // ============================================
    // SOLO ADMINISTRADOR - CRUD Pedidos
    // ============================================
    Route::middleware('role:Administrador')->group(function () {
        Route::post('pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
        Route::get('pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create');
        Route::put('pedidos/{pedido}', [PedidoController::class, 'update'])->name('pedidos.update');
        Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
        Route::get('pedidos/{pedido}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');
    });

    // ============================================
    // SOLO ADMINISTRADOR - CRUD Cotizaciones
    // ============================================
    Route::middleware('role:Administrador')->group(function () {
        Route::post('cotizaciones', [CotizacionController::class, 'store'])->name('cotizaciones.store');
        Route::get('cotizaciones/create', [CotizacionController::class, 'create'])->name('cotizaciones.create');
        Route::put('cotizaciones/{cotizacion}', [CotizacionController::class, 'update'])->name('cotizaciones.update');
        Route::delete('cotizaciones/{cotizacion}', [CotizacionController::class, 'destroy'])->name('cotizaciones.destroy');
        Route::get('cotizaciones/{cotizacion}/edit', [CotizacionController::class, 'edit'])->name('cotizaciones.edit');
    });

    // ============================================
    // SOLO ADMINISTRADOR - CRUD Proveedores
    // ============================================
    Route::middleware('role:Administrador')->group(function () {
        Route::post('proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('proveedores/create', [ProveedorController::class, 'create'])->name('proveedores.create');
        Route::put('proveedores/{proveedore}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('proveedores/{proveedore}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        Route::get('proveedores/{proveedore}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
    });

    // ============================================
    // ADMIN Y SUPERVISOR - Lectura de Pedidos
    // ============================================
    Route::middleware('role:Administrador,Supervisor')->group(function () {
        Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos-data', [PedidoController::class, 'getPedidos'])->name('pedidos.data');
        Route::get('pedidos/cotizaciones-disponibles', [PedidoController::class, 'getCotizacionesDisponibles'])->name('pedidos.cotizacionesDisponibles');
        Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::get('pedidos/reporte/pdf', [PedidoController::class, 'reportePdf'])->name('pedidos.reporte.pdf');
        Route::get('pedidos/reporte', [PedidoController::class, 'reporteGeneral'])->name('pedidos.reporteGeneral');
        Route::get('pedidos/{pedido}/pdf', [PedidoController::class, 'pedidoPdf'])->name('pedidos.pdf');
    });

    // ============================================
    // ADMIN Y SUPERVISOR - Lectura de Cotizaciones
    // ============================================
    Route::middleware('role:Administrador,Supervisor')->group(function () {
        Route::get('cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
        Route::get('cotizaciones-data', [CotizacionController::class, 'getCotizaciones'])->name('cotizaciones.data');
        Route::get('cotizaciones/{cotizacion}', [CotizacionController::class, 'show'])->name('cotizaciones.show');
        Route::get('cotizaciones/reporte/pdf', [CotizacionController::class, 'reportePdf'])->name('cotizaciones.reporte.pdf');
        Route::get('cotizaciones/reporte', [CotizacionController::class, 'reporteGeneral'])->name('cotizaciones.reporteGeneral');
        Route::get('cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'cotizacionPdf'])->name('cotizaciones.pdf');

        // Rutas para conversión de cotización a pedido
        Route::put('cotizaciones/{cotizacion}/estado', [CotizacionController::class, 'updateEstado'])->name('cotizaciones.updateEstado');
        Route::get('cotizaciones/{cotizacion}/datos-para-pedido', [CotizacionController::class, 'getDatosParaPedido'])->name('cotizaciones.datosParaPedido');
        Route::post('cotizaciones/{cotizacion}/marcar-convertida', [CotizacionController::class, 'marcarComoConvertida'])->name('cotizaciones.marcarConvertida');
        Route::post('cotizaciones/{cotizacion}/convertir-a-pedido', [CotizacionController::class, 'convertirAPedido'])->name('cotizaciones.convertirAPedido');
    });

    // ============================================
    // ADMIN Y SUPERVISOR - Lectura de Proveedores
    // ============================================
    Route::middleware('role:Administrador,Supervisor')->group(function () {
        Route::get('proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('proveedores-data', [ProveedorController::class, 'getProveedores'])->name('proveedores.data');
        Route::get('proveedores/{proveedore}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::get('proveedores-check-rif', [ProveedorController::class, 'checkRif'])->name('proveedores.check-rif');
        Route::get('proveedores-check-documento', [ProveedorController::class, 'checkDocumento'])->name('proveedores.check-documento');
        Route::get('proveedores-check-email', [ProveedorController::class, 'checkEmail'])->name('proveedores.check-email');
        Route::get('proveedores/reporte/pdf', [ProveedorController::class, 'reportePdf'])->name('proveedores.reporte.pdf');
    });

    // ============================================
    // ADMIN Y SUPERVISOR - CRUD Completo
    // ============================================
    Route::middleware('role:Administrador,Supervisor')->group(function () {
        // Productos
        Route::resource('productos', ProductoController::class);
        Route::get('productos-data', [ProductoController::class, 'getProductos'])->name('productos.data');
        Route::get('productos/reporte/pdf', [ProductoController::class, 'reportePdf'])->name('productos.reporte.pdf');

        // Tipos de Producto
        Route::get('tipo-productos', [App\Http\Controllers\TipoProductoController::class, 'index'])->name('tipo-productos.index');
        Route::post('tipo-productos', [App\Http\Controllers\TipoProductoController::class, 'store'])->name('tipo-productos.store');
        Route::get('tipo-productos/{tipoProducto}', [App\Http\Controllers\TipoProductoController::class, 'show'])->name('tipo-productos.show');
        Route::put('tipo-productos/{tipoProducto}', [App\Http\Controllers\TipoProductoController::class, 'update'])->name('tipo-productos.update');
        Route::delete('tipo-productos/{tipoProducto}', [App\Http\Controllers\TipoProductoController::class, 'destroy'])->name('tipo-productos.destroy');
        Route::get('tipo-productos/{tipoProducto}/proximo-codigo', [App\Http\Controllers\TipoProductoController::class, 'proximoCodigo'])->name('tipo-productos.proximo-codigo');
        Route::get('tipo-productos-check-nombre', [App\Http\Controllers\TipoProductoController::class, 'checkNombre'])->name('tipo-productos.check-nombre');
        Route::get('tipo-productos-check-codigo', [App\Http\Controllers\TipoProductoController::class, 'checkCodigoPrefijo'])->name('tipo-productos.check-codigo');

        // Insumos
        Route::resource('insumos', InsumoController::class);
        Route::get('insumos-data', [InsumoController::class, 'getInsumos'])->name('insumos.data');
        Route::get('insumos/reporte/pdf', [InsumoController::class, 'reportePdf'])->name('insumos.reporte.pdf');

        // Órdenes de Producción
        Route::resource('ordenes', OrdenProduccionController::class);
        Route::get('ordenes-data', [OrdenProduccionController::class, 'getOrdenes'])->name('ordenes.data');
        Route::get('pedidos/{pedido}/data-for-orden', [OrdenProduccionController::class, 'getPedidoData'])->name('pedidos.data-for-orden');

        // Control de Insumos por Orden
        Route::get('ordenes/{orden}/insumos', [DetalleOrdenInsumoController::class, 'index'])->name('ordenes.insumos.index');
        Route::get('ordenes/{orden}/insumos/data', [DetalleOrdenInsumoController::class, 'getInsumos'])->name('ordenes.insumos.data');
        Route::post('ordenes/{orden}/insumos', [DetalleOrdenInsumoController::class, 'store'])->name('ordenes.insumos.store');
        Route::put('ordenes/insumos/{id}', [DetalleOrdenInsumoController::class, 'update'])->name('ordenes.insumos.update');
        Route::delete('ordenes/insumos/{id}', [DetalleOrdenInsumoController::class, 'destroy'])->name('ordenes.insumos.destroy');

        // Producción Diaria
        Route::get('produccion/diaria', [ProduccionDiariaController::class, 'index'])->name('produccion.diaria.index');
        Route::get('produccion/diaria/data', [ProduccionDiariaController::class, 'getRegistros'])->name('produccion.diaria.data');
        Route::post('produccion/diaria', [ProduccionDiariaController::class, 'store'])->name('produccion.diaria.store');
        Route::get('produccion/diaria/{id}', [ProduccionDiariaController::class, 'show'])->name('produccion.diaria.show');
        Route::put('produccion/diaria/{id}', [ProduccionDiariaController::class, 'update'])->name('produccion.diaria.update');
        Route::delete('produccion/diaria/{id}', [ProduccionDiariaController::class, 'destroy'])->name('produccion.diaria.destroy');

        // Inventario
        Route::get('inventario/movimientos', [MovimientoInsumoController::class, 'index'])->name('inventario.movimientos.index');
        Route::get('inventario/movimientos/data', [MovimientoInsumoController::class, 'getMovimientos'])->name('inventario.movimientos.data');
        Route::post('inventario/movimientos', [MovimientoInsumoController::class, 'store'])->name('inventario.movimientos.store');
        Route::get('inventario/movimientos/{id}', [MovimientoInsumoController::class, 'show'])->name('inventario.movimientos.show');
        Route::get('inventario/reporte', [MovimientoInsumoController::class, 'reporteExistencia'])->name('inventario.reporte');
        Route::get('inventario/alertas', [MovimientoInsumoController::class, 'alertasStock'])->name('inventario.alertas');
        Route::get('inventario/movimientos/historial/{id}', [MovimientoInsumoController::class, 'historialInsumo'])->name('inventario.movimientos.historial');

        // Reportes
        Route::prefix('reportes')->group(function () {
            Route::get('/produccion', [ReportesController::class, 'produccion'])->name('reportes.produccion');
            Route::get('/eficiencia', [ReportesController::class, 'eficiencia'])->name('reportes.eficiencia');
            Route::get('/insumos', [ReportesController::class, 'insumos'])->name('reportes.insumos');
            Route::get('/empleados', [ReportesController::class, 'empleados'])->name('reportes.empleados');
        });
    });
});

require __DIR__ . '/auth.php';
