<!doctype html>
<html lang="en" data-layout="vertical" data-layout-style="detached" data-sidebar="light" data-topbar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Manufacturas R.J Atlantico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- jsvectormap css -->
    <link href="{{ asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DataTables css se carga en cada vista individual -->

    <!-- Layout config Js -->
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Estilo para campos obligatorios -->
    <style>
        .form-label.required::after,
        label.required::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }

        .required-note {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .required-note span {
            color: #dc3545;
            font-weight: bold;
        }
    </style>

    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('admin.layouts.header')
        @include('admin.layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            @include('admin.layouts.footer')
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/js/plugins.js') }}"></script>
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- datatables se cargan en cada vista individual -->

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!--Swiper slider js-->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        const lenguajeData = {
            emptyTable: "No hay datos disponibles",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron coincidencias",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            aria: {
                sortAscending: ": activar para ordenar la columna ascendente",
                sortDescending: ": activar para ordenar la columna descendente"
            }
        }
    </script>

    <!-- Script global para validación de campos -->
    <script>
        $(document).ready(function() {
            // Lista de selectores de campos que solo deben aceptar números
            const camposNumericos = [
                '#documento-number-field',       // Documento cliente
                '#documento-identidad-field',    // Documento empleado
                '#rif-field',                    // RIF proveedor
                '#ci-rif-number-field',          // CI/RIF cotización/pedido
                '#documento-number-field-cliente', // Documento en modal cliente
                'input[name="stock_actual"]',
                'input[name="stock_minimo"]',
                'input[name="cantidad"]'
            ];
            
            // Aplicar restricción a campos numéricos
            camposNumericos.forEach(function(selector) {
                $(document).on('input', selector, function() {
                    // Permitir solo números
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });
            
            // Campos de teléfono - formato especial (números y guión)
            const camposTelefono = [
                '#telefono-field',
                '#telefono-contacto-field',
                '#cliente-telefono-field',       // Teléfono en cotización/pedido
                '#telefono-field-cliente',       // Teléfono en modal cliente
                'input[name="telefono"]',
                'input[name="telefono_contacto"]',
                'input[name="cliente_telefono"]'
            ];
            
            camposTelefono.forEach(function(selector) {
                $(document).on('input', selector, function() {
                    // Permitir solo números y guión
                    this.value = this.value.replace(/[^0-9\-]/g, '');
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>