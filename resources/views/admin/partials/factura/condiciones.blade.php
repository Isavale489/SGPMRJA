{{-- Condiciones y términos estándar (compartido factura de cotización / pedido).
     Alineado con las reglas del wizard de cotización/pedido. --}}
@php($abonoPct = \App\Models\Pedido::porcentajeAbonoMinimo())
<div class="nota-especial">
    <b>Condiciones para Pedidos:</b><br>
    &bull; <b>Formalización:</b> abono del {{ $abonoPct }}% del costo total para iniciar la producción.<br>
    &bull; <b>Tiempo de ejecución:</b> {{ \App\Models\Pedido::diasHabilesEntrega() }} días hábiles desde la confirmación del pago inicial.<br>
    &bull; <b>Saldo restante:</b> el {{ 100 - $abonoPct }}% se cancela al momento de la entrega.<br>
    &bull; <b>Modificaciones:</b> una vez formalizado el pedido no se aceptan cambios en tallas, cantidades ni diseño.<br>
    &bull; <b>Entrega:</b> el plazo comienza a contarse desde el abono del {{ $abonoPct }}% inicial.<br>
    <br>
    <b>Servicio de bordado:</b><br>
    &bull; Prendas externas: deben estar limpias y en buen estado.<br>
    &bull; El cliente aprueba diseño, ubicación y tamaño antes de iniciar; luego no hay cambios.<br>
    &bull; Anticipo del 50% para programar y 50% a la entrega; tiempo estimado de 7 a 10 días hábiles.
</div>
