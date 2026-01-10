@extends('layouts.public')

@section('title', 'Contáctanos')

@section('content')
<!-- Page content-->
<section class="py-5">
    <div class="container px-5">
        <!-- Contact form-->
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <h1 class="fw-bolder">Contáctanos:</h1>
            </div>
            <!-- Contact cards-->
            <div class="row gx-5 row-cols-2 row-cols-lg-4 py-5">
                <div class="col">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-whatsapp"></i></div>
                    <div class="h5 mb-2">Habla con nosotros</div>
                    <p class="text-muted mb-0 text-justify-custom">Escríbenos al WhatsApp de nuestro representante de ventas Sr. Jose Luis Rodriguez - 0412-5358598</p>
                </div>
                <div class="col">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-instagram"></i></div>
                    <div class="h5">En Instagram</div>
                    <p class="text-muted mb-0 text-justify-custom">Explora nuestros Posts en donde podrás encontrar nuestros productos más destacados!</p>
                </div>
                <div class="col">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-telephone"></i></div>
                    <div class="h5">Haznos una llamada</div>
                    <p class="text-muted mb-0 text-justify-custom">Llámanos a nuestro teléfono fijo de 8 AM A 5 PM - 0255-6640625</p>
                </div>
                <div class="col">
                    <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-envelope"></i></div>
                    <div class="h5">Contáctanos a nuestro correo electrónico:</div>
                    <p class="text-muted mb-0 text-justify-custom text-break">rjatlantico@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5" id="ubicacion">
    <div class="container px-5">
        <!-- Ubication form-->
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <h1 class="fw-bolder">Nuestra Ubicación:</h1>
            </div>
            <img src="{{ asset('img/google-map-ubicacion.jpg') }}" class="img-fluid rounded d-block mx-auto" alt="Mapa de Ubicación">
        </div>
    </div>
</section>
@endsection 