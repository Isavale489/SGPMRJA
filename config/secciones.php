<?php

/*
|--------------------------------------------------------------------------
| Secciones del panel: identidad visual por grupo de módulos
|--------------------------------------------------------------------------
|
| Fuente única de a qué sección pertenece cada módulo de config/modulos.php.
| La consumen:
|   - HandleInertiaRequests: comparte la sección de la página actual (prop
|     `seccion`) y el layout la pone en <html data-seccion>; los colores salen
|     de los tokens [data-seccion] de resources/css/plataforma.css.
|   - config/navegacion.php: cada sección del menú declara su clave (lo
|     verifica SeccionesTest).
|   - La matriz de Roles y permisos (SeguridadController), en este orden.
|
| La clave es también la identidad de color:
|   maestros  → navy       (Gestión General)
|   operativa → esmeralda  (Gestión Operativa)
|   reportes  → sky        (Consultas y Reportes)
|   admin     → navy, como Maestros (Configuración, Usuarios)
|
| 'icono' es un nombre de lucide-react (ICONOS en resources/js/components/app/icono.tsx).
| Un módulo nuevo del registry va en exactamente una sección (lo verifica
| SeccionesTest); si no, su página queda sin color de sección.
|
*/

return [

    'maestros' => [
        'titulo' => 'Gestión General',
        'icono' => 'Database',
        'modulos' => ['clientes', 'empleados', 'departamentos', 'cargos', 'proveedores', 'productos', 'tipo-productos', 'atributos', 'colores', 'tallas', 'logos', 'insumos', 'tipo-insumos'],
    ],

    'operativa' => [
        'titulo' => 'Gestión Operativa',
        'icono' => 'ArrowLeftRight',
        'modulos' => ['cotizaciones', 'pedidos', 'ordenes', 'calidad', 'compras', 'movimiento-insumo'],
    ],

    'reportes' => [
        'titulo' => 'Consultas y Reportes',
        'icono' => 'ChartColumn',
        'modulos' => ['reportes'],
    ],

    'admin' => [
        'titulo' => 'Administración',
        'icono' => 'ShieldCheck',
        'modulos' => ['configuracion', 'users'],
    ],

];
