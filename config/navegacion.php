<?php

/*
|--------------------------------------------------------------------------
| Navegación del panel (plataforma Inertia)
|--------------------------------------------------------------------------
|
| Fuente única del menú lateral de las páginas React. HandleInertiaRequests
| la filtra por permisos (tienePermiso()) y resuelve cada 'ruta' a URL antes
| de compartirla como prop `navegacion`: el cliente nunca decide qué ve el
| usuario, solo lo dibuja.
|
| Refleja resources/views/admin/layouts/sidebar.blade.php (menú Blade) mientras
| ambos layouts convivan. Si agregas un módulo, agrégalo en los dos.
|
| FORMA
|   Sección:  ['titulo' => ..., 'icono' => ..., 'items' => [...]]
|   Grupo:    ['titulo' => ..., 'icono' => ..., 'items' => [...]]   (anidado en una sección)
|   Enlace:   ['titulo' => ..., 'icono' => ..., 'ruta' => 'nombre.ruta', 'permiso' => 'modulo.accion',
|              'inertia' => true]   ← SOLO cuando el módulo ya está migrado a Inertia
|
| 'icono' es un nombre de lucide-react (https://lucide.dev/icons). Un grupo o
| sección sin enlaces visibles para el usuario se omite.
|
*/

return [

    ['titulo' => 'Inicio', 'icono' => 'LayoutDashboard', 'ruta' => 'dashboard', 'inertia' => true],

    [
        'titulo' => 'Gestión General',
        'icono' => 'Database',
        'items' => [
            ['titulo' => 'Clientes', 'icono' => 'UserRound', 'ruta' => 'clientes.index', 'permiso' => 'clientes.ver', 'inertia' => true],
            [
                'titulo' => 'Productos',
                'icono' => 'Shirt',
                'items' => [
                    ['titulo' => 'Catálogo', 'icono' => 'ListChecks', 'ruta' => 'productos.index', 'permiso' => 'productos.ver', 'inertia' => true],
                    ['titulo' => 'Atributos', 'icono' => 'SlidersHorizontal', 'ruta' => 'atributos.index', 'permiso' => 'atributos.ver', 'inertia' => true],
                    ['titulo' => 'Colores', 'icono' => 'Palette', 'ruta' => 'colores.index', 'permiso' => 'colores.ver', 'inertia' => true],
                ],
            ],
            ['titulo' => 'Proveedores', 'icono' => 'Truck', 'ruta' => 'proveedores.index', 'permiso' => 'proveedores.ver', 'inertia' => true],
            ['titulo' => 'Insumos', 'icono' => 'Archive', 'ruta' => 'insumos.index', 'permiso' => 'insumos.ver', 'inertia' => true],
            [
                'titulo' => 'Recursos Humanos',
                'icono' => 'Users',
                'items' => [
                    ['titulo' => 'Empleados', 'icono' => 'UserCog', 'ruta' => 'empleados.index', 'permiso' => 'empleados.ver', 'inertia' => true],
                    ['titulo' => 'Departamentos', 'icono' => 'Building', 'ruta' => 'departamentos.index', 'permiso' => 'departamentos.ver', 'inertia' => true],
                    ['titulo' => 'Cargos', 'icono' => 'Briefcase', 'ruta' => 'cargos.index', 'permiso' => 'cargos.ver', 'inertia' => true],
                ],
            ],
        ],
    ],

    [
        'titulo' => 'Gestión Operativa',
        'icono' => 'ArrowLeftRight',
        'items' => [
            ['titulo' => 'Cotizaciones', 'icono' => 'FileText', 'ruta' => 'cotizaciones.index', 'permiso' => 'cotizaciones.ver', 'inertia' => true],
            ['titulo' => 'Pedidos', 'icono' => 'ShoppingCart', 'ruta' => 'pedidos.index', 'permiso' => 'pedidos.ver', 'inertia' => true],
            ['titulo' => 'Orden de Producción', 'icono' => 'CalendarCheck', 'ruta' => 'ordenes.index', 'permiso' => 'ordenes.ver', 'inertia' => true],
            ['titulo' => 'Control de Calidad', 'icono' => 'ShieldCheck', 'ruta' => 'calidad.index', 'permiso' => 'calidad.ver', 'inertia' => true],
            [
                'titulo' => 'Movimientos',
                'icono' => 'Repeat',
                'items' => [
                    ['titulo' => 'Movimientos de Insumos', 'icono' => 'Boxes', 'ruta' => 'movimiento-insumo.index', 'permiso' => 'movimiento-insumo.ver', 'inertia' => true],
                    ['titulo' => 'Compras', 'icono' => 'ShoppingBag', 'ruta' => 'compras.index', 'permiso' => 'compras.ver', 'inertia' => true],
                ],
            ],
        ],
    ],

    [
        'titulo' => 'Consultas y Reportes',
        'icono' => 'ChartColumn',
        'items' => [
            ['titulo' => 'Producción', 'icono' => 'Factory', 'ruta' => 'reportes.produccion', 'permiso' => 'reportes.ver', 'inertia' => true],
            ['titulo' => 'Eficiencia', 'icono' => 'Gauge', 'ruta' => 'reportes.eficiencia', 'permiso' => 'reportes.ver', 'inertia' => true],
            ['titulo' => 'Consumo de Insumos', 'icono' => 'Layers', 'ruta' => 'reportes.insumos', 'permiso' => 'reportes.ver', 'inertia' => true],
            ['titulo' => 'Rendimiento de Empleados', 'icono' => 'Users', 'ruta' => 'reportes.empleados', 'permiso' => 'reportes.ver', 'inertia' => true],
            ['titulo' => 'Reportes Generales', 'icono' => 'FileChartColumn', 'ruta' => 'reportes.general', 'permiso' => 'reportes.ver', 'inertia' => true],
        ],
    ],

];
