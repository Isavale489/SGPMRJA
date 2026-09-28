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
|           'icono'       => 'Database',          // ícono lucide (ver ICONOS en icono.tsx)
|           'color'       => 'navy|emerald|sky',  // identidad de la seccion de origen
|                                                 // (navy=Maestros, emerald=Operativa, sky=Reportes)
|           'reportes'    => [
|               [
|                   'titulo'      => 'Nombre humano del reporte',
|                   'descripcion' => 'Que contiene / para que sirve',
|                   'icono'       => 'FileText',
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
            'icono'       => 'Database',
            'color'       => 'navy',
            'reportes'    => [
                [
                    'titulo'      => 'Usuarios del sistema',
                    'descripcion' => 'Cuentas registradas con su rol y estatus.',
                    'icono'       => 'ShieldUser',
                    'ruta'        => 'users.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Clientes',
                    'descripcion' => 'Listado general de clientes y sus datos de contacto.',
                    'icono'       => 'UserRound',
                    'ruta'        => 'clientes.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Empleados',
                    'descripcion' => 'Personal registrado con departamento y cargo.',
                    'icono'       => 'Users',
                    'ruta'        => 'empleados.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Proveedores',
                    'descripcion' => 'Proveedores activos con documento y contacto.',
                    'icono'       => 'Truck',
                    'ruta'        => 'proveedores.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Catálogo de productos',
                    'descripcion' => 'Tipos de producto ofrecidos y sus precios base.',
                    'icono'       => 'Shirt',
                    'ruta'        => 'productos.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Insumos',
                    'descripcion' => 'Materia prima registrada con stock y costos.',
                    'icono'       => 'Archive',
                    'ruta'        => 'insumos.reporte.pdf',
                    'formato'     => 'pdf',
                ],
            ],
        ],

        [
            'titulo'      => 'Gestión operativa',
            'descripcion' => 'Transacciones del negocio: ventas, producción, compras e inventario.',
            'icono'       => 'Settings',
            'color'       => 'emerald',
            'reportes'    => [
                [
                    'titulo'      => 'Cotizaciones',
                    'descripcion' => 'Cotizaciones emitidas con estado y montos.',
                    'icono'       => 'FileText',
                    'ruta'        => 'cotizaciones.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Pedidos',
                    'descripcion' => 'Pedidos registrados con avance de pago y entrega.',
                    'icono'       => 'ShoppingBag',
                    'ruta'        => 'pedidos.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Órdenes de producción',
                    'descripcion' => 'Órdenes con estado, cantidades y responsables.',
                    'icono'       => 'Hammer',
                    'ruta'        => 'ordenes.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Control de calidad',
                    'descripcion' => 'Inspecciones realizadas con veredicto y motivos.',
                    'icono'       => 'ShieldCheck',
                    'ruta'        => 'calidad.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Compras',
                    'descripcion' => 'Compras de insumos con proveedor, estado y montos.',
                    'icono'       => 'ShoppingCart',
                    'ruta'        => 'compras.reporte.pdf',
                    'formato'     => 'pdf',
                ],
                [
                    'titulo'      => 'Movimientos de insumos',
                    'descripcion' => 'Entradas y salidas de inventario con existencias.',
                    'icono'       => 'ArrowLeftRight',
                    'ruta'        => 'movimiento-insumo.reporte.pdf',
                    'formato'     => 'pdf',
                ],
            ],
        ],

        [
            'titulo'      => 'Análisis y rendimiento',
            'descripcion' => 'Consultas analíticas con gráficos e indicadores.',
            'icono'       => 'ChartLine',
            'color'       => 'sky',
            'reportes'    => [
                [
                    'titulo'      => 'Producción',
                    'descripcion' => 'Órdenes por estado y producción mensual.',
                    'icono'       => 'Factory',
                    'ruta'        => 'reportes.produccion',
                    'formato'     => 'vista',
                ],
                [
                    'titulo'      => 'Eficiencia',
                    'descripcion' => 'Pulso de producción por pedido, con detalle por orden.',
                    'icono'       => 'Gauge',
                    'ruta'        => 'reportes.eficiencia',
                    'formato'     => 'vista',
                ],
                [
                    'titulo'      => 'Consumo de insumos',
                    'descripcion' => 'Insumos más utilizados y consumo por tipo.',
                    'icono'       => 'Layers',
                    'ruta'        => 'reportes.insumos',
                    'formato'     => 'vista',
                ],
                [
                    'titulo'      => 'Rendimiento de empleados',
                    'descripcion' => 'Producción y eficiencia por persona.',
                    'icono'       => 'Users',
                    'ruta'        => 'reportes.empleados',
                    'formato'     => 'vista',
                ],
            ],
        ],

    ],

];
