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
| Campos opcionales (UI del panel):
| - confirmar_guardado    true ⇒ guardar el módulo pide confirmación previa.
| - mensaje_confirmacion  Texto del SweetAlert de confirmación.
|
*/

return [

    // NOTA: el IVA ya NO es un parámetro de este registry. Se centralizó en la
    // tabla `impuesto` (catálogo gestionable desde el panel de configuración),
    // que es su única fuente de verdad. Ver App\Models\Impuesto::tasaIva().

    'pedidos.abono_minimo' => [
        'modulo'      => 'Pedidos',
        'nombre'      => 'Abono mínimo (%)',
        'descripcion' => 'Porcentaje mínimo del total que debe estar abonado (con pagos validados) para registrar un pedido y para formalizarlo (habilitar producción). Los textos de términos, facturas y FAQ lo reflejan automáticamente.',
        'tipo'        => 'decimal',
        'reglas'      => 'required|numeric|min:0|max:100',
        'default'     => null,
        'config_key'  => 'pedidos.abono_minimo_porcentaje',
    ],

    'cotizaciones.dias_vigencia' => [
        'modulo'      => 'Cotizaciones',
        'nombre'      => 'Vigencia de precios por defecto (días)',
        'descripcion' => 'Valor por defecto de la "Fecha validez" al crear una cotización (emisión + N días); el vendedor puede ajustarla por cliente y esa fecha pactada es la que manda: al pasarla, la cotización se marca Vencida y no puede convertirse en pedido. Este parámetro también define el plazo al reactivar una Vencida y aplica como respaldo a cotizaciones antiguas sin fecha de validez.',
        'tipo'        => 'entero',
        'reglas'      => 'required|integer|min:1|max:365',
        'default'     => 15,
    ],

    'cotizaciones.max_bordados_producto' => [
        'modulo'      => 'Cotizaciones',
        'nombre'      => 'Máximo de bordados por producto',
        'descripcion' => 'Cantidad máxima de bordados que se pueden configurar en un mismo producto de la cotización. Cuenta el TOTAL de bordados por prenda (suma de la cantidad de cada ubicación): p. ej. una manga con cantidad 10 son 10 bordados. La misma política aplica al convertir y registrar pedidos.',
        'tipo'        => 'entero',
        'reglas'      => 'required|integer|min:1|max:50',
        'default'     => 6,
    ],

];
