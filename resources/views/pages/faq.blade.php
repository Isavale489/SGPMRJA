@extends('layouts.public')

@section('title', 'Preguntas Frecuentes')

@section('content')
<!-- Page Content-->
<section class="py-5">
    <div class="container px-5 my-5">
        <div class="text-center mb-5">
            <h1 class="fw-bolder">Preguntas Frecuentes</h1>
            <p class="lead fw-normal text-muted mb-0">¿Cómo podemos ayudarte?</p>
        </div>
        <div class="row gx-5">
            <div class="col-md-8">
                <!-- FAQ Accordion 1-->
                <h2 class="fw-bolder mb-3">Términos y condiciones</h2>
                <div class="accordion mb-5" id="accordionExample">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Términos y Condiciones para Pedidos
                            </button>
                        </h3>
                        <div class="accordion-collapse collapse show" id="collapseOne" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body text-justify-custom">
                                <ul>
                                    <li><strong>Formalización del Pedido:</strong> Para iniciar la producción, el cliente debe abonar el 70% del costo total del pedido. Este pago representa la formalización del mismo.</li><br>
                                    
                                    <li><strong>Tiempo de Ejecución:</strong> El tiempo estimado para la ejecución del trabajo es de <strong>30 días hábiles</strong>, contados a partir de la confirmación del pago inicial.</li><br>
                                    
                                    <li><strong>Saldo Restante:</strong> El 30% restante deberá ser cancelado al momento de la <strong>entrega del pedido</strong>.</li><br>
                                    
                                    <li><strong>Modificaciones:</strong> Una vez formalizado el pedido, <strong>no se aceptan modificaciones</strong> en tallas, cantidades o diseño. Se recomienda revisar cuidadosamente toda la información antes de realizar el pago.</li><br>
                                    
                                    <li><strong>Entrega:</strong> El plazo de entrega comienza a contarse <strong>únicamente desde la fecha en que se realiza el abono del 70% inicial</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Términos del servicio de bordado
                            </button>
                        </h3>
                        <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body text-justify-custom">
                                <ul>
                                    <li><strong>Recepción de Prendas:</strong> El cliente puede solicitar el servicio de bordado sobre prendas confeccionadas por nuestra empresa o traer sus propias prendas para bordar.</li><br>
                                    
                                    <li><strong>Condiciones de las Prendas Externas:</strong> En caso de que el cliente proporcione las prendas, estas deben estar limpias, en buen estado y aptas para el proceso de bordado. No nos hacemos responsables por daños en prendas que presenten desgaste, materiales delicados o costuras débiles.</li><br>
                                    
                                    <li><strong>Aprobación del Diseño:</strong> El cliente debe aprobar el diseño, ubicación y tamaño del bordado antes de iniciar el trabajo. Una vez aprobado y comenzado el proceso, no se aceptan cambios.</li><br>
                                    
                                    <li><strong>Anticipo y Pago:</strong> Para la programación del trabajo se requiere un adelanto del 50% del costo total. El restante 50% se cancela al momento de la entrega.</li><br>
                                    
                                    <li><strong>Tiempo de Entrega:</strong> El tiempo estimado para completar el bordado es de entre 7 a 10 días hábiles, dependiendo del volumen del pedido. El plazo comienza a contar a partir del pago del anticipo y la aprobación del diseño.</li><br>
                                    
                                    <li><strong>Digitalización del bordado:</strong> Si el logo o diseño solicitado no está en nuestras bases de datos, el cliente debe proporcionar una imagen del logo/diseño a bordar en formato JPG/PNG para realizar el proceso de digitalización en nuestro sistema.</li><br>

                                    <li><strong>Prueba física del logo:</strong> Cabe destacar que si el logo nunca se había bordado antes, posterior a su digitalización, se realiza una prueba del logo en un pedazo de tela para presentar dicha prueba al cliente, nunca se trabajan pruebas sobre las prendas del cliente, cuando el logo ya ha sido presentado al cliente y aprobado por el mismo, se procede a realizarle su servicio de bordado.</li><br>

                                    <li><strong>Responsabilidad Limitada:</strong> En prendas proporcionadas por el cliente, no se garantiza la recuperación en caso de fallas mecánicas o errores imprevistos durante el proceso. El cliente asume este riesgo al aceptar el servicio.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Términos y Condiciones de Garantía
                            </button>
                        </h3>
                        <div class="accordion-collapse collapse" id="collapseThree" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                            <div class="accordion-body text-justify-custom">
                                <ul>
                                    <li><strong>Prendas Confeccionadas por Nosotros:</strong> Todas las prendas fabricadas por Manufacturas R.J. Atlántico cuentan con garantía contra defectos de confección.</li><br>

                                    <li><strong>Defectos de Costura:</strong> Si alguna prenda se deshila o descoce por error de fábrica, garantizamos el cambio completo de la prenda sin costo adicional.</li><br>

                                    <li><strong>Tallas Incorrectas:</strong> En caso de que la talla entregada no coincida con la solicitada, realizamos el cambio de talla sin cargo, siempre que la prenda esté en condiciones originales y sin uso.</li><br>

                                    <li><strong>Calidad de la Tela:</strong> Si la tela presenta defectos (roturas, desgarros o desgaste prematuro) garantizamos el reemplazo de la prenda defectuosa.</li><br>

                                    <li><strong>Repuestos y Reparaciones Menores:</strong> Botones, cierres y otros accesorios que se caigan o dañen estarán cubiertos por la garantía. Los repararemos o reemplazaremos sin costo adicional.</li><br>

                                    <li><strong>Arreglos y Ajustes de Talla:</strong> Ofrecemos ajustes de mangas, reducción de talle o ceñido de costados sin costo adicional, siempre que la prenda sea originalmente confeccionada por nosotros. <em>Nota:</em> No es posible aumentar talla más allá de las dimensiones originales de la prenda.</li><br>

                                    <li><strong>Prendas Externas Bordadas:</strong> Para prendas aportadas por el cliente, la garantía cubre únicamente defectos del bordado (diseño, punto o hilo). No cubre defectos previos de la tela o costuras originales de la prenda.</li><br>

                                    <li><strong>Exclusiones:</strong> No cubre daños por uso inadecuado, lavado incorrecto, desgaste normal por uso prolongado o alteraciones realizadas externamente.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light mt-md-5">
                    <div class="card-body p-4 py-lg-5">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="h6 fw-bolder">¿Tienes más preguntas?</div>
                                <p class="text-muted mb-4">
                                    Contáctanos a
                                    <br />
                                    <a href="mailto:rjatlantico@gmail.com">rjatlantico@gmail.com</a>
                                </p>
                                <div class="h6 fw-bolder">Síguenos en Instagram @uniformes_rjatlantico</div>
                                <a class="fs-5 px-2 link-dark" href="https://www.instagram.com/uniformes_rjatlantico/" target="_blank"><i class="bi-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 