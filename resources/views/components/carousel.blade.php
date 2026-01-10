<!-- Header-->
<header class="carousel-header">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="7000">
                <div class="carousel-image" style="background-image: url('{{ asset('img/mujer-trabajando-en-una-maquina-de-coser-con-tela-azul.jpg') }}');"></div>
                <div class="carousel-content">
                    <div class="carousel-caption">
                        <h1 class="display-5 fw-bolder">Gestión Integral de Pedidos</h1>
                        <p class="lead">El sistema cubrirá el ciclo completo de gestión de pedidos, desde su registro inicial hasta su entrega final.</p>
                        <div class="button-group">
                            <a class="btn btn-primary btn-lg" href="#features">Empecemos</a>
                            <a class="btn btn-outline-light btn-lg" href="#blog">Leer más</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="7000">
                <div class="carousel-image" style="background-image: url('{{ asset('img/maquina-textil-industrial-980.jpg') }}');"></div>
                <div class="carousel-content">
                    <div class="carousel-caption">
                        <h2 class="display-5 fw-bolder">Servicio de Bordado Profesional</h2>
                        <p class="lead">Calidad y precisión en cada puntada para realzar tu marca.</p>
                    </div>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="7000">
                <div class="carousel-image" style="background-image: url('{{ asset('img/grupo-pai.jpg') }}');"></div>
                <div class="carousel-content">
                    <div class="carousel-caption">
                        <h2 class="display-5 fw-bolder">Explora nuestro Catálogo General de Productos</h2>
                        <p class="lead">Descubre nuestras soluciones textiles para empresas e instituciones.</p>
                        <div class="button-group">
                            <a class="btn btn-primary btn-lg" href="{{ url('portfolio') }}">Ver Catálogo</a>
                            <a class="btn btn-outline-light btn-lg" href="{{ url('contact') }}">Contáctanos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</header>

<style>
.carousel-header {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.carousel-item {
    height: 100vh;
    min-height: 600px;
    position: relative;
}

.carousel-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    filter: brightness(0.5);
}

.carousel-content {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    z-index: 2;
}

.carousel-caption {
    background: transparent;
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.carousel-caption h1,
.carousel-caption h2 {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.carousel-caption p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.button-group {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.carousel-caption .btn {
    padding: 0.8rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.carousel-caption .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.carousel-indicators {
    margin-bottom: 3rem;
    z-index: 3;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    z-index: 3;
}

@media (max-width: 768px) {
    .carousel-item {
        height: 70vh;
        min-height: 400px;
    }

    .carousel-caption h1,
    .carousel-caption h2 {
        font-size: 2rem;
    }

    .carousel-caption p {
        font-size: 1rem;
    }

    .button-group {
        flex-direction: column;
        gap: 0.5rem;
    }

    .carousel-caption .btn {
        padding: 0.6rem 1.5rem;
    }
}
</style>