@extends('layouts.public')

@section('title', 'Catálogo General')

@section('content')
<style>
    .portfolio-filter button {
        background-color: #e9ecef;
        color: #495057;
        border: none;
        padding: 10px 20px;
        margin: 5px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .portfolio-filter button.active, .portfolio-filter button:hover {
        background-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .portfolio-item {
        opacity: 1;
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .portfolio-item.hidden {
        opacity: 0;
        height: 0;
        overflow: hidden;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        transform: scale(0.9);
    }

    .portfolio-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .portfolio-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }

    .portfolio-card img {
        width: 100%;
        height: 250px; /* Altura fija para todas las imágenes */
        object-fit: cover; /* Asegura que la imagen cubra el área sin distorsionarse */
        border-bottom: 1px solid #e9ecef;
    }

    .portfolio-card .card-body {
        padding: 20px;
    }

    .portfolio-card .card-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 15px;
    }

    .portfolio-card .card-text {
        color: #6c757d;
        font-size: 0.95rem;
        margin-bottom: 20px;
    }
</style>

<!-- Page Content-->
<section class="py-5">
    <div class="container px-5 my-5">
        <div class="text-center mb-5">
            <h1 class="fw-bolder display-4">Nuestro trabajo</h1>
            <p class="lead fw-normal text-muted mb-4">¡Los productos más solicitados!</p>
            <!-- Filter buttons -->
            <div class="portfolio-filter mt-4 d-flex justify-content-center flex-wrap">
                <button class="btn active" data-filter="all">Todos</button>
                <button class="btn" data-filter="camisas">Camisas</button>
                <button class="btn" data-filter="chemises">Chemises</button>
                <button class="btn" data-filter="franelas">Franelas</button>
                <button class="btn" data-filter="bordados">Bordados</button>
                <button class="btn" data-filter="pantalones">Pantalones</button>
                <button class="btn" data-filter="gorras">Gorras</button>
            </div>
        </div>

        <div class="row gx-5" id="portfolio-grid">
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="camisas">
                <div class="portfolio-card">
                    <img src="{{ asset('img/camisa-corte-columbia.jpg') }}" alt="Camisa Corte Columbia" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Camisa Corte Columbia</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="camisas">
                <div class="portfolio-card">
                    <img src="{{ asset('img/camisa-clasico-verde.jpg') }}" alt="Camisa Corte Clásico Unicolor" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Camisa Corte Clásico Unicolor</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="chemises">
                <div class="portfolio-card">
                    <img src="{{ asset('img/chemise-silo-amazo.jpg') }}" alt="Chemise con bordados personalizados" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Chemise con bordados personalizados</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="bordados">
                <div class="portfolio-card">
                    <img src="{{ asset('img/bordado-calidad.jpg') }}" alt="Alta Calidad en Bordados" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Alta Calidad en Bordados</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="franelas">
                <div class="portfolio-card">
                    <img src="{{ asset('img/Franela-cuello-redondo.jpg') }}" alt="Franelas Cuello Redondo bordadas" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Franelas Cuello Redondo bordadas</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="camisas">
                <div class="portfolio-card">
                    <img src="{{ asset('img/camisa-corte-clasico-thecarmen.jpg') }}" alt="Camisa Corte Clásico con Bordados" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Camisa Corte Clásico con Bordados</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="gorras">
                <div class="portfolio-card">
                    <img src="{{ asset('img/gorra-ormary.jpg') }}" alt="Gorra bordada" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Gorra bordada</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-5 portfolio-item" data-category="pantalones">
                <div class="portfolio-card">
                    <img src="{{ asset('img/pantalon.webp') }}" alt="Pantalón Triple Costura" />
                    <div class="card-body text-center">
                        <h3 class="card-title">Pantalón Triple Costura</h3>
                        <a href="#" class="btn btn-primary mt-auto">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.portfolio-filter .btn');
        const portfolioItems = document.querySelectorAll('.portfolio-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and add to the clicked one
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                portfolioItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });
        });
    });
</script>
@endsection 