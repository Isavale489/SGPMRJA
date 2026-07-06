<?php

/*
|--------------------------------------------------------------------------
| Registry de modulos, acciones y mapeo ruta -> permiso (FEAT-005)
|--------------------------------------------------------------------------
|
| Catalogo en codigo de la autorizacion del sistema, misma filosofia que
| config/parametros.php (FEAT-004): la DEFINICION vive aqui, el VALOR (que
| rol tiene que permiso) vive en la tabla permiso_rol. Agregar un modulo o
| una accion nueva = una entrada aqui; el middleware 'permiso' y la matriz
| del panel de seguridad (TASK-039) lo consumen sin tocar mas codigo.
|
| FORMA DE CADA ENTRADA
|   'modulo' => [
|       'nombre'   => 'Etiqueta humana del modulo',
|       'acciones' => ['accion' => 'Descripcion para la matriz', ...],
|       'rutas'    => [
|           'ruta.a|ruta.b|ruta.c' => 'accion',   // patron pipe -> accion
|           ...
|       ],
|   ],
|
| La clave de permiso es 'modulo.accion' (ej. 'compras.procesar'). El
| middleware resuelve el nombre de la ruta actual contra los patrones 'rutas'
| de TODOS los modulos y exige el permiso 'modulo.accion' correspondiente.
|
| CONTRATO DE VOCABULARIO (critico)
|   Las claves de los modulos operativos DEBEN coincidir LITERALMENTE con las
|   34 de la constante PERMISOS_SUPERVISOR de la migracion
|   2026_06_15_000003_migrate_user_role_enum_to_role_id.php, o la paridad del
|   Supervisor al desplegar se rompe. Las acciones solo-admin (users, clientes,
|   configuracion, escritura de pedidos/cotizaciones/proveedores) NO estan en
|   esa constante: el Supervisor no recibe fila para ellas y se le deniega por
|   defecto; el Administrador entra por Gate::before. Granularidad (spec 8):
|   fina en transaccionales (procesar/anular/clonar/avance/pdf...), gruesa
|   (ver/gestionar) en maestros.
|
| RUTAS COMUNES
|   La clave 'comunes' (al final) lista nombres de ruta accesibles para todo
|   usuario autenticado SIN consultar permisos (dashboard, perfil, logout y
|   endpoints transversales). El middleware las deja pasar antes de evaluar el
|   registry de modulos.
|
| DENEGAR POR DEFECTO
|   Cualquier ruta autenticada que NO este ni en 'comunes' ni mapeada por algun
|   modulo => el middleware aborta 403 y deja un Log::warning (hueco a cerrar en
|   QA). Mejor descubrir un hueco que dejar una puerta abierta (spec 7).
|
*/

