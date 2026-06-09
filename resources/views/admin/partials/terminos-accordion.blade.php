{{--
    Card informativa de Términos y Condiciones (acordeón estilo FAQ del homepage).
    Reutilizable en cualquier modal/paso. Param:
      - $prefix (req.) prefijo único para los IDs del acordeón (p. ej. 'cot', 'ped')
    Estilos: .cot-terminos-accordion en custom.css (compartido).
--}}
@php($tcPrefix = $prefix ?? 'tc')
@php($tcAbonoPct = \App\Models\Pedido::porcentajeAbonoMinimo())
<div class="card border-0 shadow-sm">
    <div class="card-header border-0 bg-soft-primary">
        <h6 class="mb-0 text-atlantico-dark">
            <i class="ri-file-shield-2-line me-2"></i>Términos y Condiciones
        </h6>
    </div>
    <div class="card-body p-2">
        <div class="accordion cot-terminos-accordion" id="{{ $tcPrefix }}TerminosAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="{{ $tcPrefix }}TermPedidosHead">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#{{ $tcPrefix }}TermPedidos" aria-expanded="true"
                        aria-controls="{{ $tcPrefix }}TermPedidos">
                        <i class="ri-shopping-bag-3-line me-2"></i>Condiciones para Pedidos
                    </button>
                </h2>
                <div id="{{ $tcPrefix }}TermPedidos" class="accordion-collapse collapse show"
                    aria-labelledby="{{ $tcPrefix }}TermPedidosHead" data-bs-parent="#{{ $tcPrefix }}TerminosAccordion">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Formalización del Pedido:</strong> para iniciar la producción, el cliente debe abonar el {{ $tcAbonoPct }}% del costo total.</li>
                            <li><strong>Tiempo de Ejecución:</strong> <strong>30 días hábiles</strong>, contados desde la confirmación del pago inicial.</li>
                            <li><strong>Saldo Restante:</strong> el {{ 100 - $tcAbonoPct }}% restante se cancela al momento de la <strong>entrega</strong>.</li>
                            <li><strong>Modificaciones:</strong> una vez formalizado el pedido, <strong>no se aceptan cambios</strong> en tallas, cantidades ni diseño.</li>
                            <li><strong>Entrega:</strong> el plazo comienza a contarse <strong>desde el abono del {{ $tcAbonoPct }}% inicial</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="{{ $tcPrefix }}TermBordadoHead">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#{{ $tcPrefix }}TermBordado" aria-expanded="false"
                        aria-controls="{{ $tcPrefix }}TermBordado">
                        <i class="ri-scissors-cut-line me-2"></i>Servicio de bordado
                    </button>
                </h2>
                <div id="{{ $tcPrefix }}TermBordado" class="accordion-collapse collapse"
                    aria-labelledby="{{ $tcPrefix }}TermBordadoHead" data-bs-parent="#{{ $tcPrefix }}TerminosAccordion">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Prendas externas:</strong> deben estar limpias y en buen estado; no nos responsabilizamos por prendas con desgaste o costuras débiles.</li>
                            <li><strong>Aprobación del diseño:</strong> el cliente aprueba ubicación y tamaño antes de iniciar; una vez comenzado, no hay cambios.</li>
                            <li><strong>Anticipo:</strong> 50% para programar el trabajo, 50% a la entrega.</li>
                            <li><strong>Tiempo de entrega:</strong> de 7 a 10 días hábiles según el volumen, desde el anticipo y la aprobación del diseño.</li>
                            <li><strong>Digitalización:</strong> si el logo no está en sistema, el cliente debe enviarlo en JPG/PNG; se realiza una prueba física antes de bordar las prendas.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <small class="text-muted d-block mt-2 px-1">
            <i class="ri-information-line me-1"></i>Estos términos se incluyen en el PDF.
        </small>
    </div>
</div>
