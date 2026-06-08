<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Abono mínimo para pasar a producción
    |--------------------------------------------------------------------------
    |
    | Porcentaje mínimo del total del pedido que debe estar abonado (con pagos
    | validados) antes de que el pedido pueda avanzar a producción y se le
    | puedan generar Órdenes de Producción. Configurable vía el .env con
    | PEDIDO_ABONO_MINIMO_PORCENTAJE; por defecto 50%.
    |
    */

    'abono_minimo_porcentaje' => (float) env('PEDIDO_ABONO_MINIMO_PORCENTAJE', 50),

];
