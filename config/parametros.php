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

    'pedidos.dias_entrega_habiles' => [
        'modulo'      => 'Pedidos',
        'nombre'      => 'Tiempo de ejecución (días hábiles)',
        'descripcion' => 'Días hábiles (sin sábados ni domingos) entre la formalización del pedido y la fecha de entrega estimada que calcula el sistema. La fecha elegida manualmente en el wizard siempre manda.',
        'tipo'        => 'entero',
        'reglas'      => 'required|integer|min:1|max:180',
        'default'     => 30,
    ],

    'cotizaciones.dias_vigencia' => [
        'modulo'      => 'Cotizaciones',
        'nombre'      => 'Vigencia de precios (días)',
        'descripcion' => 'Días continuos desde la emisión durante los que los precios de una cotización son válidos para convertirla en pedido. Pasado el plazo, la cotización se marca Vencida. Aplica también a cotizaciones ya emitidas (es una política, no un snapshot).',
        'tipo'        => 'entero',
        'reglas'      => 'required|integer|min:1|max:365',
        'default'     => 15,
    ],

];
