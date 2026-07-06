<?php

/*
|--------------------------------------------------------------------------
| Registry del catalogo de Reportes Generales
|--------------------------------------------------------------------------
|
| Catalogo en codigo de todos los reportes del sistema, misma filosofia que
| config/parametros.php y config/modulos.php: agregar un reporte nuevo = una
| entrada aqui; la vista /reportes/general lo consume sin tocar mas codigo.
|
| FORMA DE CADA ENTRADA
|   'grupos' => [
|       [
|           'titulo'      => 'Nombre de la seccion',
|           'descripcion' => 'Que agrupa',
|           'icono'       => 'ri-...',            // Remix Icon del grupo
|           'color'       => 'navy|emerald|sky',  // identidad de la seccion de origen
|                                                 // (navy=Maestros, emerald=Operativa, sky=Reportes)
|           'reportes'    => [
|               [
|                   'titulo'      => 'Nombre humano del reporte',
|                   'descripcion' => 'Que contiene / para que sirve',
|                   'icono'       => 'ri-...',
|                   'ruta'        => 'nombre.de.ruta',  // route() name
|                   'formato'     => 'pdf' | 'vista',   // pdf abre en pestana nueva
|               ],
|           ],
|       ],
|   ],
|
| VISIBILIDAD (no duplicar permisos aqui)
|   El permiso requerido NO se declara en este archivo: se deriva de la ruta
|   con permisoDeRuta() (registry config/modulos.php, la misma resolucion del
|   middleware CheckPermiso). Si la ruta no existe o no esta mapeada, la card
|   simplemente no se muestra — denegar por defecto, igual que el middleware.
*/

return [

    'grupos' => [

        [
            'titulo'      => 'Gestión general',
            'descripcion' => 'Maestros y catálogos: personas, productos e insumos registrados.',
            'icono'       => 'ri-database-2-line',
            'color'       => 'navy',
            'reportes'    => [
                [
                    'titulo'      => 'Usuarios del sistema',
                    'descripcion' => 'Cuentas registradas con su rol y estatus.',
                    'icono'       => 'ri-shield-user-line',
                    'ruta'        => 'users.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Clientes',
                    'descripcion' => 'Listado general de clientes y sus datos de contacto.',
                    'icono'       => 'ri-user-3-line',
                    'ruta'        => 'clientes.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Empleados',
                    'descripcion' => 'Personal registrado con departamento y cargo.',
                    'icono'       => 'ri-team-line',
                    'ruta'        => 'empleados.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Proveedores',
                    'descripcion' => 'Proveedores activos con documento y contacto.',
                    'icono'       => 'ri-truck-line',
                    'ruta'        => 'proveedores.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Catálogo de productos',
                    'descripcion' => 'Tipos de producto ofrecidos y sus precios base.',
                    'icono'       => 'ri-t-shirt-line',
                    'ruta'        => 'productos.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Insumos',
                    'descripcion' => 'Materia prima registrada con stock y costos.',
                    'icono'       => 'ri-archive-line',
                    'ruta'        => 'insumos.reporte.pdf',
                    'formato'     => 'pdf',
                ],
            ],
        ],

        [
            'titulo'      => 'Gestión operativa',
            'descripcion' => 'Transacciones del negocio: ventas, producción, compras e inventario.',
            'icono'       => 'ri-settings-3-line',
            'color'       => 'emerald',
            'reportes'    => [
                [
                    'titulo'      => 'Cotizaciones',
                    'descripcion' => 'Cotizaciones emitidas con estado y montos.',
                    'icono'       => 'ri-file-list-3-line',
                    'ruta'        => 'cotizaciones.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Pedidos',
                    'descripcion' => 'Pedidos registrados con avance de pago y entrega.',
                    'icono'       => 'ri-shopping-bag-3-line',
                    'ruta'        => 'pedidos.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Órdenes de producción',
                    'descripcion' => 'Órdenes con estado, cantidades y responsables.',
                    'icono'       => 'ri-hammer-line',
                    'ruta'        => 'ordenes.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Control de calidad',
                    'descripcion' => 'Inspecciones realizadas con veredicto y motivos.',
                    'icono'       => 'ri-checkbox-multiple-line',
                    'ruta'        => 'calidad.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Compras',
                    'descripcion' => 'Compras de insumos con proveedor, estado y montos.',
                    'icono'       => 'ri-shopping-cart-2-line',
                    'ruta'        => 'compras.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Movimientos de insumos',
                    'descripcion' => 'Entradas y salidas de inventario con existencias.',
                    'icono'       => 'ri-arrow-left-right-line',
                    'ruta'        => 'movimiento-insumo.reporte.pdf',
                    'formato'     => 'pdf',
                ],
            ],
        ],

        [
            'titulo'      => 'Análisis y rendimiento',
            'descripcion' => 'Consultas analíticas con gráficos e indicadores.',
            'icono'       => 'ri-line-chart-line',
            'color'       => 'sky',
            'reportes'    => [
                [
                    'titulo'      => 'Producción',
                    'descripcion' => 'Órdenes por estado y producción mensual.',
                    'icono'       => 'ri-building-2-line',
                    'ruta'        => 'reportes.produccion',
                    'formato'     => 'vista',
                ],
                [
                    'titulo'      => 'Eficiencia',
                    'descripcion' => 'Pulso de producción por pedido, con detalle por orden.',
                    'icono'       => 'ri-speed-line',
                    'ruta'        => 'reportes.eficiencia',
                    'formato'     => 'vista',
                ],
                [
                    'titulo'      => 'Consumo de insumos',
                    'descripcion' => 'Insumos más utilizados y consumo por tipo.',
                    'icono'       => 'ri-stack-line',
                    'ruta'        => 'reportes.insumos',
                    'formato'     => 'vista',
                ],
                [
                    'titulo'      => 'Rendimiento de empleados',
                    'descripcion' => 'Producción y eficiencia por persona.',
                    'icono'       => 'ri-team-line',
                    'ruta'        => 'reportes.empleados',
                    'formato'     => 'vista',
                ],
            ],
        ],

    ],

];