return [

    // ====================================================================
    // MODULOS SOLO-ADMINISTRADOR
    // Sin fila en permiso_rol para ningun rol no-admin; el Administrador
    // entra por Gate::before. No forman parte de PERMISOS_SUPERVISOR.
    // ====================================================================

    'configuracion' => [
        'nombre'   => 'Configuracion del sistema',
        'acciones' => [
            'ver'       => 'Ver el panel de configuracion',
            'gestionar' => 'Editar y restablecer parametros',
        ],
        'rutas' => [
            'configuracion.index'                       => 'ver',
            // Incluye el CRUD de impuestos (tabla `impuesto`), gestionado desde el
            // mismo panel: solo-admin vía 'gestionar' (si no se mapean, el middleware
            // 'permiso' los deniega por defecto — 403 incluso al admin).
            'configuracion.update|configuracion.reset|impuestos.store|impuestos.update|impuestos.destroy' => 'gestionar',
        ],
    ],

    'users' => [
        'nombre'   => 'Usuarios',
        'acciones' => [
            'ver'       => 'Ver listado y detalle de usuarios',
            'gestionar' => 'Crear, editar y eliminar usuarios',
            'pdf'       => 'Exportar PDF',
        ],
        'rutas' => [
            'users.index|users.show|users.data'  => 'ver',
            'users.create|users.store|users.edit|users.update|users.destroy|users.restore|users.check-email|users.unlock-recovery|users.reset-password' => 'gestionar',
            'users.reporte.pdf' => 'pdf',
        ],
    ],

    'clientes' => [
        'nombre'   => 'Clientes',
        'acciones' => [
            'ver'       => 'Ver listado y detalle de clientes',
            'gestionar' => 'Crear, editar y eliminar clientes',
            'pdf'       => 'Exportar PDF',
        ],
        'rutas' => [
            'clientes.index|clientes.show|clientes.data' => 'ver',
            'clientes.create|clientes.store|clientes.edit|clientes.update|clientes.destroy|clientes.restore|clientes.from-persona|clientes.check-documento|clientes.check-email' => 'gestionar',
            'clientes.reporte.pdf' => 'pdf',
        ],
    ],

    'empleados' => [
        'nombre'   => 'Empleados',
        'acciones' => [
            'ver'       => 'Ver listado y detalle de empleados',
            'gestionar' => 'Crear, editar y eliminar empleados',
            'pdf'       => 'Exportar PDF',
        ],
        'rutas' => [
            'empleados.index|empleados.show|empleados.data' => 'ver',
            'empleados.create|empleados.store|empleados.edit|empleados.update|empleados.destroy|empleados.restore|empleados.check-documento|empleados.check-email|empleados.check-codigo' => 'gestionar',
            'empleados.reporte.pdf' => 'pdf',
        ],
    ],

    'departamentos' => [
        'nombre'   => 'Departamentos',
        'acciones' => [
            'ver'       => 'Ver listado de departamentos',
            'gestionar' => 'Crear, editar y eliminar departamentos',
        ],
        'rutas' => [
            'departamentos.index|departamentos.show' => 'ver',
            'departamentos.store|departamentos.update|departamentos.destroy|departamentos.restore|departamentos.check-nombre' => 'gestionar',
        ],
    ],

    'cargos' => [
        'nombre'   => 'Cargos',
        'acciones' => [
            'ver'       => 'Ver listado de cargos',
            'gestionar' => 'Crear, editar y eliminar cargos',
        ],
        'rutas' => [
            'cargos.index|cargos.show' => 'ver',
            'cargos.store|cargos.update|cargos.destroy|cargos.restore|cargos.check-nombre' => 'gestionar',
        ],
    ],

    // ====================================================================
    // MODULOS OPERATIVOS (Administrador + Supervisor por paridad)
    // Las acciones presentes en PERMISOS_SUPERVISOR se otorgan al Supervisor;
    // las acciones de ESCRITURA marcadas "solo-admin" abajo NO estan en esa
    // constante (hoy viven en el grupo role:Administrador de web.php).
    // ====================================================================

    'pedidos' => [
        'nombre'   => 'Pedidos',
        'acciones' => [
            'ver'       => 'Ver listado y detalle de pedidos',            // Supervisor
            'pdf'       => 'Exportar PDF',                                // Supervisor
            'gestionar' => 'Crear, editar, cancelar y reactivar pedidos', // solo-admin
        ],
        'rutas' => [
            'pedidos.index|pedidos.data|pedidos.cotizacionesDisponibles|pedidos.reporteGeneral|pedidos.show|pedidos.proyeccionInsumos' => 'ver',
            'pedidos.reporte.pdf|pedidos.pdf' => 'pdf',
            // solo-admin (escritura): hoy en el grupo role:Administrador
            'pedidos.store|pedidos.create|pedidos.update|pedidos.cancelar|pedidos.reactivar|pedidos.destroy|pedidos.edit' => 'gestionar',
        ],
    ],

    'cotizaciones' => [
        'nombre'   => 'Cotizaciones',
        'acciones' => [
            'ver'        => 'Ver listado y detalle de cotizaciones', // Supervisor
            'pdf'        => 'Exportar PDF',                           // Supervisor
            'convertir'  => 'Convertir cotizacion a pedido',         // Supervisor
            'gestionar'  => 'Crear, editar y eliminar cotizaciones', // solo-admin
        ],
        'rutas' => [
            'cotizaciones.index|cotizaciones.data|cotizaciones.reporteGeneral|cotizaciones.show|cotizaciones.ubicacionesBordado.data|cotizaciones.proyeccionInsumos' => 'ver',
            'cotizaciones.reporte.pdf|cotizaciones.pdf' => 'pdf',
            // conversion a pedido (acceso compartido con Supervisor)
            'cotizaciones.datosParaPedido|cotizaciones.convertirAPedido|cotizaciones.updateEstado|cotizaciones.reactivar' => 'convertir',
            // solo-admin (escritura): hoy en el grupo role:Administrador
            'cotizaciones.store|cotizaciones.create|cotizaciones.update|cotizaciones.destroy|cotizaciones.edit' => 'gestionar',
        ],
    ],

    'proveedores' => [
        'nombre'   => 'Proveedores',
        'acciones' => [
            'ver'       => 'Ver listado, detalle y exportar PDF de proveedores', // Supervisor
            'gestionar' => 'Crear, editar y eliminar proveedores', // solo-admin
        ],
        'rutas' => [
            // El PDF va bajo 'ver' (paridad Supervisor, TASK-038): quien puede ver el listado puede exportarlo.
            'proveedores.index|proveedores.data|proveedores.show|proveedores.reporte.pdf|proveedores.check-rif|proveedores.check-documento|proveedores.check-email' => 'ver',
            // solo-admin (escritura): hoy en el grupo role:Administrador
            'proveedores.store|proveedores.from-persona|proveedores.create|proveedores.update|proveedores.destroy|proveedores.edit|proveedores.restore' => 'gestionar',
        ],
    ],

    'productos' => [
        'nombre'   => 'Productos',
        'acciones' => [
            'ver'       => 'Ver listado, detalle y exportar PDF de productos',
            'gestionar' => 'Crear, editar y eliminar productos',
        ],
        'rutas' => [
            // El PDF va bajo 'ver' (paridad Supervisor, TASK-038).
            'productos.index|productos.show|productos.data|productos.reporte.pdf|productos.sugerir-precio|productos.preview-codigo|productos.resolver-variante' => 'ver',
            'productos.create|productos.store|productos.edit|productos.update|productos.destroy|productos.restore' => 'gestionar',
        ],
    ],

    'tipo-productos' => [
        'nombre'   => 'Tipos de producto',
        'acciones' => [
            'ver'       => 'Ver tipos de producto',
            'gestionar' => 'Crear, editar y eliminar tipos de producto',
        ],
        'rutas' => [
            'tipo-productos.index|tipo-productos.show' => 'ver',
            'tipo-productos.store|tipo-productos.telas.store|tipo-productos.update|tipo-productos.destroy|tipo-productos.restore|tipo-productos.check-nombre|tipo-productos.check-codigo' => 'gestionar',
        ],
    ],

    'atributos' => [
        'nombre'   => 'Atributos de confeccion',
        'acciones' => [
            'ver'       => 'Ver atributos y sus valores',
            'gestionar' => 'Crear, editar y eliminar atributos y valores',
        ],
        'rutas' => [
            'atributos.index|atributos.show|atributos.valores.index' => 'ver',
            'atributos.store|atributos.update|atributos.destroy|atributos.check-nombre|atributos.check-codigo|atributos.valores.store|atributos.valores.update|atributos.valores.destroy|atributos.valores.reorder' => 'gestionar',
        ],
    ],

    'colores' => [
        'nombre'   => 'Colores',
        'acciones' => [
            'ver'       => 'Ver listado de colores',
            'gestionar' => 'Crear, editar y eliminar colores',
        ],
        'rutas' => [
            'colores.index|colores.show|colores.data' => 'ver',
            'colores.store|colores.update|colores.destroy|colores.restore|colores.check-nombre' => 'gestionar',
        ],
    ],

    'tallas' => [
        'nombre'   => 'Tallas',
        'acciones' => [
            'ver' => 'Ver listado de tallas',
        ],
        'rutas' => [
            'tallas.data' => 'ver',
        ],
    ],

    'logos' => [
        'nombre'   => 'Logos',
        'acciones' => [
            'ver' => 'Ver listado de logos',
        ],
        'rutas' => [
            'logos.data' => 'ver',
        ],
    ],

    'insumos' => [
        'nombre'   => 'Insumos',
        'acciones' => [
            'ver'       => 'Ver listado, detalle y exportar PDF de insumos',
            'gestionar' => 'Crear, editar y eliminar insumos',
        ],
        'rutas' => [
            // El PDF va bajo 'ver' (paridad Supervisor, TASK-038).
            'insumos.index|insumos.show|insumos.data|insumos.reporte.pdf' => 'ver',
            'insumos.create|insumos.store|insumos.edit|insumos.update|insumos.destroy|insumos.restore|insumos.check-nombre' => 'gestionar',
        ],
    ],

    'tipo-insumos' => [
        'nombre'   => 'Tipos de insumo',
        'acciones' => [
            'ver'       => 'Ver tipos de insumo',
            'gestionar' => 'Crear, editar y eliminar tipos de insumo',
        ],
        'rutas' => [
            'tipo-insumos.index|tipo-insumos.show' => 'ver',
            'tipo-insumos.store|tipo-insumos.update|tipo-insumos.destroy|tipo-insumos.restore|tipo-insumos.check-nombre' => 'gestionar',
        ],
    ],

    'ordenes' => [
        'nombre'   => 'Ordenes de produccion',
        'acciones' => [
            'ver'       => 'Ver listado y detalle de ordenes',
            'gestionar' => 'Crear, editar y eliminar ordenes, sub-ordenes e insumos por orden',
            'avance'    => 'Registrar avance de produccion',
            'cancelar'  => 'Cancelar ordenes',
            'pdf'       => 'Exportar PDF',
        ],
        'rutas' => [
            'ordenes.index|ordenes.show|ordenes.data|ordenes.pedidos-data|ordenes.pedidos-disponibles|ordenes.proyeccionInsumos|ordenes.por-empleado|ordenes.subordenes|ordenes.insumos.index|ordenes.insumos.data' => 'ver',
            'ordenes.create|ordenes.store|ordenes.edit|ordenes.update|ordenes.destroy|ordenes.batch|ordenes.subordenes.store|ordenes.subordenes.destroy|ordenes.subordenes.estado|ordenes.insumos.store|ordenes.insumos.update|ordenes.insumos.destroy' => 'gestionar',
            'ordenes.avance'      => 'avance',
            'ordenes.cancelar'    => 'cancelar',
            'ordenes.reporte.pdf|ordenes.pdf' => 'pdf',
        ],
    ],

    'calidad' => [
        'nombre'   => 'Control de Calidad',
        'acciones' => [
            'ver'          => 'Ver ordenes pendientes de calidad e historial de inspecciones',
            'inspeccionar' => 'Registrar inspeccion de calidad de una orden',
        ],
        'rutas' => [
            'calidad.index|calidad.data|calidad.pedidos-data|calidad.detalle|calidad.reporte.pdf' => 'ver',
            'calidad.inspeccionar'                                          => 'inspeccionar',
        ],
    ],

    'compras' => [
        'nombre'   => 'Compras',
        'acciones' => [
            'ver'       => 'Ver listado y detalle',
            'gestionar' => 'Crear y editar borradores',
            'procesar'  => 'Procesar (afecta inventario)',
            'anular'    => 'Anular',
            'clonar'    => 'Clonar',
            'pdf'       => 'Exportar PDF',
        ],
        'rutas' => [
            'compras.index|compras.data|compras.show|compras.detalle|compras.tasa' => 'ver',
            'compras.store|compras.update|compras.editar-datos|compras.destroy'    => 'gestionar',
            'compras.procesar' => 'procesar',
            'compras.anular'   => 'anular',
            'compras.clonar'   => 'clonar',
            'compras.pdf|compras.reporte.pdf' => 'pdf',
        ],
    ],

    'movimiento-insumo' => [
        'nombre'   => 'Movimientos de insumo',
        'acciones' => [
            'ver'       => 'Ver movimientos, existencias, historial y alertas',
            'gestionar' => 'Registrar movimientos (individual y masivo)',
        ],
        'rutas' => [
            'movimiento-insumo.index|movimiento-insumo.data|movimiento-insumo.existencias.data|movimiento-insumo.reporte|movimiento-insumo.reporte.pdf|movimiento-insumo.alertas|movimiento-insumo.historial|movimiento-insumo.show' => 'ver',
            'movimiento-insumo.store|movimiento-insumo.masivo' => 'gestionar',
        ],
    ],

    'reportes' => [
        'nombre'   => 'Reportes',
        'acciones' => [
            'ver' => 'Ver reportes (generales, produccion, eficiencia, insumos, empleados)',
        ],
        'rutas' => [
            'reportes.general|reportes.produccion|reportes.eficiencia|reportes.insumos|reportes.empleados' => 'ver',
        ],
    ],

    // ====================================================================
    // RUTAS COMUNES - accesibles a TODO usuario autenticado sin permiso.
    // El middleware 'permiso' las deja pasar antes de evaluar los modulos.
    //
    // Criterio (spec 7): dashboard, perfil, logout y endpoints transversales
    // de autocompletado/utilidad usados por varios modulos. Se incluyen las
    // busquedas globales que alimentan selects compartidos (personas/clientes/
    // proveedores) y el cargo dependiente de empleados, mas la campanita de
    // notificaciones del header.
    //
    // NOTA de criterio sobre los *.check-* (validadores AJAX de unicidad):
    //   NO se ponen en 'comunes'. Se gobiernan con el permiso 'gestionar' de su
    //   propio modulo (ver mapeos arriba), porque solo se usan dentro de los
    //   modales de alta/edicion de ese modulo y revelarian la existencia de
    //   registros (email/documento/codigo) a usuarios sin acceso al modulo.
    //   Gatearlos con el modulo es mas estricto y coherente con deny-by-default.
    // ====================================================================
    'comunes' => [
        'dashboard',
        'profile.edit',
        'profile.update',
        'profile.recovery-questions.update',
        'profile.destroy',
        'logout',
        'personas.search',
        'clientes.search',
        'proveedores.search',
        'empleados.get-cargos',
        'notificaciones.sistema',
    ],

];
