@extends('layouts.public')

@section('title', 'Sobre Nosotros')

@section('content')
<style>
    .custom-card {
        background-color: #f8f9fa;
        transition: transform 0.3s ease;
    }
    
    .custom-card:hover {
        transform: translateY(-5px);
    }
    
    .bg-light .custom-card {
        background-color: #ffffff;
    }
    
    .custom-card.primary-card {
        background: linear-gradient(145deg, #e9ecef 0%, #f8f9fa 100%);
        border-left: 4px solid #0d6efd;
    }
    
    .custom-card.secondary-card {
        background: linear-gradient(145deg, #f1f3f5 0%, #ffffff 100%);
        border-left: 4px solid #6c757d;
    }

    .team-member-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .team-member-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .team-member-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        margin: 0 auto 20px;
        border: 5px solid #f8f9fa;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .team-member-name {
        color: #212529;
        font-size: 1.25rem;
        margin-bottom: 5px;
    }

    .team-member-position {
        color: #6c757d;
        font-style: italic;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
</style>

<!-- Header-->
<header class="py-5">
    <div class="container px-5 my-5">
        <div class="card shadow border-0 rounded-4 custom-card primary-card">
            <div class="card-body p-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6 order-first order-lg-last">
                        <img class="img-fluid rounded mb-5 mb-lg-0" src="{{ asset('img/cartel-atlantico.jpg') }}" alt="..." />
                    </div>
                    <div class="col-lg-6">
                        <h1 class="display-4 fw-bolder mb-4 text-center">Reseña histórica</h1>
                        <div class="lead fw-normal text-muted mb-4 text-justify-custom">
                            <p class="mb-4">
                                Manufacturas RJ Atlántico C.A. fue adquirida el 12 de marzo de 2014 por Gregorio José Rodríguez Malvacia y Thais Mabel Jara de Rodríguez. La empresa nació como una evolución del trabajo por cuenta propia de sus fundadores y se dedica a la confección de prendas textiles (camisas, chemises, franelas) y bordados personalizados.
                            </p>
                            <p class="mb-0">
                                Desde su creación, se ha convertido en un referente de calidad textil en las ciudades de Acarigua - Araure, ofreciendo sus servicios a algunas de las empresas agricolas mas grandes del estado Porguesa, negocios locales y colegios de la zona. La misma a evolucionado hacia un entorno productivo con vocación textil, Actualmente, la empresa y su comunidad buscan fortalecer sus capacidades.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- About section one-->
<section class="py-5 bg-light" id="scroll-target">
    <div class="container px-5 my-5">
        <div class="card shadow border-0 rounded-4 custom-card secondary-card">
            <div class="card-body p-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6">
                        <img class="img-fluid rounded mb-5 mb-lg-0" src="{{ asset('img/coser-tela.jpg') }}" alt="..." />
                    </div>
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bolder mb-4 text-center">Nuestra Misión</h2>
                        <div class="lead fw-normal text-muted text-justify-custom">
                            <p class="mb-0">
                                Nuestra misión es ofrecer soluciones textiles de alta calidad que respondan a las necesidades y expectativas de nuestros clientes, tanto del ámbito corporativo como individual. Nos comprometemos con la excelencia en cada etapa del proceso productivo, desde la selección de materiales hasta el acabado final, garantizando productos duraderos, funcionales y estéticamente atractivos. Además, trabajamos bajo principios de responsabilidad, honestidad y compromiso, fomentando el desarrollo de nuestro capital humano, y aportando positivamente al entorno social y productivo del sector textil.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About section two-->
<section class="py-5">
    <div class="container px-5 my-5">
        <div class="card shadow border-0 rounded-4 custom-card primary-card">
            <div class="card-body p-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6 order-first order-lg-last">
                        <img class="img-fluid rounded mb-5 mb-lg-0" src="{{ asset('img/bordadora.jpg') }}" alt="..." />
                    </div>
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bolder mb-4 text-center">Nuestra Visión</h2>
                        <div class="lead fw-normal text-muted text-justify-custom">
                            <p class="mb-4">
                                Aspiramos a consolidarnos como una empresa textil referente en el estado Portuguesa y expandir nuestra presencia en la localidad, siendo reconocidos por la calidad superior de nuestros productos, la confiabilidad de nuestros servicios y nuestra constante capacidad de innovación.
                            </p>
                            <p class="mb-0">
                                En Manufacturas RJ Atlántico visualizamos un futuro en el que nuestros procesos estén en constante evolución, integrando tecnología, sostenibilidad y diseño como pilares fundamentales.
                                Queremos ser un modelo de empresa comprometida con la mejora continua, la satisfacción del cliente y el bienestar de nuestros trabajadores, promoviendo un ambiente laboral digno y una cultura organizacional basada en la colaboración y el respeto.
                                Nuestro objetivo es seguir creciendo de manera sostenible, construyendo alianzas sólidas con nuestros clientes y posicionándonos como un proveedor estratégico dentro de la industria textil nacional.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About section three-->
<section class="py-5 bg-light" id="scroll-target">
    <div class="container px-5 my-5">
        <div class="card shadow border-0 rounded-4 custom-card secondary-card">
            <div class="card-body p-5">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6">
                        <img class="img-fluid rounded mb-5 mb-lg-0" src="{{ asset('img/valores-600x400.jpg') }}" alt="..." />
                    </div>
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bolder mb-4 text-center">Nuestros Valores</h2>
                        <div class="lead fw-normal text-muted text-justify-custom">
                            <p class="mb-0">
                                En Manufacturas R.J. Atlántico trabajamos con el firme compromiso de brindar una experiencia que garantice la satisfacción de nuestros clientes, actuando siempre con responsabilidad en cada entrega y cumpliendo con puntualidad los plazos establecidos. Nos caracteriza la adaptabilidad para atender diferentes necesidades, desde bordados personalizados hasta confección de uniformes escolares y empresariales. Valoramos la colaboración como motor de nuestro equipo y promovemos un ambiente de trabajo armónico y comprometido. Todo lo que hacemos lo llevamos a cabo con autenticidad, cuidando cada detalle para ofrecer productos originales, funcionales y hechos con pasión.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team members section-->
<section class="py-5 bg-light">
    <div class="container px-5 my-5">
        <div class="card shadow border-0 rounded-4 custom-card primary-card">
            <div class="card-body p-5">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bolder mb-3">Nuestro Equipo</h2>
                    <p class="lead fw-normal text-muted mb-5">Dedicados a garantizar la calidad de nuestros productos y servicios</p>
                </div>
                <div class="row g-4">
                    <!-- Dirección -->
                    <div class="col-12 mb-4">
                        <h3 class="text-center fw-bold mb-4 text-primary">Dirección</h3>
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/gregorio.jpg') }}" alt="Gregoorio Rodriguez" />
                                    <h5 class="team-member-name">Gregorio Rodríguez</h5>
                                    <div class="team-member-position">Presidente</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/thais-jara.jpg') }}" alt="Thais Jara" />
                                    <h5 class="team-member-name">Thais Jara</h5>
                                    <div class="team-member-position">Vicepresidente</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas y Diseño -->
                    <div class="col-12 mb-4">
                        <h3 class="text-center fw-bold mb-4 text-primary">Ventas y Diseño</h3>
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/jose-rodriguez.jpg') }}" alt="Jose Luis Rodríguez" />
                                    <h5 class="team-member-name">Jose Luis Rodríguez</h5>
                                    <div class="team-member-position">Representante de ventas</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/emmanuel.jpg') }}" alt="Emmanuel Arroyo" />
                                    <h5 class="team-member-name">Emmanuel Arroyo</h5>
                                    <div class="team-member-position">Diseñador gráfico encargado del departamento de bordado - vendedor</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Producción -->
                    <div class="col-12">
                        <h3 class="text-center fw-bold mb-4 text-primary">Producción</h3>
                        <div class="row justify-content-center">
                            <div class="col-md-3">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/kleiver.jpg') }}" alt="Kleiver Colmenarez" />
                                    <h5 class="team-member-name">Kleiver Colmenarez</h5>
                                    <div class="team-member-position">Operador de máquina de bordado</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/yaneth-de-santis.jpg') }}" alt="Yaneth de Santis" />
                                    <h5 class="team-member-name">Yaneth de Santis</h5>
                                    <div class="team-member-position">Costurera</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/yaneth-mendoza.jpg') }}" alt="Yaneth Mendoza" />
                                    <h5 class="team-member-name">Yaneth Mendoza</h5>
                                    <div class="team-member-position">Costurera</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="team-member-card text-center">
                                    <img class="team-member-image" src="{{ asset('img/enzo.jpg') }}" alt="Enzo Rodriguez" />
                                    <h5 class="team-member-name">Enzo Rodriguez</h5>
                                    <div class="team-member-position">Supervisor</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 