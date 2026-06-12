<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tasa de IVA vigente (%)
    |--------------------------------------------------------------------------
    |
    | Porcentaje del IVA general aplicado a las líneas GRAVABLES de una compra.
    | Las líneas EXENTAS (insumo.aplica_iva = false, o destildado por línea)
    | no suman impuesto. En Venezuela la tasa general es 16%; si cambia por ley,
    | se ajusta aquí. Cada compra persiste su propio snapshot (compra.iva_porcentaje),
    | de modo que cambiar este valor NO altera las compras ya registradas.
    |
    */

    'iva' => env('IVA_TASA', 16),

];
