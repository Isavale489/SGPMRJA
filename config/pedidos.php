<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Abono mínimo para pasar a producción
    |--------------------------------------------------------------------------
    |
    | Porcentaje mínimo del total del pedido que debe estar abonado (con pagos
    | validados) para FORMALIZAR el pedido: habilita la producción / generación
    | de Órdenes de Producción, congela las líneas (tallas/cantidades/diseño) y
    | dispara el cálculo de la fecha de entrega (formalización + 30 días hábiles).
    | También se exige al registrar un pedido nuevo desde el wizard.
    | Configurable vía el .env con PEDIDO_ABONO_MINIMO_PORCENTAJE; por defecto 50%.
    |
    */

    'abono_minimo_porcentaje' => (float) env('PEDIDO_ABONO_MINIMO_PORCENTAJE', 50),

];
