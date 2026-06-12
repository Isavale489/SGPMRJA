<?php

/*
|--------------------------------------------------------------------------
| Registry de parámetros configurables del sistema (FEAT-004)
|--------------------------------------------------------------------------
|
| Catálogo de los parámetros que el administrador puede ajustar desde el
| panel /configuracion. Aquí vive la DEFINICIÓN de cada parámetro; el VALOR
| efectivo se resuelve con el helper parametro('clave'): override en la
| tabla `configuracion` → default declarado aquí.
|
| Para agregar un parámetro nuevo basta con añadir una entrada: el panel lo
| renderiza solo (campo según `tipo`, validación según `reglas`), sin tocar
| vistas ni controllers. Ver docs/conventions/system-config.md.
|
| Campos por entrada:
| - modulo      Agrupador visual en el panel (un nav-pill por módulo).
| - nombre      Etiqueta del campo.
| - descripcion Help-text bajo el campo (advertencias incluidas).
| - tipo        decimal | entero | booleano | texto — define input y casteo.
| - reglas      Reglas de validación Laravel aplicadas en el update.
| - default     Valor por defecto explícito; null ⇒ usar config(config_key).
| - config_key  Puente al config file legacy que aporta el default (.env).
|
*/

return [

    'impuestos.iva' => [
        'modulo'      => 'Impuestos',
        'nombre'      => 'Tasa de IVA general (%)',
        'descripcion' => 'Aplicada a las líneas gravables de compras. Las compras ya registradas conservan su snapshot (compra.iva_porcentaje), por lo que cambiar este valor no altera comprobantes previos.',
        'tipo'        => 'decimal',
        'reglas'      => 'required|numeric|min:0|max:100',
        'default'     => null,
        'config_key'  => 'impuestos.iva',
    ],

];
