<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tasa de IVA — VALOR SEMILLA / FALLBACK (%)
    |--------------------------------------------------------------------------
    |
    | La fuente de verdad de la tasa viva es la tabla `impuesto` (fila código
    | IVA), gestionable desde el panel /configuracion. Ver Impuesto::tasaIva().
    |
    | Este valor solo se usa como: (1) semilla inicial al sembrar la tabla
    | (ImpuestoSeeder) y (2) fallback si la fila IVA no existiera. Para cambiar
    | la tasa en uso, edítala en /configuracion → Impuestos, NO aquí.
    |
    | El IVA aplica a las líneas GRAVABLES (insumo.aplica_iva = true); cada
    | compra guarda su snapshot (compra.iva_porcentaje), así que cambiar la
    | tasa NO altera las compras ya registradas.
    |
    */

    'iva' => env('IVA_TASA', 16),

];
