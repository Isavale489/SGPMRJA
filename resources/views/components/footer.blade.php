<!-- Footer-->
<footer class="bg-dark text-center text-muted py-4">
    <div class="container">
        <!-- Logo -->
        <div class="mb-3 text-center d-flex justify-content-center">
            <img src="{{ asset('img/atlantico-logo.png') }}" alt="Logo de la empresa" style="height: 50px;">
        </div>

        <!-- Iconos de redes sociales -->
        <div class="mb-3">
            <a href="https://facebook.com/tu-negocio" class="social-icon facebook" target="_blank"><i class="fab fa-facebook-f fa-lg"></i></a>
            <a href="https://www.instagram.com/uniformes_rjatlantico/" class="social-icon instagram" target="_blank"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="https://wa.me/584245387609" class="social-icon whatsapp" target="_blank"><i class="fab fa-whatsapp fa-lg"></i></a>
            <a href="https://twitter.com/tu-negocio" class="social-icon twitter" target="_blank"><i class="fab fa-twitter fa-lg"></i></a>
        </div>

        <!-- Texto descriptivo -->
        <p>Descubre qué está sucediendo en tu <a href="#" class="text-primary">tienda favorita</a></p>

        <!-- Enlaces -->
        <div class="small mb-3">
            <a href="{{ url('contact') }}" class="text-primary mx-2">Contáctanos</a> |
            <a href="{{ url('faq') }}" class="text-primary mx-2">Términos y condiciones</a> |
            Puedes contactarnos a
            <a href="mailto:rjatlantico@gmail.com" class="text-primary">rjatlantico@gmail.com</a>
        </div>

        <!-- Derechos -->
        <div class="small">© Manufacturas R.J. Atlántico, {{ date('Y') }}</div>
    </div>
</footer> 