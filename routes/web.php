<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\DetalleOrdenInsumoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\TipoInsumoController;
use App\Http\Controllers\MovimientoInsumoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\OrdenProduccionController;
use App\Http\Controllers\ControlCalidadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SeguridadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\TallaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\PersonaController;

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
// Autorización por permisos (FEAT-005 / TASK-038): el middleware 'permiso' resuelve
// el permiso requerido desde el nombre de la ruta vía config/modulos.php (deny-by-default).
// Sustituye a los antiguos grupos role:Administrador y role:Administrador,Supervisor:
// el Administrador entra por Gate::before; el Supervisor y demás roles, por sus filas en permiso_rol.
Route::middleware(['auth', 'throttle:60,1', 'active.user', 'recovery.questions.required', 'permiso'])->group(function () {

    // Dashboard - Acceso para todos los usuarios autenticados
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/recovery-questions', [ProfileController::class, 'updateRecoveryQuestions'])
        ->name('profile.recovery-questions.update');

    // ============================================
    // CRUD DE ESCRITURA (antes role:Administrador) — ahora gateado por 'permiso'
    // vía config/modulos.php (acciones 'gestionar' / módulos solo-admin).
    // ============================================
        // Configuración del sistema (FEAT-004)
        Route::get('configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::put('configuracion/{modulo}', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::delete('configuracion/{modulo}/{clave}', [ConfiguracionController::class, 'reset'])->name('configuracion.reset');

        // Impuestos (tabla `impuesto`) — gestionados desde el panel de configuración
        Route::post('configuracion-impuestos', [ImpuestoController::class, 'store'])->name('impuestos.store');
        Route::put('configuracion-impuestos/{impuesto}', [ImpuestoController::class, 'update'])->name('impuestos.update');
        Route::delete('configuracion-impuestos/{impuesto}', [ImpuestoController::class, 'destroy'])->name('impuestos.destroy');

        // Usuarios
        Route::resource('users', UserController::class);
        Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::get('users-data', [UserController::class, 'getUsers'])->name('users.data');
        Route::get('users/reporte/pdf', [UserController::class, 'reportePdf'])->name('users.reporte.pdf');
        Route::get('users-check-email', [UserController::class, 'checkEmail'])->name('users.check-email');
        Route::post('users/{id}/unlock-recovery', [UserController::class, 'unlockRecovery'])->name('users.unlock-recovery');
        Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Clientes
        Route::resource('clientes', ClienteController::class);
        Route::get('clientes-data', [ClienteController::class, 'getClientes'])->name('clientes.data');
        Route::get('clientes-check-documento', [ClienteController::class, 'checkDocumento'])->name('clientes.check-documento');
        Route::get('clientes-check-email', [ClienteController::class, 'checkEmail'])->name('clientes.check-email');
        Route::get('clientes-search', [ClienteController::class, 'searchAjax'])->name('clientes.search');
        Route::post('clientes/from-persona/{persona}', [ClienteController::class, 'createFromPersona'])->name('clientes.from-persona');
        Route::get('/clientes/reporte/pdf', [ClienteController::class, 'exportarPDF'])->name('clientes.reporte.pdf');
        Route::post('clientes/{id}/restore', [ClienteController::class, 'restore'])->name('clientes.restore');

        // Búsqueda unificada de personas (cliente + empleado + proveedor) — usado por autocomplete de cotizaciones
        Route::get('personas-search', [PersonaController::class, 'search'])->name('personas.search');

        // Empleados
        // 'create' excluido: el alta se hace por el modal del index (no hay página aparte)
        Route::resource('empleados', EmpleadoController::class)->except(['create']);
        Route::post('empleados/{id}/restore', [EmpleadoController::class, 'restore'])->name('empleados.restore');
        Route::get('empleados-data', [EmpleadoController::class, 'getEmpleados'])->name('empleados.data');
        Route::get('empleados-check-documento', [EmpleadoController::class, 'checkDocumento'])->name('empleados.check-documento');
        Route::get('empleados-check-email', [EmpleadoController::class, 'checkEmail'])->name('empleados.check-email');
        Route::get('empleados-check-codigo', [EmpleadoController::class, 'checkCodigo'])->name('empleados.check-codigo');
        Route::get('/empleados/reporte/pdf', [EmpleadoController::class, 'reportePdf'])->name('empleados.reporte.pdf');
        Route::get('empleados-get-cargos', [EmpleadoController::class, 'getCargos'])->name('empleados.get-cargos');

        // Departamentos (CRUD — maestro)
        Route::get('departamentos', [DepartamentoController::class, 'index'])->name('departamentos.index');
        Route::post('departamentos', [DepartamentoController::class, 'store'])->name('departamentos.store');
        Route::get('departamentos/{departamento}', [DepartamentoController::class, 'show'])->name('departamentos.show');
        Route::put('departamentos/{departamento}', [DepartamentoController::class, 'update'])->name('departamentos.update');
        Route::delete('departamentos/{departamento}', [DepartamentoController::class, 'destroy'])->name('departamentos.destroy');
        Route::patch('departamentos/{id}/restore', [DepartamentoController::class, 'restore'])->name('departamentos.restore');
        Route::get('departamentos-check-nombre', [DepartamentoController::class, 'checkNombre'])->name('departamentos.check-nombre');

        // Cargos (CRUD — maestro)
        Route::get('cargos', [CargoController::class, 'index'])->name('cargos.index');
        Route::post('cargos', [CargoController::class, 'store'])->name('cargos.store');
        Route::get('cargos/{cargo}', [CargoController::class, 'show'])->name('cargos.show');
        Route::put('cargos/{cargo}', [CargoController::class, 'update'])->name('cargos.update');
        Route::delete('cargos/{cargo}', [CargoController::class, 'destroy'])->name('cargos.destroy');
        Route::patch('cargos/{id}/restore', [CargoController::class, 'restore'])->name('cargos.restore');
        Route::get('cargos-check-nombre', [CargoController::class, 'checkNombre'])->name('cargos.check-nombre');

        // Pedidos (escritura)
        Route::post('pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
        Route::get('pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create');
        Route::put('pedidos/{pedido}', [PedidoController::class, 'update'])->name('pedidos.update');
        Route::patch('pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
        Route::patch('pedidos/{pedido}/reactivar', [PedidoController::class, 'reactivar'])->name('pedidos.reactivar');
        Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
        Route::get('pedidos/{pedido}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');

        // Cotizaciones (escritura)
        Route::post('cotizaciones', [CotizacionController::class, 'store'])->name('cotizaciones.store');
        Route::get('cotizaciones/create', [CotizacionController::class, 'create'])->name('cotizaciones.create');
        Route::put('cotizaciones/{cotizacion}', [CotizacionController::class, 'update'])->name('cotizaciones.update');
        Route::delete('cotizaciones/{cotizacion}', [CotizacionController::class, 'destroy'])->name('cotizaciones.destroy');
        Route::get('cotizaciones/{cotizacion}/edit', [CotizacionController::class, 'edit'])->name('cotizaciones.edit');

        // Proveedores (escritura)
        Route::post('proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::post('proveedores/from-persona/{persona}', [ProveedorController::class, 'createFromPersona'])->name('proveedores.from-persona');
        Route::get('proveedores/create', [ProveedorController::class, 'create'])->name('proveedores.create');
        Route::put('proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        Route::get('proveedores/{proveedor}/edit', [ProveedorController::class, 'edit'])->name('proveedores.edit');
        Route::post('proveedores/{id}/restore', [ProveedorController::class, 'restore'])->name('proveedores.restore');

    // ============================================
    // LECTURA + CRUD COMPARTIDO (antes role:Administrador,Supervisor) — ahora
    // gateado por 'permiso' vía config/modulos.php (acciones 'ver'/'gestionar'/etc.).
    // ============================================
        // Pedidos (lectura)
        Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos-data', [PedidoController::class, 'getPedidos'])->name('pedidos.data');
        Route::get('pedidos/cotizaciones-disponibles', [PedidoController::class, 'getCotizacionesDisponibles'])->name('pedidos.cotizacionesDisponibles');
        Route::get('pedidos/reporte/pdf', [PedidoController::class, 'reportePdf'])->name('pedidos.reporte.pdf');
        Route::get('pedidos/reporte', [PedidoController::class, 'reporteGeneral'])->name('pedidos.reporteGeneral');
        Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::get('pedidos/{pedido}/pdf', [PedidoController::class, 'pedidoPdf'])->name('pedidos.pdf');

        // Cotizaciones (lectura + conversión)
        Route::get('cotizaciones', [CotizacionController::class, 'index'])->name('cotizaciones.index');
        Route::get('cotizaciones-data', [CotizacionController::class, 'getCotizaciones'])->name('cotizaciones.data');
        Route::get('cotizaciones/reporte/pdf', [CotizacionController::class, 'reportePdf'])->name('cotizaciones.reporte.pdf');
        Route::get('cotizaciones/reporte', [CotizacionController::class, 'reporteGeneral'])->name('cotizaciones.reporteGeneral');
        Route::get('cotizaciones/{cotizacion}', [CotizacionController::class, 'show'])->name('cotizaciones.show');
        Route::get('cotizaciones/{cotizacion}/pdf', [CotizacionController::class, 'cotizacionPdf'])->name('cotizaciones.pdf');
        Route::put('cotizaciones/{cotizacion}/estado', [CotizacionController::class, 'updateEstado'])->name('cotizaciones.updateEstado');
        Route::get('cotizaciones/{cotizacion}/datos-para-pedido', [CotizacionController::class, 'getDatosParaPedido'])->name('cotizaciones.datosParaPedido');
        Route::post('cotizaciones/{cotizacion}/convertir-a-pedido', [CotizacionController::class, 'convertirAPedido'])->name('cotizaciones.convertirAPedido');
        Route::post('cotizaciones/{cotizacion}/reactivar', [CotizacionController::class, 'reactivar'])->name('cotizaciones.reactivar');

        // Proveedores (lectura)
        Route::get('proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('proveedores-data', [ProveedorController::class, 'getProveedores'])->name('proveedores.data');
        Route::get('proveedores-check-rif', [ProveedorController::class, 'checkRif'])->name('proveedores.check-rif');
        Route::get('proveedores-check-documento', [ProveedorController::class, 'checkDocumento'])->name('proveedores.check-documento');
        Route::get('proveedores-check-email', [ProveedorController::class, 'checkEmail'])->name('proveedores.check-email');
        Route::get('proveedores/reporte/pdf', [ProveedorController::class, 'reportePdf'])->name('proveedores.reporte.pdf');
        Route::get('proveedores/search', [ProveedorController::class, 'search'])->name('proveedores.search');
        Route::get('proveedores/{proveedor}', [ProveedorController::class, 'show'])->name('proveedores.show');

        // Logos
        Route::get('logos-data', [LogoController::class, 'getLogos'])->name('logos.data');

        // Colores
        Route::get('colores-data', [ColorController::class, 'getColores'])->name('colores.data');
        Route::get('colores-check-nombre', [ColorController::class, 'checkNombre'])->name('colores.check-nombre');
        Route::get('colores', [ColorController::class, 'index'])->name('colores.index');
        Route::post('colores', [ColorController::class, 'store'])->name('colores.store');
        Route::get('colores/{color}', [ColorController::class, 'show'])->name('colores.show');
        Route::put('colores/{color}', [ColorController::class, 'update'])->name('colores.update');
        Route::delete('colores/{color}', [ColorController::class, 'destroy'])->name('colores.destroy');
        Route::patch('colores/{id}/restore', [ColorController::class, 'restore'])->name('colores.restore');

        // Tallas
        Route::get('tallas-data', [TallaController::class, 'getTallas'])->name('tallas.data');

        // Ubicaciones de bordado (catálogo)
        Route::get('cotizaciones-ubicaciones-bordado-data', [CotizacionController::class, 'getUbicacionesBordado'])->name('cotizaciones.ubicacionesBordado.data');

        // Productos
        Route::resource('productos', ProductoController::class);
        Route::get('productos-data', [ProductoController::class, 'getProductos'])->name('productos.data');
        Route::get('productos/reporte/pdf', [ProductoController::class, 'reportePdf'])->name('productos.reporte.pdf');
        Route::post('productos/{id}/restore', [ProductoController::class, 'restore'])->name('productos.restore');
        Route::get('productos-sugerir-precio', [ProductoController::class, 'sugerirPrecio'])->name('productos.sugerir-precio');
        Route::get('productos-preview-codigo', [ProductoController::class, 'previewCodigo'])->name('productos.preview-codigo');
        Route::get('productos-resolver-variante', [ProductoController::class, 'resolverVariante'])->name('productos.resolver-variante');

        // Tipos de Producto
        Route::get('tipo-productos', [App\Http\Controllers\TipoProductoController::class, 'index'])->name('tipo-productos.index');
        Route::post('tipo-productos', [App\Http\Controllers\TipoProductoController::class, 'store'])->name('tipo-productos.store');
        Route::post('tipo-productos/{tipoProducto}/telas', [App\Http\Controllers\TipoProductoController::class, 'storeTela'])->name('tipo-productos.telas.store');
        Route::get('tipo-productos/{id}', [App\Http\Controllers\TipoProductoController::class, 'show'])->name('tipo-productos.show');
        Route::put('tipo-productos/{tipoProducto}', [App\Http\Controllers\TipoProductoController::class, 'update'])->name('tipo-productos.update');
        Route::delete('tipo-productos/{tipoProducto}', [App\Http\Controllers\TipoProductoController::class, 'destroy'])->name('tipo-productos.destroy');
        Route::patch('tipo-productos/{id}/restore', [App\Http\Controllers\TipoProductoController::class, 'restore'])->name('tipo-productos.restore');
        Route::get('tipo-productos-check-nombre', [App\Http\Controllers\TipoProductoController::class, 'checkNombre'])->name('tipo-productos.check-nombre');
        Route::get('tipo-productos-check-codigo', [App\Http\Controllers\TipoProductoController::class, 'checkCodigoPrefijo'])->name('tipo-productos.check-codigo');

        // Atributos de confección (catálogo maestro)
        Route::get('atributos', [App\Http\Controllers\AtributoController::class, 'index'])->name('atributos.index');
        Route::post('atributos', [App\Http\Controllers\AtributoController::class, 'store'])->name('atributos.store');
        Route::get('atributos/{atributo}', [App\Http\Controllers\AtributoController::class, 'show'])->name('atributos.show');
        Route::put('atributos/{atributo}', [App\Http\Controllers\AtributoController::class, 'update'])->name('atributos.update');
        Route::delete('atributos/{atributo}', [App\Http\Controllers\AtributoController::class, 'destroy'])->name('atributos.destroy');
        Route::get('atributos-check-nombre', [App\Http\Controllers\AtributoController::class, 'checkNombre'])->name('atributos.check-nombre');
        Route::get('atributos-check-codigo', [App\Http\Controllers\AtributoController::class, 'checkCodigo'])->name('atributos.check-codigo');

        // Valores de cada atributo (anidado)
        Route::get('atributos/{atributo}/valores', [App\Http\Controllers\AtributoValorController::class, 'index'])->name('atributos.valores.index');
        Route::post('atributos/{atributo}/valores', [App\Http\Controllers\AtributoValorController::class, 'store'])->name('atributos.valores.store');
        Route::put('atributos/{atributo}/valores/{valor}', [App\Http\Controllers\AtributoValorController::class, 'update'])->name('atributos.valores.update');
        Route::delete('atributos/{atributo}/valores/{valor}', [App\Http\Controllers\AtributoValorController::class, 'destroy'])->name('atributos.valores.destroy');
        Route::put('atributos/{atributo}/valores-reorder', [App\Http\Controllers\AtributoValorController::class, 'reorder'])->name('atributos.valores.reorder');

        // Insumos
        Route::post('insumos/{id}/restore', [InsumoController::class, 'restore'])->name('insumos.restore');
        Route::resource('insumos', InsumoController::class);
        Route::get('insumos-data', [InsumoController::class, 'getInsumos'])->name('insumos.data');
        Route::get('insumos/reporte/pdf', [InsumoController::class, 'reportePdf'])->name('insumos.reporte.pdf');
        Route::get('insumos/check-nombre', [InsumoController::class, 'checkNombre'])->name('insumos.check-nombre');

        // Catálogo gestionable de tipos de insumo
        Route::get('tipo-insumos', [TipoInsumoController::class, 'index'])->name('tipo-insumos.index');
        Route::post('tipo-insumos', [TipoInsumoController::class, 'store'])->name('tipo-insumos.store');
        Route::get('tipo-insumos-check-nombre', [TipoInsumoController::class, 'checkNombre'])->name('tipo-insumos.check-nombre');
        Route::get('tipo-insumos/{tipoInsumo}', [TipoInsumoController::class, 'show'])->name('tipo-insumos.show');
        Route::put('tipo-insumos/{tipoInsumo}', [TipoInsumoController::class, 'update'])->name('tipo-insumos.update');
        Route::delete('tipo-insumos/{tipoInsumo}', [TipoInsumoController::class, 'destroy'])->name('tipo-insumos.destroy');
        Route::patch('tipo-insumos/{id}/restore', [TipoInsumoController::class, 'restore'])->name('tipo-insumos.restore');

        // Órdenes de Producción
        // (rutas específicas ANTES del resource para que no colisionen con ordenes/{orden})
        Route::get('ordenes/pedidos-disponibles', [OrdenProduccionController::class, 'pedidosDisponibles'])->name('ordenes.pedidos-disponibles');
        Route::get('ordenes/por-empleado/{empleado}', [OrdenProduccionController::class, 'ordenesPorEmpleado'])->name('ordenes.por-empleado');
        Route::get('ordenes-data', [OrdenProduccionController::class, 'getOrdenes'])->name('ordenes.data');
        Route::post('ordenes/batch', [OrdenProduccionController::class, 'storeBatch'])->name('ordenes.batch');
        Route::post('ordenes/{orden}/avance', [OrdenProduccionController::class, 'registrarAvance'])->name('ordenes.avance');
        Route::patch('ordenes/{orden}/cancelar', [OrdenProduccionController::class, 'cancelar'])->name('ordenes.cancelar');
        // Sub-órdenes de producción (etapas con empleados asignados)
        Route::get('ordenes/{orden}/subordenes', [OrdenProduccionController::class, 'subordenes'])->name('ordenes.subordenes');
        Route::post('ordenes/{orden}/subordenes', [OrdenProduccionController::class, 'storeSubOrden'])->name('ordenes.subordenes.store');
        Route::delete('ordenes/{orden}/subordenes/{subId}', [OrdenProduccionController::class, 'destroySubOrden'])->name('ordenes.subordenes.destroy');
        Route::patch('ordenes/{orden}/subordenes/{subId}/estado', [OrdenProduccionController::class, 'updateSubOrdenEstado'])->name('ordenes.subordenes.estado');
        Route::get('ordenes/reporte/pdf', [OrdenProduccionController::class, 'reportePdf'])->name('ordenes.reporte.pdf');
        Route::resource('ordenes', OrdenProduccionController::class);

        // Control de Calidad (FEAT-006) — inspección de órdenes finalizadas
        Route::get('calidad', [ControlCalidadController::class, 'index'])->name('calidad.index');
        Route::get('calidad/reporte/pdf', [ControlCalidadController::class, 'reportePdf'])->name('calidad.reporte.pdf');
        Route::get('calidad-data', [ControlCalidadController::class, 'getOrdenesCalidad'])->name('calidad.data');
        Route::get('calidad/{orden}/detalle', [ControlCalidadController::class, 'detalle'])->name('calidad.detalle');
        Route::post('calidad/{orden}/inspeccionar', [ControlCalidadController::class, 'inspeccionar'])->name('calidad.inspeccionar');

        // Control de Insumos por Orden
        Route::get('ordenes/{orden}/insumos', [DetalleOrdenInsumoController::class, 'index'])->name('ordenes.insumos.index');
        Route::get('ordenes/{orden}/insumos/data', [DetalleOrdenInsumoController::class, 'getInsumos'])->name('ordenes.insumos.data');
        Route::post('ordenes/{orden}/insumos', [DetalleOrdenInsumoController::class, 'store'])->name('ordenes.insumos.store');
        Route::put('ordenes/insumos/{id}', [DetalleOrdenInsumoController::class, 'update'])->name('ordenes.insumos.update');
        Route::delete('ordenes/insumos/{id}', [DetalleOrdenInsumoController::class, 'destroy'])->name('ordenes.insumos.destroy');

        // Compras
        Route::get('compras', [CompraController::class, 'index'])->name('compras.index');
        Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
        Route::put('compras/{compra}', [CompraController::class, 'update'])->name('compras.update');
        Route::get('compras/data', [CompraController::class, 'getCompras'])->name('compras.data');
        Route::get('compras/tasa', [CompraController::class, 'getTasa'])->name('compras.tasa');
        Route::get('compras/reporte/pdf', [CompraController::class, 'reportePdf'])->name('compras.reporte.pdf');
        Route::get('compras/{compra}/editar-datos', [CompraController::class, 'getParaEditar'])->name('compras.editar-datos');
        Route::get('compras/{compra}/detalle', [CompraController::class, 'getDetalle'])->name('compras.detalle');
        Route::get('compras/{compra}/pdf', [CompraController::class, 'compraPdf'])->name('compras.pdf');
        Route::patch('compras/{compra}/procesar', [CompraController::class, 'procesar'])->name('compras.procesar');
        Route::patch('compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
        Route::post('compras/{compra}/clonar', [CompraController::class, 'clonar'])->name('compras.clonar');
        Route::delete('compras/{compra}', [CompraController::class, 'destroy'])->name('compras.destroy');

        // Movimientos de Insumo (rutas literales antes del comodín {id})
        Route::get('movimiento-insumo', [MovimientoInsumoController::class, 'index'])->name('movimiento-insumo.index');
        Route::get('movimiento-insumo/data', [MovimientoInsumoController::class, 'getMovimientos'])->name('movimiento-insumo.data');
        Route::get('movimiento-insumo/existencias-data', [MovimientoInsumoController::class, 'getExistencias'])->name('movimiento-insumo.existencias.data');
        Route::get('movimiento-insumo/reporte', [MovimientoInsumoController::class, 'reporteExistencia'])->name('movimiento-insumo.reporte');
        Route::get('movimiento-insumo/reporte/pdf', [MovimientoInsumoController::class, 'reportePdf'])->name('movimiento-insumo.reporte.pdf');
        Route::get('movimiento-insumo/alertas', [MovimientoInsumoController::class, 'alertasStock'])->name('movimiento-insumo.alertas');
        Route::get('movimiento-insumo/historial/{id}', [MovimientoInsumoController::class, 'historialInsumo'])->name('movimiento-insumo.historial');
        Route::post('movimiento-insumo', [MovimientoInsumoController::class, 'store'])->name('movimiento-insumo.store');
        Route::get('movimiento-insumo/{id}', [MovimientoInsumoController::class, 'show'])->name('movimiento-insumo.show');

        // Notificaciones (campanita del header)
        Route::get('notificaciones/sistema', [NotificacionController::class, 'sistema'])->name('notificaciones.sistema');

        // Reportes
        Route::prefix('reportes')->group(function () {
            Route::get('/produccion', [ReportesController::class, 'produccion'])->name('reportes.produccion');
            Route::get('/eficiencia', [ReportesController::class, 'eficiencia'])->name('reportes.eficiencia');
            Route::get('/insumos', [ReportesController::class, 'insumos'])->name('reportes.insumos');
            Route::get('/empleados', [ReportesController::class, 'empleados'])->name('reportes.empleados');
        });
});

// ============================================
// PANEL DE SEGURIDAD (FEAT-005 / TASK-039) — SOLO Administrador.
// Deliberadamente FUERA del middleware 'permiso' y AUSENTE de config/modulos.php:
// el acceso al panel no se gobierna por la matriz dinámica, para que nadie pueda
// otorgárselo a sí mismo (anti-escalada). Gate 'acceso-seguridad' = admin por Gate::before.
// ============================================
Route::middleware(['auth', 'throttle:60,1', 'active.user', 'recovery.questions.required', 'can:acceso-seguridad'])
    ->prefix('configuracion/seguridad')
    ->name('seguridad.')
    ->group(function () {
        Route::get('/', [SeguridadController::class, 'index'])->name('index');
        Route::post('roles', [SeguridadController::class, 'storeRol'])->name('roles.store');
        Route::put('roles/{rol}', [SeguridadController::class, 'updateRol'])->name('roles.update');
        Route::delete('roles/{rol}', [SeguridadController::class, 'destroyRol'])->name('roles.destroy');
        Route::get('permisos/{rol}', [SeguridadController::class, 'getPermisos'])->name('permisos.get');
        Route::put('permisos/{rol}', [SeguridadController::class, 'guardarMatriz'])->name('permisos.update');
    });

require __DIR__ . '/auth.php';
